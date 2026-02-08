<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ArchiveAuditLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:archive {--months=6 : Number of months to keep}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive and cleanup old audit logs for system performance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $months = $this->option('months');
        $cutoffDate = now()->subMonths($months);

        $this->info("Archiving audit logs older than {$months} months (before {$cutoffDate->toDateString()})...");

        AuditLog::where('created_at', '<', $cutoffDate)
            ->chunkById(1000, function ($logs) use (&$totalArchived) {
                $filename = 'archives/audit_logs_' . now()->format('Y_m_d_His') . '.json';
                
                $data = $logs->toJson();
                Storage::disk('local')->put($filename, $data);
                
                $ids = $logs->pluck('id');
                AuditLog::whereIn('id', $ids)->delete();
                
                $this->line("Archived " . count($ids) . " records to {$filename}");
            });

        $this->info("Cleanup complete.");
    }
}
