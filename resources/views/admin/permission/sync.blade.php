@extends('layouts.app')
@section('title', 'Role Management')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">

            <!-- Sync Permission -->
            <div class="col-xl-4 mb-4 col-lg-5 col-12">
                <div class="card">
                    <div class="d-flex align-items-end row">
                        <div class="col-12">
                            <div class="card-body text-nowrap">
                                <form action="{{ route('admin.permission.assign.update', $role) }}" method="POST">
                                    @method('PUT')
                                    @csrf

                                    <div class="mb-3">
                                        <label class="form-label">Role Name</label>
                                        <select class="select2 form-select @error('role') is-invalid @enderror"
                                            data-allow-clear="true" name="role" required>
                                            <option value="">Select</option>
                                            @foreach ($roles as $item)
                                                <option {{ $role->id == $item->id ? 'selected' : '' }}>
                                                    {{ $item->name }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @error('role')
                                            <div
                                                class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Permission</label>
                                        <div class="select2-primary">
                                            <select class="select2 form-select @error('permissions') is-invalid @enderror"
                                                multiple name="permissions[]" required>
                                                <optgroup label="Select Multiple">
                                                    @foreach ($permissions as $item)
                                                        <option
                                                            {{ $role->permissions()->find($item->id) ? 'selected' : '' }}
                                                            value="{{ $item->name }}">
                                                            {{ $item->name }}
                                                        </option>
                                                        {{-- <option value="{{ $item->name }}">{{ $item->name }}</option> --}}
                                                    @endforeach
                                                </optgroup>
                                            </select>

                                            @error('permissions')
                                                <div
                                                    class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary waves-effect waves-light"
                                        style="width: 100%;">
                                        Sync Permission
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Sync Permission -->

            <!-- Role List -->
            <div class="col-xl-8 mb-4 col-lg-7 col-12">
                <div class="card">
                    <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer border-bottom">
                        <div class="row mx-1">
                            <div class="col-sm-12 col-md-3">
                                <div class="dataTables_length" id="DataTables_Table_0_length">
                                    <button type="button" class="btn btn-primary waves-effect waves-light"
                                        data-bs-toggle="modal" data-bs-target="#addRole"> Add Role </button>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-9">
                                <div
                                    class="dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-md-end justify-content-center flex-wrap me-1">
                                    <div>
                                        <form action="{{ route('admin.role.index') }}" method="GET">
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
                                        <td>Role Name</td>
                                        <td>Permission</td>
                                        <td style="width:10%;">Created At</td>
                                        <td class="text-center" style="width:10%;">Action</td>
                                    </tr>
                                </thead>
                                <tbody class="table-border-bottom-0">
                                    @php
                                        $no = 1;
                                    @endphp
                                    @forelse ($roles as $index => $item)
                                        @php
                                            $id = $item->id;
                                        @endphp
                                        <tr>
                                            <td>{{ $item->name }}</td>

                                            <td>
                                                @php
                                                    $permission = $item->getPermissionNames()->toArray();
                                                @endphp

                                                @foreach ($permission as $permit)
                                                    <span class="text-nowrap">
                                                        <span class="badge bg-label-primary m-1">
                                                            {{ $permit }}
                                                        </span>
                                                    </span>
                                                @endforeach
                                            </td>

                                            <td>{{ $item->created_at }}</td>

                                            <td class="text-center">
                                                <a href="{{ route('admin.permission.assign.edit', $id) }}">
                                                    <span class="badge badge-center bg-label-success w-px-30 h-px-30">
                                                        <i class="ti ti-lock ti-sm"></i>
                                                    </span>
                                                </a>

                                                <a href="javascript:void(0);" class="btn-edit"
                                                    data-id="{{ $item->id }}" data-name="{{ $item->name }}"
                                                    data-guard_name="{{ $item->guard_name }}">
                                                    <span class="badge badge-center bg-label-primary w-px-30 h-px-30">
                                                        <i class="ti ti-edit ti-sm"></i>
                                                    </span>
                                                </a>

                                                <a href="javascript:void(0);" class="btn-delete"
                                                    data-role_id="{{ $item->id }}">
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
                                        {{ $roles->withQueryString()->onEachSide(0)->links('vendor.pagination.bootstrap-5') }}
                                    </nav>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <!--/ Role List -->

        </div>
    </div>


    <!-- Add Role Modal -->
    <div class="modal fade" id="addRole" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCenterTitle">Add Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.role.create') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col mb-3">
                                <label class="form-label">Role Name</label>
                                <input type="text" name="name" class="form-control" placeholder="Enter role name"
                                    required />
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col mb-3">
                                <label class="form-label">Guard Name</label>
                                <input type="text" name="guard_name" class="form-control"
                                    placeholder="Enter guard name" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--/ Add Role Modal -->

    <!-- Edit Role Modal -->
    <div class="modal fade" id="editRole" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCenterTitle">Edit Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.role.update') }}" method="POST" id="editForm">
                    @method('PUT')
                    @csrf

                    <input type="text" name="id" id="id" class="form-control" hidden />
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col mb-3">
                                <label class="form-label">Role Name</label>
                                <input type="text" name="name" id="name" class="form-control"
                                    placeholder="Enter role name" required />
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col mb-3">
                                <label class="form-label">Guard Name</label>
                                <input type="text" name="guard_name" id="guard_name" class="form-control"
                                    placeholder="Enter guard name" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Edit Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--/ Edit Role Modal -->

    <!-- Delete Role Modal -->
    <div class="modal fade" id="deleteRole" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCenterTitle">Delete Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{ route('admin.role.destroy') }}" method="POST" id="deleteForm">
                    @method('DELETE')
                    @csrf

                    <div class="modal-body">
                        Are you sure to delete this role? You won't be able to revert this!

                        <input type="text" name="role_id" id="role_id" hidden />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Delete Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--/ Delete Role Modal -->
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
            var guard_name = $(this).data('guard_name');

            $('#id').val(id);
            $('#name').val(name);
            $('#guard_name').val(guard_name);

            $('#editRole').modal('show');
        });

        $('#editForm').submit(function() {
            $('#editRole').modal('hide');
        });
    </script>

    <script>
        $(document).on('click', '.btn-delete', function() {
            var role_id = $(this).data('role_id');

            $('#role_id').val(role_id);

            $('#deleteRole').modal('show');
        });

        $('#deleteForm').submit(function() {
            $('#deleteRole').modal('hide');
        });
    </script>
@endpush
