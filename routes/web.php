<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\PageController;

/*
| Pages (each tab = its own blade)
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/registration', [PageController::class, 'registration'])->name('registration');
Route::get('/doctors', [PageController::class, 'doctors'])->name('doctors');
Route::get('/history', [PageController::class, 'history'])->name('history');
Route::get('/reports', [PageController::class, 'reports'])->name('reports');

/*
| GET endpoints
*/
Route::get('/patients/list', [PatientController::class, 'index']);
Route::get('/patient/history', [PatientController::class, 'history']);
Route::get('/doctors/list', [DoctorController::class, 'index']);
Route::get('/appointments/list', [AppointmentController::class, 'index']);
Route::get('/appointments', [AppointmentController::class, 'page'])->name('appointments');
Route::get('/statistics', [StatisticsController::class, 'index']);
Route::get('/monthly-reports', [StatisticsController::class, 'monthlyReports']);
Route::get('/recommendations', [StatisticsController::class, 'recommendations']);
Route::get('/reports/patients-per-day', [StatisticsController::class, 'patientsPerDay']);

/*
| POST endpoints (CSRF protected — JS must send X-CSRF-TOKEN header)
*/
Route::post('/patients/add', [PatientController::class, 'store']);
Route::post('/patients/assign-doctor', [PatientController::class, 'assignDoctor']);
Route::post('/patients/complete', [PatientController::class, 'completeConsultation']);
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
