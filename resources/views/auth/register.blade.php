@extends('auth.layouts.auth')
@section('title', 'Register')

@section('content')
    <div class="authentication-wrapper authentication-cover authentication-bg">
        <div class="authentication-inner row">

            <div class="d-none d-lg-flex col-lg-7 p-0">
                <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
                    <img src="{{ asset('assets/img/illustrations/auth-register-illustration-light.png') }}"
                        alt="auth-login-cover" class="img-fluid my-5 auth-illustration"
                        data-app-light-img="illustrations/auth-register-illustration-light.png  "
                        data-app-dark-img="illustrations/auth-register-illustration-dark.png" />

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

                    <h3 class="mb-1">Adventure starts here 🚀</h3>
                    <p class="mb-4">Make your app management easy and fun!</p>

                    <form id="formAuthentication" class="mb-3 fv-plugins-bootstrap5 fv-plugins-framework"
                        action="{{ route('register') }}" method="POST" novalidate="novalidate">
                        @csrf

                        <div
                            class="mb-3 fv-plugins-icon-container @error('name') fv-plugins-bootstrap5-row-invalid @enderror">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                name="name" placeholder="Enter your name" autofocus="" value="{{ old('name') }}">

                            @error('name')
                                <div
                                    class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div data-field="name">{{ $message }}</div>
                                </div>
                            @enderror
                        </div>

                        <div
                            class="mb-3 fv-plugins-icon-container @error('username') fv-plugins-bootstrap5-row-invalid @enderror">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control @error('username') is-invalid @enderror"
                                id="username" name="username" placeholder="Enter your username" autofocus=""
                                value="{{ old('username') }}">

                            @error('username')
                                <div
                                    class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div data-field="username">{{ $message }}</div>
                                </div>
                            @enderror
                        </div>

                        <div
                            class="mb-3 fv-plugins-icon-container @error('email') fv-plugins-bootstrap5-row-invalid @enderror">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                name="email" placeholder="Enter your email" autofocus="" value="{{ old('email') }}">

                            @error('email')
                                <div
                                    class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div data-field="email">{{ $message }}</div>
                                </div>
                            @enderror
                        </div>

                        <div
                            class="mb-3 fv-plugins-icon-container @error('username') fv-plugins-bootstrap5-row-invalid @enderror">

                            <label class="form-label">Phone Number</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">ID (+62)</span>
                                <input class="form-control @error('phone') invalid @enderror" type="text" name="phone"
                                    oninput="validateNumber(this)" maxlength="12" value="{{ old('phone') }}"
                                    placeholder="823 200 999 71">
                            </div>
                            {{-- 
                            <div class="input-group">
                                <span class="input-group-text">62</span>
                                <input type="number" class="form-control" name="phone" id="phone"
                                    value="{{ old('phone') }}" placeholder="823200999XX">
                            </div> --}}

                            @error('phone')
                                <div
                                    class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div data-field="phone">{{ $message }}</div>
                                </div>
                            @enderror
                        </div>

                        <div
                            class="mb-3 form-password-toggle fv-plugins-icon-container @error('password') fv-plugins-bootstrap5-row-invalid @enderror">
                            <div class="d-flex justify-content-between">
                                <label class="form-label">Password</label>
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
                                    <div data-field="password">{{ $message }}</div>
                                </div>
                            @enderror
                        </div>

                        <div
                            class="mb-4 form-password-toggle fv-plugins-icon-container @error('password_confirmation') fv-plugins-bootstrap5-row-invalid @enderror">
                            <div class="d-flex justify-content-between">
                                <label class="form-label">Confirm Password</label>
                            </div>
                            <div class="input-group input-group-merge has-validation">
                                <input type="password" id="multiStepsConfirmPass"
                                    class="form-control @error('password_confirmation') is-invalid @enderror"
                                    name="password_confirmation" placeholder="············"
                                    aria-describedby="multiStepsConfirmPass">
                                <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                            </div>

                            @error('password_confirmation')
                                <div
                                    class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div data-field="multiStepsConfirmPass" data-validator="notEmpty">{{ $message }}
                                    </div>
                                </div>
                            @enderror
                        </div>

                        <button class="btn btn-primary d-grid w-100 waves-effect waves-light">Sign up</button>
                        <input type="hidden">
                    </form>

                    <p class="text-center">
                        <span>Already have an account?</span>
                        <a href="{{ route('login') }}">
                            <span>Sign in instead</span>
                        </a>
                    </p>

                </div>
            </div>

        </div>
    </div>
@endsection

@push('script')
    <script src="{{ asset('assets/js/pages-auth-multisteps.js') }}"></script>
@endpush
