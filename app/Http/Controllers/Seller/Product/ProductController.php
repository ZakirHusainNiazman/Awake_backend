<?php

namespace App\Http\Controllers\Seller\Product;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Category\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\ProductStatService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Models\Seller\Product\Product;
use Illuminate\Support\Facades\Storage;
use App\Services\TrendingProductService;
use App\Models\Seller\Product\ProductImage;
use App\Models\Seller\Product\ProductOption;
use App\Models\Seller\Product\ProductVariant;
use App\Models\Seller\Product\ProductOptionValue;
use App\Http\Resources\Seller\Product\ProductResource;
use App\Models\Seller\Product\ProductVariantOptionValue;
use App\Http\Requests\Seller\Product\StoreProductRequest;
use App\Http\Requests\Seller\Product\UpdateProductRequest;
use App\Http\Resources\Seller\Product\UpdateProductResource;

class ProductController extends Controller
{

    // it will return all products for the authenticated seller
    public function index(Request $request)
    {

        $query = Product::with([
            'category',
            'variants.optionValues.option',
            'variants.optionValues',
            'options.values',
        ])
        ->withMin('variants', 'price'); // gets minimum variant price if exists

        $priceMin = $request->query('price_min');
        $priceMax = $request->query('price_max');

        // ✅ Filter by multiple category slugs
        if ($request->filled('category_slug')) {
            $categorySlugs = (array) $request->query('category_slug');
            $categories = Category::whereIn('slug', $categorySlugs)->pluck('id');
            if ($categories->isNotEmpty()) {
                $query->whereIn('category_id', $categories);
            } else {
                return response()->json(['data' => []]);
            }
        }

        // ✅ Filter by multiple brand slugs
        if ($request->filled('brand_slug')) {
            $brandSlugs = (array) $request->query('brand_slug');
            $brandIds = \App\Models\Seller\Brand::whereIn('slug', $brandSlugs)->pluck('id');

            if ($brandIds->isNotEmpty()) {
                $query->whereIn('brand_id', $brandIds);
            } else {
                return response()->json(['data' => []]);
            }
        }

        $products = $query->get();

        // ✅ Filter by effective price (variant or base + discount)
        $filtered = $products->filter(function ($product) use ($priceMin, $priceMax) {
            $variant = $product->variants->sortBy('price')->first();
            $basePrice = $variant ? $variant->price : $product->base_price;
            $effectivePrice = $product->discount_price ?? $basePrice;

            return (!$priceMin || $effectivePrice >= $priceMin)
                && (!$priceMax || $effectivePrice <= $priceMax);
        });

        return ProductResource::collection($filtered->values());
    }



    // it will show a single product to be edited by ID
    public function show($id,ProductStatService $statService)
    {
        $product = Product::with([
            'category',
            'variants.optionValues.option', // Load option for each optionValue of the variant
            'variants.optionValues',        // Load optionValues for each variant
            'options.values',               // Load values for each product option
        ])->findOrFail($id); // Find the product by ID or fail with 404

        // Create the product stat record and get the instance
        $statService->logEvent($product->id, 'view');

        Log::info("product detail",["product",$product]);

        return updateProductResource::make($product);
    }


    public function store(StoreProductRequest $request)
    {
        // Log::info("requset data ",$request->all());
        DB::beginTransaction();

        try {

            $user = Auth::user();
            $brand = $user->seller->brand;
            $brandId = $brand->id;

            // 2) Create the Product
            $product = Product::create([
                'id'           => Str::uuid(),
                'category_id'  => $request->category_id,
                "brand_id"    => $user->seller->brand->id,
                'sku'          => $request->sku,
                'title'        => $request->title,
                'details'      => $request->details,
                'stock'        => $request->stock,
                'description'  => $request->description,
                'base_price'   => $request->base_price,
                'has_variants' => $request->has_variants,
                'attributes'   => $request->input('attributes', []),
                'has_discount'    => $request->has_discount,
                'brand_id' => $brandId
            ]);

            if (!$request->has_variants && $request->has_discount) {
                $discountStart = Carbon::parse($request->discount_start)->utc();
                $discountEnd = Carbon::parse($request->discount_end)->utc();
                $product->discount()->create([
                    'discount_amount' => $request->discount_amount,
                    'discount_start' => $discountStart,
                    'discount_end' => $discountEnd,
                ]);
            }

            // 3) Upload & save product images
            foreach ($request->file('images') as $file) {
                $path = $this->saveImageFile($file, 'products', $product->id);
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_url'  => $path,
                ]);
            }

            // 4) Create options & values
            if ($request->has_variants && $opts = $request->input('options', [])) {

                foreach ($opts as $i => $optData) {
                    $opt = $product->options()->create([
                        'name'       => $optData['name'],
                        'type'       => $optData['type'],
                        'product_id' => $product->id,
                    ]);

                    if ($optData['type'] !== 'images') {
                        foreach ($optData['values'] as $val) {
                            $opt->values()->create(['value' => $val]);
                        }
                    } else {
                        foreach ($optData['imageValues'] as $j => $iv) {
                            $swatchFile = $request->file("options.{$i}.imageValues.{$j}.file");
                            $label = $iv['label'];
                            $path = $this->saveImageFile($swatchFile, "products/{$product->id}/swatches");
                            Log::info("Swatch file:", ['key' => "options.{$i}.imageValues.{$j}.file", 'file' => $swatchFile]);

                            $opt->values()->create([
                                'value'      => $label,
                                'image_path' => $path,
                            ]);

                        }
                    }
                }
            }

