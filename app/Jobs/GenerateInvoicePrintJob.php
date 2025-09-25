<?php

namespace App\Jobs;

use App\Exports\InvoicePrintExcelExport;
use App\Models\InvoicePrint;
use App\Support\DocumentStorage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class GenerateInvoicePrintJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(private readonly string $printId)
    {
    }

    public function handle(): void
    {
        $print = InvoicePrint::query()
            ->with(['invoice.items', 'invoice.customer'])
            ->find($this->printId);

        if (! $print) {
            Log::error('InvoicePrint not found', ['printId' => $this->printId]);
            return;
        }

        try {
            $print->markAsProcessing();

            $invoice = $print->invoice;
            if (! $invoice) {
                throw new RuntimeException('Invoice not found for print task.');
            }

            $filePath = $this->generateExcel($print);

            $print->markAsCompleted($filePath);

            Log::info('Invoice print generated', [
                'printId' => $this->printId,
                'filePath' => $filePath,
            ]);
        } catch (Throwable $exception) {
            $print->markAsFailed($exception->getMessage());

            Log::error('Failed to generate invoice print', [
                'printId' => $this->printId,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function failed(Throwable $exception): void
    {
        $print = InvoicePrint::find($this->printId);
        if ($print) {
            $print->markAsFailed($exception->getMessage());
        }
    }

    private function generateExcel(InvoicePrint $print): string
    {
        $invoice = $print->invoice->loadMissing(['items', 'customer']);

        $fileName = sprintf('invoice_print_%s_%s.xlsx', $invoice->invoice_number ?? $invoice->id, now()->format('YmdHis'));
        $relativePath = 'prints/invoices/' . $fileName;
        $disk = DocumentStorage::disk($print->storage);

        $export = new InvoicePrintExcelExport($invoice, $print);
        $binary = $export->toBinary();

        Storage::disk($disk)->put($relativePath, $binary);

        return $relativePath;
    }
}
