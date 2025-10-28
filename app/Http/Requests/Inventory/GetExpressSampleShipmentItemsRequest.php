<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\BaseRequest;

final class GetExpressSampleShipmentItemsRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'itemsPerPage' => ['integer', 'nullable', 'min:1', 'max:100'],
            'page' => ['integer', 'nullable', 'min:1'],
            'expressSampleShipmentId' => ['integer', 'nullable', 'exists:express_sample_shipments,id'],
            'productId' => ['integer', 'nullable', 'exists:products,id'],
            'desiredDeliveryDate' => ['date', 'nullable'],
        ];
    }
}

