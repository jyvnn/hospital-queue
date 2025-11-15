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

// Auth routes
Auth::routes();

// Put all authenticated routes inside a single auth group, then nest admin vs patient
Route::middleware(['auth'])->group(function () {

    // Admin-only pages and APIs
    Route::middleware([App\Http\Middleware\EnsureUserIsAdmin::class])->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('admin.home');
        Route::get('/registration', [PageController::class, 'registration'])->name('registration');
        Route::get('/doctors', [PageController::class, 'doctors'])->name('doctors');
        Route::get('/history', [PageController::class, 'history'])->name('history');
        Route::get('/reports', [PageController::class, 'reports'])->name('reports');

        // GET endpoints
        Route::get('/doctors/list', [DoctorController::class, 'index']);
        Route::get('/appointments/list', [AppointmentController::class, 'index']);
        Route::get('/appointments', [AppointmentController::class, 'page'])->name('appointments');
        Route::get('/statistics', [StatisticsController::class, 'index']);
        Route::get('/monthly-reports', [StatisticsController::class, 'monthlyReports']);
        Route::get('/recommendations', [StatisticsController::class, 'recommendations']);
        Route::get('/reports/patients-per-day', [StatisticsController::class, 'patientsPerDay']);
        Route::get('/reports/by-priority', [StatisticsController::class, 'patientsByPriority']);
        Route::get('/reports/by-service-type', [StatisticsController::class, 'patientsByServiceType']);

        // POST endpoints
        Route::post('/patients/add', [PatientController::class, 'store']);
        Route::post('/patients/assign-doctor', [PatientController::class, 'assignDoctor']);
        Route::post('/patients/complete', [PatientController::class, 'completeConsultation']);

    });

    // Patient-facing pages (authenticated but not admin)
    Route::middleware([App\Http\Middleware\EnsureUserIsNotAdmin::class])->group(function () {
        Route::get('/user', function () {
            return view('user.dashboard');
        })->name('user.dashboard');
    });

    // Shared authenticated APIs (both admins and patients)
    Route::get('/patients/list', [PatientController::class, 'index']);
    Route::get('/patient/history', [PatientController::class, 'history']);

    // Botman / Chat
    Route::get('/chat', [BotManController::class, 'show'])->name('botman.chat');
    Route::match(['get', 'post'], '/botman', [BotManController::class, 'handle'])->name('botman.handle');
});

// lightweight unauthenticated version endpoint — tests and public long-polling may hit this
Route::get('/patients/version', [PatientController::class, 'version']);
