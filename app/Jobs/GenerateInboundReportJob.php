<?php

namespace App\Jobs;

use App\Contracts\Services\InboundServiceInterface;
use App\Models\Customer;
use App\Models\InboundReport;
use App\Models\InboundItem;
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

class GenerateInboundReportJob implements ShouldQueue
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
    public function handle(InboundServiceInterface $inboundService): void
    {
        $report = InboundReport::find($this->reportId);

        if (!$report) {
            Log::error('InboundReport not found', ['reportId' => $this->reportId]);
            return;
        }

        try {
            $report->markAsProcessing();

            // 获取入库数据
            $inboundData = $this->getInboundDataForReport($report->inbound_id);

            // 生成报告文件
            $filePath = $this->generateReportFile($report, $inboundData);

            $report->markAsCompleted($filePath);

            Log::info('Inbound report generated successfully', [
                'reportId' => $this->reportId,
                'filePath' => $filePath,
            ]);

        } catch (Exception $e) {
            $report->markAsFailed($e->getMessage());

            Log::error('Failed to generate inbound report', [
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
        $report = InboundReport::find($this->reportId);

        if ($report) {
            $report->markAsFailed($exception?->getMessage() ?? 'Unknown error');
        }
    }

    private function getInboundDataForReport(int $inboundId): array
    {
        $inbound = \App\Models\Inbound::query()
            ->with(['warehouse', 'customer', 'items.product'])
            ->findOrFail($inboundId);

        $inboundItems = $inbound->items()
            ->with(['product'])
            ->orderBy('product_id')
            ->orderBy('lot_number')
            ->get();

        $totalQuantity = $inboundItems->sum('quantity');

        return [
            'inbound' => $inbound,
            'inboundItems' => $inboundItems,
            'totalQuantity' => $totalQuantity,
            'warehouse' => $inbound->warehouse,
            'customer' => $inbound->customer,
        ];
    }

    private function generateReportFile(InboundReport $report, array $inboundData): string
    {
        $filename = sprintf(
            'inbound_report_%d_%s.%s',
            $report->id,
            now()->format('Y_m_d_H_i_s'),
            $report->format === 'excel' ? 'xlsx' : 'pdf'
        );

        if ($report->format === 'pdf') {
            return $this->generatePdfReport($filename, $report, $inboundData);
        } else {
            return $this->generateExcelReport($filename, $report, $inboundData);
        }
    }

    private function generatePdfReport(string $filename, InboundReport $report, array $inboundData): string
    {
        // 使用真实的入库数据
        $inboundItems = $inboundData['inboundItems'];
        /**
         * @var Warehouse $warehouse
         */
        $warehouse = $inboundData['warehouse'];
        /**
         * @var Customer $customer
         */
        $customer = $inboundData['customer'];
        $inbound = $inboundData['inbound'];

        // 转换入库数据为报告格式
        $rows = $inboundItems->map(function (InboundItem $item) {
            // 处理入库日期
            $inboundDate = null;
            if ($item->inbound_date) {
                $inboundDate = Carbon::parse($item->inbound_date);
            }

            $product = $item->product;

            // 处理制造日期
            $manufacturedDate = null;
            if ($item->manufacture_date) {
                $manufacturedDate = Carbon::parse($item->manufacture_date);
            }

            // 处理保质期
            $bestBeforeDate = null;
            if ($item->best_before_date) {
                $bestBeforeDate = Carbon::parse($item->best_before_date);
            }

            // 计算重量
            $quantity = $item->quantity ?? 0;
            $unitWeight = $product?->unit_weight ?? 0;
            $subQuantity = $item->sub_quantity ?? 0;
            $unitsPerCase = $product?->sub_package_count ?? 0;
            $subPackageWeight = 0;
            if ($subQuantity > 0 && $unitsPerCase > 0) {
                $subPackageWeight = $subQuantity / $unitsPerCase * $unitWeight;
            }
            $totalWeight = $unitWeight * $quantity + $subPackageWeight;

            return (object)[
                'product_name'      => $product?->name ?? '',
                'specifications'    => $product?->dimension_description ?? '',
                'unit_quantity'     => $product?->unit_weight ?? 0,
                'quantity'          => $quantity,
                'sub_quantity'      => $subQuantity,
                'weight'            => $totalWeight,
                'lot_number'        => $item->lot_number ?? '',
                'best_before_date'  => $bestBeforeDate,
                'notes'             => $item->notes ?? '',
            ];
        });

        $totals = [
            'quantity' => $inboundData['totalQuantity'],
            'weight' => $inboundItems->sum(function ($item) {
                $product = $item->product;
                $quantity = $item->quantity ?? 0;
                $unitWeight = $product?->unit_weight ?? 0;
                $subQuantity = $item->sub_quantity ?? 0;
                $unitsPerCase = $product?->sub_package_count ?? 0;
                $subPackageWeight = 0;
                if ($subQuantity > 0 && $unitsPerCase > 0) {
                    $subPackageWeight = $subQuantity / $unitsPerCase * $unitWeight;
                }
                return $unitWeight * $quantity + $subPackageWeight;
            })
        ];

        $data = [
            'reportDate' => now()->format('Y年n月j日'),
            'inbound'    => $inbound,
            'warehouse'  => $warehouse,
            'customer'   => $customer,
            'rows'       => $rows,
            'totals'     => $totals,
        ];

        // 生成html用于调试
        if (config('app.debug', false)) {
            $htmlContent = view('reports.inbound', $data)->render();
            $debugHtmlPath = 'reports/debug_' . str_replace('.pdf', '.html', $filename);
            Storage::disk('public')->put($debugHtmlPath, $htmlContent);
            Log::info('Debug HTML generated', ['path' => $debugHtmlPath]);
        }

        $pdf = Pdf::loadView('reports.inbound', $data);

        $pdf->setPaper('A4', 'portrait');

        // 根据报告的存储类型选择磁盘
        $disk = $report->isS3Storage() ? 's3' : 'public';
        $filePath = 'reports/' . $filename;

        Storage::disk($disk)->put($filePath, $pdf->output());

        return $filePath;
    }

    private function generateExcelReport(string $filename, InboundReport $report, array $inboundData): string
    {
        // TODO: Implement Excel generation if needed
        // For now, generate PDF as fallback
        return $this->generatePdfReport($filename, $report, $inboundData);
    }
}
