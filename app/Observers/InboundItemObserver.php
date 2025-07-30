<?php

namespace App\Observers;

use App\Models\InboundItem;
use App\Models\Product;

class InboundItemObserver
{
    public function creating(InboundItem $item): void
    {
        $inbound = $item->inbound;

        // 仓库
        $item->warehouse_id = $inbound->warehouse_id;
        $item->warehouse_name = $inbound->warehouse?->name;

        // 入库状态
        $item->inbound_status = $inbound->status;

        // 入库日期
        $item->inbound_date = $inbound->inbound_date;

        // 商品
        if ($item->product_id) {
            $product = Product::find($item->product_id);
            $item->product_name = $product?->name;
        }
    }
}
