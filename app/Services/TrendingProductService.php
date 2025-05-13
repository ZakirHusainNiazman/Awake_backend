<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Seller\Product\Product;

class TrendingProductService
{
  public function getTrendingProducts(int $limit = 10)
{
    $since = Carbon::now()->subDays(7);

    $productScores = DB::table('product_stats')
        ->select('product_id', DB::raw("
            SUM(CASE
                WHEN event = 'view' THEN 1
                WHEN event = 'wishlist' THEN 1
                WHEN event = 'cart' THEN 2
                WHEN event = 'purchase' THEN 5
                ELSE 0
            END) as score
        "))
        ->where('created_at', '>=', $since)
        ->groupBy('product_id')
        ->orderByDesc('score')
        ->limit($limit)
        ->get();

    $productIds = $productScores->pluck('product_id')->toArray();

    if (empty($productIds)) {
        return collect();
    }

    // Add quotes around UUIDs for SQL
    $quotedIds = array_map(function($id) {
        return "'" . $id . "'";
    }, $productIds);

    return Product::whereIn('id', $productIds)
                ->orderByRaw("FIELD(id, " . implode(',', $quotedIds) . ")")
                ->get();
}

}
