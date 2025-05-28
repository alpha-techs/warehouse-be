<?php

namespace App\Services;

use App\Contracts\Services\ContainerServiceInterface;
use App\Models\Container;
use Arr;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Throwable;

final class ContainerService implements ContainerServiceInterface
{
    public function getContainers(
        int $itemsPerPage = 30,
        int $page = 1,
        ?string $containerNumber = null,
        ?array $statuses = null,
    ): Paginator
    {
        $query = Container::query();
        if (! is_null($containerNumber)) {
            $query->where('container_number', 'like', '%' . $containerNumber . '%');
        }
        if (! is_null($statuses)) {
            $query->whereIn('status', $statuses);
        }
        $query->orderByDesc('id');
        return $query->paginate($itemsPerPage, ['*'], 'page', $page);
    }

    public function getContainer(int $id): Container
    {
        return Container::with(['items'])->findOrFail($id);
    }

    /**
     * @throws Throwable
     */
    public function createContainer(array $data): Container
    {
        return DB::transaction(function () use ($data) {
            $container = Container::create(Arr::except($data, ['items']));

            if (isset($data['items'])) {
                $container->items()->createMany($data['items']);
            }

            return $container->load(['items.product']);
        });
    }

    /**
     * @throws Throwable
     */
    public function updateContainer(int $id, array $data): Container
    {
        return DB::transaction(function () use ($id, $data) {
            $container = Container::query()->with(['items'])->findOrFail($id);
            $container->update(Arr::except($data, ['items']));

            $items = $data['items'] ?? [];

            /** @noinspection DuplicatedCode */
            $existingIds = $container->items->pluck('id')->toArray();
            $newItems = collect($items)->pluck('id')->filter()->toArray();
            $toDelete = array_diff($existingIds, $newItems);

            $container->items()->whereIn('id', $toDelete)->delete();
            foreach ($items as $item) {
                if (empty($item['id'])) {
                    $container->items()->create($item);
                } else {
                    $oldItem = $container->items()->findOrFail($item['id']);
                    $oldItem->update($item);
                }
            }

            return $container;
        });
    }

    public function deleteContainer(int $id): bool
    {
        $container = Container::findOrFail($id);
        return $container->delete();
    }
}
