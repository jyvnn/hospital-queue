<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    public function index(): JsonResponse
    {
        $today = Carbon::today()->toDateString();

        $total = Patient::whereDate('check_in_time',$today)->count();
        $completed = Patient::whereDate('check_in_time',$today)->where('status','Completed')->count();

        $avgWait = Patient::whereDate('check_in_time',$today)
            ->where('status','Completed')
            ->whereNotNull('completion_time')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, check_in_time, completion_time)) as avg_wait')
            ->value('avg_wait');

        return response()->json([
            'success' => true,
            'data' => [
                'totalPatientsToday' => (int)$total,
                'completed' => (int)$completed,
                'averageWait' => $avgWait ? round($avgWait) : 0
            ]
        ]);
    }

    public function monthlyReports(): JsonResponse
    {
        $rows = Patient::selectRaw("DATE_FORMAT(check_in_time,'%Y-%m') as ym, COUNT(*) as total")
            ->where('check_in_time','>=', Carbon::now()->subMonths(12))
            ->groupBy('ym')
            ->orderBy('ym')
            ->get();

        return response()->json(['success'=>true,'data'=>$rows]);
    }

    public function recommendations(): JsonResponse
    {
        // simple example: doctors utilization
        $avgUtil = DB::table('doctors')
            ->selectRaw('AVG((current_patients / NULLIF(max_patients_per_day,0))*100) as util')
            ->value('util');

        $rec = [];
        if ($avgUtil !== null && $avgUtil > 80) {
            $rec[] = 'High average doctor utilization — consider reassigning staff.';
        } else {
            $rec[] = 'Utilization within acceptable range.';
        }

        return response()->json(['success'=>true,'data'=>$rec]);
    }

    /**
     * Return patients per day (or per granularity) for a date range.
     * Query params: from=YYYY-MM-DD, to=YYYY-MM-DD, granularity=day|week|month
     */
    public function patientsPerDay(Request $request): JsonResponse
    {
        // sanitize and defaults
        $from = $request->query('from', Carbon::now()->subDays(30)->toDateString());
        $to = $request->query('to', Carbon::now()->toDateString());
        $gran = strtolower($request->query('granularity', 'day'));

        if (!in_array($gran, ['day', 'week', 'month'])) {
            $gran = 'day';
        }

        try {
            $start = Carbon::parse($from)->startOfDay();
        } catch (\Exception $e) {
            $start = Carbon::now()->subDays(30)->startOfDay();
        }

        try {
            $end = Carbon::parse($to)->endOfDay();
        } catch (\Exception $e) {
            $end = Carbon::now()->endOfDay();
        }

        // Ensure start <= end
        if ($start->gt($end)) {
            [$start, $end] = [$end, $start];
        }

        // decide DB grouping expression and PHP cursor increment/format
        if ($gran === 'month') {
            $groupExpr = "DATE_FORMAT(check_in_time,'%Y-%m')";
            $cursorIncrement = 'addMonth';
            $formatKey = function($d){ return $d->format('Y-m'); };
        } elseif ($gran === 'week') {
            // use ISO week-year to match MySQL %x-%v
            $groupExpr = "DATE_FORMAT(check_in_time,'%x-%v')";
            $cursorIncrement = 'addWeek';
            $formatKey = function($d){ return $d->format('o-W'); };
        } else {
            $groupExpr = "DATE_FORMAT(check_in_time,'%Y-%m-%d')";
            $cursorIncrement = 'addDay';
            $formatKey = function($d){ return $d->toDateString(); };
        }

        $cacheKey = "patientsPerDay:{$start->toDateString()}:{$end->toDateString()}:{$gran}";
        $payload = cache()->remember($cacheKey, 30, function() use ($start, $end, $groupExpr, $formatKey, $cursorIncrement) {
            $rows = DB::table('patients')
                ->select(DB::raw("{$groupExpr} as period"), DB::raw('COUNT(*) as total'))
                ->whereBetween('check_in_time', [$start->toDateTimeString(), $end->toDateTimeString()])
                ->groupBy('period')
                ->orderBy('period')
                ->get()
                ->pluck('total', 'period')
                ->toArray();

            // Build full list of periods with zero-fill
            $labels = [];
            $data = [];
            $cursor = $start->copy();
            while ($cursor->lte($end)) {
                $key = $formatKey($cursor);
                $labels[] = $key;
                $data[] = isset($rows[$key]) ? (int)$rows[$key] : 0;
                $cursor->{$cursorIncrement}();
            }

            return ['labels' => $labels, 'counts' => $data];
        });

        return response()->json(['success' => true, 'data' => $payload]);
    }

    /**
     * Return counts grouped by priority.
     * Response: { success: true, data: { labels: [...], counts: [...] } }
     */
    public function patientsByPriority(): JsonResponse
    {
        // expected priority buckets (canonical order)
        $buckets = ['Urgent', 'Regular'];

        $rows = Patient::selectRaw('priority, COUNT(*) as total')
            ->groupBy('priority')
            ->get()
            ->pluck('total','priority')
            ->toArray();

        $labels = [];
        $counts = [];
        foreach ($buckets as $b) {
            $labels[] = $b;
            $counts[] = isset($rows[$b]) ? (int)$rows[$b] : 0;
        }

        return response()->json(['success' => true, 'data' => ['labels' => $labels, 'counts' => $counts]]);
    }

    /**
     * Return counts grouped by service_type.
     * Response: { success: true, data: { labels: [...], counts: [...] } }
     */
    public function patientsByServiceType(): JsonResponse
    {
        $rows = Patient::selectRaw("COALESCE(service_type, 'Unknown') as service_type, COUNT(*) as total")
            ->groupBy('service_type')
            ->orderByDesc('total')
            ->get()
            ->pluck('total','service_type')
            ->toArray();

        $labels = array_keys($rows);
        $counts = array_values($rows);

        return response()->json(['success' => true, 'data' => ['labels' => $labels, 'counts' => $counts]]);
    }
}