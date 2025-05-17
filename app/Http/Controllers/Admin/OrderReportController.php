<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User\Order\Order;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class OrderReportController extends Controller
{
    // this function will return pdf all the orders based on the filters to teh admin
    public function download(Request $request)
    {
        $status = $request->input('status'); // string or null
        $min = $request->input('min');       // string or null
        $max = $request->input('max');       // string or null

        $ordersQuery = Order::query()->with('items');

        if (!empty($status)) {
            $ordersQuery->where('status', $status);
        }

        // Check if min and max are valid numeric strings
        $minIsNumeric = is_numeric($min);
        $maxIsNumeric = is_numeric($max);

        if ($minIsNumeric && $maxIsNumeric) {
            // Both min and max provided
            $ordersQuery->whereBetween('total_amount', [(float)$min, (float)$max]);
        } elseif ($minIsNumeric) {
            // Only min provided
            $ordersQuery->where('total_amount', '>=', (float)$min);
        } elseif ($maxIsNumeric) {
            // Only max provided
            $ordersQuery->where('total_amount', '<=', (float)$max);
        }

        $orders = $ordersQuery->orderBy('created_at')->get();

        \Log::info("Request data", [
            "request" => $request->all(),
            "orders_count" => $orders->count(),
        ]);

        $totalRevenue = $orders->sum('total_amount');

        $pdf = Pdf::loadView('pdf.admin-orders-report', [
            'orders' => $orders,
            'totalRevenue' => $totalRevenue,
        ]);

        return $pdf->download('orders-report.pdf');
    }

    public function downloadSingleOrder(string $id)
    {
       $order = Order::with('items', 'user')->find($id);

        if (!$order) {
            abort(404, 'Order not found.');
        }

        $totalQuantity = $order->items->sum('quantity');
        $subTotal = $order->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        Log::info("reqeust ", ["order => ",$order,"id =>",$id]);

        $pdf = Pdf::loadView('pdf.admin-single-order-report', [
            'order' => $order,
            'items' => $order->items,
            'totalQuantity' => $totalQuantity,
            'subTotal' => $subTotal,
        ]);

        return $pdf->download("order-{$order->id}-report.pdf");
    }


}
