<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        $currentStart = $now->copy()->startOfMonth();
        $currentEnd = $now->copy()->endOfMonth();

        $prevStart = $now->copy()->subMonth()->startOfMonth();
        $prevEnd = $now->copy()->subMonth()->endOfMonth();

        // Total customers (customers + sellers)
        $totalCustomers = User::whereIn('user_type', ['customer', 'seller'])->count();

        // Total sellers
        $totalSellers = User::where('user_type', 'seller')->count();

        // Growth calculation for customers
        $currentMonthCustomers = User::whereIn('user_type', ['customer', 'seller'])
            ->whereBetween('created_at', [$currentStart, $currentEnd])
            ->count();

        $previousMonthCustomers = User::whereIn('user_type', ['customer', 'seller'])
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->count();

        $percentageChangeCustomers = $previousMonthCustomers > 0
            ? (($currentMonthCustomers - $previousMonthCustomers) / $previousMonthCustomers) * 100
            : ($currentMonthCustomers > 0 ? 100 : 0);

        $trendCustomers = $percentageChangeCustomers > 0 ? 'rise' : 'fall';

        // Growth calculation for sellers
        $currentMonthSellers = User::where('user_type', 'seller')
            ->whereBetween('created_at', [$currentStart, $currentEnd])
            ->count();

        $previousMonthSellers = User::where('user_type', 'seller')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->count();

        $percentageChangeSellers = $previousMonthSellers > 0
            ? (($currentMonthSellers - $previousMonthSellers) / $previousMonthSellers) * 100
            : ($currentMonthSellers > 0 ? 100 : 0);

        $trendSellers = $percentageChangeSellers > 0 ? 'rise' : 'fall';

        return response()->json([
            'customer_growth' => [
                'total_customers' => $totalCustomers,
                'percentage_change' => round(abs($percentageChangeCustomers), 2),
                'trend' => $trendCustomers,
            ],
            'seller_growth' => [
                'total_sellers' => $totalSellers,
                'percentage_change' => round(abs($percentageChangeSellers), 2),
                'trend' => $trendSellers,
            ],
        ]);
    }


}
