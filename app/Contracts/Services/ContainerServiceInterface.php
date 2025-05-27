<?php

namespace App\Contracts\Services;

use App\Models\Container;
use Illuminate\Contracts\Pagination\Paginator;

interface ContainerServiceInterface
{
    public function getContainers(
        int $itemsPerPage = 30,
        int $page = 1,
        ?string $containerNumber = null,
        ?array $statuses = null,
    ): Paginator;

    public function getContainer(int $id): Container;

    public function createContainer(array $data): Container;

    public function updateContainer(int $id, array $data): Container;

    public function deleteContainer(int $id): bool;
}
