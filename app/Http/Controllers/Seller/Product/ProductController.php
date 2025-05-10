<?php

namespace App\Http\Controllers\Seller\Product;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Models\Seller\Product\Product;
use Illuminate\Support\Facades\Storage;
use App\Models\Seller\Product\ProductImage;
use App\Models\Seller\Product\ProductOption;
use App\Models\Seller\Product\ProductVariant;
use App\Models\Seller\Product\ProductOptionValue;
use App\Http\Resources\Seller\Product\ProductResource;
use App\Models\Seller\Product\ProductVariantOptionValue;
use App\Http\Requests\Seller\Product\StoreProductRequest;
use App\Http\Resources\Seller\Product\UpdateProductResource;

class ProductController extends Controller
{
    // it will return all products for the authenticated seller
    public function index()
        {
                        $products = Product::with([
                            'category',
                        'variants.optionValues.option', // Eager load 'option' relationship for each 'optionValue' of 'variant'
                        'variants.optionValues',         // Eager load 'optionValues' for each variant
                        'options.values',                // Eager load option values for the options
                    ])->get();


            // Structure the data for easy identification by user
            $productsWithVariants = $products->map(function ($product) {
                // Check if the product has a discount, and set discount-related fields accordingly
                $product->load('category');

                return $product;
            });

            return ProductResource::collection($productsWithVariants);
        }

    // it will show a single product by ID
    public function show($id)
    {
        $product = Product::with([
            'category',
            'variants.optionValues.option', // Load option for each optionValue of the variant
            'variants.optionValues',        // Load optionValues for each variant
            'options.values',               // Load values for each product option
        ])->findOrFail($id); // Find the product by ID or fail with 404

        return ProductResource::make($product);
    }


