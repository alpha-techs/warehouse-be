<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\BaseRequest;

class GetOutboundItemsRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'itemsPerPage' => ['integer', 'nullable', 'min:1', 'max:100'],
            'page' => ['integer', 'nullable', 'min:1'],
            'lotNumber' => ['string', 'nullable'],
            'productId' => ['integer', 'nullable'],
            'outboundDateFrom' => ['date', 'nullable'],
            'outboundDateTo' => ['date', 'nullable'],
        ];
    }
}
