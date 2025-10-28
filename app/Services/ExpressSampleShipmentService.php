<?php

namespace App\Services;

use App\Contracts\Models\ExpressSampleShipmentStatus;
use App\Contracts\Services\ExpressSampleShipmentServiceInterface;
use App\Jobs\GenerateExpressSampleShipmentReportJob;
use App\Models\ExpressSampleShipment;
use App\Models\ExpressSampleShipmentItem;
use App\Models\ExpressSampleShipmentReport;
use App\Models\InventoryItem;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

final class ExpressSampleShipmentService implements ExpressSampleShipmentServiceInterface
{
    public function getExpressSampleShipments(
        int $itemsPerPage = 30,
        int $page = 1,
        ?string $status = null,
        ?int $customerId = null,
        ?CarbonInterface $desiredDeliveryDateFrom = null,
        ?CarbonInterface $desiredDeliveryDateTo = null,
    ): Paginator {
        $query = ExpressSampleShipment::query()
            ->with([
                'warehouse',
                'customer',
                'customerContact',
                'items.product',
                'items.inventoryItem',
            ]);

        if ($status) {
            $query->whereStatus($status);
        }

        if ($customerId) {
            $query->whereCustomerId($customerId);
        }

        if ($desiredDeliveryDateFrom) {
            $query->whereDate('desired_delivery_date', '>=', $desiredDeliveryDateFrom);
        }

        if ($desiredDeliveryDateTo) {
            $query->whereDate('desired_delivery_date', '<=', $desiredDeliveryDateTo);
        }

        $query->orderByDesc('requested_ship_date');
        $query->orderByDesc('id');

        return $query->paginate($itemsPerPage, ['*'], 'page', $page);
    }

    public function getExpressSampleShipmentItems(
        int $itemsPerPage = 30,
        int $page = 1,
        ?int $expressSampleShipmentId = null,
        ?int $productId = null,
        ?CarbonInterface $desiredDeliveryDate = null,
    ): Paginator {
        $query = ExpressSampleShipmentItem::query()
            ->with([
                'product',
                'inventoryItem',
                'expressSampleShipment.warehouse',
                'expressSampleShipment.customer',
            ])
            ->whereShipmentStatus(ExpressSampleShipmentStatus::APPROVED->value);

        if ($expressSampleShipmentId) {
            $query->whereExpressSampleShipmentId($expressSampleShipmentId);
        }

        if ($productId) {
            $query->whereProductId($productId);
        }

        if ($desiredDeliveryDate) {
            $query->whereHas('expressSampleShipment', function ($shipmentQuery) use ($desiredDeliveryDate) {
                $shipmentQuery->whereDate('desired_delivery_date', '=', $desiredDeliveryDate);
            });
        }

        $query->orderByDesc('created_at');
        $query->orderByDesc('id');

        return $query->paginate($itemsPerPage, ['*'], 'page', $page);
    }

    public function getExpressSampleShipment(int $id): ExpressSampleShipment
    {
        return ExpressSampleShipment::query()
            ->with([
                'warehouse',
                'customer',
                'customerContact',
                'items.product',
                'items.inventoryItem',
            ])
            ->findOrFail($id);
    }

    /**
     * @throws Throwable
     */
    public function createExpressSampleShipment(array $data): ExpressSampleShipment
    {
        $payload = $this->normalizeShipmentData($data);
        $items = $payload['items'] ?? [];
        unset($payload['items']);

        return DB::transaction(function () use ($payload, $items) {
            $shipment = ExpressSampleShipment::create($payload);

            if (!empty($items)) {
                $shipment->items()->createMany($items);
            }

            return $shipment->load([
                'warehouse',
                'customer',
                'customerContact',
                'items.product',
                'items.inventoryItem',
            ]);
        });
    }

    /**
     * @throws Throwable
     */
    public function updateExpressSampleShipment(int $id, array $data): ExpressSampleShipment
    {
        $payload = $this->normalizeShipmentData($data);
        $items = $payload['items'] ?? [];
        unset($payload['items']);

        return DB::transaction(function () use ($id, $payload, $items) {
            $shipment = ExpressSampleShipment::query()
                ->with('items')
                ->findOrFail($id);

            $shipment->update($payload);

            $this->syncShipmentItems($shipment, $items);

            return $shipment->load([
                'warehouse',
                'customer',
                'customerContact',
                'items.product',
                'items.inventoryItem',
            ]);
        });
    }

