@extends('layouts.app')
@section('title', 'Users Management')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="card">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-3">Search Filter</h5>
                konten
            </div>

            <div class="card-body">
                <div class="table-responsive text-nowrap mb-3" style="border-bottom: 1px solid #DBDADE;">
                    <table class="table">
                        <thead class="fw-bold">
                            <tr class="text-danger">
                                <td>User</td>
                                <td style="width:15%;">Role</td>
                                <td style="width:15%;">Username</td>
                                <td style="width:15%;">Phone</td>
                                <td style="width:10%;">Status</td>
                                <td class="text-center" style="width:10%;">Action</td>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @php
                                $no = 1;
                            @endphp
                            @forelse ($users as $index => $item)
                                <tr>
                                    <td>
                                        <div class="d-flex justify-content-start align-items-center user-name">
                                            <div class="avatar-wrapper">
                                                <div class="avatar me-3">
                                                    <img src="{{ asset('assets/img/avatars/6.png') }}" alt="Avatar"
                                                        alt="Avatar" class="rounded">
                                                </div>
                                            </div>

                                            <div class="d-flex flex-column">
                                                <a href="#" class="text-body text-truncate">
                                                    <span class="fw-medium">{{ $item->name }}</span>
                                                </a>
                                                <small class="text-muted">{{ $item->email }}</small>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="text-truncate d-flex align-items-center">
                                            <span
                                                class="badge badge-center rounded-pill bg-label-success w-px-30 h-px-30 me-2">
                                                <i class="ti ti-circle-check ti-sm"></i>
                                            </span>
                                            {{ $item->roles->pluck('name')[0] ?? '' }}
                                        </span>
                                    </td>

                                    <td>{{ $item->username }}</td>
                                    <td>{{ $item->phone }}</td>

                                    <td>
                                        @if ($item->is_active == 1)
                                            <span class="badge bg-label-success">Active</span>
                                        @else
                                            <span class="badge bg-label-danger">Deactivated</span>
                                        @endif

                                    </td>

                                    <td class="text-center">
                                        <a href="#">
                                            <span class="badge badge-center bg-label-primary w-px-30 h-px-30">
                                                <i class="ti ti-eye ti-sm"></i>
                                            </span>
                                        </a>

                                        <a href="#">
                                            <span class="badge badge-center bg-label-warning w-px-30 h-px-30">
                                                <i class="ti ti-edit ti-sm"></i>
                                            </span>
                                        </a>

                                        <a href="#">
                                            <span class="badge badge-center bg-label-danger w-px-30 h-px-30">
                                                <i class="ti ti-trash ti-sm"></i>
                                            </span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-center" colspan="10">No matching records found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="row" style="margin-bottom: -30px;">
                    {{-- <div class="col-sm-6 col-md-6 hidden-xs">
                        <div>
                            <button class="dt-button add-new btn btn-sm btn-primary waves-effect waves-light"
                                type="button">
                                Add User
                            </button>

                            <button type="button" class="btn btn-sm btn-label-secondary">
                                Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }}
                            </button>
                        </div>
                    </div> --}}

                    <div class="col-sm-12 col-md-12">
                        <div>
                            <nav aria-label="Page navigation">
                                {{ $users->withQueryString()->links('vendor.pagination.bootstrap-5') }}
                            </nav>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
@endsection
