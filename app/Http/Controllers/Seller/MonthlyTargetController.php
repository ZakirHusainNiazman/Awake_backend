<?php

namespace App\Http\Controllers\Seller;

use Illuminate\Http\Request;
use App\Models\Seller\SellerOrder;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Seller\MonthlyTarget;
use Illuminate\Support\Facades\Auth;

class MonthlyTargetController extends Controller
{
   public function allTargetsWithRevenue()
    {
        $sellerId = Auth::user()->seller->id;

        $targets = MonthlyTarget::where('seller_id', $sellerId)
            ->orderBy('month', 'desc')
            ->get();

        $targetsWithRevenue = $targets->map(function ($target) {
            $actualRevenue = $this->getRevenueForMonth($target->month);

            return [
                'id' => $target->id,
                'month' => $target->month,
                'revenue_target' => $target->revenue_target,
                'orders_target' => $target->orders_target,
                'actual_revenue' => $actualRevenue,
                'created_at' => $target->created_at,
                'updated_at' => $target->updated_at,
            ];
        });

        return response()->json([
            'message' => 'All monthly targets with revenue fetched successfully.',
            'data' => $targetsWithRevenue,
        ]);
    }


    public function upsert(Request $request)
    {
        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'], // e.g., "2025-05"
            'revenue_target' => ['required', 'numeric', 'min:0'],
            'orders_target' => ['required', 'integer', 'min:0'],
        ]);

        $sellerId = Auth::user()->seller->id;

        $target = MonthlyTarget::updateOrCreate(
            [
                'seller_id' => $sellerId,
                'month' => $validated['month'],
            ],
            [
                'revenue_target' => $validated['revenue_target'],
                'orders_target' => $validated['orders_target'],
            ]
        );

        return response()->json([
            'message' => 'Monthly target saved successfully.',
            'data' => $target
        ], 200);
    }

    /**
     * Get the seller's monthly target for a specific month.
     */
    public function show(string $id)
    {
        $target = MonthlyTarget::where('seller_id', Auth::user()->seller->id)
            ->where('id', $id)
            ->first();

        if (!$target) {
            return response()->json([
                'message' => 'No target set for this month.',
                'data' => null
            ], 200);
        }

        return response()->json([
            'message' => 'Monthly target retrieved successfully.',
            'data' => $target
        ]);
    }


    public function getRevenueForMonth(string $yearMonth)
    {
        $user = auth()->user();
        $sellerId = $user->seller->id;

        // Parse year and month from 'YYYY-MM' format
        [$year, $month] = explode('-', $yearMonth);

        $revenue = DB::table('seller_orders')
            ->where('seller_id', $sellerId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->sum('total');

        return $revenue;
    }
}