    /**
     * @throws Throwable
     */
    public function deleteExpressSampleShipment(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $shipment = ExpressSampleShipment::query()
                ->with('items')
                ->findOrFail($id);

            $shipment->items()->delete();
            $shipment->delete();

            return true;
        });
    }

    /**
     * @throws Throwable
     */
    public function approveExpressSampleShipment(int $id): ExpressSampleShipment
    {
        return DB::transaction(function () use ($id) {
            $shipment = ExpressSampleShipment::query()
                ->with([
                    'items.inventoryItem',
                    'items.product',
                    'warehouse',
                    'customer',
                ])
                ->findOrFail($id);

            $shipment->status = ExpressSampleShipmentStatus::APPROVED;
            $shipment->save();

            foreach ($shipment->items as $item) {
                $inventoryItem = InventoryItem::query()->findOrFail($item->inventory_item_id);
                $inventoryItem->left_quantity = max(
                    0,
                    (int) $inventoryItem->left_quantity - (int) $item->quantity
                );
                $inventoryItem->save();
            }

            return $shipment->fresh([
                'items.inventoryItem',
                'items.product',
                'warehouse',
                'customer',
            ]);
        });
    }

    /**
     * @throws Throwable
     */
    public function rejectExpressSampleShipment(int $id): ExpressSampleShipment
    {
        return DB::transaction(function () use ($id) {
            $shipment = ExpressSampleShipment::query()
                ->with(['items', 'warehouse', 'customer'])
                ->findOrFail($id);

            $shipment->status = ExpressSampleShipmentStatus::REJECTED;
            $shipment->save();

            return $shipment;
        });
    }

    /**
     * @throws Throwable
     */
    public function cancelExpressSampleShipment(int $id): ExpressSampleShipment
    {
        return DB::transaction(function () use ($id) {
            $shipment = ExpressSampleShipment::query()
                ->with(['items', 'warehouse', 'customer'])
                ->findOrFail($id);

            $shipment->status = ExpressSampleShipmentStatus::CANCELLED;
            $shipment->save();

            return $shipment;
        });
    }

    public function getExpressSampleShipmentReportList(
        int $itemsPerPage = 30,
        int $page = 1,
        ?int $warehouseId = null,
        ?int $customerId = null,
        ?string $status = null,
        ?CarbonInterface $startDate = null,
        ?CarbonInterface $endDate = null,
    ): Paginator {
        $query = ExpressSampleShipmentReport::query()
            ->with(['expressSampleShipment', 'warehouse', 'customer'])
            ->orderByDesc('created_at');

        if ($warehouseId) {
            $query->whereWarehouseId($warehouseId);
        }

        if ($customerId) {
            $query->whereCustomerId($customerId);
        }

        if ($status) {
            $query->whereStatus($status);
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return $query->paginate($itemsPerPage, ['*'], 'page', $page);
    }

    public function getExpressSampleShipmentReport(int $id): ExpressSampleShipmentReport
    {
        return ExpressSampleShipmentReport::query()
            ->with(['expressSampleShipment', 'warehouse', 'customer'])
            ->findOrFail($id);
    }

    /**
     * @throws Throwable
     */
    public function createExpressSampleShipmentReport(int $shipmentId, string $format = 'excel'): ExpressSampleShipmentReport
    {
        $shipment = ExpressSampleShipment::query()
            ->with(['warehouse', 'customer'])
            ->findOrFail($shipmentId);

        return DB::transaction(function () use ($shipment, $format) {
            $report = ExpressSampleShipmentReport::create([
                'expressSampleShipmentId' => $shipment->id,
                'warehouseId' => $shipment->warehouse_id,
                'warehouseName' => $shipment->warehouse_name,
                'customerId' => $shipment->customer_id,
                'customerName' => $shipment->customer_name,
                'format' => $format,
                'status' => 'pending',
                'storage' => ExpressSampleShipmentReport::defaultStorageType(),
            ]);

            GenerateExpressSampleShipmentReportJob::dispatch($report->id);

            return $report->load(['expressSampleShipment', 'warehouse', 'customer']);
        });
    }

    private function normalizeShipmentData(array $data): array
    {
        $status = Arr::get($data, 'status', ExpressSampleShipmentStatus::PENDING->value);

        $normalized = Arr::only($data, [
            'expressSampleOrderId',
            'requestedShipDate',
            'desiredDeliveryDate',
            'desiredDeliveryTimeWindow',
            'deliveryService',
            'packageCount',
            'packageType',
            'deliveryFeePayer',
            'samplePurpose',
            'carrierName',
            'trackingNumber',
            'note',
            'dispatchedAt',
            'deliveredAt',
        ]);
        $normalized['status'] = $status;
        $normalized['warehouseId'] = Arr::get($data, 'warehouse.id');
        $normalized['customerId'] = Arr::get($data, 'customer.id');
        $normalized['customerContactId'] = Arr::get($data, 'customerContactId');
        $normalized['recipientCompanyName'] = Arr::get($data, 'recipient.companyName');
        $normalized['recipientDepartment'] = Arr::get($data, 'recipient.department');
        $normalized['recipientName'] = Arr::get($data, 'recipient.name');
        $normalized['recipientPhoneNumber'] = Arr::get($data, 'recipient.phoneNumber');
        $normalized['recipientPostalCode'] = Arr::get($data, 'recipient.postalCode');
        $normalized['recipientPrefecture'] = Arr::get($data, 'recipient.prefecture');
        $normalized['recipientCity'] = Arr::get($data, 'recipient.city');
        $normalized['recipientAddressLine1'] = Arr::get($data, 'recipient.addressLine1');
        $normalized['recipientAddressLine2'] = Arr::get($data, 'recipient.addressLine2');
        $normalized['emergencyContactName'] = Arr::get($data, 'emergencyContact.name');
        $normalized['emergencyContactPhoneNumber'] = Arr::get($data, 'emergencyContact.phoneNumber');
        $normalized['items'] = $this->normalizeShipmentItems(Arr::get($data, 'items', []));

        if (array_key_exists('packageCount', $normalized) && !is_null($normalized['packageCount'])) {
            $normalized['packageCount'] = (int) $normalized['packageCount'];
        }

        return array_filter(
            $normalized,
            static fn ($value) => !is_null($value)
        );
    }

    private function normalizeShipmentItems(array $items): array
    {
        return collect($items)
            ->map(function (array $item) {
                return array_filter([
                    'id' => Arr::get($item, 'id'),
                    'inventoryItemId' => Arr::get($item, 'inventoryItemId'),
                    'inboundItemId' => Arr::get($item, 'inboundItemId'),
                    'productId' => Arr::get($item, 'product.id'),
                    'quantity' => Arr::get($item, 'quantity') !== null
                        ? (int) Arr::get($item, 'quantity')
                        : null,
                    'quantityUnit' => Arr::get($item, 'quantityUnit'),
                    'samplePackaging' => Arr::get($item, 'samplePackaging'),
                    'lotNumber' => Arr::get($item, 'lotNumber'),
                    'note' => Arr::get($item, 'note'),
                ], static fn ($value) => !is_null($value));
            })
            ->values()
            ->all();
    }

    private function syncShipmentItems(ExpressSampleShipment $shipment, array $items): void
    {
        $existingItems = $shipment->items;
        $existingIds = $existingItems->pluck('id')->filter()->all();
        $incoming = collect($items);
        $incomingIds = $incoming->pluck('id')->filter()->all();

        $toDelete = array_diff($existingIds, $incomingIds);
        if (!empty($toDelete)) {
            $shipment->items()->whereIn('id', $toDelete)->delete();
        }

        /** @var Collection<int, array> $incoming */
        foreach ($incoming as $itemData) {
            $itemId = Arr::get($itemData, 'id');
            if ($itemId) {
                $itemModel = $shipment->items()->findOrFail($itemId);
                $itemModel->update(Arr::except($itemData, ['id']));
            } else {
                $shipment->items()->create(Arr::except($itemData, ['id']));
            }
        }
    }
}
