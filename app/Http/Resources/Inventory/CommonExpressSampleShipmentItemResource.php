<?php

namespace App\Http\Resources\Inventory;

use App\Http\Resources\BaseResource;
use App\Models\InventoryItem;
use App\Models\Product;
use DateTimeInterface;

class CommonExpressSampleShipmentItemResource extends BaseResource
{
    protected function compose(): array
    {
        /** @var \App\Models\ExpressSampleShipmentItem $item */
        $item = $this->resource;

        /** @var InventoryItem|null $inventoryItem */
        $inventoryItem = $item->inventoryItem ?? null;
        $inboundDate = $inventoryItem?->inbound_date;
        if ($inboundDate instanceof DateTimeInterface) {
            $inboundDate = $inboundDate->format('Y-m-d');
        }

        /** @var Product|null $product */
        $product = $item->product ?? null;

        return [
            'id' => $item->id,
            'express_sample_shipment_id' => $item->express_sample_shipment_id,
            'inventory_item_id' => $item->inventory_item_id,
            'inventory_item' => $inventoryItem ? [
                'id' => $inventoryItem->id,
                'lot_number' => $inventoryItem->lot_number,
                'inbound_no' => $inventoryItem->inbound_order_id,
                'inbound_date' => $inboundDate,
            ] : null,
            'product' => $product ? [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'specification' => $product->dimension_description,
            ] : null,
            'quantity' => (int) $item->quantity,
            'quantity_unit' => $item->quantity_unit,
            'sample_packaging' => $item->sample_packaging,
            'lot_number' => $item->lot_number,
            'note' => $item->note,
        ];
    }
}

