<?php

namespace App\Contracts\Services;

use App\Models\InventoryItem;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;

interface InventoryServiceInterface
{
    public function getInventoryList(
        int $itemsPerPage = 30,
        int $page = 1,
        ?string $lotNumber = null,
        ?int $warehouseId = null,
        ?int $productId = null,
        ?Carbon $inboundDateFrom = null,
        ?Carbon $inboundDateTo = null,
    ): Paginator;

    public function getInventoryItemDetail(int $id): InventoryItem;
}
