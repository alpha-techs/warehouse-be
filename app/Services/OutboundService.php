<?php

namespace App\Services;

use App\Contracts\Models\OutboundStatus;
use App\Contracts\Services\OutboundServiceInterface;
use App\Models\InventoryItem;
use App\Models\Outbound;
use App\Models\OutboundItem;
use Arr;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Throwable;

final class OutboundService implements OutboundServiceInterface
{
    public function getOutbounds(
        int $itemsPerPage = 30,
        int $page = 1,
        ?string $outboundOrderId = null,
        ?Carbon $outboundDateFrom = null,
        ?Carbon $outboundDateTo = null,
        ?int $warehouseId = null,
        ?int $customerId = null,
        ?string $status = null,
    ): Paginator
    {
        $query = Outbound::query()
            ->with(['items', 'warehouse', 'customer']);
        if ($outboundOrderId) {
            $query->whereOutboundOrderId($outboundOrderId);
        }
        if ($outboundDateFrom) {
            $query->whereDate('outbound_date', '>=', $outboundDateFrom);
        }
        if ($outboundDateTo) {
            $query->whereDate('outbound_date', '<=', $outboundDateTo);
        }
        if ($warehouseId) {
            $query->whereWarehouseId($warehouseId);
        }
        if ($customerId) {
            $query->whereCustomerId($customerId);
        }
        if ($status) {
            $query->whereStatus($status);
        }

        $query->orderByDesc('outbound_date');
        $query->orderByDesc('id');

        return $query->paginate($itemsPerPage, ['*'], 'page', $page);
    }

    public function getOutboundItems(
        int $itemsPerPage = 30,
        int $page = 1,
        ?string $lotNumber = null,
        ?int $productId = null,
        ?Carbon $outboundDateFrom = null,
        ?Carbon $outboundDateTo = null,
    ): Paginator
    {
        $query = OutboundItem::query()
            ->with(['product', 'outbound.warehouse'])
            ->whereOutboundStatus(OutboundStatus::PENDING);

        if ($lotNumber) {
            $query->where('lotNumber', 'like', "%$lotNumber%");
        }

        if ($productId) {
            $query->where('productId', $productId);
        }

        if ($outboundDateFrom) {
            $query->where('outboundDate', '>=', $outboundDateFrom);
        }

        if ($outboundDateTo) {
            $query->where('outboundDate', '<=', $outboundDateTo);
        }

        $query->orderByDesc('outboundDate');
        $query->orderByDesc('id');

        return $query->paginate($itemsPerPage, ['*'], 'page', $page);
    }

    /**
     * @throws Throwable
     */
    public function createOutbound(array $data): Outbound
    {
        return DB::transaction(function () use ($data) {
            $outboundData = Arr::except($data, ['items']);
            $outbound = Outbound::create($outboundData);

            $items = $data['items'];
            $outbound->items()->createMany($items);
            return $outbound;
        });
    }

    /**
     * @throws Throwable
     */
    public function updateOutbound(int $id, array $data): Outbound
    {
        return DB::transaction(function () use ($id, $data) {
            $outbound = Outbound::query()->with(['items'])->findOrFail($id);

            $outboundData = Arr::except($data, ['items']);
            /** @noinspection DuplicatedCode */
            $outbound->update($outboundData);

            $items = $data['items'];

            $existingIds = $outbound->items->pluck('id')->toArray();
            $newItemIds = collect($items)->pluck('id')->filter()->toArray();

            $toBeDeletedId = array_diff($existingIds, $newItemIds);
            $outbound->items()->whereIn('id', $toBeDeletedId)->delete();

            foreach ($items as $item) {
                if (empty($item['id'])) {
                    $outbound->items()->create($item);
                } else {
                    $oldItem = $outbound->items()->findOrFail($item['id']);
                    $oldItem->update($item);
                }
            }
            return $outbound;
        });
    }

    /**
     * @throws Throwable
     */
    public function deleteOutbound(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $outbound = Outbound::query()->findOrFail($id);
            $outbound->items()->delete();
            $outbound->delete();
            return true;
        });
    }

    /**
     * @throws Throwable
     */
    public function approveOutbound(int $id): Outbound
    {
        return DB::transaction(function () use ($id) {
            $outbound = Outbound::query()
                ->with([
                    'items.product',
                    'warehouse',
                    'customer',
                ])
                ->findOrFail($id);
            $outbound->status = OutboundStatus::APPROVED;
            $outbound->save();

            foreach ($outbound->items as $outboundItem) {
                $inventoryItemId = $outboundItem->inventory_item_id;
                $inventoryItem = InventoryItem::query()->findOrFail($inventoryItemId);
                $inventoryItem->left_quantity = $inventoryItem->left_quantity - $outboundItem->quantity;
                $inventoryItem->save();
            }

            return $outbound;
        });
    }

    /**
     * @throws Throwable
     */
    public function rejectOutbound(int $id): Outbound
    {
        return DB::transaction(function () use ($id) {
            $outbound = Outbound::query()
                ->with(['items', 'warehouse', 'customer'])
                ->findOrFail($id);
            $outbound->status = OutboundStatus::REJECTED;
            $outbound->save();
            return $outbound;
        });
    }
}
