<?php

namespace App\Contracts\Services;

use App\Models\InvoicePrint;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;

interface InvoicePrintServiceInterface
{
    public function getInvoicePrints(
        int $itemsPerPage = 20,
        int $page = 1,
        ?string $invoiceNumber = null,
        ?int $customerId = null,
        ?string $status = null,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
    ): Paginator;

    public function getInvoicePrint(string $id): InvoicePrint;

    public function createInvoicePrint(int $invoiceId, string $format = 'excel'): InvoicePrint;
}
