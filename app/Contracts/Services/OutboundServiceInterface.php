<?php

namespace App\Contracts\Services;

use App\Models\Outbound;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;

interface OutboundServiceInterface
{
    public function getOutboundItems(
        int $itemsPerPage = 30,
        int $page = 1,
        ?string $lotNumber = null,
        ?int $productId = null,
        ?Carbon $outboundDateFrom = null,
        ?Carbon $outboundDateTo = null,
    ): Paginator;

    public function createOutbound(array $data): Outbound;

    public function updateOutbound(int $id, array $data): Outbound;
}
