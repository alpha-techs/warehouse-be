<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\Outbound;
use App\Models\Warehouse;

class OutboundObserver
{
    public function creating(Outbound $outbound): void
    {
        if ($outbound->warehouse_id) {
            $warehouse = Warehouse::find($outbound->warehouse_id);
            $outbound->warehouse_name = $warehouse?->name;
        }

        if ($outbound->customer_id) {
            $customer = Customer::find($outbound->customer_id);
            $outbound->customer_name = $customer?->name;
        }
    }

    public function updating(Outbound $outbound): void
    {
        if ($outbound->isDirty('status')) {
            $outbound->items()->update(['outbound_status' => $outbound->status]);
        }

        if ($outbound->isDirty('warehouse_id')) {
            $warehouse = Warehouse::find($outbound->warehouse_id);
            $newWarehouseName = $warehouse?->name;

            $outbound->warehouse_name = $newWarehouseName;
            $outbound->items()->update([
                'warehouse_id' => $outbound->warehouse_id,
                'warehouse_name' => $newWarehouseName,
            ]);
        }

        if ($outbound->isDirty('customer_id')) {
            $customer = Customer::find($outbound->customer_id);
            $newCustomerName = $customer?->name;

            $outbound->customer_name = $newCustomerName;
            $outbound->items()->update([
                'customer_id' => $outbound->customer_id,
                'customer_name' => $newCustomerName,
            ]);
        }

        if ($outbound->isDirty('outbound_date')) {
            $outbound->items()->update(['outbound_date' => $outbound->outbound_date]);
        }
    }

    public function deleting(Outbound $outbound): void
    {
        $outbound->items()->delete();
    }

    public function forceDeleting(Outbound $outbound): void
    {
        $outbound->items()->withTrashed()->forceDelete();
    }

    public function restoring(Outbound $outbound): void
    {
        $outbound->items()->withTrashed()->restore();
    }
}
