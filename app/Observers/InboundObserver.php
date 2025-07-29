<?php

namespace App\Observers;

use App\Models\Inbound;
use App\Models\Warehouse;

class InboundObserver
{
    public function creating(Inbound $inbound): void
    {
        if ($inbound->warehouse_id) {
            $warehouse = Warehouse::find($inbound->warehouse_id);
            $inbound->warehouse_name = $warehouse?->name;
        }
    }

    public function updating(Inbound $inbound): void
    {
        if ($inbound->isDirty('status')) {
            $inbound->items()->update(['inbound_status' => $inbound->status]);
        }

        if ($inbound->isDirty('warehouse_id')) {
            $warehouse = Warehouse::find($inbound->warehouse_id);
            $newWarehouseName = $warehouse?->name;

            $inbound->warehouse_name = $newWarehouseName;
            $inbound->items()->update([
                'warehouse_id' => $inbound->warehouse_id,
                'warehouse_name' => $newWarehouseName,
            ]);
        }

        if ($inbound->isDirty('inbound_date')) {
            $inbound->items()->update(['inbound_date' => $inbound->inbound_date]);
        }
    }

    public function deleting(Inbound $inbound): void
    {
        $inbound->items()->delete();
    }

    public function forceDeleting(Inbound $inbound): void
    {
        $inbound->items()->withTrashed()->forceDelete();
    }

    public function restoring(Inbound $inbound): void
    {
        $inbound->items()->withTrashed()->restore();
    }
}
