<?php

namespace App\Contracts\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\Paginator;

interface ProductServiceInterface
{
    public function getProducts(
        int $itemsPerPage = 30,
        int $page = 1,
        ?string $name = null,
    ): Paginator;

    public function getProduct(int $id): Product;

    public function createProduct(array $formData): Product;

    public function updateProduct(int $id, array $formData): Product;

    public function deleteProduct(int $id): bool;
}
