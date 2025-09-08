<?php

namespace App\Observers;

use App\Models\NameChange;
use App\Models\NameChangeItem;
use App\Models\Product;

class NameChangeItemObserver
{
    public function creating(NameChangeItem $item): void
    {
        $nameChange = $item->nameChange;

        // 仓库
        $item->warehouse_id = $nameChange->warehouse_id;
        $item->warehouse_name = $nameChange->warehouse_name;

        // 客户
        $item->customer_id = $nameChange->customer_id;
        $item->customer_name = $nameChange->customer_name;

        // 名义变更日期
        $item->name_change_date = $nameChange->name_change_date;

        // 名义变更状态
        $item->name_change_status = $nameChange->status;

        // 商品
        if ($item->product_id) {
            $product = Product::find($item->product_id);
            $item->product_name = $product?->name;
        }
    }
}
