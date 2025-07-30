<?php

namespace App\Http\Controllers;

use App\Contracts\Models\OutboundStatus;
use App\Contracts\Services\OutboundServiceInterface;
use App\Http\Requests\Inventory\GetOutboundItemsRequest;
use App\Http\Requests\Inventory\GetOutboundListRequest;
use App\Http\Requests\Inventory\UpsertOutboundRequest;
use App\Http\Resources\Inventory\CommonOutboundItemResource;
use App\Http\Resources\Inventory\CommonOutboundResource;
use App\Models\InventoryItem;
use App\Models\Outbound;
use Arr;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

final class OutboundController extends Controller
{
    public function getOutbounds(
        GetOutboundListRequest $request,
        OutboundServiceInterface $outboundService,
    ): JsonResponse
    {
        $params = $request->validated();
        $itemsPerPage = data_get($params, 'itemsPerPage', 30);
        $page = data_get($params, 'page', 1);
        $outboundOrderId = data_get($params, 'outboundOrderId');
        $outboundDateFrom = data_get($params, 'outboundDateFrom');
        $outboundDateFrom = $outboundDateFrom? Carbon::parse($outboundDateFrom) : null;
        $outboundDateTo = data_get($params, 'outboundDateTo');
        $outboundDateTo = $outboundDateTo? Carbon::parse($outboundDateTo) : null;
        $warehouseId = data_get($params, 'warehouseId');
        $customerId = data_get($params, 'customerId');
        $status = data_get($params, 'status');

        $outbounds = $outboundService->getOutbounds(
            $itemsPerPage,
            $page,
            $outboundOrderId,
            $outboundDateFrom,
            $outboundDateTo,
            $warehouseId,
            $customerId,
            $status,
        );
        $jsonResponse = CommonOutboundResource::collection($outbounds);
        return $jsonResponse->response();
    }

    public function getOutbound($id): JsonResponse
    {
        $outbound = Outbound::query()->with(['items.product', 'warehouse', 'customer'])->find($id);
        $resource = new CommonOutboundResource($outbound);
        return $resource->response();
    }

    public function createOutbound(
        UpsertOutboundRequest $request,
        OutboundServiceInterface $outboundService,
    ): JsonResponse
    {
        $formData = $request->validated();

        $data = Arr::only($formData, [
           'outboundOrderId',
           'outboundDate',
        ]);
        $data['warehouseId'] = Arr::get($formData, 'warehouse.id');
        $data['customerId'] = Arr::get($formData, 'customer.id');
        $data['status'] = OutboundStatus::PENDING;
        foreach ($formData['items'] as $item) {
            $itemData = Arr::only($item, [
                'inboundItemId',
                'inventoryItemId',
                'quantity',
                'lotNumber',
                'note',
            ]);
            $itemData['productId'] = Arr::get($item, 'product.id');
            $data['items'][] = $itemData;
        }

        $outbound = $outboundService->createOutbound($data);
        $resource = new CommonOutboundResource($outbound);
        return $resource->response();
    }

    public function updateOutbound(
        $id,
        UpsertOutboundRequest $request,
        OutboundServiceInterface $outboundService,
    ): JsonResponse
    {
        $formData = $request->validated();
        $data = Arr::only($formData, [
            'outboundOrderId',
            'outboundDate',
        ]);
        $data['warehouseId'] = Arr::get($formData, 'warehouse.id');
        $data['customerId'] = Arr::get($formData, 'customer.id');

        foreach ($formData['items'] as $item) {
            $itemData = Arr::only($item, [
                'id',
                'inboundItemId',
                'inventoryItemId',
                'quantity',
                'lotNumber',
                'note',
            ]);
            $itemData['productId'] = Arr::get($item, 'product.id');
            $data['items'][] = $itemData;
        }

        $outbound = $outboundService->updateOutbound($id, $data);
        $resource = new CommonOutboundResource($outbound);

        return $resource->response();
    }

    public function deleteOutbound($id): JsonResponse
    {
        $outbound = Outbound::query()->find($id);
        $outbound->items()->delete();
        $outbound->delete();
        return response()->json()->setStatusCode('204');
    }

    public function approveOutbound(
        $id,
        OutboundServiceInterface $outboundService,
    ): JsonResponse
    {
        $outbound = Outbound::query()->with(['items.inventoryItem'])->findOrFail($id);
        if ($outbound->status!= 'pending') {
            return response()->json(
                ['errors' => ['message' => ['出庫ステータスが「未確認」でなければ承認できません']]],
            )->setStatusCode('400');
        }

        foreach ($outbound->items as $item) {
            $inventoryItem = InventoryItem::query()->find($item->inventory_item_id);
            if (!$inventoryItem) {
                return response()->json(
                    ['errors' => ['message' => ['在庫アイテムが存在しません']]],
                )->setStatusCode('400');
            }
            $leftQuantity = $inventoryItem->left_quantity;
            $neededQuantity = $item->quantity;
            if ($leftQuantity < $neededQuantity) {
                return response()->json(
                    ['errors' => ['message' => ['在庫数量が不足しています']]],
                )->setStatusCode('400');
            }
        }

        $outbound = $outboundService->approveOutbound($id);
        $resource = new CommonOutboundResource($outbound);
        return $resource->response();
    }

    public function rejectOutbound($id): JsonResponse
    {
        $outbound = Outbound::query()->with(['items.product', 'warehouse', 'customer'])->find($id);
        if ($outbound->status!= 'pending') {
            return response()->json()->setStatusCode('400', 'outbound status must be pending to reject' );
        }
        $outbound->status = 'rejected';
        $outbound->save();
        $resource = new CommonOutboundResource($outbound);
        return $resource->response();
    }

    public function getOutboundItems(
        GetOutboundItemsRequest $request,
        OutboundServiceInterface $outboundService,
    ): JsonResponse
    {
        $params = $request->validated();
        $itemsPerPage = data_get($params, 'itemsPerPage', 30);
        $page = data_get($params, 'page', 1);
        $lotNumber = data_get($params, 'lotNumber');
        $productId = data_get($params, 'productId');
        $outboundDateFrom = data_get($params, 'outboundDateFrom');
        $outboundDateFrom = $outboundDateFrom? Carbon::parse($outboundDateFrom) : null;
        $outboundDateTo = data_get($params, 'outboundDateTo');
        $outboundDateTo = $outboundDateTo? Carbon::parse($outboundDateTo) : null;

        $items = $outboundService->getOutboundItems(
            itemsPerPage: $itemsPerPage,
            page: $page,
            lotNumber: $lotNumber,
            productId: $productId,
            outboundDateFrom: $outboundDateFrom,
            outboundDateTo: $outboundDateTo,
        );
        $jsonResponse = CommonOutboundItemResource::collection($items);
        return $jsonResponse->response();
    }
}
