<?php

namespace App\Observers;

use App\Models\Outbound;
use App\Models\Warehouse;

class OutboundObserver
{
    public function creating(Outbound $outbound): void
    {
        if ($outbound->warehouse_id) {
            $warehouse = Warehouse::find($outbound->warehouse_id);
            if ($warehouse) {
                $outbound->warehouse_name = $warehouse->id;
            }
        }
    }
}
