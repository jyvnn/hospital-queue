<?php

namespace App\Services;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Append-only queue event log stored in DynamoDB.
 *
 * Table: partition key `queue_date` (S, YYYY-MM-DD), sort key `event_id` (S, time-sortable).
 * Only non-identifying data is stored (patient id, priority, service) - no names or contacts.
 * Credentials come from the EC2 instance role via the SDK default provider chain.
 */
class QueueEventLog
{
    private ?DynamoDbClient $client = null;
    private Marshaler $marshaler;

    public function __construct()
    {
        $this->marshaler = new Marshaler();
    }

    public function enabled(): bool
    {
        return (bool) config('services.dynamodb.events_table');
    }

    /** Record an event. Never throws: a DynamoDB outage must not break patient registration. */
    public function record(string $type, int $patientId, array $details = []): void
    {
        if (! $this->enabled()) {
            return;
        }

        try {
            $now = now('UTC');
            $this->client()->putItem([
                'TableName' => config('services.dynamodb.events_table'),
                'Item' => $this->marshaler->marshalItem([
                    'queue_date' => $now->format('Y-m-d'),
                    'event_id' => $now->format('Y-m-d\TH:i:s.u\Z') . '#' . Str::lower(Str::random(6)),
                    'event_type' => $type,
                    'patient_id' => $patientId,
                    'details' => $details,
                    'expires_at' => $now->copy()->addDays(90)->timestamp, // DynamoDB TTL
                ]),
            ]);
        } catch (Throwable $e) {
            Log::warning('DynamoDB event write failed: ' . $e->getMessage());
        }
    }

    /** Newest-first events, walking back up to 7 days of partitions. */
    public function recent(int $limit = 20): array
    {
        if (! $this->enabled()) {
            return [];
        }

        $items = [];
        for ($d = 0; $d < 7 && count($items) < $limit; $d++) {
            $res = $this->client()->query([
                'TableName' => config('services.dynamodb.events_table'),
                'KeyConditionExpression' => 'queue_date = :d',
                'ExpressionAttributeValues' => [':d' => ['S' => now('UTC')->subDays($d)->format('Y-m-d')]],
                'ScanIndexForward' => false,
                'Limit' => $limit - count($items),
            ]);
            foreach ($res['Items'] as $item) {
                $items[] = $this->marshaler->unmarshalItem($item);
            }
        }

        return $items;
    }

    private function client(): DynamoDbClient
    {
        // No key/secret passed on purpose: the SDK falls back to the instance role.
        return $this->client ??= new DynamoDbClient([
            'region' => config('services.dynamodb.region'),
            'version' => 'latest',
        ]);
    }
}
