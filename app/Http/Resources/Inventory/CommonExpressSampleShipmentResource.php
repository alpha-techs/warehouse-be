<?php

namespace App\Http\Resources\Inventory;

use App\Contracts\Models\ExpressSampleShipmentStatus;
use App\Http\Resources\BaseResource;
use DateTimeInterface;
use Illuminate\Support\Collection;

class CommonExpressSampleShipmentResource extends BaseResource
{
    protected function compose(): array
    {
        /** @var \App\Models\ExpressSampleShipment $shipment */
        $shipment = $this->resource;

        $requestedShipDate = $shipment->requested_ship_date;
        if ($requestedShipDate instanceof DateTimeInterface) {
            $requestedShipDate = $requestedShipDate->format('Y-m-d');
        }

        $desiredDeliveryDate = $shipment->desired_delivery_date;
        if ($desiredDeliveryDate instanceof DateTimeInterface) {
            $desiredDeliveryDate = $desiredDeliveryDate->format('Y-m-d');
        }

        $dispatchedAt = $shipment->dispatched_at;
        if ($dispatchedAt instanceof DateTimeInterface) {
            $dispatchedAt = $dispatchedAt->format('Y-m-d H:i:s');
        }

        $deliveredAt = $shipment->delivered_at;
        if ($deliveredAt instanceof DateTimeInterface) {
            $deliveredAt = $deliveredAt->format('Y-m-d H:i:s');
        }

        $createdAt = $shipment->created_at instanceof DateTimeInterface
            ? $shipment->created_at->format('Y-m-d H:i:s')
            : $shipment->created_at;

        $updatedAt = $shipment->updated_at instanceof DateTimeInterface
            ? $shipment->updated_at->format('Y-m-d H:i:s')
            : $shipment->updated_at;

        /** @var Collection<int, array> $items */
        $items = $shipment->relationLoaded('items') ? $shipment->items : collect();
        $itemsData = $items->map(
            fn ($item) => (new CommonExpressSampleShipmentItemResource($item))->toArray(request())
        )->all();

        $status = $shipment->status instanceof ExpressSampleShipmentStatus
            ? $shipment->status->value
            : $shipment->status;

        return [
            'id' => $shipment->id,
            'express_sample_order_id' => $shipment->express_sample_order_id,
            'status' => $status,
            'requested_ship_date' => $requestedShipDate,
            'desired_delivery_date' => $desiredDeliveryDate,
            'desired_delivery_time_window' => $shipment->desired_delivery_time_window,
            'warehouse' => $shipment->warehouse ? [
                'id' => $shipment->warehouse->id,
                'name' => $shipment->warehouse->name,
            ] : null,
            'customer' => $shipment->customer ? [
                'id' => $shipment->customer->id,
                'name' => $shipment->customer->name,
            ] : null,
            'customer_contact_id' => $shipment->customer_contact_id,
            'recipient' => [
                'company_name' => $shipment->recipient_company_name,
                'department' => $shipment->recipient_department,
                'name' => $shipment->recipient_name,
                'phone_number' => $shipment->recipient_phone_number,
                'postal_code' => $shipment->recipient_postal_code,
                'prefecture' => $shipment->recipient_prefecture,
                'city' => $shipment->recipient_city,
                'address_line1' => $shipment->recipient_address_line1,
                'address_line2' => $shipment->recipient_address_line2,
            ],
            'delivery_service' => $shipment->delivery_service,
            'package_count' => $shipment->package_count,
            'package_type' => $shipment->package_type,
            'delivery_fee_payer' => $shipment->delivery_fee_payer,
            'sample_purpose' => $shipment->sample_purpose,
            'emergency_contact' => [
                'name' => $shipment->emergency_contact_name,
                'phone_number' => $shipment->emergency_contact_phone_number,
            ],
            'carrier_name' => $shipment->carrier_name,
            'tracking_number' => $shipment->tracking_number,
            'note' => $shipment->note,
            'items' => $itemsData,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
            'dispatched_at' => $dispatchedAt,
            'delivered_at' => $deliveredAt,
        ];
    }
}

