@extends('layouts.patient')

@section('title', 'Patient Portal — Register')

@section('content')
<div class="min-vh-75 d-flex align-items-start justify-content-center bg-light" style="padding-top:42px;">
    <div class="w-100" style="max-width:760px; margin-top:0;">
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
    </div>
</div>
@endsection
