<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseRequest;

final class UpsertProductRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => ['string',  'required', 'max:255'],
            'sku' => ['string',  'nullable', 'max:255'],
            'cargoMark'  => ['string',  'nullable', 'max:255'],
            'dimension.description' => ['string',  'nullable', 'max:255'],
            'dimension.length' => ['numeric',  'nullable'],
            'dimension.width' => ['numeric',  'nullable'],
            'dimension.height' => ['numeric',  'nullable'],
            'dimension.lengthUnit' => ['nullable', 'in:mm,cm,m'],
            'dimension.unitWeight' => ['numeric',  'nullable'],
            'dimension.totalWeight' => ['numeric',  'nullable'],
            'dimension.weightUnit' => ['nullable', 'in:g,kg'],
            'hasSubPackage' =>  ['boolean',  'nullable'],
            'subPackageCount' => ['integer',  'nullable'],
            'isFixedWeight' => ['boolean',  'nullable'],
        ];
    }
}
