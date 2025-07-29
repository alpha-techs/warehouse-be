<?php

namespace App\Services;

use App\Contracts\Models\InboundStatus;
use App\Contracts\Services\InboundServiceInterface;
use App\Models\Inbound;
use App\Models\InboundItem;
use App\Models\InventoryItem;
use Arr;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Throwable;

final class InboundService implements InboundServiceInterface
{

    public function getInboundItems(
        int $itemsPerPage = 30,
        int $page = 1,
        ?string $lotNumber = null,
        ?int $productId = null,
        ?Carbon $inboundDateFrom = null,
        ?Carbon $inboundDateTo = null,
    ): Paginator
    {
        $query = InboundItem::query()
            ->with(['product', 'inbound.warehouse'])
            ->whereInboundStatus(InboundStatus::APPROVED);
        if ($lotNumber) {
            $query->whereLotNumber($lotNumber);
        }
        if ($productId) {
            $query->whereProductId($productId);
        }
        if ($inboundDateFrom) {
            $query->whereDate('inbound_date', '>=', $inboundDateFrom);
        }
        if ($inboundDateTo) {
            $query->whereDate('inbound_date', '<=', $inboundDateTo);
        }
        return $query->paginate($itemsPerPage, ['*'], 'page', $page);
    }


    /**
     * @throws Throwable
     */
    public function createInbound(array $data): Inbound
    {
        return DB::transaction(function () use ($data) {
            $inboundData = Arr::except($data, ['items']);
            $inbound = Inbound::create($inboundData);

            $items = $data['items'];
            $inbound->items()->createMany($items);
            return  $inbound;
        });
    }

    /**
     * @throws Throwable
     */
    public function updateInbound(int $id, array $data): Inbound
    {
        return DB::transaction(function () use ($id, $data) {
            $inboundData = Arr::except($data, ['items']);

            $inbound = Inbound::query()->with(['items'])->findOrFail($id);
            /** @noinspection DuplicatedCode */
            $inbound->update($inboundData);

            $items = $data['items'];

            $existingIds = $inbound->items->pluck('id')->toArray();
            $newItemIds = collect($items)->pluck('id')->filter()->toArray();

            $toBeDeletedId = array_diff($existingIds, $newItemIds);

            $inbound->items()->whereIn('id', $toBeDeletedId)->delete();
            foreach ($items as $item) {
                if (empty($item['id'])) {
                    $inbound->items()->create($item);
                } else {
                    $oldItem = $inbound->items()->findOrFail($item['id']);
                    $oldItem->update($item);
                }
            }
            return  $inbound;
        });
    }

    /**
     * @throws Throwable
     */
    public function deleteInbound(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $inbound = Inbound::query()->findOrFail($id);
            $inbound->items()->delete();
            $inbound->delete();
            return true;
        });
    }

    /**
     * @throws Throwable
     */
    public function approveInbound(int $id): Inbound
    {
        return DB::transaction(function () use ($id, &$inbound) {
            $inbound = Inbound::query()
                ->with([
                    'items.product',
                    'warehouse',
                    'customer',
                ])
                ->findOrFail($id);

            $inbound->status = InboundStatus::APPROVED;
            $inbound->save();

            foreach ($inbound->items as $inboundItem) {
                $inventoryItem = new InventoryItem();
                $inventoryItem->warehouse_id = $inbound->warehouse_id;
                $inventoryItem->warehouse_name = $inbound->warehouse_name;
                $inventoryItem->customer_id = $inbound->customer_id;
                $inventoryItem->customer_name = $inbound->customer_name;
                $inventoryItem->inbound_order_id = $inbound->inbound_order_id;
                $inventoryItem->inbound_id = $inbound->id;
                $inventoryItem->inbound_item_id = $inboundItem->id;
                $inventoryItem->inbound_date = $inbound->inbound_date;
                $inventoryItem->product_id = $inboundItem->product_id;
                $inventoryItem->product_name = $inboundItem->product_name;
                $inventoryItem->per_item_weight  = $inboundItem->per_item_weight;
                $inventoryItem->per_item_weight_unit  = $inboundItem->per_item_weight_unit;
                $inventoryItem->total_weight  = $inboundItem->total_weight;
                $inventoryItem->manufacture_date  = $inboundItem->manufacture_date;
                $inventoryItem->best_before_date  = $inboundItem->best_before_date;
                $inventoryItem->lot_number  = $inboundItem->lot_number;
                $inventoryItem->ship_name  = $inboundItem->ship_name;
                $inventoryItem->inbound_quantity  = $inboundItem->quantity;
                $inventoryItem->left_quantity  = $inboundItem->quantity;
                $inventoryItem->left_sub_quantity  = 0;

                $inventoryItem->save();
            }

            return $inbound;
        });
    }

    /**
     * @throws Throwable
     */
    public function rejectInbound(int $id): Inbound
    {
        return DB::transaction(function () use ($id) {
            $inbound = Inbound::query()
                ->with(['items.product', 'warehouse', 'customer'])
                ->findOrFail($id);
            $inbound->inboundStatus = InboundStatus::REJECTED;
            $inbound->save();
            return $inbound;
        });
    }
}
