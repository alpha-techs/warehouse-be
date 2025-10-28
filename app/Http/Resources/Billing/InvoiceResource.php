<?php

namespace App\Http\Resources\Billing;

use App\Http\Resources\BaseResource;
use Illuminate\Support\Collection;

final class InvoiceResource extends BaseResource
{
    protected function compose(): array
    {
        $items = $this->resource->items instanceof Collection
            ? $this->resource->items
            : collect($this->resource->items ?? []);

        $items = $items->values();

        return [
            'id' => $this->resource->id,
            'invoice_number' => $this->resource->invoice_number,
            'status' => $this->resource->status?->value ?? $this->resource->status,
            'customer' => [
                'id' => $this->resource->customer_id,
                'name' => $this->resource->customer_name ?? $this->resource->customer?->name,
            ],
            'due_date' => $this->resource->due_date,
            'issue_date' => $this->resource->issue_date,
            'currency' => $this->resource->currency,
            'subtotal_amount' => (float) $this->resource->subtotal_amount,
            'tax_amount' => (float) $this->resource->tax_amount,
            'total_amount' => (float) $this->resource->total_amount,
            'outbound_id' => $this->resource->outbound_id,
            'items' => InvoiceLineItemResource::collection($items),
            'notes' => $this->resource->notes,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
