<?php

namespace App\Http\Requests\Billing;

use App\Http\Requests\BaseRequest;

final class CancelInvoiceRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:1024'],
        ];
    }
}
