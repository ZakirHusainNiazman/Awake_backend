<?php

namespace App\Http\Controllers\Seller;

use Log;
use Exception;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Seller\SellerOrder;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Seller\SellerOrderResource;

class SellerOrderController extends Controller
{

    // this function will return a sellers spacific orders
    function index(){
        $user = Auth::user();

        $seller = $user->seller;

        $orders = SellerOrder::with(['items'])
        ->where('seller_id', $seller->id)
        ->latest()
        ->get();

        return SellerOrderResource::collection($orders);
    }

    //this will return a single order
    function show(Request $request)
    {
        $user = Auth::user();

        // Get the seller associated with the user
        $seller = $user->seller;

        // Fetch the single order for the seller
        $order = $seller->orders()->where("id", $request->id)->with(['items'])->first(); // Use first() instead of get()

        // Check if the order exists
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Return the order using SellerOrderResource
        return new SellerOrderResource($order); // Use the resource properly for a single instance
    }




    // reporst functions

//    public function downloadOrderReport($orderId)
//     {
//         Log::info('Download Order Report Request for Order ID: ' . $orderId); // Add logging here
//         $order = SellerOrder::with('items')->findOrFail($orderId);

//         $pdf = Pdf::loadView('pdf.order-report', [
//             'title' => 'Order Report',
//             'order' => $order,
//             'companyName' => 'My Company',
//             'companyAddress' => '123 Main Street, City',
//             'companyEmail' => 'info@mycompany.com'
//         ]);

//         return response()->streamDownload(function () use ($pdf) {
//             echo $pdf->output();
//         }, 'order-report.pdf');
//     }

public function downloadOrderReport($orderId)
{
    try {
        $order = SellerOrder::with('items')->findOrFail($orderId);

        // Check if order has items
        if ($order->items->isEmpty()) {
            return response()->json(['message' => 'Order has no items'], 404);
        }

        // Generate the PDF
        $pdf = Pdf::loadView('pdf.order-report', [
            'title' => 'Order Report',
            'order' => $order,
            'companyName' => 'My Company',
            'companyAddress' => '123 Main Street, City',
            'companyEmail' => 'info@mycompany.com'
        ]);

        // Try to return the PDF directly to debug
        return $pdf->download('order-report.pdf');  // Use download() instead of streamDownload temporarily to test

    } catch (Exception $e) {
        \Log::error('Error generating PDF: ' . $e->getMessage());
        return response()->json(['message' => 'Failed to generate PDF'], 500);
    }
}


}
