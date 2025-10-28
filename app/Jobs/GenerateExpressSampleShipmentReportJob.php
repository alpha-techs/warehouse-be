<?php

namespace App\Jobs;

use App\Exports\ExpressSampleShipmentExcelExport;
use App\Models\ExpressSampleShipment;
use App\Models\ExpressSampleShipmentReport;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateExpressSampleShipmentReportJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $timeout = 300;
    public int $tries = 3;

    public function __construct(private readonly int $reportId)
    {
    }

    public function handle(): void
    {
        $report = ExpressSampleShipmentReport::find($this->reportId);

        if (!$report) {
            Log::error('ExpressSampleShipmentReport not found', ['reportId' => $this->reportId]);
            return;
        }

        try {
            $report->markAsProcessing();

            $shipment = ExpressSampleShipment::query()
                ->with([
                    'warehouse',
                    'customer',
                    'items.product',
                    'items.inventoryItem',
                ])
                ->findOrFail($report->express_sample_shipment_id);

            $filePath = $this->generateExcelReport($report, $shipment);
            $expiresAt = now()->addDays(7);

            $report->markAsCompleted($filePath, $expiresAt);

            Log::info('Express sample shipment report generated', [
                'reportId' => $this->reportId,
                'filePath' => $filePath,
            ]);
        } catch (Exception $exception) {
            $report->markAsFailed($exception->getMessage());

            Log::error('Failed to generate express sample shipment report', [
                'reportId' => $this->reportId,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function failed(?Exception $exception): void
    {
        if (!$exception) {
            return;
        }

        $report = ExpressSampleShipmentReport::find($this->reportId);
        if ($report && !$report->isFailed()) {
            $report->markAsFailed($exception->getMessage());
        }
    }

    private function generateExcelReport(ExpressSampleShipmentReport $report, ExpressSampleShipment $shipment): string
    {
        $export = new ExpressSampleShipmentExcelExport($shipment, $report);
        $binary = $export->toBinary();

        $filename = sprintf(
            'express_sample_shipment_report_%d_%s.xlsx',
            $report->id,
            now()->format('Ymd_His')
        );
        $filePath = 'reports/express_sample_shipments/' . $filename;

        Storage::disk($report->getStorageDisk())->put($filePath, $binary);

        return $filePath;
    }
}

