<?php

namespace App\Http\Requests\Inventory;

use App\Contracts\Models\ExpressSampleShipmentStatus;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rules\Enum;

final class GetExpressSampleShipmentListRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'itemsPerPage' => ['integer', 'nullable', 'min:1', 'max:100'],
            'page' => ['integer', 'nullable', 'min:1'],
            'status' => [
                'nullable',
                new Enum(ExpressSampleShipmentStatus::class),
            ],
            'customerId' => ['integer', 'nullable', 'exists:customers,id'],
            'desiredDeliveryDateFrom' => ['date', 'nullable'],
            'desiredDeliveryDateTo' => ['date', 'nullable'],
        ];
    }
}

