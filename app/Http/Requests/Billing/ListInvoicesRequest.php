<?php

namespace App\Http\Requests\Billing;

use App\Contracts\Models\InvoiceStatus;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rules\Enum;

final class ListInvoicesRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'pageSize' => ['nullable', 'integer', 'min:1', 'max:100'],
            'customerId' => ['nullable', 'integer', 'exists:customers,id'],
            'status' => ['nullable', 'string', new Enum(InvoiceStatus::class)],
            'outboundDateFrom' => ['nullable', 'date'],
            'outboundDateTo' => ['nullable', 'date', 'after_or_equal:outboundDateFrom'],
        ];
    }
}
