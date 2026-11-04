<?php

namespace App\Console\Commands;

use App\Services\QueueEventLog;
use Illuminate\Console\Command;

class RecentEvents extends Command
{
    protected $signature = 'hq:recent-events {--limit=10}';
    protected $description = 'Print the latest queue events stored in DynamoDB';

    public function handle(QueueEventLog $log): int
    {
        if (! $log->enabled()) {
            $this->error('DYNAMODB_EVENTS_TABLE is not set.');
            return self::FAILURE;
        }

        $rows = array_map(fn ($e) => [
            $e['event_id'], $e['event_type'], $e['patient_id'], json_encode($e['details'] ?? []),
        ], $log->recent((int) $this->option('limit')));

        $this->table(['event_id', 'type', 'patient', 'details'], $rows);
        return self::SUCCESS;
    }
}
