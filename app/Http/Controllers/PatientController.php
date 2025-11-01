<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PatientController extends Controller
{
    public function index()
    {
        $patients = Patient::orderBy('id', 'desc')->get()
            ->map(function($p) {
                return array_merge($p->toArray(), ['name' => $p->full_name]);
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
            'contact' => 'required|string|max:50',
            'gender' => 'required|in:Male,Female,Other',
            'symptoms' => 'required|string|max:2000',
            'priority' => 'nullable|in:Regular,Urgent',
            'serviceType' => 'required|string|max:100',
        ]);

        $patient = Patient::create([
            'first_name' => $data['firstName'],
            'last_name'  => $data['lastName'],
            'age'        => $data['age'],
            'gender'     => $data['gender'],
            'contact'    => $data['contact'],
            'symptoms'   => $data['symptoms'],
            'priority'   => $data['priority'] ?? 'Regular',
            'service_type'=> $data['serviceType'],
            'status'     => $data['status'] ?? 'Waiting',
            'check_in_time' => $data['checkInTime'] ?? Carbon::now()->toDateTimeString()
        ]);

        // If request expects JSON (AJAX) return JSON, otherwise redirect back to registration page
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => (bool)$patient,
                'id' => $patient->id ?? null,
                'data' => array_merge($patient->toArray(), ['name' => $patient->full_name])
            ]);
        }

        // Redirect to the dashboard (home) so the user sees the queue immediately
        return redirect()->route('home')->with('success', 'Patient added to queue.')->with('activeTab', 'queue');
    }

    public function history(Request $request)
    {
        $query = Patient::query();

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

        return redirect()->route('home')->with('success', 'Doctor assigned.')->with('activeTab','queue');
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

        return redirect()->route('home')->with('success', 'Consultation completed.')->with('activeTab','queue');
    }
}