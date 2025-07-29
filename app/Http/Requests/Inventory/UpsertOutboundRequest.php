<?php

namespace App\Http\Requests\Inventory;

final class UpsertOutboundRequest
{
    public function rules(): array
    {
        return [
            'outboundOrderId' => [
                'string',
                'nullable',
                'max:255',
            ],
            'outboundDate' => ['date', 'required'],
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
            'items.*.lotNumber' => [
                'nullable',
                'string',
            ],
            'items.*.note' => [
                'nullable',
                'string',
            ],
        ];
    }
}
