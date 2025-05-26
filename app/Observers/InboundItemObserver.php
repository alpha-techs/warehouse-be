<?php

namespace App\Observers;

use App\Models\InboundItem;
use App\Models\Product;

class InboundItemObserver
{
    public function creating(InboundItem $item): void
    {
        $inbound = $item->inbound;
        $item->warehouse_id = $inbound->warehouse_id;
        $item->warehouse_name = $inbound->warehouse?->name;
        $item->inbound_status = $inbound->status;

        if ($item->product_id) {
            $product = Product::find($item->product_id);
            $item->product_name = $product?->name;
        }
    }
}
