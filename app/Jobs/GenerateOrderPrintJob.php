<?php

namespace App\Jobs;

use App\Exports\OrderPrintExcelExport;
use App\Models\OrderPrint;
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

class GenerateOrderPrintJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(private readonly string $printId)
    {
    }

    public function handle(): void
    {
        $print = OrderPrint::query()
            ->with(['order.items', 'order.customer'])
            ->find($this->printId);

        if (! $print) {
            Log::error('OrderPrint not found', ['printId' => $this->printId]);
            return;
        }

        try {
            $print->markAsProcessing();

            $order = $print->order;
            if (! $order) {
                throw new RuntimeException('Order not found for print task.');
            }

            $filePath = $this->generateExcel($print);

            $print->markAsCompleted($filePath);

            Log::info('Order print generated', [
                'printId' => $this->printId,
                'filePath' => $filePath,
            ]);
        } catch (Throwable $exception) {
            $print->markAsFailed($exception->getMessage());

            Log::error('Failed to generate order print', [
                'printId' => $this->printId,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function failed(Throwable $exception): void
    {
        $print = OrderPrint::find($this->printId);
        if ($print) {
            $print->markAsFailed($exception->getMessage());
        }
    }

    private function generateExcel(OrderPrint $print): string
    {
        $order = $print->order->loadMissing(['items', 'customer']);

        $fileName = sprintf('order_print_%s_%s.xlsx', $order->order_number ?? $order->id, now()->format('YmdHis'));
        $relativePath = 'prints/orders/' . $fileName;
        $disk = DocumentStorage::disk($print->storage);

        $export = new OrderPrintExcelExport($order, $print);
        $binary = $export->toBinary();

        Storage::disk($disk)->put($relativePath, $binary);

        return $relativePath;
    }
}
