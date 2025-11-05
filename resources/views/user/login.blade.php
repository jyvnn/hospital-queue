@extends('layouts.app')

@section('title', 'Patient Portal — Login')

@section('content')
<div class="min-vh-100 d-flex flex-column justify-content-center align-items-center bg-light">
    <div class="w-100" style="max-width:760px;">
        <div class="text-center mb-4">
            <h1 class="fw-bold">Patient Portal</h1>
            <p class="text-muted">Hospital Queue — Login to check your queue and history</p>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <strong>Login</strong>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ url('/user/login') }}">
                    @csrf

                    <div class="mb-3 row">
                        <label for="email" class="col-sm-3 col-form-label text-end">Email Address</label>
                        <div class="col-sm-7">
                            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="password" class="col-sm-3 col-form-label text-end">Password</label>
                        <div class="col-sm-7">
                            <input id="password" type="password" class="form-control" name="password" required>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <div class="offset-sm-3 col-sm-7">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                <label class="form-check-label" for="remember">Remember Me</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-0 row">
                        <div class="offset-sm-3 col-sm-7 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Login</button>
                            <a href="{{ url('/user/register') }}" class="btn btn-outline-secondary">Register</a>
                            <a href="#" class="ms-auto align-self-center small">Forgot Your Password?</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="text-center mt-4 text-muted small">
            © {{ date('Y') }} Health Queue Management System. Patient Portal.
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('title','User Login')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="bg-white rounded-4 shadow-sm p-4 card">
                <h3 class="card-title">Patient Portal Login</h3>
                <p class="card-description">Sign in to view your queue position and visit history.</p>

                <form method="POST" action="{{ url('/user/login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required class="form-input">
                        @error('email') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" type="password" name="password" required class="form-input">
                        @error('password') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <label><input type="checkbox" name="remember"> Remember me</label>
                        </div>
                        <div>
                            <button class="btn btn-primary" type="submit">Login</button>
                        </div>
                    </div>
                </form>

                <div class="mt-3">
                    <a href="{{ url('/user/register') }}">Create an account</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
