<?php

namespace App\Http\Controllers;

use App\Contracts\Models\ExpressSampleShipmentStatus;
use App\Contracts\Services\ExpressSampleShipmentServiceInterface;
use App\Http\Requests\Inventory\GetExpressSampleShipmentItemsRequest;
use App\Http\Requests\Inventory\GetExpressSampleShipmentListRequest;
use App\Http\Requests\Inventory\UpsertExpressSampleShipmentRequest;
use App\Http\Requests\ExpressSampleShipment\GenerateExpressSampleShipmentReportRequest;
use App\Http\Requests\ExpressSampleShipment\GetExpressSampleShipmentReportListRequest;
use App\Http\Resources\BaseResourceCollection;
use App\Http\Resources\Inventory\CommonExpressSampleShipmentItemResource;
use App\Http\Resources\Inventory\CommonExpressSampleShipmentResource;
use App\Http\Resources\ExpressSampleShipment\ExpressSampleShipmentReportResource;
use App\Models\ExpressSampleShipment;
use App\Models\InventoryItem;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ExpressSampleShipmentController extends Controller
{
    public function getExpressSampleShipments(
        GetExpressSampleShipmentListRequest $request,
        ExpressSampleShipmentServiceInterface $service,
    ): JsonResponse {
        $params = $request->validated();
        $itemsPerPage = data_get($params, 'itemsPerPage', 30);
        $page = data_get($params, 'page', 1);
        $status = data_get($params, 'status');
        $customerId = data_get($params, 'customerId');
        $desiredDeliveryDateFrom = data_get($params, 'desiredDeliveryDateFrom');
        $desiredDeliveryDateFrom = $desiredDeliveryDateFrom ? Carbon::parse($desiredDeliveryDateFrom) : null;
        $desiredDeliveryDateTo = data_get($params, 'desiredDeliveryDateTo');
        $desiredDeliveryDateTo = $desiredDeliveryDateTo ? Carbon::parse($desiredDeliveryDateTo) : null;

        $shipments = $service->getExpressSampleShipments(
            $itemsPerPage,
            $page,
            $status,
            $customerId,
            $desiredDeliveryDateFrom,
            $desiredDeliveryDateTo,
        );

        $resource = CommonExpressSampleShipmentResource::collection($shipments);
        return $resource->response();
    }

    public function getExpressSampleShipment(
        int $id,
        ExpressSampleShipmentServiceInterface $service,
    ): JsonResponse {
        $shipment = $service->getExpressSampleShipment($id);
        $resource = new CommonExpressSampleShipmentResource($shipment);
        return $resource->response();
    }

    public function createExpressSampleShipment(
        UpsertExpressSampleShipmentRequest $request,
        ExpressSampleShipmentServiceInterface $service,
    ): JsonResponse {
        $formData = $request->validated();
        $formData['status'] = $formData['status'] ?? ExpressSampleShipmentStatus::PENDING->value;

        $shipment = $service->createExpressSampleShipment($formData);
        $resource = new CommonExpressSampleShipmentResource($shipment);

        return $resource->response()->setStatusCode(201);
    }

    public function updateExpressSampleShipment(
        int $id,
        UpsertExpressSampleShipmentRequest $request,
        ExpressSampleShipmentServiceInterface $service,
    ): JsonResponse {
        $formData = $request->validated();

        $shipment = $service->updateExpressSampleShipment($id, $formData);
        $resource = new CommonExpressSampleShipmentResource($shipment);

        return $resource->response();
    }

    public function deleteExpressSampleShipment(
        int $id,
        ExpressSampleShipmentServiceInterface $service,
    ): JsonResponse {
        $service->deleteExpressSampleShipment($id);
        return response()->json()->setStatusCode(204);
    }

    public function approveExpressSampleShipment(
        int $id,
        ExpressSampleShipmentServiceInterface $service,
    ): JsonResponse {
        $shipment = ExpressSampleShipment::query()
            ->with(['items.inventoryItem'])
            ->findOrFail($id);

        if ($shipment->status !== ExpressSampleShipmentStatus::PENDING) {
            return response()->json(
                ['errors' => ['message' => ['宅急便出庫ステータスが「未確認」でなければ承認できません']]],
            )->setStatusCode(400);
        }

        foreach ($shipment->items as $item) {
            $inventoryItem = InventoryItem::query()->find($item->inventory_item_id);
            if (!$inventoryItem) {
                return response()->json(
                    ['errors' => ['message' => ['在庫アイテムが存在しません']]],
                )->setStatusCode(400);
            }

            if ((int) $inventoryItem->left_quantity < (int) $item->quantity) {
                return response()->json(
                    ['errors' => ['message' => ['在庫数量が不足しています']]],
                )->setStatusCode(400);
            }
        }

        $shipment = $service->approveExpressSampleShipment($id);
        $resource = new CommonExpressSampleShipmentResource($shipment);

        return $resource->response();
    }

    public function rejectExpressSampleShipment(
        int $id,
        ExpressSampleShipmentServiceInterface $service,
    ): JsonResponse {
        $shipment = ExpressSampleShipment::query()->findOrFail($id);
        if ($shipment->status !== ExpressSampleShipmentStatus::PENDING) {
            return response()->json()->setStatusCode(400, 'express sample shipment status must be pending to reject');
        }

        $shipment = $service->rejectExpressSampleShipment($id);
        $resource = new CommonExpressSampleShipmentResource($shipment);

        return $resource->response();
    }

    public function cancelExpressSampleShipment(
        int $id,
        ExpressSampleShipmentServiceInterface $service,
    ): JsonResponse {
        $shipment = ExpressSampleShipment::query()->findOrFail($id);
        if (in_array($shipment->status, [
            ExpressSampleShipmentStatus::CANCELLED,
            ExpressSampleShipmentStatus::DELIVERED,
        ], true)) {
            return response()->json()->setStatusCode(400, 'express sample shipment cannot be cancelled');
        }

        $shipment = $service->cancelExpressSampleShipment($id);
        $resource = new CommonExpressSampleShipmentResource($shipment);

        return $resource->response();
    }

    public function getExpressSampleShipmentReports(
        GetExpressSampleShipmentReportListRequest $request,
        ExpressSampleShipmentServiceInterface $service,
    ): JsonResponse {
        $params = $request->validated();
        $itemsPerPage = data_get($params, 'itemsPerPage', 30);
        $page = data_get($params, 'page', 1);
        $warehouseId = data_get($params, 'warehouseId');
        $customerId = data_get($params, 'customerId');
        $status = data_get($params, 'status');
        $startDate = data_get($params, 'startDate');
        $startDate = $startDate ? Carbon::parse($startDate) : null;
        $endDate = data_get($params, 'endDate');
        $endDate = $endDate ? Carbon::parse($endDate) : null;

        $reports = $service->getExpressSampleShipmentReportList(
            $itemsPerPage,
            $page,
            $warehouseId,
            $customerId,
            $status,
            $startDate,
            $endDate,
        );

        $resource = new BaseResourceCollection($reports, ExpressSampleShipmentReportResource::class);
        return $resource->response();
    }

    public function generateExpressSampleShipmentReport(
        GenerateExpressSampleShipmentReportRequest $request,
        ExpressSampleShipmentServiceInterface $service,
    ): JsonResponse {
        $params = $request->validated();
        $shipmentId = data_get($params, 'expressSampleShipmentId');
        $format = data_get($params, 'format', 'excel');

        $report = $service->createExpressSampleShipmentReport($shipmentId, $format);
        $resource = new ExpressSampleShipmentReportResource($report);

        return $resource->response()->setStatusCode(202);
    }

    public function getExpressSampleShipmentReportStatus(
        int $id,
        ExpressSampleShipmentServiceInterface $service,
    ): JsonResponse {
        $report = $service->getExpressSampleShipmentReport($id);
        $resource = new ExpressSampleShipmentReportResource($report);
        return $resource->response();
    }

    public function downloadExpressSampleShipmentReport(
        int $id,
        ExpressSampleShipmentServiceInterface $service,
    ): StreamedResponse {
        $report = $service->getExpressSampleShipmentReport($id);

        if (!$report->isCompleted() || !$report->file_path) {
            abort(404, 'Report file not found or not ready');
        }

        $disk = $report->getStorageDisk();

        if (!Storage::disk($disk)->exists($report->file_path)) {
            abort(404, 'Report file missing on storage');
        }

        $filename = sprintf(
            'express_sample_shipment_report_%s.%s',
            $report->created_at?->format('Y_m_d') ?? $report->id,
            $report->format === 'excel' ? 'xlsx' : $report->format
        );

        return Storage::disk($disk)->download($report->file_path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function getExpressSampleShipmentItems(
        GetExpressSampleShipmentItemsRequest $request,
        ExpressSampleShipmentServiceInterface $service,
    ): JsonResponse {
        $params = $request->validated();
        $itemsPerPage = data_get($params, 'itemsPerPage', 30);
        $page = data_get($params, 'page', 1);
        $shipmentId = data_get($params, 'expressSampleShipmentId');
        $productId = data_get($params, 'productId');
        $desiredDeliveryDate = data_get($params, 'desiredDeliveryDate');
        $desiredDeliveryDate = $desiredDeliveryDate ? Carbon::parse($desiredDeliveryDate) : null;

        $items = $service->getExpressSampleShipmentItems(
            $itemsPerPage,
            $page,
            $shipmentId,
            $productId,
            $desiredDeliveryDate,
        );

        $resource = new BaseResourceCollection($items, CommonExpressSampleShipmentItemResource::class);
        return $resource->response();
    }
}
