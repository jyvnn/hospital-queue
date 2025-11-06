<?php

// // PUBLIC / USER-FACING ROUTES
// // --------------------------------------------------
// // This file intentionally exposes only the public `/user` page which
// // serves the demo user portal (queue + history). All staff/admin routes
// // (management, authenticated pages and APIs) should live in
// // `routes/admin.php` — do NOT add admin routes here.
// // --------------------------------------------------

// use Illuminate\Support\Facades\Route;

// // Public user-facing dashboard (no auth required) — static UI for queue & history
// // Authentication routes for public users (patient portal)
// Route::get('/user/login', [App\Http\Controllers\UserAuthController::class, 'showLogin'])->name('user.login');
// Route::post('/user/login', [App\Http\Controllers\UserAuthController::class, 'login']);

// Route::get('/user/register', [App\Http\Controllers\UserAuthController::class, 'showRegister'])->name('user.register');
// Route::post('/user/register', [App\Http\Controllers\UserAuthController::class, 'register']);

// Route::post('/user/logout', [App\Http\Controllers\UserAuthController::class, 'logout'])->name('user.logout');

// // Protected user dashboard — requires authentication (patients)
// Route::get('/user', function () {
// 	return view('patient.dashboard');
// })->middleware('auth')->name('user.dashboard');

// // Helpful informational endpoint for developers
// Route::get('/routes-info', function () {
// 	return response('This application separates admin/staff routes (routes/admin.php) from public user routes (routes/user.php).', 200);
// });
