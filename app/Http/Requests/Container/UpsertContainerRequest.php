<?php

namespace App\Http\Requests\Container;

use App\Contracts\Models\ContainerStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpsertContainerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'containerNumber' => ['required', 'string', 'max:255'],
            'shippingLine' => ['nullable', 'string', 'max:255'],
            'vesselName' => ['nullable', 'string', 'max:255'],
            'voyageNumber' => ['nullable', 'string', 'max:255'],
            'arrivalDate' => ['nullable', 'date'],
            'clearanceDate' => ['nullable', 'date'],
            'dischargeDate' => ['nullable', 'date'],
            'returnDate' => ['nullable', 'date'],
            'status' => ['nullable', Rule::enum(ContainerStatus::class)],
            'items' => ['nullable', 'array'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.product.id' => ['required', 'integer', 'exists:products,id'],
            'items.*.product.name' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.manufactureDate' => ['nullable', 'date'],
            'items.*.bestBeforeDate' => ['nullable', 'date'],
        ];
    }
}
