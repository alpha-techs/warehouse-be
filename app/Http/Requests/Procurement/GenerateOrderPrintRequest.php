<?php

namespace App\Http\Requests\Procurement;

use App\Http\Requests\BaseRequest;

final class GenerateOrderPrintRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'orderId' => ['required', 'integer', 'exists:orders,id'],
            'format' => ['nullable', 'string', 'in:excel'],
        ];
    }
}
