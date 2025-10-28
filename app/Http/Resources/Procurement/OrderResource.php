<?php

namespace App\Http\Resources\Procurement;

use App\Http\Resources\BaseResource;
use Illuminate\Support\Collection;

final class OrderResource extends BaseResource
{
    protected function compose(): array
    {
        $items = $this->resource->items instanceof Collection
            ? $this->resource->items
            : collect($this->resource->items ?? []);

        $items = $items->values();

        return [
            'id' => $this->resource->id,
            'order_number' => $this->resource->order_number,
            'status' => $this->resource->status?->value ?? $this->resource->status,
            'customer' => [
                'id' => $this->resource->customer_id,
                'name' => $this->resource->customer_name ?? $this->resource->customer?->name,
            ],
            'delivery_due_date' => $this->resource->delivery_due_date?->format('Y-m-d'),
            'delivery_address' => $this->resource->delivery_address,
            'contact_name' => $this->resource->contact_name,
            'contact_phone' => $this->resource->contact_phone,
            'currency' => $this->resource->currency,
            'total_amount' => (float) $this->resource->total_amount,
            'items' => OrderItemResource::collection($items),
            'notes' => $this->resource->notes,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
