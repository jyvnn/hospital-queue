@extends('layouts.patient')

@section('title', 'Patient Portal — Login')

@section('content')
<div class="min-vh-75 d-flex align-items-start justify-content-center bg-light" style="padding-top:42px;">
    <div class="w-100" style="max-width:760px; margin-top:0;">
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
                            <a href="{{ url('/password/reset') }}" class="btn btn-primary">Forgot Your Password?</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
