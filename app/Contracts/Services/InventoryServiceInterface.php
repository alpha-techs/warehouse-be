<?php

namespace App\Contracts\Services;

use Illuminate\Contracts\Pagination\Paginator;

interface InventoryServiceInterface
{
    public function getInventoryList(
        int $itemsPerPage = 30,
        int $page = 1,
        array $filters = [],
    ): Paginator;
}
