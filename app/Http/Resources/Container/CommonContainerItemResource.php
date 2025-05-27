<?php

namespace App\Http\Resources\Container;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Product\CommonProductResource;
use App\Models\ContainerItem;

final class CommonContainerItemResource extends BaseResource
{
    protected function compose(): array
    {
        /** @var ContainerItem $this */
        return [
            'id' => $this->id,
            'containerId' => $this->container_id,
            'productId' => $this->product_id,
            'product' => CommonProductResource::make($this->product),
            'productName' => $this->product_name,
            'quantity' => $this->quantity,
            'manufactureDate' => $this->manufacture_date?->format('Y-m-d'),
            'bestBeforeDate' => $this->best_before_date?->format('Y-m-d'),
        ];
    }
}
