<?php

namespace App\Http\Requests\Procurement;

use App\Contracts\Models\OrderStatus;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rules\Enum;

final class ListOrdersRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'itemsPerPage' => ['nullable', 'integer', 'min:1', 'max:100'],
            'orderNumber' => ['nullable', 'string', 'max:255'],
            'customerId' => ['nullable', 'integer', 'exists:customers,id'],
            'status' => ['nullable', 'string', new Enum(OrderStatus::class)],
            'deliveryDueStart' => ['nullable', 'date'],
            'deliveryDueEnd' => ['nullable', 'date'],
        ];
    }
}
