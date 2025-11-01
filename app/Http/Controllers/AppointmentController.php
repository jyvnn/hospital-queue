<?php
namespace App\Http\Controllers;

use App\Models\Appointment;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::orderBy('scheduled_at','desc')->get();
        return response()->json(['success' => true, 'data' => $appointments]);
    }

    /**
     * Render appointments page (server-side) so frontend doesn't require JS to fetch JSON.
     */
    public function page()
    {
        $appointments = Appointment::orderBy('scheduled_at','desc')->get();
        return view('appointments.index', ['appointments' => $appointments]);
    }
}