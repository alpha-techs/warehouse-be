<?php

namespace App\Console\Commands;

use App\Jobs\GenerateInventoryReportJob;
use App\Models\Customer;
use App\Models\InventoryReport;
use App\Models\Warehouse;
use Illuminate\Console\Command;

class TestReportGeneration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:report-generation {--sync : Run synchronously instead of queued}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test inventory report generation functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing inventory report generation...');

        // 获取第一个仓库和客户
        $warehouse = Warehouse::first();
        $customer = Customer::first();

        if (!$warehouse) {
            $this->error('No warehouse found. Please run seeders first.');
            return 1;
        }

        if (!$customer) {
            $this->error('No customer found. Please run seeders first.');
            return 1;
        }

        $this->info("Using warehouse: {$warehouse->name} (ID: {$warehouse->id})");
        $this->info("Using customer: {$customer->name} (ID: {$customer->id})");

        // 创建报告记录
        $report = InventoryReport::create([
            'warehouse_id' => $warehouse->id,
            'warehouse_name' => $warehouse->name,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'format' => 'pdf',
            'status' => 'pending',
            'storage' => InventoryReport::STORAGE_LOCAL,
        ]);

        $this->info("Created report with ID: {$report->id}");

        if ($this->option('sync')) {
            $this->info('Running report generation synchronously...');
            $job = new GenerateInventoryReportJob($report->id);
            $job->handle(app(\App\Contracts\Services\InventoryServiceInterface::class));
            $this->info('Report generation completed.');
        } else {
            $this->info('Dispatching report generation job to queue...');
            GenerateInventoryReportJob::dispatch($report->id);
            $this->info('Job dispatched. Check queue status with: php artisan queue:work');
        }

        $report->refresh();
        $this->info("Report status: {$report->status}");

        if ($report->file_path) {
            $this->info("Report file: {$report->file_path}");
        }

        return 0;
    }
}
