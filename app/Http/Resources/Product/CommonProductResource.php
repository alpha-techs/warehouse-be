<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\BaseResource;

class CommonProductResource extends BaseResource
{
    protected function compose(): array
    {
        $resource = $this->resource->toArray();
        unset(
            $resource['dimension_description'],
            $resource['length'],
            $resource['width'],
            $resource['height'],
            $resource['unit_weight'],
            $resource['total_weight'],
            $resource['length_unit'],
            $resource['weight_unit']
        );
        return $resource;
    }
}
