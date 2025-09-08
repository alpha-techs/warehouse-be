<?php

namespace App\Http\Controllers;

use App\Contracts\Models\NameChangeStatus;
use App\Contracts\Services\NameChangeServiceInterface;
use App\Http\Requests\Inventory\GetNameChangeItemsRequest;
use App\Http\Requests\Inventory\GetNameChangeListRequest;
use App\Http\Requests\Inventory\UpsertNameChangeRequest;
use App\Http\Requests\NameChange\GenerateNameChangeReportRequest;
use App\Http\Requests\NameChange\GetNameChangeReportListRequest;
use App\Http\Resources\Inventory\CommonNameChangeItemResource;
use App\Http\Resources\Inventory\CommonNameChangeResource;
use App\Http\Resources\NameChange\NameChangeReportResource;
use App\Http\Resources\BaseResourceCollection;
use App\Jobs\GenerateNameChangeReportJob;
use App\Models\InventoryItem;
use App\Models\NameChangeReport;
use App\Models\NameChange;
use Arr;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

final class NameChangeController extends Controller
{
    public function getNameChanges(
        GetNameChangeListRequest $request,
        NameChangeServiceInterface $nameChangeService,
    ): JsonResponse
    {
        $params = $request->validated();
        $itemsPerPage = data_get($params, 'itemsPerPage', 30);
        $page = data_get($params, 'page', 1);
        $nameChangeOrderId = data_get($params, 'nameChangeOrderId');
        $nameChangeDateFrom = data_get($params, 'nameChangeDateFrom');
        $nameChangeDateFrom = $nameChangeDateFrom? Carbon::parse($nameChangeDateFrom) : null;
        $nameChangeDateTo = data_get($params, 'nameChangeDateTo');
        $nameChangeDateTo = $nameChangeDateTo? Carbon::parse($nameChangeDateTo) : null;
        $warehouseId = data_get($params, 'warehouseId');
        $customerId = data_get($params, 'customerId');
        $status = data_get($params, 'status');

        $nameChanges = $nameChangeService->getNameChanges(
            $itemsPerPage,
            $page,
            $nameChangeOrderId,
            $nameChangeDateFrom,
            $nameChangeDateTo,
            $warehouseId,
            $customerId,
            $status,
        );
        $jsonResponse = CommonNameChangeResource::collection($nameChanges);
        return $jsonResponse->response();
    }

    public function getNameChange($id): JsonResponse
    {
        $nameChange = NameChange::query()->with(['items.product', 'warehouse', 'customer'])->find($id);
        $resource = new CommonNameChangeResource($nameChange);
        return $resource->response();
    }

    public function createNameChange(
        UpsertNameChangeRequest $request,
        NameChangeServiceInterface $nameChangeService,
    ): JsonResponse
    {
        $formData = $request->validated();

        $data = Arr::only($formData, [
           'nameChangeOrderId',
           'nameChangeDate',
        ]);
        $data['warehouseId'] = Arr::get($formData, 'warehouse.id');
        $data['customerId'] = Arr::get($formData, 'customer.id');
        $data['status'] = NameChangeStatus::PENDING;
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

        $nameChange = $nameChangeService->createNameChange($data);
        $resource = new CommonNameChangeResource($nameChange);
        return $resource->response();
    }

