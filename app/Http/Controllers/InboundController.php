<?php

namespace App\Http\Controllers;

use App\Contracts\Models\InboundStatus;
use App\Contracts\Services\CustomerServiceInterface;
use App\Contracts\Services\InboundServiceInterface;
use App\Http\Requests\Inventory\GetInboundItemsRequest;
use App\Http\Requests\Inventory\GetInboundListRequest;
use App\Http\Requests\Inventory\UpsertInboundRequest;
use App\Http\Resources\Inventory\CommonInboundResource;
use App\Models\Inbound;
use Arr;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

final class InboundController extends Controller
{
    public function getInbounds(
        GetInboundListRequest $request,
        InboundServiceInterface $inboundService,
    ): JsonResponse
    {
        $params = $request->validated();
        $itemsPerPage = data_get($params, 'itemsPerPage', 30);
        $page = data_get($params, 'page', 1);
        $inboundOrderId = data_get($params, 'inboundOrderId');
        $inboundDateFrom = data_get($params, 'inboundDateFrom');
        $inboundDateFrom = $inboundDateFrom? Carbon::parse($inboundDateFrom) : null;
        $inboundDateTo = data_get($params, 'inboundDateTo');
        $inboundDateTo = $inboundDateTo? Carbon::parse($inboundDateTo) : null;
        $warehouseId = data_get($params, 'warehouseId');
        $status = data_get($params, 'status');

        $inbounds = $inboundService->getInbounds(
            $itemsPerPage,
            $page,
            $inboundOrderId,
            $inboundDateFrom,
            $inboundDateTo,
            $warehouseId,
            $status
        );

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

    public function deleteInbound(
        $id,
        InboundServiceInterface $inboundService,
    ): JsonResponse
    {
        $inboundService->deleteInbound($id);
        return response()->json()->setStatusCode('204');
    }

    public function approveInbound(
        $id,
        InboundServiceInterface $inboundService,
    ): JsonResponse
    {
        $inbound = Inbound::query()->findOrFail($id);
        if ($inbound->status != InboundStatus::PENDING) {
            return response()->json()->setStatusCode('400', 'inbound status must be pending to approve' );
        }

        $inbound = $inboundService->approveInbound($id);

        $resource = new CommonInboundResource($inbound);
        return $resource->response();
    }

    public function rejectInbound(
        $id,
        InboundServiceInterface $inboundService,
    ): JsonResponse
    {
        $inbound = Inbound::query()->findOrFail($id);
        if ($inbound->status!= InboundStatus::PENDING) {
            return response()->json()->setStatusCode('400', 'inbound status must be pending to reject' );
        }

        $inbound = $inboundService->rejectInbound($id);

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
        $productId = data_get($params, 'productId');
        $inboundDateFrom = data_get($params, 'inboundDateFrom');
        $inboundDateFrom = $inboundDateFrom? Carbon::parse($inboundDateFrom) : null;
        $inboundDateTo = data_get($params, 'inboundDateTo');
        $inboundDateTo = $inboundDateTo? Carbon::parse($inboundDateTo) : null;

        $items = $inboundService->getInboundItems(
            $itemsPerPage,
            $page,
            $lotNumber,
            $productId,
            $inboundDateFrom,
            $inboundDateTo,
        );

        $jsonResponse = CommonInboundResource::collection($items);
        return $jsonResponse->response();
    }
}
