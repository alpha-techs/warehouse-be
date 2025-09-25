<?php

namespace App\Jobs;

use App\Contracts\Services\InventoryServiceInterface;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\InventoryReport;
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

class GenerateInventoryReportJob implements ShouldQueue
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
    public function handle(InventoryServiceInterface $inventoryService): void
    {
        $report = InventoryReport::find($this->reportId);

        if (!$report) {
            Log::error('InventoryReport not found', ['reportId' => $this->reportId]);
            return;
        }

        try {
            $report->markAsProcessing();

            // 获取库存数据
            $inventoryData = $inventoryService->getInventoryDataForReportData(
                $report->warehouse_id,
                $report->customer_id
            );

            // 生成报告文件
            $filePath = $this->generateReportFile($report, $inventoryData);

            $report->markAsCompleted($filePath);

            Log::info('Inventory report generated successfully', [
                'reportId' => $this->reportId,
                'filePath' => $filePath,
            ]);

        } catch (Exception $e) {
            $report->markAsFailed($e->getMessage());

            Log::error('Failed to generate inventory report', [
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
        $report = InventoryReport::find($this->reportId);

        if ($report) {
            $report->markAsFailed($exception?->getMessage() ?? 'Unknown error');
        }
    }

    private function generateReportFile(InventoryReport $report, array $inventoryData): string
    {
        $filename = sprintf(
            'inventory_report_%d_%s.%s',
            $report->id,
            now()->format('Y_m_d_H_i_s'),
            $report->format === 'excel' ? 'xlsx' : 'pdf'
        );

        if ($report->format === 'pdf') {
            return $this->generatePdfReport($filename, $report, $inventoryData);
        } else {
            return $this->generateExcelReport($filename, $report, $inventoryData);
        }
    }

    private function generatePdfReport(string $filename, InventoryReport $report, array $inventoryData): string
    {
        // 使用真实的库存数据
        $inventoryItems = $inventoryData['inventoryItems'];
        /**
         * @var Warehouse $warehouse
         */
        $warehouse = $inventoryData['warehouse'];
        /**
         * @var Customer $customer
         */
        $customer = $inventoryData['customer'];

        // 转换库存数据为报告格式
        $rows = $inventoryItems->map(function (InventoryItem $item) {
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
            $leftQuantity = $item->left_quantity ?? 0;
            $unitWeight = $product?->unit_weight ?? 0;
            $leftSubQuantity = $item->left_sub_quantity ?? 0;
            $unitsPerCase = $product?->sub_package_count ?? 0;
            $subPackageWeight = 0;
            if ($leftSubQuantity > 0 && $unitsPerCase > 0) {
                $subPackageWeight = $leftSubQuantity / $unitsPerCase * $unitWeight;
            }
            $onhandWeight = $unitWeight * $leftQuantity + $subPackageWeight;

            return (object)[
                'receipt_no'        => $item->inbound_item?->inbound?->receipt_no ?? $item->lot_number ?? '-',
                'product_sku'       => $item->product?->sku ?? '',
                'inbound_date'      => $inboundDate,
                'product_name'      => $product?->name ?? '',
                'pack_spec_text'    => $product?->dimension_description ?? '',
                'cargo_mark'        => $product?->cargo_mark ?? '',
                'is_fixed_weight'   => $product?->is_fixed_weight ?? true,
                'unit_weight'       => $unitWeight,
                'weight_unit'       => $product?->weight_unit ?? '',
                'inbound_quantity'  => $item->inbound_quantity ?? 0,
                'left_quantity'     => $leftQuantity,
                'left_sub_quantity' => $leftSubQuantity,
                'units_per_case'    => $unitsPerCase,
                'onhand_weight'     => $onhandWeight,
                'manufactured_date' => $manufacturedDate,
                'best_before_date'  => $bestBeforeDate,
                'contract_no'       => null,
                'client_name'       => null,
                'client_code'       => null,
                'vessel_name'       => $item->ship_name ?? '',
            ];
        });

        $totals = [
            'left_quantity' => $inventoryData['totalQuantity']
        ];

        $issuer = [
            'zip'     => $customer->postal_code,
            'address' => $customer->detail_address1 ?? '' . $customer->detail_address2 ?? '',
            'name'    => $customer->name,
            'fax'     => $customer->fax,
        ];
        $company = [
            'name'    => $warehouse->name,
            'address' => $warehouse->detail_address1 ?? '' . $warehouse->detail_address2 ?? '',
            'tel'     => $warehouse->tel,
            'fax'     => $warehouse->fax,
        ];

        // 分页处理
        $itemsPerPage = 10; // 每页显示的行数，避免PDF自动分页
        $pages = $rows->chunk($itemsPerPage);
        $totalPages = $pages->count();

        $data = [
            'reportDate' => now()->format('Y年n月j日'),
            'issuer'     => $issuer,
            'company'    => $company,
            'pages'      => $pages,
            'totalPages' => $totalPages,
            'totals'     => $totals,
        ];

        // 生成html用于调试
        if (config('app.debug', false)) {
            $htmlContent = view('reports.inventory', $data)->render();
            $debugHtmlPath = 'reports/debug_' . str_replace('.pdf', '.html', $filename);
            Storage::disk('public')->put($debugHtmlPath, $htmlContent);
            Log::info('Debug HTML generated', ['path' => $debugHtmlPath]);
        }


        $pdf = Pdf::loadView('reports.inventory', $data);

        $pdf->setPaper('A4', 'landscape');

        // 根据报告的存储类型选择磁盘
        $filePath = 'reports/' . $filename;

        Storage::disk($report->getStorageDisk())->put($filePath, $pdf->output());

        return $filePath;
    }

    private function generateExcelReport(string $filename, InventoryReport $report, array $inventoryData): string
    {
        // TODO: Implement Excel generation if needed
        // For now, generate PDF as fallback
        return $this->generatePdfReport($filename, $report, $inventoryData);
    }

}
