<?php

namespace App\Http\Requests\Billing;

use App\Http\Requests\BaseRequest;

final class IssueInvoiceRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'issueDate' => ['nullable', 'date'],
            'message' => ['nullable', 'string', 'max:1024'],
        ];
    }
}
