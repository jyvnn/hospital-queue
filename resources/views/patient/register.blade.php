@extends('layouts.app')

@section('title', 'Patient Portal — Register')

@section('content')
<div class="min-vh-100 d-flex flex-column justify-content-center align-items-center bg-light">
    <div class="w-100" style="max-width:760px;">
        <div class="text-center mb-4">
            <h1 class="fw-bold">Patient Portal</h1>
            <p class="text-muted">Create an account to access your queue and visit history</p>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <strong>Register</strong>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ url('/user/register') }}">
                    @csrf

                    <div class="mb-3 row">
                        <label for="name" class="col-sm-3 col-form-label text-end">Full Name</label>
                        <div class="col-sm-7">
                            <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="email" class="col-sm-3 col-form-label text-end">Email Address</label>
                        <div class="col-sm-7">
                            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="password" class="col-sm-3 col-form-label text-end">Password</label>
                        <div class="col-sm-7">
                            <input id="password" type="password" class="form-control" name="password" required>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="password_confirmation" class="col-sm-3 col-form-label text-end">Confirm Password</label>
                        <div class="col-sm-7">
                            <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" required>
                        </div>
                    </div>

                    <div class="mb-0 row">
                        <div class="offset-sm-3 col-sm-7 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Create Account</button>
                            <a href="{{ url('/user/login') }}" class="btn btn-outline-secondary">Back to Login</a>
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

@section('title','User Register')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="bg-white rounded-4 shadow-sm p-4 card">
                <h3 class="card-title">Create Patient Account</h3>
                <p class="card-description">Register to track your queue position and appointment history.</p>

                <form method="POST" action="{{ url('/user/register') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Full name</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required class="form-input">
                        @error('name') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

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

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required class="form-input">
                    </div>

                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary" type="submit">Register</button>
                    </div>
                </form>

                <div class="mt-3">
                    <a href="{{ url('/user/login') }}">Already have an account? Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
