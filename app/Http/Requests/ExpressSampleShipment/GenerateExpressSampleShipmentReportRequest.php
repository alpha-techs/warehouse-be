<?php

namespace App\Http\Requests\ExpressSampleShipment;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

final class GenerateExpressSampleShipmentReportRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'expressSampleShipmentId' => ['required', 'integer', 'exists:express_sample_shipments,id'],
            'format' => [
                'nullable',
                'string',
                Rule::in(['excel']),
            ],
        ];
    }
}

