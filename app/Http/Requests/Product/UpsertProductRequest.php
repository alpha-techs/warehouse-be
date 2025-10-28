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
            'images' => ['array'],
            'images.*.id' => ['nullable', 'integer', 'exists:product_images,id'],
            'images.*.mediaId' => [
                'required_without:images.*.id',
                'string',
                'size:26',
                'regex:/^[0-9A-HJKMNP-TV-Z]{26}$/i',
                'exists:media,id',
            ],
            'images.*.url' => ['nullable', 'string', 'url', 'max:2048'],
            'images.*.order' => ['nullable', 'integer', 'min:0'],
            'images.*.altText' => ['nullable', 'string', 'max:255'],
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
