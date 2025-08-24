<?php

namespace App\Jobs;

use App\Contracts\Services\OutboundServiceInterface;
use App\Models\Customer;
use App\Models\OutboundReport;
use App\Models\OutboundItem;
use App\Models\Warehouse;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerateOutboundReportJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $timeout = 300; // 5 minutes
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly int $reportId,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(OutboundServiceInterface $outboundService): void
    {
        $report = OutboundReport::find($this->reportId);

        if (!$report) {
            Log::error('OutboundReport not found', ['reportId' => $this->reportId]);
            return;
        }

        try {
            $report->markAsProcessing();

            // 获取出库数据
            $outboundData = $this->getOutboundDataForReport($report->outbound_id);

            // 生成报告文件
            $filePath = $this->generateReportFile($report, $outboundData);

            $report->markAsCompleted($filePath);

            Log::info('Outbound report generated successfully', [
                'reportId' => $this->reportId,
                'filePath' => $filePath,
            ]);

        } catch (Exception $e) {
            $report->markAsFailed($e->getMessage());

            Log::error('Failed to generate outbound report', [
                'reportId' => $this->reportId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Exception $exception): void
    {
        $report = OutboundReport::find($this->reportId);

        if ($report) {
            $report->markAsFailed($exception?->getMessage() ?? 'Unknown error');
        }
    }

    private function getOutboundDataForReport(int $outboundId): array
    {
        $outbound = \App\Models\Outbound::query()
            ->with(['warehouse', 'customer', 'items.product'])
            ->findOrFail($outboundId);

        $outboundItems = $outbound->items()
            ->with(['product'])
            ->orderBy('product_id')
            ->orderBy('lot_number')
            ->get();

        $totalQuantity = $outboundItems->sum('quantity');

        return [
            'outbound' => $outbound,
            'outboundItems' => $outboundItems,
            'totalQuantity' => $totalQuantity,
            'warehouse' => $outbound->warehouse,
            'customer' => $outbound->customer,
        ];
    }

    private function generateReportFile(OutboundReport $report, array $outboundData): string
    {
        $filename = sprintf(
            'outbound_report_%d_%s.%s',
            $report->id,
            now()->format('Y_m_d_H_i_s'),
            $report->format === 'excel' ? 'xlsx' : 'pdf'
        );

        if ($report->format === 'pdf') {
            return $this->generatePdfReport($filename, $report, $outboundData);
        } else {
            return $this->generateExcelReport($filename, $report, $outboundData);
        }
    }

    private function generatePdfReport(string $filename, OutboundReport $report, array $outboundData): string
    {
        // 使用真实的出库数据
        $outboundItems = $outboundData['outboundItems'];
        /**
         * @var Warehouse $warehouse
         */
        $warehouse = $outboundData['warehouse'];
        /**
         * @var Customer $customer
         */
        $customer = $outboundData['customer'];
        $outbound = $outboundData['outbound'];

        // 转换出库数据为报告格式
        $rows = $outboundItems->map(function (OutboundItem $item) {
            $product = $item->product;

            return (object)[
                'inbound_lot_number' => $item->inbound_item?->lot_number ?? '',
                'product_name'       => $product?->name ?? '',
                'specifications'     => $product?->dimension_description ?? '',
                'original_quantity'  => $item->inventory_item?->left_quantity ?? 0,
                'quantity'           => $item->quantity ?? 0,
                'notes'              => $item->notes ?? '',
            ];
        });

        $totals = [
            'quantity' => $outboundData['totalQuantity']
        ];

        $data = [
            'reportDate' => now()->format('Y年n月j日'),
            'outbound'   => $outbound,
            'warehouse'  => $warehouse,
            'customer'   => $customer,
            'rows'       => $rows,
            'totals'     => $totals,
        ];

        // 生成html用于调试
        if (config('app.debug', false)) {
            $htmlContent = view('reports.outbound', $data)->render();
            $debugHtmlPath = 'reports/debug_' . str_replace('.pdf', '.html', $filename);
            Storage::disk('public')->put($debugHtmlPath, $htmlContent);
            Log::info('Debug HTML generated', ['path' => $debugHtmlPath]);
        }

        $pdf = Pdf::loadView('reports.outbound', $data);

        $pdf->setPaper('A4', 'portrait');

        // 根据报告的存储类型选择磁盘
        $disk = $report->isS3Storage() ? 's3' : 'public';
        $filePath = 'reports/' . $filename;

        Storage::disk($disk)->put($filePath, $pdf->output());

        return $filePath;
    }

    private function generateExcelReport(string $filename, OutboundReport $report, array $outboundData): string
    {
        // TODO: Implement Excel generation if needed
        // For now, generate PDF as fallback
        return $this->generatePdfReport($filename, $report, $outboundData);
    }
}
