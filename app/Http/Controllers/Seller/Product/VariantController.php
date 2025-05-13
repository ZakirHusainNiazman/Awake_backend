<?php

namespace App\Http\Controllers\Seller\Product;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Models\Seller\Product\ProductVariant;

class VariantController extends Controller
{
    public function updateVariantInventory(Request $request)
    {
        $validated = $request->validate([
            'variant_id' => ['required', 'exists:product_variants,id'],
            'product_id' => ['required', 'exists:products,id'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'stock' => ['required', 'integer', 'min:0'],
            'has_discount' => ['required', 'boolean'],
            'discount_amount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_start' => ['nullable', 'date'],
            'discount_end' => ['nullable', 'date', 'after:discount_start'],
            'sku' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('product_variants', 'sku')->ignore($request->variant_id),
            ],
        ]);

        $variant = ProductVariant::findOrFail($validated['variant_id']);

        $variant->update([
            'product_id' => $validated['product_id'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'sku' => $validated['sku'],
            'has_discount' => $validated['has_discount'],
        ]);

        $variant->discount()->update([
            'discount_amount' => $validated['has_discount'] ? $validated['discount_amount'] : null,
            'discount_start' => $validated['has_discount'] ? $validated['discount_start'] : null,
            'discount_end' => $validated['has_discount'] ? $validated['discount_end'] : null,
        ]);


        return response()->json([
            'message' => 'Variant inventory updated successfully.',
            'variant' => $variant,
        ]);
    }
}
