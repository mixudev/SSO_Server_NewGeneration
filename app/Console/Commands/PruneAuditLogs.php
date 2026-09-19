<?php

namespace App\Console\Commands;

use App\Models\Identity\SecurityEvent;
use Illuminate\Console\Command;

class PruneAuditLogs extends Command
{
    protected $signature = 'audit:prune {--days=90 : Retention period in days}';

    protected $description = 'Prune application security events beyond the retention period';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $deleted = SecurityEvent::query()
            ->where('occurred_at', '<=', now()->subDays($days))
            ->delete();

        $this->info("[DONE] Pruned {$deleted} application security events older than {$days} days.");

        return self::SUCCESS;
    }
}
