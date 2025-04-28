<?php

namespace App\Http\Controllers;

use App\Contracts\Services\ProductServiceInterface;
use App\Http\Requests\Product\GetProductsRequest;
use App\Http\Requests\Product\UpsertProductRequest;
use App\Http\Resources\Product\CommonProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

final class ProductController extends Controller
{
    public function getProducts(
        GetProductsRequest $request,
        ProductServiceInterface $productService,
    ): JsonResponse
    {
        $itemsPerPage = intval($request->validated('itemsPerPage', 30));
        $page = intval($request->validated('page', 1));
        $name = $request->validated('name');

        $products = $productService->getProducts($itemsPerPage, $page, $name);
        $resource = CommonProductResource::collection($products);

        return $resource->response();
    }

    public function getProduct($id): JsonResponse
    {
        $product = Product::query()->find($id);
        $resource = new CommonProductResource($product);
        return $resource->response();
    }

    public function createProduct(
        UpsertProductRequest $request,
        ProductServiceInterface $productService,
    ): JsonResponse
    {
        $form = $request->validated();

        $product = $productService->createProduct($form);
        $resource = new CommonProductResource($product);

        return $resource->response();
    }

    public function updateProduct(
        $id,
        UpsertProductRequest $request,
        ProductServiceInterface $productService,
    ): JsonResponse
    {
        $form = $request->validated();

        $product = $productService->updateProduct($id, $form);
        $resource = new CommonProductResource($product);

        return $resource->response();
    }

    public function deleteProduct(
        $id,
        ProductServiceInterface $productService,
    ): JsonResponse
    {
        $productService->deleteProduct($id);
        return response()->json()->setStatusCode(204);
    }
}
