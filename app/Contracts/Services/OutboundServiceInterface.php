<?php

namespace App\Contracts\Services;

use App\Models\Outbound;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;

interface OutboundServiceInterface
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
    ): Paginator;

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

    public function deleteOutbound(int $id): bool;

    public function approveOutbound(int $id): Outbound;

    public function rejectOutbound(int $id): Outbound;
}
