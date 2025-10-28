<?php

namespace App\Contracts\Services;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;

interface InvoiceServiceInterface
{
    public function getInvoices(
        int $itemsPerPage = 20,
        int $page = 1,
        ?int $customerId = null,
        ?string $status = null,
        ?Carbon $outboundDateFrom = null,
        ?Carbon $outboundDateTo = null,
    ): Paginator;

    public function createInvoice(array $data): Invoice;

    public function getInvoice(int $id): Invoice;

    public function issueInvoice(int $id, ?Carbon $issueDate = null, ?string $message = null): Invoice;

    public function cancelInvoice(int $id, ?string $reason = null): Invoice;
}
