@extends('layouts.app')
@section('title', 'Users Management')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="card">
            <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer border-bottom">
                <div class="row mx-1">
                    <div class="col-sm-12 col-md-3">
                        <div class="dataTables_length" id="DataTables_Table_0_length">
                            <button class="dt-button add-new btn btn-primary mb-3 mb-md-0 waves-effect waves-light"
                                tabindex="0" aria-controls="DataTables_Table_0" type="button" data-bs-toggle="modal"
                                data-bs-target="#addPermissionModal"><span>Add User</span>
                            </button>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-9">
                        <div
                            class="dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-md-end justify-content-center flex-wrap me-1">
                            <div>
                                <form action="{{ route('admin.user.index') }}" method="GET">
                                    <div class="dataTables_filter">
                                        <label>Search
                                            <input type="search" class="form-control" name="keyword"
                                                value="{{ request('keyword') }}" placeholder="Search..">
                                        </label>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body" style="margin-top: -20px;">
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
                                                    @if (is_null($item->profile_photo_path))
                                                        <img src="{{ asset('assets/img/avatars/14.png') }}" alt="Avatar"
                                                            alt="Avatar" class="rounded">
                                                    @else
                                                        <img src="{{ asset('storage') . '/' . $item->profile_photo_path }}"
                                                            alt="Avatar" alt="Avatar" class="rounded">
                                                    @endif
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
                                                class="badge badge-center rounded-pill bg-label-primary w-px-30 h-px-30 me-2">
                                                <i class="ti ti-user ti-sm"></i>
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
                    <div class="col-sm-12 col-md-12">
                        <div>
                            <nav aria-label="Page navigation">
                                {{ $users->withQueryString()->onEachSide(0)->links('vendor.pagination.bootstrap-5') }}
                            </nav>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
@endsection
