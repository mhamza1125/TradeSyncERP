<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - {{ config('app.name', 'TradeSyncERP') }}</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon.ico') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/vendors.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/theme.min.css') }}">
</head>

<body>
    <main class="auth-creative-wrapper">
        <div class="auth-creative-inner">
            <div class="creative-card-wrapper">
                <div class="card my-4 overflow-hidden" style="z-index: 1">
                    <div class="row flex-1 g-0">

                        {{-- Left: Form Panel --}}
                        <div class="col-lg-6 h-100 my-auto order-1 order-lg-0">
                            <div class="wd-50 bg-white p-2 rounded-circle shadow-lg position-absolute translate-middle top-50 start-50 d-none d-lg-block">
                                <img src="{{ asset('assets/images/logo-abbr.png') }}" alt="{{ config('app.name') }}" class="img-fluid">
                            </div>
                            <div class="creative-card-body card-body p-sm-5">
                                <h2 class="fs-20 fw-bolder mb-4">Login</h2>
                                <h4 class="fs-13 fw-bold mb-2">Login to your account</h4>
                                <p class="fs-12 fw-medium text-muted">Welcome back to <strong>{{ config('app.name', 'TradeSyncERP') }}</strong>. Please enter your credentials to continue.</p>

                                {{-- Session Status --}}
                                @if (session('status'))
                                    <div class="alert alert-success fs-12 mb-3" role="alert">
                                        {{ session('status') }}
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('login') }}" class="w-100 mt-4 pt-2">
                                    @csrf

                                    {{-- Email --}}
                                    <div class="mb-4">
                                        <input
                                            type="email"
                                            name="email"
                                            id="email"
                                            class="form-control @error('email') is-invalid @enderror"
                                            placeholder="Email Address"
                                            value="{{ old('email') }}"
                                            required
                                            autofocus
                                            autocomplete="username"
                                        >
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Password --}}
                                    <div class="mb-3">
                                        <input
                                            type="password"
                                            name="password"
                                            id="password"
                                            class="form-control @error('password') is-invalid @enderror"
                                            placeholder="Password"
                                            required
                                            autocomplete="current-password"
                                        >
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Remember Me & Forgot Password --}}
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="custom-control custom-checkbox">
                                            <input
                                                type="checkbox"
                                                class="custom-control-input"
                                                id="rememberMe"
                                                name="remember"
                                                {{ old('remember') ? 'checked' : '' }}
                                            >
                                            <label class="custom-control-label c-pointer" for="rememberMe">Remember Me</label>
                                        </div>
                                        @if (Route::has('password.request'))
                                            <a href="{{ route('password.request') }}" class="fs-11 text-primary">Forgot password?</a>
                                        @endif
                                    </div>

                                    <div class="mt-5">
                                        <button type="submit" class="btn btn-lg btn-primary w-100">Login</button>
                                    </div>
                                </form>

                                @if (Route::has('register'))
                                    <div class="mt-5 text-muted">
                                        <span>Don't have an account?</span>
                                        <a href="{{ route('register') }}" class="fw-bold">Create an Account</a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Right: Illustration Panel --}}
                        <div class="col-lg-6 bg-primary order-0 order-lg-1">
                            <div class="h-100 d-flex align-items-center justify-content-center">
                                <img src="{{ asset('assets/images/auth/auth-user.png') }}" alt="" class="img-fluid">
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="{{ asset('assets/vendors/js/vendors.min.js') }}"></script>
    <script src="{{ asset('assets/js/common-init.min.js') }}"></script>
</body>

</html>
