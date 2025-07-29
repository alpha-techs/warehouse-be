<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\BaseRequest;

final class GetInventoryListRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'itemsPerPage' => ['integer', 'nullable', 'min:1', 'max:100'],
            'page' => ['integer', 'nullable', 'min:1'],
            'lotNumber' => ['string', 'nullable'],
            'warehouseId' => ['integer', 'nullable', 'exists:warehouses,id'],
            'productId' => ['integer', 'nullable', 'exists:products,id'],
            'inboundDateFrom' => ['date', 'nullable'],
            'inboundDateTo' => ['date', 'nullable'],
        ];
    }
}
