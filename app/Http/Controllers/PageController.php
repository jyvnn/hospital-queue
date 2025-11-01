<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function registration()
    {
        return view('pages.registration', $this->gatherDashboardData());
    }

    public function doctors()
    {
        return view('pages.doctors', $this->gatherDashboardData());
    }

    public function history()
    {
        return view('pages.history', $this->gatherDashboardData());
    }

    public function reports()
    {
        return view('pages.reports', $this->gatherDashboardData());
    }

    /**
     * Collect dashboard data so tabs can render without client-side JS.
     */
    protected function gatherDashboardData()
    {
        $today = Carbon::today()->toDateString();

        $doctors = Doctor::all();

        // recent appointments (last 30 days)
        $appointments = Appointment::orderBy('scheduled_at','desc')
            ->where('scheduled_at','>=', Carbon::now()->subDays(30))
            ->get();

        $total = Patient::whereDate('check_in_time',$today)->count();
        $completed = Patient::whereDate('check_in_time',$today)->where('status','Completed')->count();

        $avgWait = Patient::whereDate('check_in_time',$today)
            ->where('status','Completed')
            ->whereNotNull('completion_time')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, check_in_time, completion_time)) as avg_wait')
            ->value('avg_wait');

        // simple doctor utilization: current_patients / max_patients_per_day (percent)
        $doctorUtil = $doctors->map(function($d){
            $max = $d->max_patients_per_day ?: 1;
            return $max ? round(($d->current_patients / $max) * 100) : 0;
        })->avg();

        // current queue lists so server can render without requiring client JS
        $waitingPatients = Patient::where('status', 'Waiting')
            ->orderBy('check_in_time', 'asc')
            ->get()
            ->map(function($p) { return array_merge($p->toArray(), ['name' => $p->full_name]); });

        $inProgressPatients = Patient::where('status', 'In Progress')
            ->orderBy('check_in_time', 'asc')
            ->get()
            ->map(function($p) { return array_merge($p->toArray(), ['name' => $p->full_name]); });

        // recent history (limit for server-rendered view)
        // allow filtering via query params: search, status, date(from quick-select or from/to)
        $historyQuery = Patient::query();

        // search by name, contact or symptoms
        if ($s = request('search')) {
            $historyQuery->where(function($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                  ->orWhere('last_name', 'like', "%{$s}%")
                  ->orWhere('contact', 'like', "%{$s}%")
                  ->orWhere('symptoms', 'like', "%{$s}%");
            });
        }

        // status filter
        if ($st = request('status')) {
            if ($st !== 'all') {
                $historyQuery->where('status', $st);
            }
        }

        // date quick filters: today, week, month
        if ($dateFilter = request('date')) {
            if ($dateFilter === 'today') {
                $historyQuery->whereDate('check_in_time', Carbon::today());
            } elseif ($dateFilter === 'week') {
                $historyQuery->where('check_in_time', '>=', Carbon::now()->subDays(7));
            } elseif ($dateFilter === 'month') {
                $historyQuery->where('check_in_time', '>=', Carbon::now()->subDays(30));
            }
        }

        // explicit from/to date filters (optional)
        if ($from = request('from')) {
            $historyQuery->whereDate('check_in_time', '>=', $from);
        }
        if ($to = request('to')) {
            $historyQuery->whereDate('check_in_time', '<=', $to);
        }

        $historyPatients = $historyQuery->orderBy('check_in_time','desc')
            ->limit(100)
            ->get()
            ->map(function($p) { return array_merge($p->toArray(), ['name' => $p->full_name]); });

        return [
            'doctors' => $doctors,
            'appointments' => $appointments,
            'stats' => [
                'totalPatientsToday' => (int)$total,
                'completed' => (int)$completed,
                'averageWait' => $avgWait ? round($avgWait) : 0,
                'doctorUtilization' => round($doctorUtil)
            ],
            // server-rendered patient lists (so redirected pages show fresh data)
            'waitingPatients' => $waitingPatients,
            'inProgressPatients' => $inProgressPatients,
            'historyPatients' => $historyPatients,
            // allow controllers or redirects to set which tab should be active via session or query
            'activeTab' => session('activeTab', request('activeTab') ?? 'queue')
        ];
    }
}
