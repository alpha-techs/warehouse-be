<?php

namespace App\Http\Requests\Outbound;

use App\Http\Requests\BaseRequest;

final class GenerateOutboundReportRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'outboundId' => 'required|integer|exists:outbounds,id',
            'format' => [
                'string',
                'nullable',
                'in:pdf,excel'
            ],
        ];
    }
}
