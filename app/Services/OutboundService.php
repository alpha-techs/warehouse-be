<?php

namespace App\Services;

use App\Contracts\Models\OutboundStatus;
use App\Contracts\Services\OutboundServiceInterface;
use App\Models\InventoryItem;
use App\Models\Outbound;
use App\Models\OutboundItem;
use App\Models\OutboundReport;
use Arr;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Throwable;

final class OutboundService implements OutboundServiceInterface
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
    ): Paginator
    {
        $query = Outbound::query()
            ->with(['items', 'warehouse', 'customer']);
        if ($outboundOrderId) {
            $query->whereOutboundOrderId($outboundOrderId);
        }
        if ($outboundDateFrom) {
            $query->whereDate('outbound_date', '>=', $outboundDateFrom);
        }
        if ($outboundDateTo) {
            $query->whereDate('outbound_date', '<=', $outboundDateTo);
        }
        if ($warehouseId) {
            $query->whereWarehouseId($warehouseId);
        }
        if ($customerId) {
            $query->whereCustomerId($customerId);
        }
        if ($status) {
            $query->whereStatus($status);
        }

        $query->orderByDesc('outbound_date');
        $query->orderByDesc('id');

        return $query->paginate($itemsPerPage, ['*'], 'page', $page);
    }

    public function getOutboundItems(
        int $itemsPerPage = 30,
        int $page = 1,
        ?string $lotNumber = null,
        ?int $productId = null,
        ?Carbon $outboundDateFrom = null,
        ?Carbon $outboundDateTo = null,
    ): Paginator
    {
        $query = OutboundItem::query()
            ->with(['product', 'outbound.warehouse'])
            ->whereOutboundStatus(OutboundStatus::APPROVED);

        if ($lotNumber) {
            $query->whereLotNumber($lotNumber);
        }

        if ($productId) {
            $query->whereProductId($productId);
        }

        if ($outboundDateFrom) {
            $query->whereDate('outbound_date', '>=', $outboundDateFrom);
        }

        if ($outboundDateTo) {
            $query->whereDate('outbound_date', '<=', $outboundDateTo);
        }

        $query->orderByDesc('outbound_date');
        $query->orderByDesc('id');

        return $query->paginate($itemsPerPage, ['*'], 'page', $page);
    }

    /**
     * @throws Throwable
     */
    public function createOutbound(array $data): Outbound
    {
        $data = $this->normalizeOutboundData($data);

        return DB::transaction(function () use ($data) {
            $outboundData = Arr::except($data, ['items']);
            $outbound = Outbound::create($outboundData);

            $items = $data['items'];
            $outbound->items()->createMany($items);
            return $outbound;
        });
    }

    /**
     * @throws Throwable
     */
    public function updateOutbound(int $id, array $data): Outbound
    {
        $data = $this->normalizeOutboundData($data);

        return DB::transaction(function () use ($id, $data) {
            $outbound = Outbound::query()->with(['items'])->findOrFail($id);

            $outboundData = Arr::except($data, ['items']);
            /** @noinspection DuplicatedCode */
            $outbound->update($outboundData);

            $items = $data['items'];

            $existingIds = $outbound->items->pluck('id')->toArray();
            $newItemIds = collect($items)->pluck('id')->filter()->toArray();

            $toBeDeletedId = array_diff($existingIds, $newItemIds);
            $outbound->items()->whereIn('id', $toBeDeletedId)->delete();

            foreach ($items as $item) {
                if (empty($item['id'])) {
                    $outbound->items()->create($item);
                } else {
                    $oldItem = $outbound->items()->findOrFail($item['id']);
                    $oldItem->update($item);
                }
            }
            return $outbound;
        });
    }

    private function normalizeOutboundData(array $data): array
    {
        $currency = Arr::get($data, 'currency') ?: 'JPY';
        $items = Arr::get($data, 'items', []);

        $subtotal = 0;
        $tax = 0;
        $normalizedItems = [];

        foreach ($items as $item) {
            $quantity = (int) Arr::get($item, 'quantity', 0);
            $unitPrice = $this->castAmount(Arr::get($item, 'unitPrice', 0));
            $lineAmount = Arr::get($item, 'lineAmount');
            $lineAmount = is_null($lineAmount)
                ? $unitPrice * $quantity
                : $this->castAmount($lineAmount);
            $taxAmount = $this->castAmount(Arr::get($item, 'taxAmount', 0));
            $itemCurrency = Arr::get($item, 'currency') ?: $currency;

            $normalizedItems[] = array_merge($item, [
                'unitPrice' => $unitPrice,
                'lineAmount' => $lineAmount,
                'taxAmount' => $taxAmount,
                'currency' => $itemCurrency,
            ]);

            $subtotal += $lineAmount;
            $tax += $taxAmount;
        }

        $data['currency'] = $currency;
        $data['items'] = $normalizedItems;
        $data['subtotalAmount'] = $this->castAmount($subtotal);
        $data['taxAmount'] = $this->castAmount($tax);
        $data['totalAmount'] = $this->castAmount($subtotal + $tax);

        return $data;
    }

    private function castAmount($value): int
    {
        if (is_null($value)) {
            return 0;
        }

        return (int) round((float) $value);
    }

    public function getOutbound(int $id): Outbound
    {
        return Outbound::query()
            ->with([
                'warehouse',
                'customer',
                'items.product',
            ])
            ->findOrFail($id);
    }

    /**
     * @throws Throwable
     */
    public function deleteOutbound(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $outbound = Outbound::query()->findOrFail($id);
            $outbound->items()->delete();
            $outbound->delete();
            return true;
        });
    }

    /**
     * @throws Throwable
     */
    public function approveOutbound(int $id): Outbound
    {
        return DB::transaction(function () use ($id) {
            $outbound = Outbound::query()
                ->with([
                    'items.product',
                    'warehouse',
                    'customer',
                ])
                ->findOrFail($id);
            $outbound->status = OutboundStatus::APPROVED;
            $outbound->save();

            foreach ($outbound->items as $outboundItem) {
                $inventoryItemId = $outboundItem->inventory_item_id;
                $inventoryItem = InventoryItem::query()->findOrFail($inventoryItemId);
                $inventoryItem->left_quantity = $inventoryItem->left_quantity - $outboundItem->quantity;
                $inventoryItem->save();
            }

            return $outbound;
        });
    }

    /**
     * @throws Throwable
     */
    public function rejectOutbound(int $id): Outbound
    {
        return DB::transaction(function () use ($id) {
            $outbound = Outbound::query()
                ->with(['items', 'warehouse', 'customer'])
                ->findOrFail($id);
            $outbound->status = OutboundStatus::REJECTED;
            $outbound->save();
            return $outbound;
        });
    }

    // 出库报告相关方法
    public function getOutboundReportList(int $itemsPerPage = 30, int $page = 1): Paginator
    {
        $query = OutboundReport::query()
            ->with(['outbound', 'warehouse', 'customer'])
            ->orderByDesc('id');
        return $query->paginate($itemsPerPage, ['*'], 'page', $page);
    }

    public function getOutboundReportDetail(int $id): OutboundReport
    {
        return OutboundReport::query()
            ->with(['outbound', 'warehouse', 'customer'])
            ->findOrFail($id);
    }
}
