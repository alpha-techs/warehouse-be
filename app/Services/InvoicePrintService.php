<?php

namespace App\Services;

use App\Contracts\Models\InvoiceStatus;
use App\Contracts\Services\InvoicePrintServiceInterface;
use App\Jobs\GenerateInvoicePrintJob;
use App\Models\Invoice;
use App\Models\InvoicePrint;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class InvoicePrintService implements InvoicePrintServiceInterface
{
    public function getInvoicePrints(
        int $itemsPerPage = 20,
        int $page = 1,
        ?string $invoiceNumber = null,
        ?int $customerId = null,
        ?string $status = null,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
    ): Paginator {
        $query = InvoicePrint::query()
            ->with(['invoice.customer'])
            ->orderByDesc('created_at');

        if ($invoiceNumber) {
            $query->whereHas('invoice', function ($relation) use ($invoiceNumber) {
                $relation->where('invoice_number', 'like', '%' . $invoiceNumber . '%');
            });
        }

        if ($customerId) {
            $query->whereHas('invoice', function ($relation) use ($customerId) {
                $relation->where('customer_id', $customerId);
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return $query->paginate($itemsPerPage, ['*'], 'page', $page);
    }

    public function getInvoicePrint(string $id): InvoicePrint
    {
        return InvoicePrint::query()
            ->with(['invoice.customer'])
            ->findOrFail($id);
    }

    /**
     * @throws ValidationException
     */
    public function createInvoicePrint(int $invoiceId, string $format = 'excel'): InvoicePrint
    {
        if ($format !== 'excel') {
            throw ValidationException::withMessages([
                'format' => ['目前仅支持生成 Excel 文件。'],
            ]);
        }

        return DB::transaction(function () use ($invoiceId, $format) {
            $invoice = Invoice::query()
                ->with(['items', 'customer', 'outbound'])
                ->findOrFail($invoiceId);

            if (! in_array($invoice->status, [InvoiceStatus::ISSUED, InvoiceStatus::PAID], true)) {
                throw new HttpException(409, '发票状态不支持生成打印版');
            }

            $print = InvoicePrint::create([
                'invoice_id' => $invoice->id,
                'format' => $format,
                'status' => 'pending',
                'storage' => InvoicePrint::STORAGE_LOCAL,
            ]);

            GenerateInvoicePrintJob::dispatch($print->id);

            return $print->fresh(['invoice.customer']);
        });
    }
}
