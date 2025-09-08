<?php

namespace App\Http\Requests\NameChange;

use App\Http\Requests\BaseRequest;

final class GetNameChangeReportListRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'itemsPerPage' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }
}
