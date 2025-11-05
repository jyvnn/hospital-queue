<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\BotManController;

/*
| Admin Pages and API (management-facing)
| These routes are intended for hospital staff (admins/workers). The file was
| renamed from `web.php` to `admin.php` to make its purpose explicit.
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

/*
| Botman / Chat
| - GET `/chat` serves a simple chat UI
| - POST `/botman` receives messages from the UI and returns JSON replies
*/
Route::get('/chat', [BotManController::class, 'show'])->name('botman.chat');
Route::match(['get', 'post'], '/botman', [BotManController::class, 'handle'])->name('botman.handle');

// Public user-facing dashboard (no auth required) — static UI for queue & history
Route::get('/user', function () {
    return view('user.dashboard');
})->name('user.dashboard');

// Include public user routes (login/register/dashboard) so the web entrypoint
// exposes the patient-facing pages. This keeps admin.php as the primary
// web route file while still loading the public user routes.
require __DIR__ . '/user.php';