    public function updateNameChange(
        $id,
        UpsertNameChangeRequest $request,
        NameChangeServiceInterface $nameChangeService,
    ): JsonResponse
    {
        $formData = $request->validated();
        $data = Arr::only($formData, [
            'nameChangeOrderId',
            'nameChangeDate',
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

        $nameChange = $nameChangeService->updateNameChange($id, $data);
        $resource = new CommonNameChangeResource($nameChange);

        return $resource->response();
    }

    public function deleteNameChange($id): JsonResponse
    {
        $nameChange = NameChange::query()->find($id);
        if (!$nameChange) {
            return response()->json(['message' => 'Name change not found'], 404);
        }

        $nameChange->delete();
        return response()->json(['message' => 'Name change deleted successfully']);
    }

    public function approveNameChange(
        $id,
        NameChangeServiceInterface $nameChangeService,
    ): JsonResponse
    {
        $nameChange = NameChange::query()->with(['items.inventoryItem'])->findOrFail($id);
        if ($nameChange->status != 'pending') {
            return response()->json(
                ['errors' => ['message' => ['名义变更ステータスが「未確認」でなければ承認できません']]],
            )->setStatusCode('400');
        }

        foreach ($nameChange->items as $item) {
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

        $nameChange = $nameChangeService->approveNameChange($id);
        $resource = new CommonNameChangeResource($nameChange);
        return $resource->response();
    }

    public function rejectNameChange($id): JsonResponse
    {
        $nameChange = NameChange::query()->with(['items.product', 'warehouse', 'customer'])->find($id);
        if ($nameChange->status != 'pending') {
            return response()->json()->setStatusCode('400', 'name change status must be pending to reject');
        }
        $nameChange->status = 'rejected';
        $nameChange->save();
        $resource = new CommonNameChangeResource($nameChange);
        return $resource->response();
    }

    public function getNameChangeItems(
        GetNameChangeItemsRequest $request,
        NameChangeServiceInterface $nameChangeService,
    ): JsonResponse
    {
        $params = $request->validated();
        $itemsPerPage = data_get($params, 'itemsPerPage', 30);
        $page = data_get($params, 'page', 1);
        $lotNumber = data_get($params, 'lotNumber');
        $productId = data_get($params, 'productId');
        $nameChangeDateFrom = data_get($params, 'nameChangeDateFrom');
        $nameChangeDateFrom = $nameChangeDateFrom? Carbon::parse($nameChangeDateFrom) : null;
        $nameChangeDateTo = data_get($params, 'nameChangeDateTo');
        $nameChangeDateTo = $nameChangeDateTo? Carbon::parse($nameChangeDateTo) : null;

        $items = $nameChangeService->getNameChangeItems(
            itemsPerPage: $itemsPerPage,
            page: $page,
            lotNumber: $lotNumber,
            productId: $productId,
            nameChangeDateFrom: $nameChangeDateFrom,
            nameChangeDateTo: $nameChangeDateTo,
        );
        $jsonResponse = CommonNameChangeItemResource::collection($items);
        return $jsonResponse->response();
    }

    // 名义变更报告相关方法
    public function generateNameChangeReport(
        GenerateNameChangeReportRequest $request,
        NameChangeServiceInterface $nameChangeService,
    ): JsonResponse
    {
        $params = $request->validated();
        $nameChangeId = data_get($params, 'nameChangeId');
        $format = data_get($params, 'format', 'pdf');

        // 获取名义变更单信息
        $nameChange = NameChange::with(['warehouse', 'customer'])->findOrFail($nameChangeId);

        // 创建报告记录
        $report = NameChangeReport::create([
            'name_change_id' => $nameChangeId,
            'warehouse_id' => $nameChange->warehouse_id,
            'warehouse_name' => $nameChange->warehouse?->name,
            'customer_id' => $nameChange->customer_id,
            'customer_name' => $nameChange->customer?->name,
            'format' => $format,
            'status' => 'pending',
            'storage' => NameChangeReport::STORAGE_LOCAL, // 默认使用本地存储
        ]);

        // 分发异步任务
        GenerateNameChangeReportJob::dispatch($report->id);

        $resource = new NameChangeReportResource($report);
        return $resource->response()->setStatusCode(202);
    }

    public function getNameChangeReports(
        GetNameChangeReportListRequest $request,
        NameChangeServiceInterface $nameChangeService,
    ): JsonResponse
    {
        $params = $request->validated();
        $itemsPerPage = data_get($params, 'itemsPerPage', 30);
        $page = data_get($params, 'page', 1);

        $reports = $nameChangeService->getNameChangeReportList(
            $itemsPerPage,
            $page,
        );

        $resources = new BaseResourceCollection($reports, NameChangeReportResource::class);
        return $resources->response();
    }

    public function getNameChangeReportStatus(
        int $id,
        NameChangeServiceInterface $nameChangeService,
    ): JsonResponse
    {
        $report = $nameChangeService->getNameChangeReportDetail($id);

        $resource = new NameChangeReportResource($report);
        return $resource->response();
    }

    public function downloadNameChangeReport(int $id): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $report = NameChangeReport::findOrFail($id);

        if (!$report->isCompleted()) {
            abort(404, 'Report not ready for download');
        }

        $filePath = $report->file_path;
        $fullPath = storage_path('app/public/' . $filePath);

        if (!file_exists($fullPath)) {
            abort(404, 'Report file not found');
        }

        return response()->download($fullPath);
    }
}
