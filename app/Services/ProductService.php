<?php

namespace App\Services;

use App\Contracts\Services\ProductServiceInterface;
use App\Models\Product;
use Illuminate\Contracts\Pagination\Paginator;

final class ProductService implements ProductServiceInterface
{
    public function getProducts(int $itemsPerPage = 30, int $page = 1, ?string $name = null): Paginator
    {
        $query = Product::query();
        if ($name) {
            $query->whereLike('name', "%$name%");
        }
        return $query->paginate($itemsPerPage, ['*'], 'page', $page);
    }

    public function getProduct(int $id): Product
    {
        return Product::query()->find($id);
    }

    public function createProduct(array $formData): Product
    {
        $product = new Product($formData);
        $product->save();
        return $product;
    }

    public function updateProduct(int $id, array $formData): Product
    {
        $product = Product::query()->find($id);
        $product->update($formData);
        return $product;
    }

    public function deleteProduct(int $id): bool
    {
        $product = Product::query()->find($id);
        return $product->delete();
    }
}
