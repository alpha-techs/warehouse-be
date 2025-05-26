<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\BaseRequest;

final class UpsertInboundRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'inboundOrderId' => [
                'string',
                'nullable',
                'max:255',
            ],
            'inboundDate' => ['date', 'required'],
            'warehouse.id' => [
                'required',
                'exists:warehouses,id',
            ],
            'customer.id' => [
                'required',
                'exists:customers,id',
            ],
            'items' => [
                'array',
            ],
            'items.*.id' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'items.*.product.id' => [
                'required',
                'exists:products,id',
            ],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
            'items.*.perItemWeight' => [
                'nullable',
                'numeric',
            ],
            'items.*.perItemWeightUnit' => [
                'nullable',
                'string',
                'in:g,kg',
            ],
            'items.*.totalWeight' => [
                'nullable',
                'numeric',
            ],
            'items.*.manufactureDate' => [
                'nullable',
                'date',
            ],
            'items.*.bestBeforeDate' => [
                'nullable',
                'date',
            ],
            'items.*.lotNumber' => [
                'nullable',
                'string',
            ],
            'items.*.shipName' => [
                'nullable',
                'string',
            ],
        ];
    }
}
