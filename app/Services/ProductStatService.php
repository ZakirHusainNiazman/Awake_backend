<?php

namespace App\Services;

use App\Models\Seller\Product\ProductStat;


class ProductStatService
{
    public function logEvent(string $productId, string $event): void
    {
        if (!in_array($event, ['view', 'cart','wishlist', 'purchase'])) {
            throw new \InvalidArgumentException("Invalid event type: $event");
        }

        ProductStat::create([
            'product_id' => $productId,
            'event' => $event,
        ]);
    }
}
