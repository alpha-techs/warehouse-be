<?php

namespace App\Services;

use App\Contracts\Models\InvoiceStatus;
use App\Contracts\Services\InvoiceServiceInterface;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Outbound;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

final class InvoiceService implements InvoiceServiceInterface
{
    public function getInvoices(
        int $itemsPerPage = 20,
        int $page = 1,
        ?int $customerId = null,
        ?string $status = null,
        ?Carbon $outboundDateFrom = null,
        ?Carbon $outboundDateTo = null,
    ): Paginator {
        $query = Invoice::query()
            ->with(['items.product', 'customer', 'outbound']);

        if ($customerId) {
            $query->whereCustomerId($customerId);
        }

        if ($status) {
            $query->whereStatus($status);
        }

        if ($outboundDateFrom || $outboundDateTo) {
            $query->whereHas('outbound', function ($relation) use ($outboundDateFrom, $outboundDateTo) {
                if ($outboundDateFrom) {
                    $relation->whereDate('outbound_date', '>=', $outboundDateFrom);
                }

                if ($outboundDateTo) {
                    $relation->whereDate('outbound_date', '<=', $outboundDateTo);
                }
            });
        }

        $query->orderByDesc('issue_date');
        $query->orderByDesc('created_at');

        return $query->paginate($itemsPerPage, ['*'], 'page', $page);
    }

    /**
     * @throws Throwable
     */
    public function createInvoice(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $customerId = (int) data_get($data, 'customerId');
            $customer = Customer::query()->find($customerId);
            if (! $customer) {
                throw ValidationException::withMessages([
                    'customerId' => ['指定的客户不存在。'],
                ]);
            }

            $outboundId = (int) data_get($data, 'outboundId');

            $outbound = Outbound::query()
                ->with(['items'])
                ->find($outboundId);

            if (! $outbound) {
                throw ValidationException::withMessages([
                    'outboundId' => ['指定的出库单不存在。'],
                ]);
            }

            if ($outbound->customer_id !== $customer->id) {
                throw ValidationException::withMessages([
                    'outboundId' => ['出库单与所选客户不匹配。'],
                ]);
            }

            $lineItems = $this->buildInvoiceItems($outbound);

            if ($lineItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'outboundId' => ['选定的出库单没有可计费的明细。'],
                ]);
            }

            $subtotal = (int) $lineItems->sum('line_amount');
            $taxAmount = (int) $lineItems->sum('tax_amount');
            $totalAmount = $subtotal + $taxAmount;

            $invoiceData = [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'outbound_id' => $outbound->id,
                'status' => InvoiceStatus::DRAFT,
                'due_date' => data_get($data, 'dueDate'),
                'currency' => data_get($data, 'currency') ?: ($outbound->currency ?? 'JPY'),
                'subtotal_amount' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'notes' => data_get($data, 'notes'),
            ];

            if (data_get($data, 'autoIssue')) {
                $invoiceData['status'] = InvoiceStatus::ISSUED;
                $invoiceData['issue_date'] = Carbon::now();
            }

            $invoice = Invoice::create($invoiceData);
            $invoice->items()->createMany($lineItems->toArray());

            return $invoice->fresh(['items.product', 'customer', 'outbound']);
        });
    }

    public function getInvoice(int $id): Invoice
    {
        return Invoice::query()
            ->with(['items.product', 'customer', 'outbound'])
            ->findOrFail($id);
    }

    /**
     * @throws Throwable
     */
    public function issueInvoice(int $id, ?Carbon $issueDate = null, ?string $message = null): Invoice
    {
        return DB::transaction(function () use ($id, $issueDate, $message) {
            $invoice = Invoice::query()->findOrFail($id);

            if ($invoice->status === InvoiceStatus::CANCELLED) {
                throw ValidationException::withMessages([
                    'status' => ['已取消的发票无法再次下发。'],
                ]);
            }

            if ($invoice->status === InvoiceStatus::PAID) {
                throw ValidationException::withMessages([
                    'status' => ['已付款的发票无法再次下发。'],
                ]);
            }

            if ($invoice->status !== InvoiceStatus::DRAFT && $invoice->status !== InvoiceStatus::ISSUED) {
                throw ValidationException::withMessages([
                    'status' => ['只有草稿或已下发的发票可以执行下发操作。'],
                ]);
            }

            $invoice->status = InvoiceStatus::ISSUED;
            $invoice->issue_date = $issueDate ?? Carbon::now();
            if (! is_null($message)) {
                $invoice->issue_message = $message;
            }
            $invoice->save();

            return $invoice->fresh(['items.product', 'customer', 'outbound']);
        });
    }

    /**
     * @throws Throwable
     */
    public function cancelInvoice(int $id, ?string $reason = null): Invoice
    {
        return DB::transaction(function () use ($id, $reason) {
            $invoice = Invoice::query()->findOrFail($id);

            if ($invoice->status === InvoiceStatus::PAID) {
                throw ValidationException::withMessages([
                    'status' => ['已付款的发票不能取消。'],
                ]);
            }

            if ($invoice->status === InvoiceStatus::CANCELLED) {
                return $invoice->fresh(['items.product', 'customer', 'outbound']);
            }

            $invoice->status = InvoiceStatus::CANCELLED;
            if (! is_null($reason)) {
                $invoice->cancel_reason = $reason;
            }
            $invoice->save();

            return $invoice->fresh(['items.product', 'customer', 'outbound']);
        });
    }

    private function buildInvoiceItems(Outbound $outbound): Collection
    {
        $lineItems = collect();

        foreach ($outbound->items as $item) {
            $lineItems->push([
                'outbound_id' => $outbound->id,
                'outbound_item_id' => $item->id,
                'outbound_order_id' => $outbound->outbound_order_id,
                'outbound_date' => $outbound->outbound_date,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'currency' => $item->currency ?? $outbound->currency ?? 'JPY',
                'unit_price' => $item->unit_price ?? 0,
                'line_amount' => $item->line_amount ?? 0,
                'tax_amount' => $item->tax_amount ?? 0,
                'note' => $item->note,
            ]);
        }

        return $lineItems;
    }
}
