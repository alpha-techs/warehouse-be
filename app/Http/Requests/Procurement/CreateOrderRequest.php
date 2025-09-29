<?php

namespace App\Http\Requests\Procurement;

use App\Http\Requests\BaseRequest;

final class CreateOrderRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'customerId' => ['required', 'integer', 'exists:customers,id'],
            'deliveryDueDate' => ['required', 'date'],
            'deliveryAddress' => ['required', 'array'],
            'deliveryAddress.postalCode' => ['nullable', 'string', 'max:32'],
            'deliveryAddress.detailAddress1' => ['required', 'string', 'max:255'],
            'deliveryAddress.detailAddress2' => ['nullable', 'string', 'max:255'],
            'contactName' => ['required', 'string', 'max:255'],
            'contactPhone' => ['required', 'string', 'max:64'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.productId' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit' => ['required', 'string', 'max:64'],
            'items.*.unitPrice' => ['required', 'numeric', 'gte:0'],
            'items.*.currency' => ['nullable', 'string', 'in:JPY'],
            'items.*.note' => ['nullable', 'string', 'max:1024'],
            'notes' => ['nullable', 'string', 'max:1024'],
        ];
    }
}
