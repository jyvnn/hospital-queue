<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>@yield('title', 'Patient Portal') - Hospital Queue</title>

	{{-- Page stacks and small critical CSS to avoid FOUC --}}
	@stack('styles')
	<style>
		html,body{height:100%;}
		body{font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, system-ui, sans-serif; background:#f3fbfb; color:#1f2937;}
		.container{max-width:1100px;margin:0 auto;padding:12px}
		.navbar-brand{font-weight:700}
		.card{border-radius:12px}
		.btn{border-radius:8px}
		.form-control{border-radius:8px}
	</style>

	{{-- Vite entry (loads compiled CSS + JS) --}}
	@vite(['resources/js/app.js'])
</head>
<body>

	<nav class="navbar navbar-expand-lg navbar-light bg-transparent py-3">
		<div class="container">
			<a class="navbar-brand" href="{{ route('home') }}">Patient Portal</a>

			<ul class="navbar-nav ms-auto">
				@guest
					@if (Route::has('user.login'))
						<li class="nav-item"><a class="nav-link" href="{{ route('user.login') }}">Login</a></li>
					@endif
					@if (Route::has('user.register'))
						<li class="nav-item"><a class="nav-link" href="{{ route('user.register') }}">Register</a></li>
					@endif
				@else
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" id="patientMenu" role="button" data-bs-toggle="dropdown">{{ Auth::user()->name }}</a>
						<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="patientMenu">
							<li><a class="dropdown-item" href="{{ route('user.dashboard') }}">Dashboard</a></li>
							<li><hr class="dropdown-divider"></li>
							<li>
								<a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('patient-logout').submit();">Logout</a>
								<form id="patient-logout" action="{{ route('user.logout') }}" method="POST" class="d-none">@csrf</form>
							</li>
						</ul>
					</li>
				@endguest
			</ul>
		</div>
	</nav>

	<main class="container mt-4">
		@yield('content')
	</main>

	<footer class="mt-5 py-3">
		<div class="container text-center text-muted">
			&copy; {{ date('Y') }} Hospital Queue — Patient Portal
		</div>
	</footer>

	@stack('scripts')
</body>
</html>

