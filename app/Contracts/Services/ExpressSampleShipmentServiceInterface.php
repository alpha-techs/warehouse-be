<?php

namespace App\Contracts\Services;

use App\Models\ExpressSampleShipment;
use App\Models\ExpressSampleShipmentItem;
use App\Models\ExpressSampleShipmentReport;
use Illuminate\Contracts\Pagination\Paginator;

interface ExpressSampleShipmentServiceInterface
{
    public function getExpressSampleShipments(
        int $itemsPerPage = 30,
        int $page = 1,
        ?string $status = null,
        ?int $customerId = null,
        ?\Carbon\CarbonInterface $desiredDeliveryDateFrom = null,
        ?\Carbon\CarbonInterface $desiredDeliveryDateTo = null,
    ): Paginator;

    public function getExpressSampleShipmentItems(
        int $itemsPerPage = 30,
        int $page = 1,
        ?int $expressSampleShipmentId = null,
        ?int $productId = null,
        ?\Carbon\CarbonInterface $desiredDeliveryDate = null,
    ): Paginator;

    public function getExpressSampleShipment(int $id): ExpressSampleShipment;

    public function createExpressSampleShipment(array $data): ExpressSampleShipment;

    public function updateExpressSampleShipment(int $id, array $data): ExpressSampleShipment;

    public function deleteExpressSampleShipment(int $id): bool;

    public function approveExpressSampleShipment(int $id): ExpressSampleShipment;

    public function rejectExpressSampleShipment(int $id): ExpressSampleShipment;

    public function cancelExpressSampleShipment(int $id): ExpressSampleShipment;

    public function getExpressSampleShipmentReportList(
        int $itemsPerPage = 30,
        int $page = 1,
        ?int $warehouseId = null,
        ?int $customerId = null,
        ?string $status = null,
        ?\Carbon\CarbonInterface $startDate = null,
        ?\Carbon\CarbonInterface $endDate = null,
    ): Paginator;

    public function getExpressSampleShipmentReport(int $id): ExpressSampleShipmentReport;

    public function createExpressSampleShipmentReport(int $shipmentId, string $format = 'excel'): ExpressSampleShipmentReport;
}

