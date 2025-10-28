<?php

namespace App\Services;

use App\Contracts\Models\NameChangeStatus;
use App\Contracts\Services\NameChangeServiceInterface;
use App\Models\InventoryItem;
use App\Models\NameChange;
use App\Models\NameChangeItem;
use App\Models\NameChangeReport;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

final class NameChangeService implements NameChangeServiceInterface
{
    public function getNameChanges(
        int $itemsPerPage = 30,
        int $page = 1,
        ?string $nameChangeOrderId = null,
        ?Carbon $nameChangeDateFrom = null,
        ?Carbon $nameChangeDateTo = null,
        ?int $warehouseId = null,
        ?int $customerId = null,
        ?string $status = null,
    ): Paginator
    {
        $query = NameChange::query()
            ->with(['items.product', 'warehouse', 'customer']);

        if ($nameChangeOrderId) {
            $query->whereNameChangeOrderId($nameChangeOrderId);
        }

        if ($nameChangeDateFrom) {
            $query->whereDate('name_change_date', '>=', $nameChangeDateFrom);
        }

        if ($nameChangeDateTo) {
            $query->whereDate('name_change_date', '<=', $nameChangeDateTo);
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

        $query->orderByDesc('name_change_date');
        $query->orderByDesc('id');

        return $query->paginate($itemsPerPage, ['*'], 'page', $page);
    }

    public function getNameChangeItems(
        int $itemsPerPage = 30,
        int $page = 1,
        ?string $lotNumber = null,
        ?int $productId = null,
        ?Carbon $nameChangeDateFrom = null,
        ?Carbon $nameChangeDateTo = null,
    ): Paginator
    {
        $query = NameChangeItem::query()
            ->with(['product', 'nameChange.warehouse'])
            ->whereNameChangeStatus(NameChangeStatus::APPROVED);

        if ($lotNumber) {
            $query->whereLotNumber($lotNumber);
        }

        if ($productId) {
            $query->whereProductId($productId);
        }

        if ($nameChangeDateFrom) {
            $query->whereDate('name_change_date', '>=', $nameChangeDateFrom);
        }

        if ($nameChangeDateTo) {
            $query->whereDate('name_change_date', '<=', $nameChangeDateTo);
        }

        $query->orderByDesc('name_change_date');
        $query->orderByDesc('id');

        return $query->paginate($itemsPerPage, ['*'], 'page', $page);
    }

    /**
     * @throws Throwable
     */
    public function createNameChange(array $data): NameChange
    {
        return DB::transaction(function () use ($data) {
            $nameChangeData = Arr::except($data, ['items']);
            $nameChange = NameChange::create($nameChangeData);

            $items = $data['items'];
            $nameChange->items()->createMany($items);
            return $nameChange;
        });
    }

    /**
     * @throws Throwable
     */
    public function updateNameChange(int $id, array $data): NameChange
    {
        return DB::transaction(function () use ($id, $data) {
            $nameChange = NameChange::query()->findOrFail($id);

            $nameChangeData = Arr::except($data, ['items']);
            $nameChange->update($nameChangeData);

            $items = $data['items'];

            $existingIds = $nameChange->items->pluck('id')->toArray();
            $newItemIds = collect($items)->pluck('id')->filter()->toArray();

            $toBeDeletedId = array_diff($existingIds, $newItemIds);
            $nameChange->items()->whereIn('id', $toBeDeletedId)->delete();

            foreach ($items as $item) {
                if (empty($item['id'])) {
                    $nameChange->items()->create($item);
                } else {
                    $oldItem = $nameChange->items()->findOrFail($item['id']);
                    $oldItem->update($item);
                }
            }
            return $nameChange;
        });
    }

    /**
     * @throws Throwable
     */
    public function deleteNameChange(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $nameChange = NameChange::query()->findOrFail($id);
            $nameChange->items()->delete();
            $nameChange->delete();
            return true;
        });
    }

    /**
     * @throws Throwable
     */
    public function approveNameChange(int $id): NameChange
    {
        return DB::transaction(function () use ($id) {
            $nameChange = NameChange::query()
                ->with([
                    'items.product',
                    'warehouse',
                    'customer',
                ])
                ->findOrFail($id);
            $nameChange->status = NameChangeStatus::APPROVED;
            $nameChange->save();

            // 减少对应的物品在库数量
            foreach ($nameChange->items as $nameChangeItem) {
                $inventoryItemId = $nameChangeItem->inventory_item_id;
                $inventoryItem = InventoryItem::query()->findOrFail($inventoryItemId);
                
                // 减少在库数量
                $inventoryItem->left_quantity = $inventoryItem->left_quantity - $nameChangeItem->quantity;
                $inventoryItem->save();
            }

            return $nameChange;
        });
    }

    /**
     * @throws Throwable
     */
    public function rejectNameChange(int $id): NameChange
    {
        return DB::transaction(function () use ($id) {
            $nameChange = NameChange::query()
                ->with(['items', 'warehouse', 'customer'])
                ->findOrFail($id);
            $nameChange->status = NameChangeStatus::REJECTED;
            $nameChange->save();
            return $nameChange;
        });
    }

    // 名义变更报告相关方法
    public function getNameChangeReportList(int $itemsPerPage = 30, int $page = 1): Paginator
    {
        $query = NameChangeReport::query()
            ->with(['nameChange', 'warehouse', 'customer'])
            ->orderByDesc('id');
        return $query->paginate($itemsPerPage, ['*'], 'page', $page);
    }

    public function getNameChangeReportDetail(int $id): NameChangeReport
    {
        return NameChangeReport::query()
            ->with(['nameChange', 'warehouse', 'customer'])
            ->findOrFail($id);
    }
}
