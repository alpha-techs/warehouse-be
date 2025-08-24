<?php

namespace App\Http\Requests\Outbound;

use App\Http\Requests\BaseRequest;

final class GetOutboundReportListRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'itemsPerPage' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }
}
