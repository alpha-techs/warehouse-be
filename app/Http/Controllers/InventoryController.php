<?php

namespace App\Http\Controllers;

use App\Contracts\Services\InventoryServiceInterface;
use App\Http\Requests\Inventory\GenerateReportRequest;
use App\Http\Requests\Inventory\GetAgedInventoryItemListRequest;
use App\Http\Requests\Inventory\GetInventoryListRequest;
use App\Http\Requests\Inventory\GetInventoryReportListRequest;
use App\Http\Resources\Inventory\CommonInventoryResource;
use App\Http\Resources\Inventory\InventoryReportResource;
use App\Http\Resources\BaseResourceCollection;
use App\Jobs\GenerateInventoryReportJob;
use App\Models\InventoryReport;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class InventoryController extends Controller
{
    public function getList(
        GetInventoryListRequest $request,
        InventoryServiceInterface $inventoryService,
    ): JsonResponse
    {
        $params = $request->validated();
        $itemsPerPage = data_get($params, 'itemsPerPage', 30);
        $page = data_get($params, 'page', 1);
        $lotNumber = data_get($params, 'lotNumber');
        $warehouseId = data_get($params, 'warehouseId');
        $productId = data_get($params, 'productId');
        $inboundDateFrom = data_get($params, 'inboundDateFrom');
        $inboundDateFrom = $inboundDateFrom ? Carbon::parse($inboundDateFrom) : null;
        $inboundDateTo = data_get($params, 'inboundDateTo');
        $inboundDateTo = $inboundDateTo ? Carbon::parse($inboundDateTo) : null;

        $items = $inventoryService->getInventoryList(
            $itemsPerPage,
            $page,
            $lotNumber,
            $warehouseId,
            $productId,
            $inboundDateFrom,
            $inboundDateTo
        );
        $resources = CommonInventoryResource::collection($items);
        return $resources->response();
    }

    public function getDetail(
        int $id,
        InventoryServiceInterface $inventoryService,
    ): JsonResponse
    {
        $item = $inventoryService->getInventoryItemDetail($id);
        $resource = new CommonInventoryResource($item);
        return $resource->response();
    }

    public function getAgedItems(
        GetAgedInventoryItemListRequest $request,
        InventoryServiceInterface $inventoryService,
    ): JsonResponse
    {
        $params = $request->validated();
        $itemsPerPage = data_get($params, 'itemsPerPage', 30);
        $page = data_get($params, 'page', 1);
        $items = $inventoryService->getAgedItems($itemsPerPage, $page);
        $resources = CommonInventoryResource::collection($items);
        return $resources->response();
    }

    public function muteItem(
        int $id,
        InventoryServiceInterface $inventoryService,
    ): JsonResponse
    {
        $item = $inventoryService->muteItem($id);
        $resource = new CommonInventoryResource($item);
        return $resource->response();
    }

    public function generateReport(
        GenerateReportRequest $request,
        InventoryServiceInterface $inventoryService,
    ): JsonResponse
    {
        $params = $request->validated();
        $warehouseId = data_get($params, 'warehouseId');
        $customerId = data_get($params, 'customerId');
        $format = data_get($params, 'format', 'pdf');

        // 获取仓库和客户信息
        $warehouse = \App\Models\Warehouse::find($warehouseId);
        $customer = \App\Models\Customer::find($customerId);

        // 创建报告记录
        $report = InventoryReport::create([
            'warehouse_id' => $warehouseId,
            'warehouse_name' => $warehouse?->name,
            'customer_id' => $customerId,
            'customer_name' => $customer?->name,
            'format' => $format,
            'status' => 'pending',
            'storage' => InventoryReport::STORAGE_LOCAL, // 默认使用本地存储
        ]);

        // 分发异步任务
        GenerateInventoryReportJob::dispatch($report->id);

        $resource = new InventoryReportResource($report);
        return $resource->response()->setStatusCode(202);
    }

    public function getReports(
        GetInventoryReportListRequest $request,
        InventoryServiceInterface $inventoryService,
    ): JsonResponse
    {
        $params = $request->validated();
        $itemsPerPage = data_get($params, 'itemsPerPage', 30);
        $page = data_get($params, 'page', 1);

        $reports = $inventoryService->getReportList(
            $itemsPerPage,
            $page,
        );

        $resources = new BaseResourceCollection($reports, InventoryReportResource::class);
        return $resources->response();
    }

    public function getReportStatus(
        int $id,
        InventoryServiceInterface $inventoryService,
    ): JsonResponse
    {
        $report = $inventoryService->getReportDetail($id);

        $resource = new InventoryReportResource($report);
        return $resource->response();
    }

    public function downloadReport(int $id): StreamedResponse
    {
        $report = InventoryReport::findOrFail($id);

        if (!$report->isCompleted() || !$report->file_path) {
            abort(404, 'Report file not found or not ready');
        }

        // 根据存储类型选择合适的磁盘
        $disk = $report->isS3Storage() ? 's3' : 'public';

        if (!Storage::disk($disk)->exists($report->file_path)) {
            abort(404, 'Report file not found on storage');
        }

        $filename = sprintf(
            'inventory_report_%s_%s.%s',
            $report->warehouse?->name ?? 'warehouse',
            $report->created_at->format('Y_m_d'),
            $report->format === 'excel' ? 'xlsx' : 'pdf'
        );

        return Storage::disk($disk)->download($report->file_path, $filename);
    }
}
