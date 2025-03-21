<?php

namespace App\Http\Resources\Warehouse;

use App\Http\Resources\BaseResource;

class CommonWarehouseResource extends BaseResource
{
    protected function compose(): array
    {
        $resource = $this->resource->toArray();
        $resource['address'] = [
            'postal_code' => $resource['postal_code'],
            'detail_address1' => $resource['detail_address1'],
            'detail_address2' => $resource['detail_address2'],
        ];
        unset(
            $resource['postal_code'],
            $resource['detail_address1'],
            $resource['detail_address2']
        );
        return $resource;
    }
}