            // 5) Create variants & pivot relationships
            $pivotRows = [];
            if ($request->has_variants && $vars = $request->input('variants', [])) {
                foreach ($vars as $k => $vData) {
                    $imgUrl = null;

                    if ($request->file("variants.{$k}.image")) {
                        $vf = $request->file("variants.{$k}.image");
                        $imgUrl = $this->saveImageFile($vf, "products/{$product->id}/variants");

                    }

                    // Create the variant including its own discount
                    $variant = $product->variants()->create([
                        'sku'             => $vData['sku'],
                        'price'           => $vData['price'],
                        'stock'           => $vData['stock'],
                        'image'           => $imgUrl,
                        'attributes'      => json_encode($vData['attributes']),
                        'has_discount'    => ($vData['has_discount'] === 'true'),
                    ]);

                    if($variant->has_discount){
                        $variant->discount()->create([
                            'discount_amount' => $vData['discount_amount'] ?? null,
                            'discount_start'  => !empty($vData['discount_start']) ? Carbon::parse($vData['discount_start'])->utc() : null,
                            'discount_end'    => !empty($vData['discount_end']) ? Carbon::parse($vData['discount_end'])->utc() : null,
                        ]);
                    }

                    // Link to option values
                    foreach ($vData['attributes'] as $optName => $valName) {
                        $ov = ProductOptionValue::where('value', $valName)
                            ->whereHas('option', fn($q) => $q->where('name', $optName)->where('product_id', $product->id))
                            ->first();

                        if ($ov) {
                            $pivotRows[] = [
                                'product_variant_id'      => $variant->id,
                                'product_option_value_id' => $ov->id,
                                'created_at'              => now(),
                                'updated_at'              => now(),
                            ];
                        }
                    }
                }

                if (!empty($pivotRows)) {
                    ProductVariantOptionValue::insert($pivotRows);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Product created successfully.',
                'product' => $product->load('images', 'options.values', 'variants'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

           Log::error('Product creation failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Product creation failed.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    private function saveImageFile(UploadedFile $file, string $baseFolder, ?string $prefix = null): string
    {
        $datePath = now()->format('Y/m/d');
        $hashSegment = substr(md5($prefix ?? Str::uuid()), 0, 2);
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $dir = public_path("images/{$baseFolder}/{$datePath}/{$hashSegment}");

        Log::info("Swatch file:", ['path' => $dir]);

        File::ensureDirectoryExists($dir, 0755, true);
        $file->move($dir, $filename);

        return "images/{$baseFolder}/{$datePath}/{$hashSegment}/{$filename}";
    }





    public function update($id,UpdateProductRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            $product = Product::find($id);

            if(!$product){
                return response()->json([
                    'status'=>'faild',
                    "message"=>"The proudct id is invalid",
                ]);
            }

            // 2) Create the Product
            $product->update([
                'category_id'  => $request->category_id,
                'brand_id'     => $user->seller->brand->id,
                'sku'          => $request->sku,
                'title'        => $request->title,
                'details'      => $request->details,
                'stock'        => $request->stock,
                'description'  => $request->description,
                'base_price'   => $request->base_price,
                'has_variants' => $request->has_variants,
                'attributes'   => $request->input('attributes', []),
                'has_discount'    => $request->has_discount,
            ]);

            if($request->hasFile('new_images')){
                foreach ($request->file('new_images') as $file){
                    $path = $this->saveImageFile($file, 'products', $product->id);
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_url'  => $path,
                    ]);
                }
            }

            // Pull them as strings or an empty array
            $raw = $request->input('removed_images', []);

            // Cast every entry to ints
            $removedIds = array_map('intval', (array)$raw);

            if (!empty($removedIds)) {
                // Fetch once using the integer IDs
                 $toDelete = ProductImage::whereIn('id', $removedIds)->get();

                 // Log how many we found
                Log::info("Found ". $toDelete->count() ." images to delete", ['ids' => $removedIds]);


                // Delete each image
                foreach ($toDelete as $image) {
                    $filePath = public_path($image->image_url); // Note: using image_url not url

                    if (File::exists($filePath)) {
                        File::delete($filePath); // delete the file from the filesystem
                    }

                    $image->delete(); // delete the DB record
                }
            }

             if (!$request->has_variants) {
                if ($request->has_discount) {
                    $discountData = [
                        'discount_amount' => $request->discount_amount,
                        'discount_start' => Carbon::parse($request->discount_start)->utc(),
                        'discount_end' => Carbon::parse($request->discount_end)->utc(),
                    ];

                    $product->discount()->updateOrCreate([], $discountData);
                } else {
                    $product->discount()->delete();
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Product updated successfully.',
                'product' => $product->load('images', 'variants'),
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Product update failed.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



    // it will show a single product by ID to be shown to user
    public function showProduct($id,ProductStatService $statService)
    {
        $product = Product::with([
            'category',
            'variants.optionValues.option', // Load option for each optionValue of the variant
            'variants.optionValues',        // Load optionValues for each variant
            'options.values',               // Load values for each product option
        ])->findOrFail($id); // Find the product by ID or fail with 404

        // Create the product stat record and get the instance
        $statService->logEvent($product->id, 'view');


        return ProductResource::make($product);
    }






///product other methods like trending products and new arrivels


    public function newArrivals()
    {
        $products = Product::with(['category', 'variants.optionValues.option', 'variants.optionValues', 'options.values'])
            ->newArrivals()
            ->get();

        return ProductResource::collection($products);
    }

    // this will use the service to get trending products based on producat state
    public function trending(TrendingProductService $trending)
    {
        $trendingProducts = $trending->getTrendingProducts();

        return ProductResource::collection($trendingProducts);
    }

}
