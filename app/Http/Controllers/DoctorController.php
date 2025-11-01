<?php
namespace App\Http\Controllers;

use App\Models\Doctor;

class DoctorController extends Controller
{
    public function index()
    {
        $doctors = Doctor::orderBy('last_name')->orderBy('first_name')->get()
            ->map(function($d) {
                return array_merge($d->toArray(), ['name' => $d->full_name]);
            });

        return response()->json(['success' => true, 'data' => $doctors]);
    }
}