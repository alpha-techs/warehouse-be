<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Support\Str;

class InvoiceObserver
{
    public function creating(Invoice $invoice): void
    {
        if (empty($invoice->invoice_number)) {
            $invoice->invoice_number = $this->generateInvoiceNumber();
        }

        if ($invoice->customer_id && empty($invoice->customer_name)) {
            $customer = Customer::find($invoice->customer_id);
            $invoice->customer_name = $customer?->name;
        }
    }

    public function updating(Invoice $invoice): void
    {
        if ($invoice->isDirty('customer_id')) {
            $customer = Customer::find($invoice->customer_id);
            $invoice->customer_name = $customer?->name;
        }
    }

    private function generateInvoiceNumber(): string
    {
        do {
            $invoiceNumber = sprintf(
                'INV-%s-%s',
                now()->format('Ymd'),
                Str::upper(Str::random(4))
            );
        } while (Invoice::whereInvoiceNumber($invoiceNumber)->exists());

        return $invoiceNumber;
    }
}
