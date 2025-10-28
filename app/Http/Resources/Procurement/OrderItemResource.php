<?php

namespace App\Http\Resources\Procurement;

use App\Http\Resources\BaseResource;

final class OrderItemResource extends BaseResource
{
    protected function compose(): array
    {
        $productName = $this->resource->product_name ?? $this->resource->product?->name;
        $productSku = $this->resource->product_sku ?? $this->resource->product?->sku;

        return [
            'id' => $this->resource->id,
            'product' => [
                'id' => $this->resource->product_id,
                'name' => $productName,
                'sku' => $productSku,
            ],
            'quantity' => (float) $this->resource->quantity,
            'unit' => $this->resource->unit,
            'unit_price' => (float) $this->resource->unit_price,
            'line_amount' => (float) $this->resource->line_amount,
            'currency' => $this->resource->currency,
            'note' => $this->resource->note,
        ];
    }
}
