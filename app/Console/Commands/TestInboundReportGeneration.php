<?php

namespace App\Console\Commands;

use App\Jobs\GenerateInboundReportJob;
use App\Models\Inbound;
use App\Models\InboundReport;
use Illuminate\Console\Command;

class TestInboundReportGeneration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:inbound-report-generation {--inbound-id= : Specific inbound ID to use} {--sync : Run synchronously instead of queued}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test inbound report generation functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing inbound report generation...');

        // 获取入库单
        $inboundId = $this->option('inbound-id');
        
        if ($inboundId) {
            $inbound = Inbound::with(['warehouse', 'customer'])->find($inboundId);
            if (!$inbound) {
                $this->error("Inbound with ID {$inboundId} not found.");
                return 1;
            }
        } else {
            $inbound = Inbound::with(['warehouse', 'customer', 'items'])->whereHas('items')->first();
            if (!$inbound) {
                $this->error('No inbound with items found. Please run seeders first or create some inbound data.');
                return 1;
            }
        }

        $this->info("Using inbound: {$inbound->inbound_order_id} (ID: {$inbound->id})");
        $this->info("Warehouse: {$inbound->warehouse?->name} (ID: {$inbound->warehouse_id})");
        $this->info("Customer: {$inbound->customer?->name} (ID: {$inbound->customer_id})");
        $this->info("Inbound date: {$inbound->inbound_date}");
        
        // 显示入库项目数量
        $itemCount = $inbound->items()->count();
        $this->info("Items count: {$itemCount}");

        // 创建报告记录
        $report = InboundReport::create([
            'inbound_id' => $inbound->id,
            'warehouse_id' => $inbound->warehouse_id,
            'warehouse_name' => $inbound->warehouse?->name,
            'customer_id' => $inbound->customer_id,
            'customer_name' => $inbound->customer?->name,
            'format' => 'pdf',
            'status' => 'pending',
            'storage' => InboundReport::STORAGE_LOCAL,
        ]);

        $this->info("Created inbound report with ID: {$report->id}");

        if ($this->option('sync')) {
            $this->info('Running report generation synchronously...');
            $job = new GenerateInboundReportJob($report->id);
            try {
                $job->handle(app(\App\Contracts\Services\InboundServiceInterface::class));
                $this->info('Report generation completed.');
            } catch (\Exception $e) {
                $this->error("Report generation failed: {$e->getMessage()}");
                return 1;
            }
        } else {
            $this->info('Dispatching report generation job to queue...');
            GenerateInboundReportJob::dispatch($report->id);
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
