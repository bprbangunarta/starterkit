@extends('layouts.auth')
@section('title', 'Verify Email')

@section('content')
    <div class="authentication-wrapper authentication-cover authentication-bg">
        <div class="authentication-inner row">

            <div class="d-none d-lg-flex col-lg-7 p-0">
                <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
                    <img src="{{ asset('assets/img/illustrations/auth-verify-email-illustration-light.png') }}"
                        alt="auth-login-cover" class="img-fluid my-5 auth-illustration" />

                    <img src="{{ asset('assets/img/illustrations/bg-shape-image-light.png') }}" alt="auth-login-cover"
                        class="platform-bg" />
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

                    <h3 class="mb-1">Verify your email ✉️</h3>

                    @if (session('status') == 'verification-link-sent')
                        <p class="mb-4">A new verification link has been sent to the email address you provided in your
                            profile settings.</p>
                    @else
                        <p class="text-start mb-4">
                            Account activation link sent to your email address. Please follow the
                            link
                            inside to
                            continue.
                        </p>
                    @endif

                    <form id="formAuthentication" class="mb-3 fv-plugins-bootstrap5 fv-plugins-framework"
                        action="{{ route('verification.send') }}" method="POST" novalidate="novalidate">
                        @csrf

                        <button type="submit" class="btn btn-primary d-grid w-100">Resend Verification Email</button>
                    </form>

                    <div class="text-center">
                        <p class="text-center"> Skip for now?
                            <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modalLogout"> Log Out </a>
                        </p>
                    </div>

                </div>
            </div>

        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="modalLogout" tabindex="-1" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLogoutTitle">Confirm Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <div class="modal-body">
                        Are you sure you want to leave? A verification link has been sent to the email address you provided
                        in your profile settings.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary waves-effect" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button type="submit" class="btn btn-primary waves-effect waves-light"
                            onclick="event.preventDefault(); this.closest('form').submit();">Log Out</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ asset('assets/js/pages-auth.js') }}"></script>
@endpush
