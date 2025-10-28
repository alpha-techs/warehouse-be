<?php

namespace App\Http\Resources\Billing;

use App\Http\Resources\BaseResource;

final class InvoiceLineItemResource extends BaseResource
{
    protected function compose(): array
    {
        $productName = $this->resource->product_name ?? $this->resource->product?->name;

        return [
            'id' => $this->resource->id,
            'outbound_id' => $this->resource->outbound_id,
            'outbound_order_id' => $this->resource->outbound_order_id,
            'outbound_date' => $this->resource->outbound_date,
            'product' => [
                'id' => $this->resource->product_id,
                'name' => $productName,
            ],
            'quantity' => (int) $this->resource->quantity,
            'unit_price' => (float) $this->resource->unit_price,
            'currency' => $this->resource->currency,
            'line_amount' => (float) $this->resource->line_amount,
            'tax_amount' => (float) $this->resource->tax_amount,
            'note' => $this->resource->note,
        ];
    }
}
