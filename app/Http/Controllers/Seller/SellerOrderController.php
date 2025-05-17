<?php

namespace App\Http\Controllers\Seller;

use Log;
use Exception;
use App\Helpers\ImageHelper;
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
        $order = $seller->orders()->where("id", $request->id)->with(['items', 'order.user', 'order.shippingAddress'])->first(); // Use first() instead of get()

        // Check if the order exists
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Return the order using SellerOrderResource
        return new SellerOrderResource($order); // Use the resource properly for a single instance
    }




    public function markAsDelivered(Request $request, string $id)
    {
        $sellerOrder = SellerOrder::find($id);

        $request->validate([
            'receipt_image' => 'required|image|mimes:jpeg,png,jpg|max:2048', // 2MB max
        ]);

        // Store the image
        $path = ImageHelper::saveImageFile($request->file('receipt_image'), 'receipts');

        if(!$sellerOrder){
            return response()->json([
                'message'=>"Order not found",
            ],404);
        }

        Log::info("Marking order as delivered", ['seller_order' => $sellerOrder->toArray()]);

        $sellerOrder->update([
            'receipt_image' => $path,
            'status'=>'delivered',
            'delivered_at' => now(),
        ]);

        return response()->json(['message' => 'Marked as Delivere with receipt.']);
    }

    public function recent()
    {
        $user = Auth::user();
        $seller = $user->seller;

        $orders = SellerOrder::with('items')
            ->where('seller_id', $seller->id)
            ->latest()         // Orders by created_at descending
            ->take(10)         // Only fetch latest 10 orders
            ->get();

            Log::info("request => ",['recent orders => ',$orders]);
        return SellerOrderResource::collection($orders);
    }







    // reporst functions
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
