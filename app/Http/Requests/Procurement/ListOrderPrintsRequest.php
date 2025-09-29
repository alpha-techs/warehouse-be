<?php

namespace App\Http\Requests\Procurement;

use App\Http\Requests\BaseRequest;

final class ListOrderPrintsRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'itemsPerPage' => ['nullable', 'integer', 'min:1', 'max:100'],
            'orderNumber' => ['nullable', 'string', 'max:255'],
            'customerId' => ['nullable', 'integer', 'exists:customers,id'],
            'status' => ['nullable', 'string', 'in:pending,processing,completed,failed'],
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date'],
        ];
    }
}
