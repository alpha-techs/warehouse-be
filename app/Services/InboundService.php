<?php

namespace App\Services;

use App\Contracts\Models\InboundStatus;
use App\Contracts\Services\InboundServiceInterface;
use App\Models\Inbound;
use App\Models\InboundItem;
use Arr;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Throwable;

final class InboundService implements InboundServiceInterface
{

    public function getInboundItems(int $itemsPerPage = 30, int $page = 1, ?string $lotNumber = null,): Paginator
    {
        $query = InboundItem::query()
            ->with(['product', 'inbound.warehouse'])
            ->whereInboundStatus(InboundStatus::APPROVED);
        if ($lotNumber) {
            $query->whereLotNumber($lotNumber);
        }
        return $query->paginate($itemsPerPage, ['*'], 'page', $page);
    }


    /**
     * @throws Throwable
     */
    public function createInbound(array $data): Inbound
    {
        return DB::transaction(function () use ($data) {
            $inboundData = Arr::except($data, ['items']);
            $inbound = Inbound::create($inboundData);

            $items = $data['items'];
            $inbound->items()->createMany($items);
            return  $inbound;
        });
    }

    /**
     * @throws Throwable
     */
    public function updateInbound(int $id, array $data): Inbound
    {
        return DB::transaction(function () use ($id, $data) {
            $inboundData = Arr::except($data, ['items']);

            $inbound = Inbound::query()->with(['items'])->findOrFail($id);
            $inbound->update($inboundData);

            $items = $data['items'];

            $existingIds = $inbound->items->pluck('id')->toArray();
            $newItemIds = collect($items)->pluck('id')->filter()->toArray();

            $toBeDeletedId = array_diff($existingIds, $newItemIds);

            $inbound->items()->whereIn('id', $toBeDeletedId)->delete();
            foreach ($items as $item) {
                if (empty($item['id'])) {
                    $inbound->items()->create($item);
                } else {
                    $oldItem = $inbound->items()->findOrFail($item['id']);
                    $oldItem->update($item);
                }
            }
            return  $inbound;
        });
    }
}
