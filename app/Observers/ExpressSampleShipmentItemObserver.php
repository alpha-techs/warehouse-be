<?php

namespace App\Observers;

use App\Models\ExpressSampleShipmentItem;
use App\Models\InventoryItem;
use App\Models\Product;

class ExpressSampleShipmentItemObserver
{
    public function creating(ExpressSampleShipmentItem $item): void
    {
        $shipment = $item->expressSampleShipment;
        if ($shipment) {
            $item->shipment_status = $shipment->status;
        }

        if ($item->product_id) {
            $product = Product::find($item->product_id);
            $item->product_name = $product?->name;
        }

        if ($item->inventory_item_id) {
            $inventoryItem = InventoryItem::find($item->inventory_item_id);
            if ($inventoryItem) {
                $item->lot_number = $inventoryItem->lot_number ?? $item->lot_number;
                $item->inbound_no = $inventoryItem->inbound_order_id ?? $item->inbound_no;
                $item->inbound_date = $inventoryItem->inbound_date ?? $item->inbound_date;
                $item->inbound_item_id = $item->inbound_item_id ?: $inventoryItem->inbound_item_id;
            }
        }
    }

    public function updating(ExpressSampleShipmentItem $item): void
    {
        if ($item->isDirty('product_id')) {
            $product = Product::find($item->product_id);
            $item->product_name = $product?->name;
        }

        if ($item->isDirty('inventory_item_id')) {
            $inventoryItem = InventoryItem::find($item->inventory_item_id);
            if ($inventoryItem) {
                $item->lot_number = $inventoryItem->lot_number;
                $item->inbound_no = $inventoryItem->inbound_order_id;
                $item->inbound_date = $inventoryItem->inbound_date;
                $item->inbound_item_id = $inventoryItem->inbound_item_id;
            }
        }
    }
}

