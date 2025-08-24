<?php

namespace App\Services;

use App\Contracts\Services\CustomerServiceInterface;
use App\Contracts\Services\InventoryServiceInterface;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\InventoryReport;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;

final class InventoryService implements InventoryServiceInterface
{
    public function getInventoryList(
        int $itemsPerPage = 30,
        int $page = 1,
        ?string $lotNumber = null,
        ?int $warehouseId = null,
        ?int $productId = null,
        ?Carbon $inboundDateFrom = null,
        ?Carbon $inboundDateTo = null,
    ): Paginator
    {
        $query = InventoryItem::query()->with(['warehouse', 'product']);
        $query->whereCustomerId(CustomerServiceInterface::MARUOKA_JAPAN_CUSTOMER_ID);
        if ($lotNumber) {
            $query->whereLotNumber($lotNumber);
        }
        if ($warehouseId) {
            $query->whereWarehouseId($warehouseId);
        }
        if ($inboundDateFrom) {
            $query->where('inbound_date', '>=', $inboundDateFrom);
        }
        if ($inboundDateTo) {
            $query->where('inbound_date', '<=', $inboundDateTo);
        }
        if ($productId) {
            $query->whereProductId($productId);
        }
        return $query->paginate($itemsPerPage, ['*'], 'page', $page);
    }

    public function getInventoryItemDetail(int $id): InventoryItem
    {
        return InventoryItem::query()
            ->with([
                'warehouse',
                'product',
                'inboundItem.inbound',
                'outboundItems.outbound',
            ])
            ->findOrFail($id);
    }

    public function getAgedItems(
        int $itemsPerPage = 30,
        int $page = 1,
    ): Paginator
    {
        $agedDate = Carbon::now()->subMonths(3)->toDateString();

        $query = InventoryItem::query()
            ->with(['warehouse', 'product'])
            ->where('left_quantity', '>', 0)
            ->where('inbound_date', '<', $agedDate)
            ->whereMuted(false);

        return $query->paginate($itemsPerPage, ['*'], 'page', $page);
    }

    public function muteItem(int $id): InventoryItem
    {
        $item = InventoryItem::query()->findOrFail($id);
        $item->muted = true;
        $item->save();
        return $item;
    }

    public function getInventoryDataForReportData(int $warehouseId, int $customerId): array
    {
        $warehouse = Warehouse::query()->findOrFail($warehouseId);
        $customer = Customer::query()->findOrFail($customerId);

        $inventoryItems = InventoryItem::query()
            ->with(['product'])
            ->where('warehouse_id', $warehouseId)
            ->where('customer_id', $customerId)
            ->where('left_quantity', '>', 0)
            ->orderBy('inbound_date')
            ->orderBy('product_id')
            ->orderBy('lot_number')
            ->get();

        $totalQuantity = $inventoryItems->sum('left_quantity');

        return [
            'inventoryItems' => $inventoryItems,
            'totalQuantity' => $totalQuantity,
            'warehouse' => $warehouse,
            'customer' => $customer,
        ];
    }

    public function getReportList(int $itemsPerPage = 30, int $page = 1): Paginator
    {
        $query = InventoryReport::query()
            ->with(['warehouse', 'customer'])
            ->orderByDesc('id');
        return $query->paginate($itemsPerPage, ['*'], 'page', $page);
    }

    public function getReportDetail(int $id): InventoryReport
    {
        return InventoryReport::query()
            ->with(['warehouse', 'customer'])
            ->findOrFail($id);
    }
}
