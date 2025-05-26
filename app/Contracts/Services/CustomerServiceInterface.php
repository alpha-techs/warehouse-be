<?php

namespace App\Contracts\Services;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\Paginator;

interface CustomerServiceInterface
{
    public const MARUOKA_JAPAN_CUSTOMER_ID = 1;

    public function getCustomers(
        int $itemsPerPage = 30,
        int $page = 1,
        ?string $name = null,
    ): Paginator;

    public function getCustomer(int $id): Customer;

    public function createCustomer(array $data): Customer;

    public function updateCustomer(int $id, array $data): Customer;

    public function deleteCustomer(int $id): bool;
}
