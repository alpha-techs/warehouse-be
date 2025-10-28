<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

final class UpsertOutboundRequest extends BaseRequest
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
            'carrierName' => [
                'nullable',
                'string',
                'max:255',
            ],
            'currency' => [
                'nullable',
                'string',
                Rule::in(['JPY']),
            ],
            'subtotalAmount' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'taxAmount' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'totalAmount' => [
                'nullable',
                'numeric',
                'min:0',
            ],
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
            'items.*.inboundItemId' => [
                'integer',
                'min:1',
            ],
            'items.*.inventoryItemId' => [
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
            'items.*.unitPrice' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'items.*.lineAmount' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'items.*.taxAmount' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'items.*.currency' => [
                'nullable',
                'string',
                Rule::in(['JPY']),
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
