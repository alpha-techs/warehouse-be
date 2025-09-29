<?php

namespace App\Http\Controllers;

use App\Contracts\Services\OrderPrintServiceInterface;
use App\Http\Requests\Procurement\GenerateOrderPrintRequest;
use App\Http\Requests\Procurement\ListOrderPrintsRequest;
use App\Http\Resources\Procurement\OrderPrintCreatedResource;
use App\Http\Resources\Procurement\OrderPrintResource;
use App\Models\OrderPrint;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class OrderPrintController extends Controller
{
    public function getOrderPrints(
        ListOrderPrintsRequest $request,
        OrderPrintServiceInterface $service,
    ): JsonResponse {
        $params = $request->validated();
        $itemsPerPage = (int) data_get($params, 'itemsPerPage', 20);
        $page = (int) data_get($params, 'page', 1);
        $orderNumber = data_get($params, 'orderNumber');
        $customerId = data_get($params, 'customerId');
        $status = data_get($params, 'status');
        $startDate = data_get($params, 'startDate');
        $endDate = data_get($params, 'endDate');

        $customerId = $customerId !== null ? (int) $customerId : null;
        $startDate = $startDate ? Carbon::parse($startDate) : null;
        $endDate = $endDate ? Carbon::parse($endDate) : null;

        $prints = $service->getOrderPrints(
            $itemsPerPage,
            $page,
            $orderNumber,
            $customerId,
            $status,
            $startDate,
            $endDate,
        );

        $resources = OrderPrintResource::collection($prints);

        return $resources->response();
    }

    public function generateOrderPrint(
        GenerateOrderPrintRequest $request,
        OrderPrintServiceInterface $service,
    ): JsonResponse {
        $params = $request->validated();
        $orderId = (int) data_get($params, 'orderId');
        $format = data_get($params, 'format', 'excel');

        $print = $service->createOrderPrint($orderId, $format);

        $resource = new OrderPrintCreatedResource($print);
        return $resource->response()->setStatusCode(202);
    }

    public function getOrderPrintStatus(
        string $printId,
        OrderPrintServiceInterface $service,
    ): JsonResponse {
        $print = $service->getOrderPrint($printId);

        $resource = new OrderPrintResource($print);
        return $resource->response();
    }

    public function downloadOrderPrint(string $printId): StreamedResponse
    {
        $print = OrderPrint::findOrFail($printId);

        if (! $print->isCompleted() || ! $print->file_path) {
            abort(404, 'Order print not ready');
        }

        if ($print->expires_at && $print->expires_at->isPast()) {
            abort(410, 'Order print download has expired');
        }

        $disk = $print->getStorageDisk();

        if (! Storage::disk($disk)->exists($print->file_path)) {
            abort(404, 'Order print file not found');
        }

        $order = $print->order;
        $filename = sprintf(
            'order_print_%s_%s.xlsx',
            $order?->order_number ?? $print->order_id,
            $print->created_at?->format('Ymd') ?? now()->format('Ymd')
        );

        return Storage::disk($disk)->download($print->file_path, $filename);
    }
}
