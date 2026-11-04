<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Queue event log (DynamoDB)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: system-ui, sans-serif; margin: 2rem auto; max-width: 56rem; padding: 0 1rem; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border-bottom: 1px solid #ddd; padding: .4rem .6rem; text-align: left; font-size: .9rem; }
    </style>
</head>
<body>
    <h1>Queue event log</h1>
    <p>Live read from Amazon DynamoDB table <code>{{ config('services.dynamodb.events_table') }}</code>
       ({{ config('services.dynamodb.region') }}). Newest first; no patient names are stored.</p>
    @if ($error)
        <p style="color:#b00">DynamoDB read failed: {{ $error }}</p>
    @endif
    <table>
        <tr><th>Event ID (UTC)</th><th>Type</th><th>Patient #</th><th>Details</th></tr>
        @forelse ($events as $e)
            <tr>
                <td>{{ $e['event_id'] }}</td>
                <td>{{ $e['event_type'] }}</td>
                <td>{{ $e['patient_id'] }}</td>
                <td>{{ json_encode($e['details'] ?? []) }}</td>
            </tr>
        @empty
            <tr><td colspan="4">No events yet. Register a patient to create one.</td></tr>
        @endforelse
    </table>
</body>
</html>