    public function store(StoreProductRequest $request)
    {
        DB::beginTransaction();

        try {

            $user = Auth::user();

            // 2) Create the Product
            $product = Product::create([
                'id'           => Str::uuid(),
                'category_id'  => $request->category_id,
                "seller_id"    => $user->seller->id,
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
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
                    $dir = public_path("images/products/{$product->id}");
                    File::ensureDirectoryExists($dir, 0755, true);
                    $file->move($dir, $filename);
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_url'  => "/images/products/{$product->id}/{$filename}",
                    ]);
                }
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

                            $extension = $swatchFile->getClientOriginalExtension() ?: ($swatchFile->guessExtension() ?? 'bin');
                            $fname = Str::uuid().'.'.$extension;
                            $dir = public_path("images/products/{$product->id}/swatches");

                            File::ensureDirectoryExists($dir, 0755, true);
                            $swatchFile->move($dir, $fname);

                            $opt->values()->create([
                                'value'      => $label,
                                'image_path' => "/images/products/{$product->id}/swatches/{$fname}",
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
                        $fn = Str::uuid().'.'.$vf->getClientOriginalExtension();
                        $d = public_path("images/products/{$product->id}/variants");
                        File::ensureDirectoryExists($d, 0755, true);
                        $vf->move($d, $fn);
                        $imgUrl = "/images/products/{$product->id}/variants/{$fn}";
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





    public function update($id, StoreProductRequest $request)
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
                "seller_id"    => $user->seller->id,
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

            //product images handlers

            // 1. Delete ALL existing images
            $product->images()->each(function ($image) {
                Storage::delete($image->path);  // Delete physical file
                $image->delete();              // Delete database record
            });


            // Update product images
            if ($request->hasFile('images')) {
                // Delete old images (optional)
                foreach ($product->images as $oldImage) {
                    File::delete(public_path($oldImage->image_url));
                    $oldImage->delete();
                }

                // Upload and save new images
                foreach ($request->file('images') as $imageFile) {
                    $filename = Str::uuid() . '.' . $imageFile->getClientOriginalExtension();
                    $destinationPath = public_path('images/products/' . $product->id);

                    if (!File::exists($destinationPath)) {
                        File::makeDirectory($destinationPath, 0755, true);
                    }

                    $imageFile->move($destinationPath, $filename);

                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_url'  => '/images/products/' . $product->id . '/' . $filename,
                    ]);
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








///product other methods like trending products and new arrivels


    public function newArrivals()
    {
        $products = Product::with(['category', 'variants.optionValues.option', 'variants.optionValues', 'options.values'])
            ->newArrivals()
            ->get();

        return ProductResource::collection($products);
    }

}





// public function store(StoreProductRequest $request)
// {
//     DB::beginTransaction();

//     try {
//         // 1) Create the Product
//         $product = Product::create([
//             'id'               => Str::uuid(),
//             'category_id'      => $request->category_id,
//             'sku'              => $request->sku,
//             'title'            => $request->title,
//             'details'          => $request->details,
//             'stock'            => $request->stock,
//             'description'      => $request->description,
//             'base_price'       => $request->base_price,
//             'has_variants'     => $request->has_variants,
//             'attributes'       => $request->input('attributes', []),
//             'has_discount'     => $request->has_discount,
//             'discount_amount'  => $request->discount_amount,
//             'discount_start'   => $request->discount_start,
//             'discount_end'     => $request->discount_end,
//         ]);

//         // 2) Upload & save product images
//         if ($request->hasFile('images')) {
//             foreach ($request->file('images') as $file) {
//                 $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
//                 $dir = public_path("images/products/{$product->id}");
//                 if (!File::exists($dir)) File::makeDirectory($dir,0755,true);
//                 $file->move($dir, $filename);
//                 ProductImage::create([
//                     'product_id' => $product->id,
//                     'image_url'  => "/images/products/{$product->id}/{$filename}",
//                 ]);
//             }
//         }

//         // 3) Create options & values
//         if ($request->has_variants && $opts = $request->input('options', [])) {
//             foreach ($opts as $i => $optData) {
//                 $opt = $product->options()->create([
//                     'name'       => $optData['name'],
//                     'type'       => $optData['type'],
//                     'product_id' => $product->id,
//                 ]);

//                 if ($optData['type'] !== 'images') {
//                     // select/text: simple string values
//                     foreach ($optData['values'] as $val) {
//                         $opt->values()->create(['value' => $val]);
//                     }
//                 } else {
//                     // image swatches: upload each file & save label+path
//                     // In the options handling section of store()
//                     foreach ($optData['imageValues'] as $j => $iv) {
//                         /** @var \Illuminate\Http\UploadedFile $swatchFile */
//                         $swatchFile = $request->file("options.{$i}.imageValues.{$j}.file");
//                         $label = $iv['label'];

//                         // Get file extension safely
//                         $extension = $swatchFile->getClientOriginalExtension();
//                         if (empty($extension)) {
//                             $extension = $swatchFile->guessExtension() ?? 'bin';
//                         }

//                         $fname = Str::uuid().'.'.$extension;
//                         $dir = public_path("images/products/{$product->id}/swatches");

//                         File::ensureDirectoryExists($dir, 0755, true);

//                         try {
//                             $swatchFile->move($dir, $fname);
//                         } catch (\Exception $e) {
//                             Log::error("File move failed: {$e->getMessage()}");
//                             throw new \Exception("Could not save image file: {$e->getMessage()}");
//                         }

//                         $opt->values()->create([
//                             'value' => $label,
//                             'image_path' => "/images/products/{$product->id}/swatches/{$fname}",
//                         ]);
//                     }
//                 }
//             }
//         }

//         // 4) Build variants & pivot data
//         $pivotRows = [];
//         if ($request->has_variants && $vars = $request->input('variants', [])) {
//             foreach ($vars as $k => $vData) {
//                 // handle variant image
//                 $imgUrl = null;
//                 if ($request->file("variants.{$k}.image")) {
//                     $vf = $request->file("variants.{$k}.image");
//                     $fn = Str::uuid().'.'.$vf->getClientOriginalExtension();
//                     $d  = public_path("images/products/{$product->id}/variants");
//                     if (!File::exists($d)) File::makeDirectory($d,0755,true);
//                     $vf->move($d,$fn);
//                     $imgUrl = "/images/products/{$product->id}/variants/{$fn}";
//                 }

//                 // create variant row
//                 $variant = $product->variants()->create([
//                     'sku'        => $vData['sku'],
//                     'price'      => $vData['price'],
//                     'stock'      => $vData['stock'],
//                     'image'      => $imgUrl,
//                     'attributes' => json_encode($vData['attributes']),
//                 ]);

//                 // prepare pivot between this variant and each selected option-value
//                 foreach ($vData['attributes'] as $optName => $valName) {
//                     $ov = ProductOptionValue::where('value',$valName)
//                           ->whereHas('option',fn($q)=>$q->where('name',$optName))
//                           ->first();
//                     if ($ov) {
//                         $pivotRows[] = [
//                             'product_variant_id'      => $variant->id,
//                             'product_option_value_id' => $ov->id,
//                             'created_at'              => now(),
//                             'updated_at'              => now(),
//                         ];
//                     }
//                 }
//             }

//             if (count($pivotRows)) {
//                 ProductVariantOptionValue::insert($pivotRows);
//             }
//         }

//         DB::commit();

//         return response()->json([
//             'message' => 'Product created successfully.',
//             'product' => $product->load('images','options.values','variants'),
//         ], 201);
//     }
//     catch (\Exception $e) {
//         DB::rollBack();
//         Log::error($e);
//         return response()->json([
//             'message' => 'Product creation failed.',
//             'error'   => $e->getMessage(),
//         ], 500);
//     }
// }
