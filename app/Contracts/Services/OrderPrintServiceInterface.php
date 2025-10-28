<?php

namespace App\Contracts\Services;

use App\Models\OrderPrint;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;

interface OrderPrintServiceInterface
{
    public function getOrderPrints(
        int $itemsPerPage = 20,
        int $page = 1,
        ?string $orderNumber = null,
        ?int $customerId = null,
        ?string $status = null,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
    ): Paginator;

    public function getOrderPrint(string $id): OrderPrint;

    public function createOrderPrint(int $orderId, string $format = 'excel'): OrderPrint;
}
