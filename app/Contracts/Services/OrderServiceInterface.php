<?php

namespace App\Contracts\Services;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;

interface OrderServiceInterface
{
    public function getOrders(
        int $itemsPerPage = 20,
        int $page = 1,
        ?string $orderNumber = null,
        ?int $customerId = null,
        ?string $status = null,
        ?Carbon $deliveryDueStart = null,
        ?Carbon $deliveryDueEnd = null,
    ): Paginator;

    public function createOrder(array $data): Order;

    public function getOrder(int $id): Order;

    public function updateOrder(int $id, array $data): Order;

    public function cancelOrder(int $id): Order;

    public function submitOrder(int $id): Order;

    public function sendOrder(int $id): Order;

    public function completeOrder(int $id): Order;
}
