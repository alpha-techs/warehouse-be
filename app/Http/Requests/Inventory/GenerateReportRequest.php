<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\BaseRequest;

final class GenerateReportRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'warehouseId' => 'required|integer|exists:warehouses,id',
            'customerId' => 'required|integer|exists:customers,id',
            'format' => [
                'string',
                'nullable',
                'in:pdf,excel'
            ],
        ];
    }
}
