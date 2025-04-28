<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseRequest;

class GetProductsRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'itemsPerPage' => 'integer|nullable|min:1|max:100',
            'page' => 'integer|nullable|min:1',
            'name' => 'string|nullable',
        ];
    }
}
