@extends('layouts.app')
@section('title', 'Change Password')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row fv-plugins-icon-container">
            <div class="col-md-12">

                <div class="card mb-4">
                    <h5 class="card-header">Change Password</h5>
                    <div class="card-body">
                        <form id="formAccountSettings" method="POST" class="fv-plugins-bootstrap5 fv-plugins-framework"
                            novalidate="novalidate" action="{{ route('user.update.password') }}">
                            @method('PUT')
                            @csrf

                            <div class="row">
                                <div
                                    class="mb-3 col-md-6 form-password-toggle fv-plugins-icon-container @error('current_password') fv-plugins-bootstrap5-row-invalid @enderror">
                                    <div class="d-flex justify-content-between">
                                        <label class="form-label">Current Password</label>
                                    </div>
                                    <div class="input-group input-group-merge has-validation">
                                        <input type="password" id="current_password"
                                            class="form-control @error('current_password') is-invalid @enderror"
                                            name="current_password" placeholder="············"
                                            aria-describedby="current_password">
                                        <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                                    </div>

                                    @error('current_password')
                                        <div
                                            class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                            <div data-field="current_password">{{ $message }}</div>
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div
                                    class="mb-3 col-md-6 form-password-toggle fv-plugins-icon-container @error('password') fv-plugins-bootstrap5-row-invalid @enderror">
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
                                            <div data-field="password">{{ $message }}</div>
                                        </div>
                                    @enderror
                                </div>

                                <div
                                    class="mb-3 col-md-6 form-password-toggle fv-plugins-icon-container @error('password_confirmation') fv-plugins-bootstrap5-row-invalid @enderror">
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
                                            <div data-field="multiStepsConfirmPass" data-validator="notEmpty">
                                                {{ $message }}
                                            </div>
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-12 mb-4">
                                    <h6>Password Requirements:</h6>
                                    <ul class="ps-3 mb-0">
                                        <li class="mb-1">Minimum 8 characters long - the more, the better</li>
                                        <li class="mb-1">At least one lowercase character</li>
                                        <li>At least one number, symbol, or whitespace character</li>
                                    </ul>
                                </div>
                                <div>
                                    <button class="btn btn-primary me-2 waves-effect waves-light">
                                        Save changes
                                    </button>
                                    <button type="reset" class="btn btn-label-secondary waves-effect">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
