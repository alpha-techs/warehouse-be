<?php

namespace App\Http\Resources\Container;

use App\Http\Resources\BaseResource;
use App\Models\Container;

final class CommonContainerResource extends BaseResource
{
    protected function compose(): array
    {
        /** @var Container $this */
        return [
            'id' => $this->id,
            'containerNumber' => $this->container_number,
            'shippingLine' => $this->shipping_line,
            'vesselName' => $this->vessel_name,
            'voyageNumber' => $this->voyage_number,
            'arrivalDate' => $this->arrival_date?->format('Y-m-d'),
            'clearanceDate' => $this->clearance_date?->format('Y-m-d'),
            'dischargeDate' => $this->discharge_date?->format('Y-m-d'),
            'returnDate' => $this->return_date?->format('Y-m-d'),
            'status' => $this->status->value,
            'items' => CommonContainerItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
