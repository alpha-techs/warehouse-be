<?php

namespace App\Http\Requests\Inventory;

use App\Contracts\Models\OutboundStatus;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rules\Enum;

final class GetOutboundListRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'itemsPerPage' => ['integer', 'nullable', 'min:1', 'max:100'],
            'page' => ['integer', 'nullable', 'min:1'],
            'outboundOrderId' => ['string', 'nullable'],
            'outboundDateFrom' => ['date', 'nullable'],
            'outboundDateTo' => ['date', 'nullable'],
            'warehouseId' => ['integer', 'nullable', 'exists:warehouses,id'],
            'customerId' => ['integer', 'nullable', 'exists:customers,id'],
            'status' => [
                'string',
                'nullable',
                new Enum(OutboundStatus::class),
            ],
        ];
    }
}
