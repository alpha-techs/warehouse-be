<?php

namespace App\Http\Controllers;

use App\Contracts\Models\InboundStatus;
use App\Contracts\Services\CustomerServiceInterface;
use App\Contracts\Services\InboundServiceInterface;
use App\Http\Requests\Inventory\GetInboundItemsRequest;
use App\Http\Requests\Inventory\GetInboundListRequest;
use App\Http\Requests\Inventory\UpsertInboundRequest;
use App\Http\Requests\Inbound\GenerateInboundReportRequest;
use App\Http\Requests\Inbound\GetInboundReportListRequest;
use App\Http\Resources\Inventory\CommonInboundItemResource;
use App\Http\Resources\Inventory\CommonInboundResource;
use App\Http\Resources\Inbound\InboundReportResource;
use App\Http\Resources\BaseResourceCollection;
use App\Jobs\GenerateInboundReportJob;
use App\Models\InboundReport;
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

        $jsonResponse = CommonInboundItemResource::collection($items);
        return $jsonResponse->response();
    }

    // 入库报告相关方法
    public function generateInboundReport(
        GenerateInboundReportRequest $request,
        InboundServiceInterface $inboundService,
    ): JsonResponse
    {
        $params = $request->validated();
        $inboundId = data_get($params, 'inboundId');
        $format = data_get($params, 'format', 'pdf');

        // 获取入库单信息
        $inbound = Inbound::with(['warehouse', 'customer'])->findOrFail($inboundId);

        // 创建报告记录
        $report = InboundReport::create([
            'inbound_id' => $inboundId,
            'warehouse_id' => $inbound->warehouse_id,
            'warehouse_name' => $inbound->warehouse?->name,
            'customer_id' => $inbound->customer_id,
            'customer_name' => $inbound->customer?->name,
            'format' => $format,
            'status' => 'pending',
            'storage' => InboundReport::defaultStorageType(),
        ]);

        // 分发异步任务
        GenerateInboundReportJob::dispatch($report->id);

        $resource = new InboundReportResource($report);
        return $resource->response()->setStatusCode(202);
    }

    public function getInboundReports(
        GetInboundReportListRequest $request,
        InboundServiceInterface $inboundService,
    ): JsonResponse
    {
        $params = $request->validated();
        $itemsPerPage = data_get($params, 'itemsPerPage', 30);
        $page = data_get($params, 'page', 1);
        $customerId = data_get($params, 'customerId');
        $warehouseId = data_get($params, 'warehouseId');
        $startDate = data_get($params, 'startDate');
        $startDate = $startDate? Carbon::parse($startDate) : null;
        $endDate = data_get($params, 'endDate');
        $endDate = $endDate? Carbon::parse($endDate) : null;

        $reports = $inboundService->getInboundReportList(
            $itemsPerPage,
            $page,
            $customerId,
            $warehouseId,
            $startDate,
            $endDate,
        );

        $resources = new BaseResourceCollection($reports, InboundReportResource::class);
        return $resources->response();
    }

    public function getInboundReportStatus(
        int $id,
        InboundServiceInterface $inboundService,
    ): JsonResponse
    {
        $report = $inboundService->getInboundReportDetail($id);

        $resource = new InboundReportResource($report);
        return $resource->response();
    }

    public function downloadInboundReport(int $id): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $report = InboundReport::findOrFail($id);

        if (!$report->isCompleted() || !$report->file_path) {
            abort(404, 'Report file not found or not ready');
        }

        // 根据存储类型选择合适的磁盘
        $disk = $report->getStorageDisk();

        if (!\Illuminate\Support\Facades\Storage::disk($disk)->exists($report->file_path)) {
            abort(404, 'Report file not found on storage');
        }

        $filename = sprintf(
            'inbound_report_%s_%s.%s',
            $report->warehouse?->name ?? 'warehouse',
            $report->created_at->format('Y_m_d'),
            $report->format === 'excel' ? 'xlsx' : 'pdf'
        );

        return \Illuminate\Support\Facades\Storage::disk($disk)->download($report->file_path, $filename);
    }
}
