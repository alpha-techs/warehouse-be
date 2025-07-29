<?php

namespace App\Services;

use App\Contracts\Services\OutboundServiceInterface;
use App\Models\Outbound;
use App\Models\OutboundItem;
use Arr;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Throwable;

final class OutboundService implements OutboundServiceInterface
{
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
            ->whereOutboundStatus(OutboundStatus::PENDING);
    }

    /**
     * @throws Throwable
     */
    public function createOutbound(array $data): Outbound
    {
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
        return DB::transaction(function () use ($id, $data) {
            $outbound = Outbound::findOrFail($id);

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
}
