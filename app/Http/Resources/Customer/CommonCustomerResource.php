<?php

namespace App\Http\Resources\Customer;

use App\Http\Resources\BaseResource;

class CommonCustomerResource extends BaseResource
{
    protected function compose(): array
    {
        $resource = $this->resource->toArray();
        unset(
            $resource['postal_code'],
            $resource['detail_address1'],
            $resource['detail_address2'],
        );
        $resource['contact'] = $this->resource->contact ?? [];
        return $resource;
    }
}
