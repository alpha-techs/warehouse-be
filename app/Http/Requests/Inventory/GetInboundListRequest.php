<?php

namespace App\Http\Requests\Inventory;

use App\Contracts\Models\InboundStatus;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rules\Enum;

final class GetInboundListRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'itemsPerPage' => ['integer', 'nullable', 'min:1', 'max:100'],
            'page' => ['integer', 'nullable', 'min:1'],
            'inboundOrderId' => ['string', 'nullable'],
            'inboundDateFrom' => ['date', 'nullable'],
            'inboundDateTo' => ['date', 'nullable'],
            'warehouseId' => ['integer', 'nullable', 'exists:warehouses,id'],
            'status' => [
                'string',
                'nullable',
                new Enum(InboundStatus::class),
            ],
        ];
    }
}
