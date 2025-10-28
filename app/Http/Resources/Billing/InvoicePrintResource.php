<?php

namespace App\Http\Resources\Billing;

use App\Contracts\Models\InvoiceStatus;
use App\Http\Resources\BaseResource;

class InvoicePrintResource extends BaseResource
{
    protected function compose(): array
    {
        $print = $this->resource;
        $invoice = $print->invoice;

        $invoiceData = null;
        if ($invoice) {
            $status = $invoice->status instanceof InvoiceStatus ? $invoice->status->value : $invoice->status;
            $customer = $invoice->customer;

            $invoiceData = [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'status' => $status,
                'customer' => $customer ? [
                    'id' => $customer->id,
                    'name' => $customer->name,
                ] : null,
                'due_date' => $invoice->due_date,
                'issue_date' => $invoice->issue_date,
                'currency' => $invoice->currency,
                'subtotal_amount' => $invoice->subtotal_amount,
                'tax_amount' => $invoice->tax_amount,
                'total_amount' => $invoice->total_amount,
            ];
        }

        return [
            'id' => $print->id,
            'invoice' => $invoiceData,
            'format' => $print->format,
            'status' => $print->status,
            'download_url' => $print->getDownloadUrl(),
            'error_message' => $print->error_message,
            'created_at' => $print->created_at,
            'completed_at' => $print->completed_at,
            'expires_at' => $print->expires_at,
        ];
    }
}
