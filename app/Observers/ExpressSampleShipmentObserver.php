<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\ExpressSampleShipment;
use App\Models\Warehouse;

class ExpressSampleShipmentObserver
{
    public function creating(ExpressSampleShipment $shipment): void
    {
        if ($shipment->warehouse_id) {
            $warehouse = Warehouse::find($shipment->warehouse_id);
            $shipment->warehouse_name = $warehouse?->name;
        }

        if ($shipment->customer_id) {
            $customer = Customer::find($shipment->customer_id);
            $shipment->customer_name = $customer?->name;
        }
    }

    public function updating(ExpressSampleShipment $shipment): void
    {
        if ($shipment->isDirty('warehouse_id')) {
            $warehouse = Warehouse::find($shipment->warehouse_id);
            $shipment->warehouse_name = $warehouse?->name;
        }

        if ($shipment->isDirty('customer_id')) {
            $customer = Customer::find($shipment->customer_id);
            $shipment->customer_name = $customer?->name;
        }

        if ($shipment->isDirty('status')) {
            $shipment->items()->update(['shipment_status' => $shipment->status]);
        }
    }

    public function deleting(ExpressSampleShipment $shipment): void
    {
        $shipment->items()->delete();
    }

    public function forceDeleting(ExpressSampleShipment $shipment): void
    {
        $shipment->items()->withTrashed()->forceDelete();
    }

    public function restoring(ExpressSampleShipment $shipment): void
    {
        $shipment->items()->withTrashed()->restore();
    }
}
