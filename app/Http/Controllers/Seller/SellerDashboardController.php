<?php

namespace App\Http\Controllers\Seller;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Seller\SellerOrder;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Seller\MonthlyTarget;
use Illuminate\Support\Facades\Auth;

class SellerDashboardController extends Controller
{

   public function sellerOrdersCount()
    {
        $seller = Auth::user()->seller;
        $currentMonth = now()->format('Y-m');

        // Get actual orders placed in the current month
        $actualOrders = SellerOrder::where('seller_id', $seller->id)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        // Get target orders for the current month
        $target = MonthlyTarget::where('seller_id', $seller->id)
            ->where('month', $currentMonth)
            ->first();

        $ordersTarget = $target?->orders_target ?? 0;

        $percentage = $ordersTarget > 0
            ? round(($actualOrders / $ordersTarget) * 100, 2)
            : 0;

        return response()->json([
            'month' => $currentMonth,
            'orders_target' => $ordersTarget,
            'actual_orders' => $actualOrders,
            'target_completion_percentage' => $percentage,
        ]);
    }



    public function getMonthlySales(Request $request)
    {
        $user = auth()->user();

        $sales = DB::table('seller_orders')
            ->selectRaw('MONTH(created_at) as month, SUM(total) as total_sales')
            ->where('seller_id', $user->seller->id)
            ->whereYear('created_at', Carbon::now()->year)
            ->groupByRaw('MONTH(created_at)')
            ->pluck('total_sales', 'month');

        $monthlySales = collect(range(1, 12))->mapWithKeys(function ($month) use ($sales) {
            return [Carbon::create()->month($month)->format('M') => $sales->get($month, 0)];
        });

        return response()->json([
            'monthly_sales' => $monthlySales
        ]);
    }


public function getTotalRevenue()
{
    $user = auth()->user(); // current seller
    $sellerId = $user->seller->id;

    $now = Carbon::now(); // current date
    $yearMonth = $now->format('Y-m'); // e.g., "2025-05"
    $todayDate = $now->toDateString();  // e.g., "2025-05-16"

    // Fetch total revenue for the current month
    $monthlyRevenue = DB::table('seller_orders')
        ->where('seller_id', $sellerId)
        ->whereYear('created_at', $now->year)
        ->whereMonth('created_at', $now->month)
        ->sum('total');

    // Today's revenue
    $todayRevenue = DB::table('seller_orders')
        ->where('seller_id', $sellerId)
        ->whereDate('created_at', $todayDate)
        ->sum('total');

    // Fetch the monthly revenue target (if exists), default to 0
    $monthlyTarget = MonthlyTarget::where('seller_id', $sellerId)
        ->where('month', $yearMonth)
        ->first();

    $revenueTarget = $monthlyTarget?->revenue_target ?? 0;

    // Calculate percentage (avoid division by 0)
    $percentage = $revenueTarget > 0
        ? round(($monthlyRevenue / $revenueTarget) * 100, 2)
        : 0;

    return response()->json([
        'month' => $yearMonth,
        'revenue_target' => $revenueTarget,
        'actual_revenue' => $monthlyRevenue,
        'today_revenue'                => $todayRevenue,
        'target_completion_percentage' => $percentage
    ]);
}


}
