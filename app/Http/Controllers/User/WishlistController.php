<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Models\User\Wishlist;
use App\Models\User\WishlistItem;
use App\Http\Controllers\Controller;
use App\Services\ProductStatService;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\User\Wishlist\WishlistResource;
use App\Http\Resources\User\Wishlist\WishlistItemResource;
use App\Http\Requests\User\Wishlist\StoreWishlistItemRequest;

class WishlistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $wishlist = $user->wishlist; // This gives the actual Wishlist model (or null)

        if ($wishlist) {
            $wishlist->load([
                'items.product.images',
                'items.variant.optionValues'
            ]);
        }

        return WishlistResource::make($wishlist);
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(StoreWishlistItemRequest $request,ProductStatService $statService)
{
    $user = Auth::user();
    $wishlist = $user->wishlist;

    if (!$wishlist) {
        return response()->json([
            'message' => 'No wishlist found for this user.'
        ], 404);
    }

    // Check for existing item with same product + variant combo
    $exists = WishlistItem::where('wishlist_id', $wishlist->id)
        ->where('product_id', $request->product_id)
        ->where(function ($query) use ($request) {
            if (filled($request->variant_id)) {
                $query->where('variant_id', $request->variant_id);
            } else {
                $query->whereNull('variant_id');
            }
        })
        ->exists();

    if ($exists) {
        return response()->json([
            "message" => "The product with the selected variant already exists in the wishlist",
        ], 409); // Conflict
    }

     //this log teh event to product stat
        $statService->logEvent($request->product_id, 'wishlist');


    // Create wishlist item
    $wishlistItem = WishlistItem::create([
        "wishlist_id" => $wishlist->id,
        "product_id" => $request->product_id,
        "variant_id" => $request->variant_id,
    ]);

    return WishlistItemResource::make($wishlistItem);
}



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $wishlist = $user->wishlist;

        $wishlistItem = $wishlist->items()->find($id);

        if(!$wishlistItem){
            return response()->json([
                "message"=> "Wishlist item not found."
            ],404);
        }

        return WishlistItemResource::make($wishlistItem);
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
        $user = Auth::user();
        $wishlist = $user->wishlist;

        $wishlistItem = $wishlist->items()->find($id);

        if(!$wishlistItem){
            return response()->json([
                "message"=> "Wishlist item not found."
            ],404);
        }

        $wishlistItem->delete();

        return response()->json([
                "message"=> "Wishlist item deleted successfully."
            ],200);
    }
}
