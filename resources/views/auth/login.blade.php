@extends('layouts.auth')
@section('title', 'Login')

@section('content')
    <div class="authentication-wrapper authentication-cover authentication-bg">
        <div class="authentication-inner row">

            <div class="d-none d-lg-flex col-lg-7 p-0">
                <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
                    <img src="{{ asset('assets/img/illustrations/auth-login-illustration-light.png') }}"
                        alt="auth-login-cover" class="img-fluid my-5 auth-illustration"
                        data-app-light-img="illustrations/auth-login-illustration-light.png"
                        data-app-dark-img="illustrations/auth-login-illustration-dark.png" />

                    <img src="{{ asset('assets/img/illustrations/bg-shape-image-light.png') }}" alt="auth-login-cover"
                        class="platform-bg" data-app-light-img="illustrations/bg-shape-image-light.png"
                        data-app-dark-img="illustrations/bg-shape-image-dark.png" />
                </div>
            </div>

            <div class="d-flex col-12 col-lg-5 align-items-center p-sm-5 p-4">
                <div class="w-px-400 mx-auto">

                    <div class="app-brand mb-4">
                        <a href="{{ route('login') }}" class="app-brand-link gap-2">
                            <span class="app-brand-logo demo">
                                <x-app-logo></x-app-logo>
                            </span>
                        </a>
                    </div>

                    <h3 class="mb-1">Welcome to <x-app-name></x-app-name>! 👋</h3>
                    <p class="mb-4">Please sign-in to your account and start the adventure</p>

                    <form id="formAuthentication" class="mb-3" action="{{ route('login') }}" method="POST">
                        @csrf
                        <div
                            class="mb-3 fv-plugins-icon-container @error('auth') fv-plugins-bootstrap5-row-invalid @enderror">
                            <label class="form-label">Email or Username</label>
                            <input type="text" class="form-control @error('auth') is-invalid @enderror" id="auth"
                                name="auth" placeholder="Enter your email or username" value="{{ old('auth') }}"
                                required>

                            @error('auth')
                                <div
                                    class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div data-field="email-username" data-validator="notEmpty">{{ $message }}</div>
                                </div>
                            @enderror
                        </div>

                        <div
                            class="mb-3 form-password-toggle fv-plugins-icon-container @error('password') fv-plugins-bootstrap5-row-invalid @enderror">
                            <div class="d-flex justify-content-between">
                                <label class="form-label">Password</label>
                                <a href="{{ route('password.request') }}">
                                    <small>Forgot Password?</small>
                                </a>
                            </div>
                            <div class="input-group input-group-merge has-validation">
                                <input type="password" id="password"
                                    class="form-control @error('password') is-invalid @enderror" name="password"
                                    placeholder="············" aria-describedby="password">
                                <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                            </div>

                            @error('password')
                                <div
                                    class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div data-field="password" data-validator="notEmpty">{{ $message }}</div>
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember-me">
                                <label class="form-check-label"> Remember Me </label>
                            </div>
                        </div>
                        <button class="btn btn-primary d-grid w-100 waves-effect waves-light">Sign in</button>
                        <input type="hidden">
                    </form>

                    <p class="text-center">
                        <span>New on our platform?</span>
                        <a href="{{ route('register') }}">
                            <span>Create an account</span>
                        </a>
                    </p>

                </div>
            </div>

        </div>
    </div>
@endsection

@push('script')
    <script src="{{ asset('assets/js/pages-auth.js') }}"></script>
@endpush
