<?php

namespace App\Http\Controllers;

use App\Http\Requests\Warehouse\UpsertWarehouseRequest;
use App\Http\Resources\Warehouse\CommonWarehouseResource;
use App\Models\Warehouse;
use App\Services\SnakeCaseData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class WarehouseController extends Controller
{
    use SnakeCaseData;

    public function getWarehouses(Request $request): JsonResponse
    {
        $query = Warehouse::query();
        $name = $request->get('name');
        if ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }
        $warehouses = $query->paginate();
        $jsonResponse = CommonWarehouseResource::collection($warehouses);
        return $jsonResponse->response();
    }

    public function getWarehouse($id): JsonResponse
    {
        $warehouse = Warehouse::query()->find($id);
        $resource = new CommonWarehouseResource($warehouse);
        return $resource->response();
    }

    public function createWarehouse(UpsertWarehouseRequest $request): JsonResponse
    {
        $formRequest = $request->all();

        $formRequest['postalCode'] = $formRequest['address']['postalCode'] ?? null;
        $formRequest['detailAddress1'] = $formRequest['address']['detailAddress1'] ?? null;
        $formRequest['detailAddress2'] = $formRequest['address']['detailAddress2'] ?? null;
        unset($formRequest['address']);
        $warehouse = Warehouse::query()->create($this->snakeCaseData($formRequest));
        $jsonResponse = CommonWarehouseResource::make($warehouse);
        return $jsonResponse->response();
    }

    public function updateWarehouse(UpsertWarehouseRequest $request, $id): JsonResponse
    {
        $formRequest = $request->all();

        $formRequest['postalCode'] = $formRequest['address']['postalCode'];
        $formRequest['detailAddress1'] = $formRequest['address']['detailAddress1'];
        $formRequest['detailAddress2'] = $formRequest['address']['detailAddress2'];
        unset($formRequest['address']);
        $warehouse = Warehouse::query()->find($id);
        $warehouse->update($this->snakeCaseData($formRequest));
        $jsonResponse = CommonWarehouseResource::make($warehouse);
        return $jsonResponse->response();
    }

    public function deleteWarehouse($id): JsonResponse
    {
        $warehouse = Warehouse::query()->find($id);
        $warehouse->delete();
        return response()->json()->setStatusCode(204);
    }
}
