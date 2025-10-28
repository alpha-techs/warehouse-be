<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\BaseRequest;

final class GetNameChangeItemsRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'itemsPerPage' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
            'lotNumber' => 'nullable|string|max:255',
            'productId' => 'nullable|integer|exists:products,id',
            'nameChangeDateFrom' => 'nullable|date',
            'nameChangeDateTo' => 'nullable|date',
        ];
    }
}
