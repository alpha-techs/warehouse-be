<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\BaseRequest;

final class GetNameChangeListRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'itemsPerPage' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
            'nameChangeOrderId' => 'nullable|string|max:255',
            'nameChangeDateFrom' => 'nullable|date',
            'nameChangeDateTo' => 'nullable|date',
            'warehouseId' => 'nullable|integer|exists:warehouses,id',
            'customerId' => 'nullable|integer|exists:customers,id',
            'status' => 'nullable|string|in:pending,approved,rejected',
        ];
    }
}
