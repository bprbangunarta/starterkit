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
                                <svg width="32" height="22" viewBox="0 0 32 22" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z"
                                        fill="#7367F0" />
                                    <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd"
                                        d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z"
                                        fill="#161616" />
                                    <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd"
                                        d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z"
                                        fill="#161616" />
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z"
                                        fill="#7367F0" />
                                </svg>
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
