<?php

namespace App\Services;

use App\Contracts\Services\CustomerServiceInterface;
use App\Contracts\Services\InventoryServiceInterface;
use App\Models\InventoryItem;
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
}
