<?php

namespace App\Contracts\Services;

use App\Models\NameChange;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;

interface NameChangeServiceInterface
{
    public function getNameChanges(
        int $itemsPerPage = 30,
        int $page = 1,
        ?string $nameChangeOrderId = null,
        ?Carbon $nameChangeDateFrom = null,
        ?Carbon $nameChangeDateTo = null,
        ?int $warehouseId = null,
        ?int $customerId = null,
        ?string $status = null,
    ): Paginator;

    public function getNameChangeItems(
        int $itemsPerPage = 30,
        int $page = 1,
        ?string $lotNumber = null,
        ?int $productId = null,
        ?Carbon $nameChangeDateFrom = null,
        ?Carbon $nameChangeDateTo = null,
    ): Paginator;

    public function createNameChange(array $data): NameChange;

    public function updateNameChange(int $id, array $data): NameChange;

    public function deleteNameChange(int $id): bool;

    public function approveNameChange(int $id): NameChange;

    public function rejectNameChange(int $id): NameChange;

    // 名义变更报告相关方法
    public function getNameChangeReportList(int $itemsPerPage = 30, int $page = 1): Paginator;

    public function getNameChangeReportDetail(int $id): \App\Models\NameChangeReport;
}
