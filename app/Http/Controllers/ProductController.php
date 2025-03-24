<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\UpsertProductRequest;
use App\Http\Resources\Product\CommonProductResource;
use App\Models\Product;
use App\Services\SnakeCaseData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use SnakeCaseData;

    public function getProducts(Request $request): JsonResponse
    {
        $name = $request->input('name');
        $query = Product::query();
        if ($name) {
            $query->where('name', 'like', "%{$name}%");
        }
        $products = $query->paginate();
        $jsonResponse = CommonProductResource::collection($products);
        return $jsonResponse->response();
    }

    public function getProduct($id): JsonResponse
    {
        $product = Product::query()->find($id);
        $resource = new CommonProductResource($product);
        return $resource->response();
    }

    public function createProduct(UpsertProductRequest $request): JsonResponse
    {
        $form = $request->all();
        unset($form['leafCategory']);

        $form['dimension_description'] = $form['dimension']['description'] ?? null;
        $form['length'] = $form['dimension']['length'] ?? null;
        $form['width'] = $form['dimension']['width'] ?? null;
        $form['height'] = $form['dimension']['height'] ?? null;
        $form['weight'] = $form['dimension']['weight'] ?? null;
        $form['length_unit'] = $form['dimension']['lengthUnit'] ?? null;
        $form['weight_unit'] = $form['dimension']['weightUnit'] ?? null;
        unset($form['dimension']);

        $product = new Product($this->snakeCaseData($form));
        $product->save();

        $resource = new CommonProductResource($product);
        return $resource->response();
    }

    public function updateProduct(UpsertProductRequest $request, $id): JsonResponse
    {
        $form = $request->all();
        unset($form['leafCategory']);

        $form['dimension_description'] = $form['dimension']['description'] ?? null;
        $form['length'] = $form['dimension']['length'] ?? null;
        $form['width'] = $form['dimension']['width'] ?? null;
        $form['height'] = $form['dimension']['height'] ?? null;
        $form['weight'] = $form['dimension']['weight'] ?? null;
        $form['length_unit'] = $form['dimension']['lengthUnit'] ?? null;
        $form['weight_unit'] = $form['dimension']['weightUnit'] ?? null;
        unset($form['dimension']);

        $product = Product::query()->find($id);
        $product->update($this->snakeCaseData($form));

        $resource = new CommonProductResource($product);
        return $resource->response();
    }

    public function deleteProduct($id): JsonResponse
    {
        $product = Product::query()->find($id);
        $product->delete();
        return response()->json()->setStatusCode(204);
    }
}
