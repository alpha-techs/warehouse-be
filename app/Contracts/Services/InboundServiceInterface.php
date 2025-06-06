<?php

namespace App\Contracts\Services;

use App\Models\Inbound;
use Illuminate\Contracts\Pagination\Paginator;

interface InboundServiceInterface
{
    public function getInboundItems(
        int $itemsPerPage = 30,
        int $page = 1,
        ?string $lotNumber = null,
    ): Paginator;

    public function createInbound(array $data): Inbound;

    public function updateInbound(int $id, array $data): Inbound;
}
