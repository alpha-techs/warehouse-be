<?php

namespace App\Observers;

use App\Models\OutboundItem;
use App\Models\Product;

class OutboundItemObserver
{
    public function creating(OutboundItem $item): void
    {
        $outbound = $item->outbound;

        // 仓库
        $item->warehouse_id = $outbound->warehouse_id;
        $item->warehouse_name = $outbound->warehouse_name;

        // 客户
        $item->customer_id = $outbound->customer_id;
        $item->customer_name = $outbound->customer_name;

        // 出库日期
        $item->outbound_date = $outbound->outbound_date;

        // 出库状态
        $item->outbound_status = $outbound->status;

        // 商品
        if ($item->product_id) {
            $product = Product::find($item->product_id);
            $item->product_name = $product?->name;
        }
    }
}
