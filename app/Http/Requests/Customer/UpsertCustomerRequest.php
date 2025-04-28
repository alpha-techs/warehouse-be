<?php

namespace App\Http\Requests\Customer;

use App\Http\Requests\BaseRequest;

class UpsertCustomerRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => ['string', 'required', 'max:255'],
            'tel' => ['string', 'nullable', 'max:255'],
            'fax' => ['string', 'nullable', 'max:255'],
            'address.postalCode' => ['string', 'nullable', 'max:255'],
            'address.detailAddress1' => ['string', 'nullable', 'max:255'],
            'address.detailAddress2' => ['string', 'nullable', 'max:255'],
            'contact.name' => ['string', 'nullable', 'max:255'],
            'contact.tel' => ['string', 'nullable', 'max:255'],
            'contact.email' => ['string', 'nullable', 'max:255'],
        ];
    }
}
