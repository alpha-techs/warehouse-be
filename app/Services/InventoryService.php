<?php

namespace App\Services;

use App\Contracts\Services\CustomerServiceInterface;
use App\Contracts\Services\InventoryServiceInterface;
use App\Models\InventoryItem;
use Illuminate\Contracts\Pagination\Paginator;

final class InventoryService implements InventoryServiceInterface
{
    public function getInventoryList(int $itemsPerPage = 30, int $page = 1, array $filters = []): Paginator
    {
        $query = InventoryItem::query()->with(['warehouse', 'product']);
        $query->whereCustomerId(CustomerServiceInterface::MARUOKA_JAPAN_CUSTOMER_ID);
        if ($filters['warehouseId'] ?? null) {
            $query->whereWarehouseId($filters['warehouseId']);
        }
        if ($filters['lotNumber']  ?? null) {
            $query->whereLotNumber($filters['lotNumber']);
        }
        if ($filters['inboundDateFrom'] ?? null) {
            $query->where('inbound_date', '>=', $filters['inboundDateFrom']);
        }
        if ($filters['inboundDateTo']  ?? null) {
            $query->where('inbound_date', '<=', $filters['inboundDateTo']);
        }
        if ($filters['productId'] ?? null) {
            $query->whereProductId($filters['productId']);
        }
        return $query->paginate($itemsPerPage, ['*'], 'page', $page);
    }
}
