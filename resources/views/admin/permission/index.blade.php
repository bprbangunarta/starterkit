@extends('layouts.app')
@section('title', 'Permission Management')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="card">
            <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer border-bottom">
                <div class="row mx-1">
                    <div class="col-sm-12 col-md-3">
                        <div class="dataTables_length" id="DataTables_Table_0_length">
                            <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                                data-bs-target="#addPermission"> Add Permission </button>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-9">
                        <div
                            class="dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-md-end justify-content-center flex-wrap me-1">
                            <div>
                                <form action="{{ route('admin.permission.index') }}" method="GET">
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
                                <td>Permission Name</td>
                                <td>Assign To</td>
                                <td style="width:10%;">Guard</td>
                                <td class="text-center" style="width:10%;">Action</td>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @php
                                $no = 1;
                            @endphp
                            @forelse ($permissions as $index => $item)
                                <tr>
                                    <td>{{ $item->name }}</td>

                                    <td>
                                        <span class="text-nowrap">
                                            <a href="{{ route('admin.user.index') }}?keyword=Administrator">
                                                <span class="badge bg-label-primary m-1">Administrator
                                                </span>
                                            </a>
                                        </span>
                                    </td>

                                    <td>{{ $item->guard_name }}</td>

                                    <td class="text-center">
                                        <a href="javascript:void(0);" class="btn-give" data-id="{{ $item->id }}">
                                            <span class="badge badge-center bg-label-success w-px-30 h-px-30">
                                                <i class="ti ti-lock ti-sm"></i>
                                            </span>
                                        </a>

                                        <a href="javascript:void(0);" class="btn-edit" data-id="{{ $item->id }}"
                                            data-name="{{ $item->name }}" data-guard_name="{{ $item->guard_name }}">
                                            <span class="badge badge-center bg-label-primary w-px-30 h-px-30">
                                                <i class="ti ti-edit ti-sm"></i>
                                            </span>
                                        </a>

                                        <a href="javascript:void(0);" class="btn-delete"
                                            data-permission_id="{{ $item->id }}">
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
                                {{ $permissions->withQueryString()->onEachSide(0)->links('vendor.pagination.bootstrap-5') }}
                            </nav>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Add Permission Modal -->
    <div class="modal fade" id="addPermission" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCenterTitle">Add Permission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.permission.create') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col mb-3">
                                <label class="form-label">Permission</label>
                                <input type="text" name="name" class="form-control" placeholder="Enter permission"
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
                        <button type="submit" class="btn btn-primary">Add Permission</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--/ Add Permission Modal -->

    <!-- Edit Permission Modal -->
    <div class="modal fade" id="editPermission" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCenterTitle">Edit Permission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.permission.update') }}" method="POST" id="editForm">
                    @method('PUT')
                    @csrf

                    <input type="text" name="id" id="id" class="form-control" hidden />
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col mb-3">
                                <label class="form-label">Permission</label>
                                <input type="text" name="name" id="name" class="form-control"
                                    placeholder="Enter Permission" required />
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
                        <button type="submit" class="btn btn-primary">Edit Permission</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--/ Edit Permission Modal -->

    <!-- Delete Permission Modal -->
    <div class="modal fade" id="deletePermission" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCenterTitle">Delete Permission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{ route('admin.permission.destroy') }}" method="POST" id="deleteForm">
                    @method('DELETE')
                    @csrf

                    <div class="modal-body">
                        Are you sure to delete this permission? You won't be able to revert this!

                        <input type="text" name="permission_id" id="permission_id" class="form-control" hidden />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Delete Permission</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--/ Delete Permission Modal -->
@endsection

@push('script')
    <script>
        $(document).on('click', '.btn-edit', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var guard_name = $(this).data('guard_name');

            $('#id').val(id);
            $('#name').val(name);
            $('#guard_name').val(guard_name);

            $('#editPermission').modal('show');
        });

        $('#editForm').submit(function() {
            $('#editPermission').modal('hide');
        });
    </script>

    <script>
        $(document).on('click', '.btn-delete', function() {
            var permission_id = $(this).data('permission_id');

            $('#permission_id').val(permission_id);

            $('#deletePermission').modal('show');
        });

        $('#deleteForm').submit(function() {
            $('#deletePermission').modal('hide');
        });
    </script>
@endpush
