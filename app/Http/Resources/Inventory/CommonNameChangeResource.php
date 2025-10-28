<?php

namespace App\Http\Resources\Inventory;

use App\Http\Resources\BaseResource;

class CommonNameChangeResource extends BaseResource
{
    protected function compose(): array
    {
        $data = parent::compose();
        $nameChangeDate = $this->resource->name_change_date;
        $createdAt = $this->resource->created_at;
        return [
            ...$data,
            'name_change_date' => $nameChangeDate,
            'created_at' => $createdAt,
        ];
    }
}
