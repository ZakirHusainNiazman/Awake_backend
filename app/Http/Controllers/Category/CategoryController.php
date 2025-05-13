<?php

namespace App\Http\Controllers\Category;

use Log;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Category\Category;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\CategoryRequest;
use App\Http\Resources\Category\CategoryResource;
use App\Http\Requests\Category\UpdateCategoryRequest;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::all();

        return CategoryResource::collection($categories);
    }

   public function categoriesWithChildren()
    {
        $categories = Category::with('children')
            ->whereNull('parent_id')
            ->get();

        return CategoryResource::collection($categories);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        $file = $request->file('category_icon');

        // Generate unique filename
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();

        // Move file to public/images/country_images
        $file->move(public_path('images/category_images'), $filename);

        // Store the relative path
        $imagePath = 'images/category_images/' . $filename;

        // Get English name to generate slug
        $englishName = $request->input('translations.en.name');

        // Generate initial slug
        $slug = Str::slug($englishName);

        // Validate and create a new category
        $category = Category::create([
            'name'=>$request->name,
            'slug' => $slug,
            "category_icon"=>$imagePath,
            "commission_rate"=>$request->commission_rate,
            "parent_id"=>$request->parent_id ?? null, // key and handle null
        ]);

        $category->refresh();

        return  response()->json([
            'type'=>"success",
            'message'=>"Category Created successfully",
            'data'=>CategoryResource::make($category)
        ],201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $slug)
    {
        // Fetch category by slug with all translations
       $category = Category::where('slug', $slug)->first();


        if(!$category){
            return response()->json([
                "status"=>"error",
                "message"=>"The category does not exists",
            ],404);
        }

        return response()->json([
            'status'=>'success',
            "data"=>CategoryResource::make($category),
        ],200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, string $slug)
    {
        // Fetch category by slug
        $category = Category::where('slug', $slug)->first();

        //ensures category exists
        if(!$category){
            return response()->json([
                "status"=>"error",
                "message"=>"The category does not exists",
            ],404);
        }

        $name = $request->input('name');

        // Generate initial slug
        $slug = Str::slug($name);

        // Prepare update data
        $updateData = [
            'name'=>$request->name,
            'slug' => $slug,
            'is_active'=>$request->is_active,
            "commission_rate"=>$request->commission_rate,
            "parent_id"=>$request->parent_id ?? null, // key and handle null
        ];

        if($request->hasFile('category_icon')){

            if ($category->category_icon && file_exists(public_path($category->category_icon))) {
                unlink(public_path($category->category_icon));
            }

            $file = $request->file('category_icon');
            // Generate unique filename
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();

            // Move file to public/images/country_images
            $file->move(public_path('images/category_images'), $filename);

            // Store the relative path
            $imagePath = 'images/category_images/' . $filename;

            // Add category_icon to update data
            $updateData['category_icon'] = $imagePath;
        }

        // Update the category data
        $category->update($updateData);

        $category->refresh();

        return  response()->json([
            'type'=>"success",
            'message'=>"Category Updated successfully",
            'data'=>CategoryResource::make($category)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $slug)
    {
        $category = Category::where('slug', $slug)->first();

        if(!$category){
            return response()->json([
                "status"=>"error",
                "message"=>"The category does not exist",
            ],404);
        }

        if ($category->category_icon && file_exists(public_path($category->category_icon))) {
            unlink(public_path($category->category_icon));
        }


        // Soft delete the category
        $category->delete();

        return response()->json(['status'=>'success','message' => 'Category deleted successfully']);
    }

    public function trending(Request $request)
    {
        $trendingCategoryStats = DB::table('product_stats')
            ->join('products', 'product_stats.product_id', '=', 'products.id')
            ->select('products.category_id', DB::raw('
                SUM(CASE WHEN event = "view" THEN 1
                        WHEN event = "wishlist" THEN 3
                        WHEN event = "cart" THEN 5
                        WHEN event = "purchase" THEN 10
                    END) as trend_score
            '))
            ->groupBy('products.category_id')
            ->orderByDesc('trend_score')
            ->limit(10)
            ->get();

        // Extract brand_ids
        $categoryIds = $trendingCategoryStats->pluck('category_id')->toArray();

        // Get brands in the same order as trend_score (preserve order)
        $brands = Category::whereIn('id', $categoryIds)
            ->get()
            ->sortBy(function ($category) use ($categoryIds) {
                return array_search($category->id, $categoryIds);
            })
            ->values(); // Reset keys

        return CategoryResource::collection($brands);
    }
}
