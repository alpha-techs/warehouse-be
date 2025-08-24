<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\BaseRequest;

final class GenerateInboundReportRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'inboundId' => 'required|integer|exists:inbounds,id',
            'format' => [
                'string',
                'nullable',
                'in:pdf,excel'
            ],
        ];
    }
}
