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
                                <label class="form-label" for="multicol-username">App Name</label>
                                <input type="text" class="form-control" name="app_name" value="{{ $site->app_name }}"
                                    placeholder="Starterkit">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="multicol-username">Meta Image (1280x720)</label>
                                <input type="file" class="form-control" name="meta_image">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="multicol-username">Meta Description</label>
                                <input type="text" class="form-control" name="meta_description"
                                    value="{{ $site->meta_description }}" placeholder="Starterkit">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="multicol-username">Site Logo (300x150)</label>
                                <input type="file" class="form-control" name="logo">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="multicol-username">Meta Keyword</label>
                                <input type="text" class="form-control" name="meta_keyword"
                                    value="{{ $site->meta_keyword }}" placeholder="starterkit, laravel">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="multicol-username">Favicon (100x100)</label>
                                <input type="file" class="form-control" name="favicon">
                            </div>
                        </div>
                        <hr class="my-4 mx-n4">

                        <h6>Footer Text</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="multicol-username">Text Left</label>
                                <input type="text" class="form-control" name="footer_left"
                                    value="{{ $site->footer_left }}" placeholder="Starterkit">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="multicol-username">Footer Right</label>
                                <input type="text" class="form-control" name="footer_right"
                                    value="{{ $site->footer_right }}" placeholder="Starterkit">
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
