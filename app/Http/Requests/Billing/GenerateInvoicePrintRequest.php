<?php

namespace App\Http\Requests\Billing;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

final class GenerateInvoicePrintRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'invoiceId' => 'required|integer|exists:invoices,id',
            'format' => ['nullable', 'string', Rule::in(['excel'])],
        ];
    }
}
