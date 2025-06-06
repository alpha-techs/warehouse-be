<?php

namespace App\Http\Controllers;

use App\Contracts\Models\InboundStatus;
use App\Contracts\Services\CustomerServiceInterface;
use App\Contracts\Services\InboundServiceInterface;
use App\Http\Requests\Inventory\GetInboundItemsRequest;
use App\Http\Requests\Inventory\UpsertInboundRequest;
use App\Http\Resources\Inventory\CommonInboundResource;
use App\Models\Inbound;
use App\Models\InventoryItem;
use App\Services\SnakeCaseData;
use Arr;
use Illuminate\Http\JsonResponse;

final class InboundController extends Controller
{
    use SnakeCaseData;

    public function getInbounds(): JsonResponse
    {
        $inbounds = Inbound::query()->with(['items', 'warehouse', 'customer'])->paginate();
        $jsonResponse = CommonInboundResource::collection($inbounds);
        return $jsonResponse->response();
    }

    public function getInbound($id): JsonResponse
    {
        $inbound = Inbound::query()->with(['items.product', 'warehouse', 'customer'])->find($id);
        $resource = new CommonInboundResource($inbound);
        return $resource->response();
    }

    public function createInbound(
        UpsertInboundRequest $request,
        InboundServiceInterface $inboundService,
    ): JsonResponse
    {
        $formData = $request->validated();

        $data = Arr::only($formData, [
            'inboundOrderId',
            'inboundDate',
        ]);
        $data['warehouseId'] = Arr::get($formData, 'warehouse.id');
        $data['customerId'] = Arr::get(
            $formData,
            'customer.id',
            CustomerServiceInterface::MARUOKA_JAPAN_CUSTOMER_ID
        );
        $data['status'] = InboundStatus::PENDING;
        foreach ($formData['items'] as $item) {
            $itemData = Arr::only($item, [
                'quantity',
                'perItemWeight',
                'perItemWeightUnit',
                'totalWeight',
                'manufactureDate',
                'bestBeforeDate',
                'lotNumber',
                'shipName',
            ]);
            $itemData['productId'] = Arr::get($item, 'product.id');
            $data['items'][] = $itemData;
        }

        $inbound = $inboundService->createInbound($data);
        $resource = new CommonInboundResource($inbound);
        return $resource->response();
    }

    public function updateInbound(
        $id,
        UpsertInboundRequest $request,
        InboundServiceInterface $inboundService,
    ): JsonResponse
    {
        $formData = $request->validated();

        $data = Arr::only($formData, [
            'inboundOrderId',
            'inboundDate',
        ]);
        $data['warehouseId'] = Arr::get($formData, 'warehouse.id');
        $data['customerId'] = Arr::get(
            $formData,
            'customer.id',
            CustomerServiceInterface::MARUOKA_JAPAN_CUSTOMER_ID
        );
        foreach ($formData['items'] as $item) {
            $itemData = Arr::only($item, [
                'id',
                'quantity',
                'perItemWeight',
                'perItemWeightUnit',
                'totalWeight',
                'manufactureDate',
                'bestBeforeDate',
                'lotNumber',
                'shipName',
            ]);
            $itemData['productId'] = Arr::get($item, 'product.id');
            $data['items'][] = $itemData;
        }

        $inbound = $inboundService->updateInbound($id, $data);
        $resource = new CommonInboundResource($inbound);
        return $resource->response();
    }

    public function deleteInbound($id): JsonResponse
    {
        $inbound = Inbound::query()->find($id);
        $inbound->items()->delete();
        $inbound->delete();
        return response()->json()->setStatusCode('204');
    }

    public function approveInbound($id): JsonResponse
    {
        $inbound = Inbound::query()->with(['items.product', 'warehouse', 'customer'])->find($id);
        if ($inbound->status != InboundStatus::PENDING) {
            return response()->json()->setStatusCode('400', 'inbound status must be pending to approve' );
        }
        $inbound->status = INboundStatus::APPROVED;
        $inbound->save();

        foreach ($inbound->items as $inboundItem) {
            $inventoryItemModel = [
                'warehouse_id' => $inbound['warehouse']['id'] ?? null,
                'customer_id' => $inbound['customer']['id'] ?? null,
                'inbound_order_id' => $inbound['inbound_order_id'] ?? null,
                'inbound_id' => $inbound['id'],
                'inbound_item_id' => $inboundItem['id'],
                'inbound_date' => $inbound['inbound_date'] ?? null,
                'product_id' => $inboundItem['product']['id'] ?? null,
                'per_item_weight' => $inboundItem['per_item_weight'] ?? null,
                'per_item_weight_unit' => $inboundItem['per_item_weight_unit'] ?? null,
                'total_weight' => $inboundItem['total_weight'] ?? null,
                'manufacture_date' => $inboundItem['manufacture_date'] ?? null,
                'best_before_date' => $inboundItem['best_before_date'] ?? null,
                'lot_number' => $inboundItem['lot_number'] ?? null,
                'ship_name' => $inboundItem['ship_name'] ?? null,
                'inbound_quantity' => $inboundItem['quantity'] ?? null,
                'left_quantity' => $inboundItem['quantity'] ?? null,
                'left_sub_quantity' => 0,
            ];
            $inventoryItem = new InventoryItem($inventoryItemModel);
            $inventoryItem->save();
        }

        $resource = new CommonInboundResource($inbound);
        return $resource->response();
    }

    public function rejectInbound($id): JsonResponse
    {
        $inbound = Inbound::query()->with(['items.product', 'warehouse', 'customer'])->find($id);
        if ($inbound->status!= InboundStatus::PENDING) {
            return response()->json()->setStatusCode('400', 'inbound status must be pending to reject' );
        }
        $inbound->status = InboundStatus::REJECTED;
        $inbound->save();

        $resource = new CommonInboundResource($inbound);
        return $resource->response();
    }

    public function getInboundItems(
        GetInboundItemsRequest $request,
        InboundServiceInterface $inboundService,
    ): JsonResponse
    {
        $params = $request->validated();
        $itemsPerPage = data_get($params, 'itemsPerPage', 30);
        $page = data_get($params, 'page', 1);
        $lotNumber = data_get($params, 'lotNumber');

        $items = $inboundService->getInboundItems(
            $itemsPerPage,
            $page,
            $lotNumber,
        );

        $jsonResponse = CommonInboundResource::collection($items);
        return $jsonResponse->response();
    }
}
