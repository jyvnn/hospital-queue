<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Doctor;
use App\Events\PatientsUpdated;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PatientController extends Controller
{
    public function index()
    {
        // Order patients by priority (Urgent > High > Regular) then by check-in time
        // Eager load assigned doctor and include assigned_doctor_name for client convenience
        $patients = Patient::with('assignedDoctor')
            ->orderByRaw("CASE WHEN priority='Urgent' THEN 3 WHEN priority='High' THEN 2 WHEN priority='Regular' THEN 1 ELSE 0 END DESC")
            ->orderBy('check_in_time', 'asc')
            ->get()
            ->map(function($p) {
                return array_merge($p->toArray(), [
                    'name' => $p->full_name,
                    'assigned_doctor_name' => $p->assignedDoctor ? $p->assignedDoctor->full_name : null
                ]);
            });
        return response()->json(['success' => true, 'data' => $patients]);
    }

    public function store(Request $request)
    {
        // inline validation instead of a separate FormRequest per preference
        $data = $request->validate([
            'firstName' => 'required|string|max:100',
            'lastName' => 'required|string|max:100',
            'age' => 'required|integer|min:0|max:150',
            // require exactly 11 digits for contact number
            'contact' => 'required|digits:11',
            'email' => 'nullable|email|max:150',
            'gender' => 'required|in:Male,Female,Other',
            'symptoms' => 'required|string|max:2000',
            'priority' => 'nullable|in:Regular,Urgent,High',
            'serviceType' => 'required|string|max:100',
        ]);
        // normalize priority values to canonical set
        $priorityInput = isset($data['priority']) ? trim(strtolower($data['priority'])) : null;
        $priority = 'Regular';
        if ($priorityInput) {
            if (in_array($priorityInput, ['urgent', 'emergency'])) $priority = 'Urgent';
            elseif ($priorityInput === 'high') $priority = 'High';
            else $priority = 'Regular';
        }

        $patient = Patient::create([
            'first_name' => $data['firstName'],
            'last_name'  => $data['lastName'],
            'age'        => $data['age'],
            'gender'     => $data['gender'],
            'contact'    => $data['contact'],
            'email'      => $data['email'] ?? null,
            'symptoms'   => $data['symptoms'],
            'priority'   => $priority,
            'service_type'=> $data['serviceType'],
            'status'     => $data['status'] ?? 'Waiting',
            'check_in_time' => $data['checkInTime'] ?? Carbon::now()->toDateTimeString()
        ]);

        // bump version file and broadcast to listeners
        try {
            $version = $this->bumpPatientsVersion();
            event(new PatientsUpdated($version));
        } catch (\Exception $e) {
            // non-fatal; proceed
        }

        // If request expects JSON (AJAX) return JSON, otherwise redirect back to registration page
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => (bool)$patient,
                'id' => $patient->id ?? null,
                'data' => array_merge($patient->toArray(), ['name' => $patient->full_name])
            ]);
        }

        // Redirect to the dashboard (home) so the user sees the queue immediately
        return redirect()->route('admin.home')->with('success', 'Patient added to queue.')->with('activeTab', 'queue');
    }

    public function history(Request $request)
    {
        $query = Patient::query();

        // If email filter provided, restrict to that email.
        // For non-admin users, force the email to the authenticated user's email to prevent data leakage.
        if ($request->filled('email')) {
            $emailFilter = $request->email;
        } else {
            $emailFilter = null;
        }
        if (auth()->check() && !auth()->user()->is_admin) {
            $emailFilter = auth()->user()->email;
        }
        if ($emailFilter) {
            $query->where('email', $emailFilter);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('first_name','like', "%{$s}%")
                  ->orWhere('last_name','like', "%{$s}%")
                  ->orWhere('contact','like', "%{$s}%")
                  ->orWhere('symptoms','like', "%{$s}%");
            });
        }

        if ($request->filled('from')) {
            $query->whereDate('check_in_time', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('check_in_time', '<=', $request->to);
        }

        $patients = $query->orderBy('check_in_time','desc')->get()
            ->map(function($p) { return array_merge($p->toArray(), ['name'=>$p->full_name]); });

        // simple stats
        $total = $query->count();
        $completed = (clone $query)->where('status','Completed')->count();

        return response()->json([
            'success' => true,
            'data' => $patients,
            'stats' => ['total' => $total, 'completed' => $completed]
        ]);
    }

    public function assignDoctor(Request $request)
    {
        $patientId = $request->post('patient_id');
        $doctorId = $request->post('doctor_id');

        $p = Patient::find($patientId);
        if (!$p) return response()->json(['success'=>false,'message'=>'Patient not found'], 404);

        $p->assigned_doctor_id = $doctorId;
        $p->status = 'In Progress';
        $ok = $p->save();

        // increment doctor's count
        if ($ok && $doctorId) {
            DB::table('doctors')->where('id',$doctorId)->increment('current_patients');
        }

        // If request expects JSON (AJAX) return JSON, otherwise redirect back so server-rendered pages update
        if ($request->wantsJson() || $request->ajax()) {
            $doctorName = null;
            if ($doctorId) {
                $doc = Doctor::find($doctorId);
                $doctorName = $doc ? $doc->full_name : null;
            }

            return response()->json([
                'success' => (bool)$ok,
                'patient' => $p ? array_merge($p->toArray(), ['name' => $p->full_name]) : null,
                'doctor' => $doctorName
            ]);
        }

        // bump version and notify listeners on non-AJAX flow too
        try { $version = $this->bumpPatientsVersion(); event(new PatientsUpdated($version)); } catch (\Exception $e) {}

        return redirect()->route('admin.home')->with('success', 'Doctor assigned.')->with('activeTab','queue');
    }

    public function completeConsultation(Request $request)
    {
        $patientId = $request->post('patient_id');
        $p = Patient::find($patientId);
        if (!$p) return response()->json(['success'=>false,'message'=>'Patient not found'], 404);

        $p->status = 'Completed';
        $p->completion_time = Carbon::now()->toDateTimeString();
        $ok = $p->save();

        if ($ok && $p->assigned_doctor_id) {
            DB::table('doctors')->where('id',$p->assigned_doctor_id)
                ->where('current_patients','>',0)
                ->decrement('current_patients');
        }

        // If request expects JSON (AJAX) return JSON, otherwise redirect back so UI shows updated lists
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => (bool)$ok,
                'patient_id' => $p->id,
                'patient' => array_merge($p->toArray(), ['name' => $p->full_name])
            ]);
        }

        // bump version and broadcast
        try { $version = $this->bumpPatientsVersion(); event(new PatientsUpdated($version)); } catch (\Exception $e) {}

        return redirect()->route('admin.home')->with('success', 'Consultation completed.')->with('activeTab','queue');
    }

    /**
     * Return a small version token used by long-polling clients to detect changes.
     */
    public function version(Request $request)
    {
        $file = storage_path('app/patients_version');
        $version = 0;
        if (file_exists($file)) {
            $version = (int) trim(file_get_contents($file));
        }
        return response()->json(['success' => true, 'version' => $version]);
    }

    /**
     * Write a simple version token to storage and return it.
     */
    protected function bumpPatientsVersion()
    {
        $file = storage_path('app/patients_version');
        $v = time();
        // ensure directory exists
        if (!is_dir(dirname($file))) mkdir(dirname($file), 0755, true);
        file_put_contents($file, (string)$v);
        return $v;
    }
}