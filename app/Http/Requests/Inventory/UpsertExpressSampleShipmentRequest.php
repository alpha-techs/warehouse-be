<?php

namespace App\Http\Requests\Inventory;

use App\Contracts\Models\ExpressSampleShipmentStatus;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

final class UpsertExpressSampleShipmentRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'expressSampleOrderId' => ['nullable', 'string', 'max:255'],
            'status' => [
                'nullable',
                new Enum(ExpressSampleShipmentStatus::class),
            ],
            'requestedShipDate' => ['required', 'date'],
            'desiredDeliveryDate' => ['nullable', 'date'],
            'desiredDeliveryTimeWindow' => [
                'nullable',
                Rule::in(['morning', 'noon', 'afternoon', 'evening', 'anytime']),
            ],
            'deliveryService' => [
                'nullable',
                Rule::in(['regular', 'cool', 'frozen', 'timeValue']),
            ],
            'packageCount' => ['nullable', 'integer', 'min:1'],
            'packageType' => ['nullable', 'string', 'max:255'],
            'deliveryFeePayer' => [
                'nullable',
                Rule::in(['sender', 'recipient', 'thirdParty']),
            ],
            'samplePurpose' => ['nullable', 'string'],
            'carrierName' => ['nullable', 'string', 'max:255'],
            'trackingNumber' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'dispatchedAt' => ['nullable', 'date'],
            'deliveredAt' => ['nullable', 'date'],
            'warehouse.id' => ['required', 'exists:warehouses,id'],
            'customer.id' => ['required', 'exists:customers,id'],
            'customerContactId' => ['nullable', 'exists:customer_contacts,id'],
            'recipient.companyName' => ['nullable', 'string', 'max:255'],
            'recipient.department' => ['nullable', 'string', 'max:255'],
            'recipient.name' => ['required', 'string', 'max:255'],
            'recipient.phoneNumber' => ['required', 'string', 'max:255'],
            'recipient.postalCode' => ['required', 'string', 'max:20'],
            'recipient.prefecture' => ['required', 'string', 'max:255'],
            'recipient.city' => ['required', 'string', 'max:255'],
            'recipient.addressLine1' => ['required', 'string', 'max:255'],
            'recipient.addressLine2' => ['nullable', 'string', 'max:255'],
            'emergencyContact.name' => ['nullable', 'string', 'max:255'],
            'emergencyContact.phoneNumber' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'integer', 'min:1'],
            'items.*.inventoryItemId' => ['required', 'integer', 'exists:inventory_items,id'],
            'items.*.inboundItemId' => ['nullable', 'integer', 'exists:inbound_items,id'],
            'items.*.product.id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.quantityUnit' => ['nullable', 'string', 'max:50'],
            'items.*.samplePackaging' => ['nullable', 'string'],
            'items.*.lotNumber' => ['nullable', 'string', 'max:255'],
            'items.*.note' => ['nullable', 'string'],
        ];
    }
}

