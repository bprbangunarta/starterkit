@extends('layouts.app')
@section('title', 'Site Configuration')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">

            <div class="col-xl-12 mb-4 col-lg-5 col-12">
                <div class="card mb-4">
                    <form class="card-body" action="{{ route('site.update') }}" method="POST" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf

                        <input type="hidden" name="id" value="{{ $site->id }}">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">App Name</label>
                                <input type="text" class="form-control @error('app_name') is-invalid @enderror"
                                    name="app_name" value="{{ $site->app_name }}" placeholder="Starterkit">

                                @error('app_name')
                                    <div
                                        class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                        <div data-field="email-username" data-validator="notEmpty">{{ $message }}</div>
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Meta Image (1280x720)</label>
                                <input type="file" class="form-control @error('meta_image') is-invalid @enderror"
                                    name="meta_image">

                                @error('meta_image')
                                    <div
                                        class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                        <div data-field="email-username" data-validator="notEmpty">{{ $message }}</div>
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Meta Description</label>
                                <input type="text" class="form-control @error('meta_description') is-invalid @enderror"
                                    name="meta_description" value="{{ $site->meta_description }}" placeholder="Starterkit">

                                @error('meta_description')
                                    <div
                                        class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                        <div data-field="email-username" data-validator="notEmpty">{{ $message }}</div>
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Site Logo (100x100)</label>
                                <input type="file" class="form-control @error('logo') is-invalid @enderror"
                                    name="logo">

                                @error('logo')
                                    <div
                                        class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                        <div data-field="email-username" data-validator="notEmpty">{{ $message }}</div>
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Meta Keyword</label>
                                <input type="text" class="form-control @error('meta_keyword') is-invalid @enderror"
                                    name="meta_keyword" value="{{ $site->meta_keyword }}" placeholder="starterkit, laravel">

                                @error('meta_keyword')
                                    <div
                                        class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                        <div data-field="email-username" data-validator="notEmpty">{{ $message }}</div>
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Favicon (100x100)</label>
                                <input type="file" class="form-control @error('favicon') is-invalid @enderror"
                                    name="favicon">

                                @error('favicon')
                                    <div
                                        class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                        <div data-field="email-username" data-validator="notEmpty">{{ $message }}</div>
                                    </div>
                                @enderror
                            </div>
                        </div>
                        <hr class="my-4 mx-n4">

                        <h6>Footer Text</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Text Left</label>
                                <input type="text" class="form-control @error('footer_left') is-invalid @enderror"
                                    name="footer_left" value="{{ $site->footer_left }}" placeholder="Starterkit">

                                @error('footer_left')
                                    <div
                                        class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                        <div data-field="email-username" data-validator="notEmpty">{{ $message }}</div>
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Footer Right</label>
                                <input type="text" class="form-control @error('footer_right') is-invalid @enderror"
                                    name="footer_right" value="{{ $site->footer_right }}" placeholder="Starterkit">

                                @error('footer_right')
                                    <div
                                        class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                        <div data-field="email-username" data-validator="notEmpty">{{ $message }}</div>
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="pt-4">
                            <button type="submit"
                                class="btn btn-primary me-sm-3 me-1 waves-effect waves-light">Submit</button>
                            <button type="reset" class="btn btn-label-secondary waves-effect">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection
