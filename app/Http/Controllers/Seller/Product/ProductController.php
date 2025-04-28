<?php

namespace App\Http\Controllers\Seller\Product;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Seller\Product\Product;
use App\Models\Seller\Product\ProductOption;
use App\Models\Seller\Product\ProductVariant;
use App\Models\Seller\Product\ProductOptionValue;
use App\Models\Seller\Product\ProductVariantOptionValue;
use App\Http\Requests\Seller\Product\StoreProductRequest;

class ProductController extends Controller
{
    // it will return all products for the authenticated seller
     public function index()
{
                $products = Product::with([
                'variants.optionValues.option', // Eager load 'option' relationship for each 'optionValue' of 'variant'
                'variants.optionValues',         // Eager load 'optionValues' for each variant
                'options.values',                // Eager load option values for the options
            ])->get();


    // Structure the data for easy identification by user
    $productsWithVariants = $products->map(function ($product) {



        return $product->options()->values;
    });

    return response()->json([
        'products' => $productsWithVariants,
    ], 200);
}

    // it will show a single product by ID
    public function show($id)
    {
        //
    }


    public function store(Request $request)
{
    DB::beginTransaction();
    try {
        // Convert attributes to JSON format if present
        $attributesJson = $request->has('attributes') ? json_encode($request->attributes) : null;

        // Create the product
        $product = Product::create([
            'id' => Str::uuid(),
            'category_id' => $request->category_id,
            'sku' => $request->sku,
            'title' => $request->title,
            'details' => $request->details,
            'stock' => $request->stock,
            'description' => $request->description,
            'base_price' => $request->base_price,
            'has_variants' => $request->has_variants,
            'attributes' => $attributesJson,
            'has_discount' => $request->has_discount,
            'discount_amount' => $request->discount_amount,
            'discount_start' => $request->discount_start,
            'discount_end' => $request->discount_end,
        ]);

        // Create the options for the product
        foreach ($request->options as $optionData) {
            $option = $product->options()->create([
                'name' => $optionData['name'],
                'type' => $optionData['type'],
                'product_id' => $product->id,
            ]);

            // Create the option values for each option
            foreach ($optionData['values'] as $value) {
                $option->values()->create([
                    'value' => $value,
                ]);
            }
        }

        // Initialize an array to collect all option values to link to variants
        $variantOptionValues = [];

        // Create the variants for the product
        foreach ($request->variants as $variantData) {
            $variant = $product->variants()->create([
                'sku' => $variantData['sku'],
                'price' => $variantData['price'],
                'stock' => $variantData['stock'],
                'image' => $variantData['image'],
                'attributes' => json_encode($variantData['attributes']),
            ]);

            // Collect option values for each variant to bulk insert later
            foreach ($variantData['attributes'] as $optionName => $valueName) {
                $optionValue = ProductOptionValue::where('value', $valueName)
                    ->whereHas('option', function ($query) use ($optionName) {
                        $query->where('name', $optionName);
                    })
                    ->first();

                // Add to the bulk insert array
                $variantOptionValues[] = [
                    'product_variant_id' => $variant->id,
                    'product_option_value_id' => $optionValue->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Perform a bulk insert for variant option values (if any)
        if (!empty($variantOptionValues)) {
            ProductVariantOptionValue::insert($variantOptionValues);
        }

        DB::commit();

        return response()->json([
            'message' => 'Product created successfully.',
            'product' => $product,
        ], 201);
    } catch (Exception $e) {
        DB::rollBack();

        return response()->json([
            'message' => 'Product creation failed.',
            'error' => $e->getMessage(),
        ], 500);
    }
}





}



//    // it will return all products for the authenticated seller
//      public function index()
// {
//                 $products = Product::with([
//                 'variants.optionValues.option', // Eager load 'option' relationship for each 'optionValue' of 'variant'
//                 'variants.optionValues',         // Eager load 'optionValues' for each variant
//                 'options.values',                // Eager load option values for the options
//             ])->get();






//     // Structure the data for easy identification by user
//     $productsWithVariants = $products->map(function ($product) {
//         // Check if the product has a discount, and set discount-related fields accordingly
//         if ($product->has_discount) {
//             $product->discount = $product->discount;
//             $product->discount_start = $product->discount_start;
//             $product->discount_end = $product->discount_end;
//         } else {
//             // Set discount-related fields to null if there is no discount
//             $product->discount = null;
//             $product->discount_start = null;
//             $product->discount_end = null;
//         }


//         // Process variants and option values

//                 $product->load(['variants.optionValues.productOptionValue.option']);


//         $product->variants->map(function ($variant) {
//             $variant->option_values = $variant->optionValues->map(function ($optionValue) {
//                 Log::info("option values",$optionValue->toArray());
//             });

//             return $variant;
//         });

//         return $product;
//     });

//     return response()->json([
//         'products' => $productsWithVariants,
//     ], 200);
// }
