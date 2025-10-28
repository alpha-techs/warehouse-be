<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\NameChange;
use App\Models\Warehouse;

class NameChangeObserver
{
    public function creating(NameChange $nameChange): void
    {
        if ($nameChange->warehouse_id) {
            $warehouse = Warehouse::find($nameChange->warehouse_id);
            $nameChange->warehouse_name = $warehouse?->name;
        }

        if ($nameChange->customer_id) {
            $customer = Customer::find($nameChange->customer_id);
            $nameChange->customer_name = $customer?->name;
        }
    }

    public function updating(NameChange $nameChange): void
    {
        if ($nameChange->isDirty('status')) {
            $nameChange->items()->update(['name_change_status' => $nameChange->status]);
        }

        if ($nameChange->isDirty('warehouse_id')) {
            $warehouse = Warehouse::find($nameChange->warehouse_id);
            $newWarehouseName = $warehouse?->name;

            $nameChange->warehouse_name = $newWarehouseName;
            $nameChange->items()->update([
                'warehouse_id' => $nameChange->warehouse_id,
                'warehouse_name' => $newWarehouseName,
            ]);
        }

        if ($nameChange->isDirty('customer_id')) {
            $customer = Customer::find($nameChange->customer_id);
            $newCustomerName = $customer?->name;

            $nameChange->customer_name = $newCustomerName;
            $nameChange->items()->update([
                'customer_id' => $nameChange->customer_id,
                'customer_name' => $newCustomerName,
            ]);
        }

        if ($nameChange->isDirty('name_change_date')) {
            $nameChange->items()->update(['name_change_date' => $nameChange->name_change_date]);
        }
    }

    public function deleting(NameChange $nameChange): void
    {
        $nameChange->items()->delete();
    }

    public function forceDeleting(NameChange $nameChange): void
    {
        $nameChange->items()->withTrashed()->forceDelete();
    }

    public function restoring(NameChange $nameChange): void
    {
        $nameChange->items()->withTrashed()->restore();
    }
}
