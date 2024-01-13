@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-3 mb-4">Page 1</h4>
        <p>
            Sample page.<br />For more layout options use
            <a href="" target="_blank" class="fw-medium">HTML starter template generator</a>
            and refer
            <a href="https://demos.pixinvent.com/vuexy-html-admin-template/documentation//layouts.html" target="_blank"
                class="fw-medium">Layout docs</a>.
        </p>

        <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
            data-bs-target="#modalLogout">
            Launch modal
        </button>
    </div>
@endsection
