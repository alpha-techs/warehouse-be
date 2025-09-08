<?php

namespace App\Jobs;

use App\Contracts\Services\NameChangeServiceInterface;
use App\Models\Customer;
use App\Models\NameChangeReport;
use App\Models\NameChangeItem;
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

class GenerateNameChangeReportJob implements ShouldQueue
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
    public function handle(NameChangeServiceInterface $nameChangeService): void
    {
        $report = NameChangeReport::find($this->reportId);

        if (!$report) {
            Log::error('NameChangeReport not found', ['reportId' => $this->reportId]);
            return;
        }

        try {
            $report->markAsProcessing();

            // 获取名义变更数据
            $nameChangeData = $this->getNameChangeDataForReport($report->name_change_id);

            // 生成报告文件
            $filePath = $this->generateReportFile($report, $nameChangeData);

            $report->markAsCompleted($filePath);

            Log::info('Name change report generated successfully', [
                'reportId' => $this->reportId,
                'filePath' => $filePath,
            ]);

        } catch (Exception $e) {
            $report->markAsFailed($e->getMessage());

            Log::error('Failed to generate name change report', [
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
    public function failed(\Throwable $exception): void
    {
        $report = NameChangeReport::find($this->reportId);
        if ($report) {
            $report->markAsFailed($exception->getMessage());
        }
    }

    /**
     * 获取名义变更数据用于生成报告
     */
    private function getNameChangeDataForReport(int $nameChangeId): array
    {
        $nameChange = \App\Models\NameChange::with([
            'items.product',
            'warehouse',
            'customer'
        ])->findOrFail($nameChangeId);

        return [
            'nameChange' => $nameChange,
            'items' => $nameChange->items,
            'warehouse' => $nameChange->warehouse,
            'customer' => $nameChange->customer,
        ];
    }

    /**
     * 生成报告文件
     */
    private function generateReportFile(NameChangeReport $report, array $data): string
    {
        $format = $report->format;
        $fileName = 'name_change_report_' . $report->id . '_' . time() . '.' . $format;
        
        if ($format === 'pdf') {
            return $this->generatePdfReport($report, $data, $fileName);
        } elseif ($format === 'excel') {
            return $this->generateExcelReport($report, $data, $fileName);
        }

        throw new Exception("Unsupported format: {$format}");
    }

    /**
     * 生成PDF报告
     */
    private function generatePdfReport(NameChangeReport $report, array $data, string $fileName): string
    {
        $pdf = Pdf::loadView('reports.name_change_pdf', [
            'report' => $report,
            'nameChange' => $data['nameChange'],
            'items' => $data['items'],
            'warehouse' => $data['warehouse'],
            'customer' => $data['customer'],
            'generatedAt' => now()->format('Y-m-d H:i:s'),
        ]);

        $pdf->setPaper('A4', 'portrait');
        
        $filePath = 'reports/name_change/' . $fileName;
        Storage::disk('public')->put($filePath, $pdf->output());

        return $filePath;
    }

    /**
     * 生成Excel报告
     */
    private function generateExcelReport(NameChangeReport $report, array $data, string $fileName): string
    {
        // 这里可以实现Excel报告生成逻辑
        // 暂时返回一个占位符
        $filePath = 'reports/name_change/' . $fileName;
        Storage::disk('public')->put($filePath, 'Excel report content placeholder');
        
        return $filePath;
    }
}
