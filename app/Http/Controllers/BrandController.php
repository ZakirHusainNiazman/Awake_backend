<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Seller\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\FeaturedBrandService;
use App\Http\Resources\Seller\BrandResource;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $brands = Brand::all();

        return BrandResource::collection($brands);
    }

    /**
     * return trending bradns.
     */
    public function trending(Request $request)
    {
        $trendingBrandStats = DB::table('product_stats')
            ->join('products', 'product_stats.product_id', '=', 'products.id')
            ->select('products.brand_id', DB::raw('
                SUM(CASE WHEN event = "view" THEN 1
                        WHEN event = "wishlist" THEN 3
                        WHEN event = "cart" THEN 5
                        WHEN event = "purchase" THEN 10
                    END) as trend_score
            '))
            ->groupBy('products.brand_id')
            ->orderByDesc('trend_score')
            ->limit(10)
            ->get();

        // Extract brand_ids
        $brandIds = $trendingBrandStats->pluck('brand_id')->toArray();

        // Get brands in the same order as trend_score (preserve order)
        $brands = Brand::whereIn('id', $brandIds)
            ->get()
            ->sortBy(function ($brand) use ($brandIds) {
                return array_search($brand->id, $brandIds);
            })
            ->values(); // Reset keys

        return BrandResource::collection($brands);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
