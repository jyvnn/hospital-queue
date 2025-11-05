<?php

// PUBLIC / USER-FACING ROUTES
// --------------------------------------------------
// This file intentionally exposes only the public `/user` page which
// serves the demo user portal (queue + history). All staff/admin routes
// (management, authenticated pages and APIs) should live in
// `routes/admin.php` — do NOT add admin routes here.
// --------------------------------------------------

use Illuminate\Support\Facades\Route;

// Public user-facing dashboard (no auth required) — static UI for queue & history
Route::get('/user', function () {
	return view('user.dashboard');
})->name('user.dashboard');

// Helpful informational endpoint for developers
Route::get('/routes-info', function () {
	return response('This application separates admin/staff routes (routes/admin.php) from public user routes (routes/user.php).', 200);
});
