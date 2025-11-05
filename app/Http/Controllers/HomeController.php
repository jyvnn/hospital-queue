<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Patient;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // provide the same dashboard data as PageController so home can render without JS
        $today = Carbon::today()->toDateString();

        $doctors = Doctor::all();
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

        $doctorUtil = $doctors->map(function($d){
            $max = $d->max_patients_per_day ?: 1;
            return $max ? round(($d->current_patients / $max) * 100) : 0;
        })->avg();

        // include current queue and recent history so the dashboard can render without JS
        $waitingPatients = Patient::where('status', 'Waiting')
            ->orderBy('check_in_time', 'asc')
            ->get()
            ->map(function($p) { return array_merge($p->toArray(), ['name' => $p->full_name]); });

        $inProgressPatients = Patient::where('status', 'In Progress')
            ->orderBy('check_in_time', 'asc')
            ->get()
            ->map(function($p) { return array_merge($p->toArray(), ['name' => $p->full_name]); });

        $historyPatients = Patient::orderBy('check_in_time','desc')
            ->limit(100)
            ->get()
            ->map(function($p) { return array_merge($p->toArray(), ['name' => $p->full_name]); });

        return view('admin.pages.queue', [
            'doctors' => $doctors,
            'appointments' => $appointments,
            'stats' => [
                'totalPatientsToday' => (int)$total,
                'completed' => (int)$completed,
                'averageWait' => $avgWait ? round($avgWait) : 0,
                'doctorUtilization' => round($doctorUtil)
            ],
            // include server-rendered patient lists so the dashboard shows the latest queue and history
            'waitingPatients' => $waitingPatients,
            'inProgressPatients' => $inProgressPatients,
            'historyPatients' => $historyPatients,
            'activeTab' => session('activeTab', request('activeTab') ?? 'queue')
        ]);
    }
}
