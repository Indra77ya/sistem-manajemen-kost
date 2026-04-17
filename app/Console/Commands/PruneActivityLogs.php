<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;

class PruneActivityLogs extends Command
{
    protected $signature = 'kost:prune-logs {days=180}';
    protected $description = 'Prune activity logs older than specified days';

    public function handle()
    {
        $days = $this->argument('days');
        $date = Carbon::now()->subDays($days);

        $count = Activity::where('created_at', '<', $date)->delete();

        $this->info("Successfully pruned {$count} activity logs older than {$days} days.");
    }
}
