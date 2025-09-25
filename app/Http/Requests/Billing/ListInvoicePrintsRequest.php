<?php

namespace App\Http\Requests\Billing;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

final class ListInvoicePrintsRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'itemsPerPage' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
            'invoiceNumber' => 'nullable|string',
            'customerId' => 'nullable|integer|min:1',
            'status' => ['nullable', 'string', Rule::in(['pending', 'processing', 'completed', 'failed'])],
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date',
        ];
    }
}
