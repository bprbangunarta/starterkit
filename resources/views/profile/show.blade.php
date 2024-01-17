@extends('layouts.app')
@section('title', 'Account Setting')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row fv-plugins-icon-container">
            <div class="col-md-12">

                <div class="card mb-4">
                    <h5 class="card-header">Account Details</h5>
                    <div class="card-body">
                        <form action="{{ route('user.update.account') }}" method="POST" enctype="multipart/form-data">
                            @method('PUT')
                            @csrf



                            <div class="d-flex align-items-start align-items-sm-center gap-4 mb-4">

                                @if (is_null(Auth::user()->profile_photo_path))
                                    <img src="{{ asset('assets/img/avatars/14.png') }}" alt="user-avatar"
                                        class="d-block w-px-100 h-px-100 rounded" id="uploadedAvatar">
                                @else
                                    <img src="{{ asset('storage') . '/' . Auth::user()->profile_photo_path }}"
                                        alt="user-avatar" class="d-block w-px-100 h-px-100 rounded" id="uploadedAvatar">
                                @endif

                                <div class="button-wrapper">
                                    <label for="upload" class="btn btn-primary me-2 mb-3 waves-effect waves-light"
                                        tabindex="0">
                                        <span class="d-none d-sm-block">Upload new photo</span>
                                        <i class="ti ti-upload d-block d-sm-none"></i>
                                        <input type="file" id="upload" name="profile_photo_path"
                                            class="account-file-input" hidden="" accept="image/png, image/jpeg">
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
                                        id="email" name="email" value="{{ Auth::user()->email }}"
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
                                <button class="btn btn-primary me-2 waves-effect waves-light">
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
                        <button data-bs-toggle="modal" data-bs-target="#modalDeactive"
                            class="btn btn-danger deactivate-account waves-effect waves-light">
                            Deactivate Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="modalDeactive" tabindex="-1" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDeactiveTitle">Confirm Delete Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('user.deactivation') }}">
                    @method('PUT')
                    @csrf
                    <div class="modal-body">
                        Are you sure you want to delete your account?
                        Once you delete your account, there is no going back. Please be certain.
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary waves-effect" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button type="submit" class="btn btn-danger waves-effect waves-light">Confirm</button>
                    </div>
                </form>
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
