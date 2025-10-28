<?php

namespace App\Http\Requests\Billing;

use App\Http\Requests\BaseRequest;

final class CreateInvoiceRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'customerId' => ['required', 'integer', 'exists:customers,id'],
            'outboundId' => ['required', 'integer', 'exists:outbounds,id'],
            'dueDate' => ['nullable', 'date'],
            'currency' => ['nullable', 'string', 'in:JPY'],
            'autoIssue' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1024'],
        ];
    }
}
