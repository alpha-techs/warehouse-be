<?php

namespace App\Console\Commands;

use App\Jobs\GenerateOutboundReportJob;
use App\Models\Outbound;
use App\Models\OutboundReport;
use Illuminate\Console\Command;

class TestOutboundReportGeneration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:outbound-report-generation {--outbound-id= : Specific outbound ID to use} {--sync : Run synchronously instead of queued}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test outbound report generation functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing outbound report generation...');

        // 获取出库单
        $outboundId = $this->option('outbound-id');

        if ($outboundId) {
            $outbound = Outbound::with(['warehouse', 'customer'])->find($outboundId);
            if (!$outbound) {
                $this->error("Outbound with ID {$outboundId} not found.");
                return 1;
            }
        } else {
            $outbound = Outbound::with(['warehouse', 'customer', 'items'])->whereHas('items')->first();
            if (!$outbound) {
                $this->error('No outbound with items found. Please run seeders first or create some outbound data.');
                return 1;
            }
        }

        $this->info("Using outbound: {$outbound->outbound_order_id} (ID: {$outbound->id})");
        $this->info("Warehouse: {$outbound->warehouse?->name} (ID: {$outbound->warehouse_id})");
        $this->info("Customer: {$outbound->customer?->name} (ID: {$outbound->customer_id})");
        $this->info("Outbound date: {$outbound->outbound_date}");

        // 显示出库项目数量
        $itemCount = $outbound->items()->count();
        $this->info("Items count: {$itemCount}");

        // 创建报告记录
        $report = OutboundReport::create([
            'outbound_id' => $outbound->id,
            'warehouse_id' => $outbound->warehouse_id,
            'warehouse_name' => $outbound->warehouse?->name,
            'customer_id' => $outbound->customer_id,
            'customer_name' => $outbound->customer?->name,
            'format' => 'pdf',
            'status' => 'pending',
            'storage' => OutboundReport::STORAGE_LOCAL,
        ]);

        $this->info("Created outbound report with ID: {$report->id}");

        if ($this->option('sync')) {
            $this->info('Running report generation synchronously...');
            $job = new GenerateOutboundReportJob($report->id);
            try {
                $job->handle(app(\App\Contracts\Services\OutboundServiceInterface::class));
                $this->info('Report generation completed.');
            } catch (\Exception $e) {
                $this->error("Report generation failed: {$e->getMessage()}");
                return 1;
            }
        } else {
            $this->info('Dispatching report generation job to queue...');
            GenerateOutboundReportJob::dispatch($report->id);
            $this->info('Job dispatched. Check queue status with: php artisan queue:work');
        }

        $report->refresh();
        $this->info("Report status: {$report->status}");

        if ($report->file_path) {
            $this->info("Report file: {$report->file_path}");
            $this->info("Download URL: " . url('storage/' . $report->file_path));
        }

        if ($report->error_message) {
            $this->error("Error message: {$report->error_message}");
        }

        return 0;
    }
}
