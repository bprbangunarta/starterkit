@extends('layouts.app')
@section('title', 'Change Password')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row fv-plugins-icon-container">
            <div class="col-md-12">

                <div class="card mb-4">
                    <h5 class="card-header">Account Details</h5>
                    <div class="card-body">
                        <form id="formAccountSettings" action="{{ route('user.update.account') }}" method="POST"
                            class="fv-plugins-bootstrap5 fv-plugins-framework" novalidate="novalidate"
                            enctype="multipart/form-data">
                            @method('PUT')
                            @csrf

                            <div class="d-flex align-items-start align-items-sm-center gap-4 mb-4">
                                <img src="{{ asset('assets/img/avatars/14.png') }}" alt="user-avatar"
                                    class="d-block w-px-100 h-px-100 rounded" id="uploadedAvatar">
                                <div class="button-wrapper">
                                    <label for="upload" class="btn btn-primary me-2 mb-3 waves-effect waves-light"
                                        tabindex="0">
                                        <span class="d-none d-sm-block">Upload new photo</span>
                                        <i class="ti ti-upload d-block d-sm-none"></i>
                                        <input type="file" id="upload" class="account-file-input" hidden=""
                                            accept="image/png, image/jpeg">
                                    </label>
                                    <button type="button"
                                        class="btn btn-label-secondary account-image-reset mb-3 waves-effect">
                                        <i class="ti ti-refresh-dot d-block d-sm-none"></i>
                                        <span class="d-none d-sm-block">Reset</span>
                                    </button>

                                    <div class="text-muted">Allowed JPG, GIF or PNG. Max size of 800K</div>
                                </div>
                            </div>
                            <hr class="my-0 mb-3">

                            <div class="row">
                                <div class="mb-3 col-md-6 fv-plugins-icon-container">
                                    <label class="form-label">Full Name</label>
                                    <input class="form-control @error('name') invalid @enderror" type="text"
                                        id="name" name="name" value="{{ Auth::user()->name }}"
                                        placeholder="Zulfadli Rizal">

                                    @error('name')
                                        <div
                                            class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="mb-3 col-md-6 fv-plugins-icon-container">
                                    <label class="form-label">Username</label>
                                    <input class="form-control @error('username') invalid @enderror" type="text"
                                        id="username" name="username" value="{{ Auth::user()->username }}"
                                        placeholder="Zulfadli Rizal">

                                    @error('username')
                                        <div
                                            class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="mb-3 col-md-6 fv-plugins-icon-container">
                                    <label class="form-label">E-mail</label>
                                    <input class="form-control @error('email') invalid @enderror" type="text"
                                        id="name" name="name" value="{{ Auth::user()->email }}"
                                        placeholder="zulfadlirizal@gmail.com">

                                    @error('email')
                                        <div
                                            class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="mb-3 col-md-6 fv-plugins-icon-container">
                                    <label class="form-label">Phone Number</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text">ID (+62)</span>
                                        <input class="form-control @error('phone') invalid @enderror" type="text"
                                            name="phone" oninput="validateNumber(this)" maxlength="12"
                                            value="{{ substr(Auth::user()->phone, 2) }}" placeholder="823 200 999 71">
                                    </div>

                                    @error('phone')
                                        <div
                                            class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="mt-2">
                                <button type="submit" class="btn btn-primary me-2 waves-effect waves-light">
                                    Save changes
                                </button>
                                <button type="reset" class="btn btn-label-secondary waves-effect">Cancel</button>
                            </div>
                            <input type="hidden">
                        </form>
                    </div>
                </div>

                <div class="card">
                    <h5 class="card-header">Delete Account</h5>
                    <div class="card-body">
                        <div class="mb-3 col-12 mb-0">
                            <div class="alert alert-warning">
                                <h5 class="alert-heading mb-1">Are you sure you want to delete your account?</h5>
                                <p class="mb-0">Once you delete your account, there is no going back. Please be certain.
                                </p>
                            </div>
                        </div>
                        <form id="formAccountDeactivation" onsubmit="return false"
                            class="fv-plugins-bootstrap5 fv-plugins-framework" novalidate="novalidate">
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" name="accountActivation"
                                    id="accountActivation">
                                <label class="form-check-label" for="accountActivation">I confirm my account
                                    deactivation</label>
                                <div
                                    class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                </div>
                            </div>
                            <button type="submit"
                                class="btn btn-danger deactivate-account waves-effect waves-light">Deactivate
                                Account</button>
                            <input type="hidden">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        function validateNumber(input) {
            input.value = input.value.replace(/\D/g, '');
        }
    </script>
    <script src="{{ asset('assets/js/pages-account-settings-account.js') }}"></script>
@endpush
