<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\Cart\CartItemResource;
use App\Http\Resources\User\UserResourceWithSeller;

class ProfileController extends Controller
{
    // it will return a single user
    public function show(Request $request)
    {
        $user = $request->user()->load([
            'seller',
            'seller.user.addresses',
            'cart.items.product.variants.optionValues',
            'cart.items.product.discount',
            'cart.items.variant',
            'wishlist.items.product.variants.optionValues',
            'wishlist.items.product.discount',
            'wishlist.items.variant.discount'
        ]);

        return new UserResourceWithSeller($user, 200);
    }


   protected function transformCart($cart)
    {
        if (!$cart || !$cart->items) {
            return [
                'count' => 0,
                'items' => [],
                'total' => 0,
            ];
        }

        $items = $cart->items;

        return [
            'count' => $items->count(),
            'items' => CartItemResource::collection($items),
            'total' => $items->sum(function ($item) {
                return $item->quantity * ($item->variant?->price ?? $item->product->price);
            }),
        ];
    }

}
