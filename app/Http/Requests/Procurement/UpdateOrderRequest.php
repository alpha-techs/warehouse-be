<?php

namespace App\Http\Requests\Procurement;

use App\Http\Requests\BaseRequest;

final class UpdateOrderRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'deliveryDueDate' => ['sometimes', 'date'],
            'deliveryAddress' => ['sometimes', 'array'],
            'deliveryAddress.postalCode' => ['nullable', 'string', 'max:32'],
            'deliveryAddress.detailAddress1' => ['required_with:deliveryAddress', 'string', 'max:255'],
            'deliveryAddress.detailAddress2' => ['nullable', 'string', 'max:255'],
            'contactName' => ['sometimes', 'string', 'max:255'],
            'contactPhone' => ['sometimes', 'string', 'max:64'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.productId' => ['required_with:items', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'gt:0'],
            'items.*.unit' => ['required_with:items', 'string', 'max:64'],
            'items.*.unitPrice' => ['required_with:items', 'numeric', 'gte:0'],
            'items.*.currency' => ['nullable', 'string', 'in:JPY'],
            'items.*.note' => ['nullable', 'string', 'max:1024'],
            'notes' => ['sometimes', 'string', 'max:1024'],
        ];
    }
}
