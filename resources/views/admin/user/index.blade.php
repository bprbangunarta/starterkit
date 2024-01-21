@extends('layouts.app')
@section('title', 'Users Management')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="card">
            <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer border-bottom">
                <div class="row mx-1">
                    <div class="col-sm-12 col-md-3">
                        <div class="dataTables_length" id="DataTables_Table_0_length">
                            <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                                data-bs-target="#addUser"> Add User </button>
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
                                        <a href="#" class="btn-edit" data-id="{{ $item->id }}"
                                            data-name="{{ $item->name }}" data-username="{{ $item->username }}"
                                            data-email="{{ $item->email }}" data-phone="{{ substr($item->phone, 2) }}"
                                            data-role="{{ $item->roles->pluck('name')[0] ?? '' }}"
                                            data-is_active="{{ $item->is_active }}">
                                            <span class="badge badge-center bg-label-primary w-px-30 h-px-30">
                                                <i class="ti ti-edit ti-sm"></i>
                                            </span>
                                        </a>

                                        <a href="javascript:void(0);" class="btn-delete" data-id="{{ $item->id }}">
                                            <span class="badg e badge-center bg-label-danger w-px-30 h-px-30">
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

    <!-- Add User Modal -->
    <div class="modal fade" id="addUser" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCenterTitle">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.user.create') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" placeholder="Enter your name"
                                    required />
                            </div>
                            <div class="col mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control"
                                    placeholder="Enter your username" required />
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="Enter your email"
                                    required />
                            </div>
                            <div class="col mb-3">
                                <label class="form-label">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text">ID (+62)</span>
                                    <input type="text" name="phone" class="form-control"
                                        placeholder="82320099971" />
                                </div>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col mb-0">
                                <label class="form-label">Role Name</label>
                                <select name="role" class="select2 form-select" data-allow-clear="true" required>
                                    <option value="">Select</option>
                                    @foreach ($roles as $item)
                                        <option value="{{ $item->name }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col mb-0">
                                <label class="form-label">Status</label>
                                <select name="is_active" class="select2 form-select" data-allow-clear="true" required>
                                    <option value="">Select</option>
                                    <option value="1">Active</option>
                                    <option value="0">Deactivated</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--/ Add User Modal -->

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUser" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCenterTitle">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.user.update') }}" method="POST" id="editForm">
                    @method('PUT')
                    @csrf

                    <input type="text" name="id" id="id" class="form-control" hidden />

                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" id="name" class="form-control"
                                    placeholder="Enter your name" required />
                            </div>
                            <div class="col mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" id="username" class="form-control"
                                    placeholder="Enter your username" required />
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" id="email" class="form-control"
                                    placeholder="Enter your email" required />
                            </div>
                            <div class="col mb-3">
                                <label class="form-label">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text">ID (+62)</span>
                                    <input type="text" name="phone" id="phone" class="form-control"
                                        placeholder="82320099971" />
                                </div>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col mb-0">
                                <label class="form-label">Role Name</label>
                                <select name="role" id="role" class="select2 form-select"
                                    data-allow-clear="true" required>
                                    <option value="">Select</option>
                                    @foreach ($roles as $item)
                                        <option value="{{ $item->name }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col mb-0">
                                <label class="form-label">Status</label>
                                <select name="is_active" id="is_active" class="select2 form-select"
                                    data-allow-clear="true" required>
                                    <option value="">Select</option>
                                    <option value="1">Active</option>
                                    <option value="0">Deactivated</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Edit User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--/ Edit User Modal -->
@endsection

@push('script')
    <script>
        $(function() {
            const select2 = $('.select2');

            if (select2.length) {
                select2.each(function() {
                    var $this = $(this);
                    $this.wrap('<div class="position-relative"></div>').select2({
                        placeholder: 'Select value',
                        dropdownParent: $this.parent()
                    });
                });
            }
        });
    </script>

    <script>
        $(document).on('click', '.btn-edit', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var username = $(this).data('username');
            var email = $(this).data('email');
            var phone = $(this).data('phone');
            var role = $(this).data('role');
            var is_active = $(this).data('is_active');

            $('#id').val(id);
            $('#name').val(name);
            $('#username').val(username);
            $('#email').val(email);
            $('#phone').val(phone);

            $('#role').val(role).trigger('change');
            $('#is_active').empty();

            if (is_active == 0) {
                $("#is_active").append(
                    $("<option>", {
                        value: 0,
                        text: "Deactivated",
                    }).prop("selected", true)
                );
                $("#is_active").append(
                    $("<option>", {
                        value: 1,
                        text: "Active",
                    })
                );
            } else if (is_active == 1) {
                $("#is_active").append(
                    $("<option>", {
                        value: 1,
                        text: "Active",
                    }).prop("selected", true)
                );
                $("#is_active").append(
                    $("<option>", {
                        value: 0,
                        text: "Deactivated",
                    })
                );
            }

            $('#editUser').modal('show');
        });

        $('#editForm').submit(function() {
            $('#editUser').modal('hide');
        });
    </script>

    <script>
        $(document).on('click', '.btn-delete', function() {
            var user_id = $(this).data('id');

            Swal.fire({
                text: 'Are you sure? You won\'t be able to revert this!',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/user/' + user_id + '/destroy',
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                text: 'Deleted successfully!',
                                showConfirmButton: false,
                                timer: 1500

                            }).then((result) => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON.message,
                                showConfirmButton: true,
                                confirmButtonColor: "#DD4B39",
                                confirmButtonText: 'Close',
                            });
                        }
                    });
                }
            });
        });
    </script>
@endpush
