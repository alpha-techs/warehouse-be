<?php

namespace App\Http\Controllers;

use App\Contracts\Services\InvoiceServiceInterface;
use App\Http\Requests\Billing\CancelInvoiceRequest;
use App\Http\Requests\Billing\CreateInvoiceRequest;
use App\Http\Requests\Billing\IssueInvoiceRequest;
use App\Http\Requests\Billing\ListInvoicesRequest;
use App\Http\Resources\Billing\InvoiceResource;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

final class InvoiceController extends Controller
{
    public function getInvoices(
        ListInvoicesRequest $request,
        InvoiceServiceInterface $invoiceService,
    ): JsonResponse {
        $params = $request->validated();
        $itemsPerPage = (int) data_get($params, 'pageSize', 20);
        $page = (int) data_get($params, 'page', 1);
        $customerId = data_get($params, 'customerId');
        $status = data_get($params, 'status');
        $outboundDateFrom = data_get($params, 'outboundDateFrom');
        $outboundDateTo = data_get($params, 'outboundDateTo');

        $outboundDateFrom = $outboundDateFrom ? Carbon::parse($outboundDateFrom) : null;
        $outboundDateTo = $outboundDateTo ? Carbon::parse($outboundDateTo) : null;

        $invoices = $invoiceService->getInvoices(
            itemsPerPage: $itemsPerPage,
            page: $page,
            customerId: $customerId,
            status: $status,
            outboundDateFrom: $outboundDateFrom,
            outboundDateTo: $outboundDateTo,
        );

        $resourceCollection = InvoiceResource::collection($invoices);

        return $resourceCollection->response();
    }

    public function createInvoice(
        CreateInvoiceRequest $request,
        InvoiceServiceInterface $invoiceService,
    ): JsonResponse {
        $invoice = $invoiceService->createInvoice($request->validated());
        $resource = new InvoiceResource($invoice);

        return $resource->response();
    }

    public function getInvoice(
        int $id,
        InvoiceServiceInterface $invoiceService,
    ): JsonResponse {
        $invoice = $invoiceService->getInvoice($id);
        $resource = new InvoiceResource($invoice);

        return $resource->response();
    }

    public function issueInvoice(
        int $id,
        IssueInvoiceRequest $request,
        InvoiceServiceInterface $invoiceService,
    ): JsonResponse {
        $payload = $request->validated();
        $issueDateValue = data_get($payload, 'issueDate');
        $issueDate = $issueDateValue ? Carbon::parse($issueDateValue) : null;
        $message = data_get($payload, 'message');

        $invoice = $invoiceService->issueInvoice($id, $issueDate, $message);
        $resource = new InvoiceResource($invoice);

        return $resource->response();
    }

    public function cancelInvoice(
        int $id,
        CancelInvoiceRequest $request,
        InvoiceServiceInterface $invoiceService,
    ): JsonResponse {
        $payload = $request->validated();
        $reason = data_get($payload, 'reason');

        $invoice = $invoiceService->cancelInvoice($id, $reason);
        $resource = new InvoiceResource($invoice);

        return $resource->response();
    }
}
