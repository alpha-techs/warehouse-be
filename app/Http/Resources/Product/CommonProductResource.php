<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\BaseResource;

class CommonProductResource extends BaseResource
{
    protected function compose(): array
    {
        $resource = $this->resource->toArray();
        $resource['dimension'] = [
            'length' => $resource['length'],
            'width' => $resource['width'],
            'height' => $resource['height'],
            'weight' => $resource['weight'],
            'lengthUnit' => $resource['length_unit'],
            'weightUnit' => $resource['weight_unit'],
            'description' => $resource['dimension_description'],
        ];
        unset(
            $resource['length'],
            $resource['width'],
            $resource['height'],
            $resource['weight'],
            $resource['length_unit'],
            $resource['weight_unit']
        );
        return $resource;
    }
}
