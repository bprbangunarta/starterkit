@extends('auth.layouts.auth')
@section('title', 'Forgot Password')

@section('content')
    <div class="authentication-wrapper authentication-cover authentication-bg">
        <div class="authentication-inner row">

            <div class="d-none d-lg-flex col-lg-7 p-0">
                <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
                    <img src="{{ asset('assets/img/illustrations/auth-forgot-password-illustration-light.png') }}"
                        alt="auth-login-cover" class="img-fluid my-5 auth-illustration"
                        data-app-light-img="illustrations/auth-forgot-password-illustration-light.png  "
                        data-app-dark-img="illustrations/auth-forgot-password-illustration-dark.png" />

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

                    <h3 class="mb-1">Forgot Password? 🔒</h3>

                    @if (session('status'))
                        <p class="mb-4">{{ session('status') }}</p>
                    @else
                        <p class="mb-4">Enter your email and we'll send you instructions to reset your password</p>
                    @endif

                    <form id="formAuthentication" class="mb-3 fv-plugins-bootstrap5 fv-plugins-framework"
                        action="{{ route('password.email') }}" method="POST" novalidate="novalidate">
                        @csrf

                        <div
                            class="mb-3 fv-plugins-icon-container @error('email') fv-plugins-bootstrap5-row-invalid @enderror">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                name="email" placeholder="Enter your email" autofocus="" value="{{ old('email') }}">

                            @error('email')
                                <div
                                    class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div data-field="email" data-validator="notEmpty">{{ $message }}</div>
                                </div>
                            @enderror
                        </div>

                        <button class="btn btn-primary d-grid w-100">Send Reset Link</button>
                    </form>

                    <div class="text-center">
                        <a href="{{ route('login') }}" class="d-flex align-items-center justify-content-center">
                            <i class="ti ti-chevron-left scaleX-n1-rtl"></i>
                            Back to login
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
@endsection

@push('script')
    <script src="{{ asset('assets/js/pages-auth.js') }}"></script>
@endpush
