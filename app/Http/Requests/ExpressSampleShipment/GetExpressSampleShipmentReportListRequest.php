<?php

namespace App\Http\Requests\ExpressSampleShipment;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

final class GetExpressSampleShipmentReportListRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'itemsPerPage' => ['integer', 'nullable', 'min:1', 'max:100'],
            'page' => ['integer', 'nullable', 'min:1'],
            'warehouseId' => ['nullable', 'integer', 'exists:warehouses,id'],
            'customerId' => ['nullable', 'integer', 'exists:customers,id'],
            'status' => [
                'nullable',
                Rule::in(['pending', 'processing', 'completed', 'failed']),
            ],
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date'],
        ];
    }
}

