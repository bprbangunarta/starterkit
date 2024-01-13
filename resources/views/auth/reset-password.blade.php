@extends('auth.layouts.auth')
@section('title', 'Forgot Password')

@section('content')
    <div class="authentication-wrapper authentication-cover authentication-bg">
        <div class="authentication-inner row">

            <div class="d-none d-lg-flex col-lg-7 p-0">
                <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
                    <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
                        <img src="{{ asset('assets/img/illustrations/auth-reset-password-illustration-light.png') }}"
                            alt="auth-reset-password-cover" class="img-fluid my-5 auth-illustration">

                        <img src="{{ asset('assets/img/illustrations/bg-shape-image-light.png') }}"
                            alt="auth-reset-password-cover" class="platform-bg">
                    </div>
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

                    <h3 class="mb-1">Reset Password 🔒</h3>
                    <p class="mb-4">for <span class="fw-medium">{{ old('email', $request->email) }}</span></p>

                    <form id="formAuthentication" class="mb-3 fv-plugins-bootstrap5 fv-plugins-framework"
                        action="{{ route('password.update') }}" method="POST" novalidate="novalidate">
                        @csrf

                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                        <div
                            class="mb-3 fv-plugins-icon-container @error('email') fv-plugins-bootstrap5-row-invalid @enderror">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                name="email" placeholder="Enter your email" autofocus=""
                                value="{{ old('email', $request->email) }}" readonly>

                            @error('email')
                                <div
                                    class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div data-field="email" data-validator="notEmpty">{{ $message }}</div>
                                </div>
                            @enderror
                        </div>

                        <div
                            class="mb-3 form-password-toggle fv-plugins-icon-container @error('password') fv-plugins-bootstrap5-row-invalid @enderror">
                            <div class="d-flex justify-content-between">
                                <label class="form-label">New Password</label>
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

                        <div
                            class="mb-4 form-password-toggle fv-plugins-icon-container @error('password_confirmation') fv-plugins-bootstrap5-row-invalid @enderror">
                            <div class="d-flex justify-content-between">
                                <label class="form-label">Confirm Password</label>
                            </div>
                            <div class="input-group input-group-merge has-validation">
                                <input type="password" id="confirm-password"
                                    class="form-control @error('password_confirmation') is-invalid @enderror"
                                    name="password_confirmation" placeholder="············"
                                    aria-describedby="confirm-password">
                                <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                            </div>

                            @error('password_confirmation')
                                <div
                                    class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div data-field="confirm-password" data-validator="notEmpty">{{ $message }}
                                    </div>
                                </div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary d-grid w-100 mb-3 waves-effect waves-light">Set new
                            password</button>

                        <div class="text-center">
                            <a href="{{ route('login') }}" class="d-flex align-items-center justify-content-center">
                                <i class="ti ti-chevron-left scaleX-n1-rtl"></i>
                                Back to login
                            </a>
                        </div>
                        <input type="hidden">
                    </form>

                </div>
            </div>

        </div>
    </div>
@endsection

@push('script')
    <script src="{{ asset('assets/js/pages-auth.js') }}"></script>
@endpush
