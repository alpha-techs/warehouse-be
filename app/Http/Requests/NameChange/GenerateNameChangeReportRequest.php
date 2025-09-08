<?php

namespace App\Http\Requests\NameChange;

use App\Http\Requests\BaseRequest;

final class GenerateNameChangeReportRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'nameChangeId' => 'required|integer|exists:name_changes,id',
            'format' => [
                'string',
                'nullable',
                'in:pdf,excel'
            ],
        ];
    }
}
