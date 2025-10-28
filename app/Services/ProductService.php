<?php

namespace App\Services;

use App\Contracts\Services\ProductServiceInterface;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class ProductService implements ProductServiceInterface
{
    public function getProducts(int $itemsPerPage = 30, int $page = 1, ?string $name = null): Paginator
    {
        $query = Product::query()->with(['images.media']);
        if ($name) {
            $query->whereLike('name', "%$name%");
        }
        $query->orderByDesc('id');
        return $query->paginate($itemsPerPage, ['*'], 'page', $page);
    }

    public function getProduct(int $id): Product
    {
        return Product::query()
            ->with(['images.media'])
            ->findOrFail($id);
    }

    public function createProduct(array $formData): Product
    {
        return DB::transaction(function () use ($formData) {
            $productData = Arr::except($formData, ['images']);
            $images = $formData['images'] ?? [];

            $product = new Product($productData);
            $product->save();

            if (! empty($images)) {
                $this->syncImages($product, $images);
            }

            return $product->fresh(['images.media']);
        });
    }

    public function updateProduct(int $id, array $formData): Product
    {
        return DB::transaction(function () use ($id, $formData) {
            $productData = Arr::except($formData, ['images']);
            $product = Product::query()->findOrFail($id);

            $product->update($productData);

            if (array_key_exists('images', $formData)) {
                $images = $formData['images'] ?? [];
                $this->syncImages($product, $images);
            }

            return $product->fresh(['images.media']);
        });
    }

    public function deleteProduct(int $id): bool
    {
        $product = Product::query()->findOrFail($id);
        return $product->delete();
    }

    private function syncImages(Product $product, array $images): void
    {
        $existingImages = $product->images()->get()->keyBy('id');
        $incomingIds = collect($images)
            ->pluck('id')
            ->filter()
            ->map(static fn ($id) => (int) $id)
            ->all();
        $existingIds = $existingImages
            ->keys()
            ->map(static fn ($id) => (int) $id)
            ->all();
        $idsToDelete = array_diff($existingIds, $incomingIds);

        if (! empty($idsToDelete)) {
            $product->images()->whereIn('id', $idsToDelete)->delete();
        }

        foreach ($images as $image) {
            $imageId = $image['id'] ?? null;
            /** @var ProductImage|null $existing */
            $existing = $imageId ? $existingImages->get((int) $imageId) : null;

            $mediaId = $this->resolveMediaId($image, $existing);

            if (is_null($existing) && is_null($mediaId)) {
                throw new InvalidArgumentException('mediaId is required for product images.');
            }

            $payload = Arr::only($image, ['order', 'altText']);

            if (! is_null($mediaId)) {
                $payload['mediaId'] = $mediaId;
            }

            if (is_null($existing)) {
                $product->images()->create($payload);
                continue;
            }

            $existing->update($payload);
        }
    }

    private function resolveMediaId(array $image, ?ProductImage $existing): ?string
    {
        if (array_key_exists('mediaId', $image) && $image['mediaId'] !== null) {
            $mediaId = trim((string) $image['mediaId']);
            if ($mediaId !== '') {
                return strtoupper($mediaId);
            }
        }

        if ($existing) {
            return strtoupper((string) $existing->media_id);
        }

        return null;
    }
}
