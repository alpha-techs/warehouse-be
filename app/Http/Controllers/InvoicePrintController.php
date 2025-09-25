<?php

namespace App\Http\Controllers;

use App\Contracts\Services\InvoicePrintServiceInterface;
use App\Http\Requests\Billing\GenerateInvoicePrintRequest;
use App\Http\Requests\Billing\ListInvoicePrintsRequest;
use App\Http\Resources\BaseResourceCollection;
use App\Http\Resources\Billing\InvoicePrintResource;
use App\Models\InvoicePrint;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class InvoicePrintController extends Controller
{
    public function getInvoicePrints(
        ListInvoicePrintsRequest $request,
        InvoicePrintServiceInterface $service,
    ): JsonResponse {
        $params = $request->validated();
        $itemsPerPage = (int) data_get($params, 'itemsPerPage', 20);
        $page = (int) data_get($params, 'page', 1);
        $invoiceNumber = data_get($params, 'invoiceNumber');
        $customerId = data_get($params, 'customerId');
        $status = data_get($params, 'status');
        $startDate = data_get($params, 'startDate');
        $endDate = data_get($params, 'endDate');

        $customerId = $customerId !== null ? (int) $customerId : null;

        $startDate = $startDate ? Carbon::parse($startDate) : null;
        $endDate = $endDate ? Carbon::parse($endDate) : null;

        $prints = $service->getInvoicePrints(
            $itemsPerPage,
            $page,
            $invoiceNumber,
            $customerId,
            $status,
            $startDate,
            $endDate,
        );

        $resources = new BaseResourceCollection($prints, InvoicePrintResource::class);

        return $resources->response();
    }

    public function generateInvoicePrint(
        GenerateInvoicePrintRequest $request,
        InvoicePrintServiceInterface $service,
    ): JsonResponse {
        $params = $request->validated();
        $invoiceId = (int) data_get($params, 'invoiceId');
        $format = data_get($params, 'format', 'excel');

        $print = $service->createInvoicePrint($invoiceId, $format);

        $resource = new InvoicePrintResource($print);
        return $resource->response()->setStatusCode(202);
    }

    public function getInvoicePrintStatus(
        string $printId,
        InvoicePrintServiceInterface $service,
    ): JsonResponse {
        $print = $service->getInvoicePrint($printId);

        $resource = new InvoicePrintResource($print);
        return $resource->response();
    }

    public function downloadInvoicePrint(string $printId): StreamedResponse
    {
        $print = InvoicePrint::findOrFail($printId);

        if (! $print->isCompleted() || ! $print->file_path) {
            abort(404, 'Invoice print not ready');
        }

        if ($print->expires_at && $print->expires_at->isPast()) {
            abort(410, 'Invoice print download has expired');
        }

        $disk = $print->getStorageDisk();

        if (! Storage::disk($disk)->exists($print->file_path)) {
            abort(404, 'Invoice print file not found');
        }

        $invoice = $print->invoice;
        $filename = sprintf(
            'invoice_print_%s_%s.xlsx',
            $invoice?->invoice_number ?? $print->invoice_id,
            $print->created_at?->format('Ymd') ?? now()->format('Ymd')
        );

        return Storage::disk($disk)->download($print->file_path, $filename);
    }
}
