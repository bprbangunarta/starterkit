@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">

            <!-- View sales -->
            <div class="col-xl-4 mb-4 col-lg-5 col-12">
                <div class="card">
                    <div class="d-flex align-items-end row">
                        <div class="col-7">
                            <div class="card-body text-nowrap">
                                <h5 class="card-title mb-0 text-capitalize">Congratulations {{ Auth::user()->username }}! 🎉
                                </h5>
                                <p class="mb-4">Realization this month</p>
                                <h4 class="text-primary mb-1">Rp. 3.876.987.000</h4>
                            </div>
                        </div>
                        <div class="col-5 text-center text-sm-left">
                            <div class="card-body pb-0 px-0 px-md-4">
                                <img src="{{ asset('assets/img/illustrations/card-advance-sale.png') }}" height="140"
                                    alt="view sales">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- View sales -->

            <!-- Statistics -->
            <div class="col-xl-8 mb-4 col-lg-7 col-12">
                <div class="card h-100">
                    <div class="card-header">
                        <div class="d-flex justify-content-between mb-3">
                            <h5 class="card-title mb-0">Statistics</h5>
                            <small class="text-muted">Updated 1 month ago</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row gy-3">
                            <div class="col-md-3 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-primary me-3 p-2">
                                        <i class="ti ti-chart-pie-2 ti-sm"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0">230k</h5>
                                        <small>Sales</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-info me-3 p-2">
                                        <i class="ti ti-users ti-sm"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0">8.549k</h5>
                                        <small>Customers</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-danger me-3 p-2">
                                        <i class="ti ti-shopping-cart ti-sm"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0">1.423k</h5>
                                        <small>Products</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-success me-3 p-2">
                                        <i class="ti ti-currency-dollar ti-sm"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0">$9745</h5>
                                        <small>Revenue</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--/ Statistics -->

            <!-- User Table -->
            <div class="col-xl-12 mb-4 col-lg-12 col-12">
                <div class="card h-100">
                    <div class="card-header">
                        header
                    </div>

                    <div class="card-body">
                        <div class="table-responsive text-nowrap mb-3" style="border-bottom: 1px solid #DBDADE;">
                            <table class="table">
                                <thead class="fw-bold">
                                    <tr class="text-danger">
                                        <td>User</td>
                                        <td>Role</td>
                                        <td style="width:10%;">Username</td>
                                        <td style="width:10%;">Region</td>
                                        <td style="width:10%;">Status</td>
                                        <td class="text-center" style="width:10%;">Action</td>
                                    </tr>
                                </thead>
                                <tbody class="table-border-bottom-0">
                                    <tr>
                                        <td>
                                            <div class="d-flex justify-content-start align-items-center user-name">
                                                <div class="avatar-wrapper">
                                                    <div class="avatar me-3">
                                                        <img src="{{ asset('assets/img/avatars/6.png') }}" alt="Avatar"
                                                            class="rounded">
                                                    </div>
                                                </div>

                                                <div class="d-flex flex-column">
                                                    <a href="#" class="text-body text-truncate">
                                                        <span class="fw-medium">Zulfadli Rizal</span>
                                                    </a>
                                                    <small class="text-muted">zulfadlirizal@gmail.com</small>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <span class="text-truncate d-flex align-items-center">
                                                <span
                                                    class="badge badge-center rounded-pill bg-label-success w-px-30 h-px-30 me-2">
                                                    <i class="ti ti-circle-check ti-sm"></i>
                                                </span>
                                                Author
                                            </span>
                                        </td>

                                        <td>zulfame</td>
                                        <td>Pamanukan</td>

                                        <td>
                                            <span class="badge bg-label-success">Active</span>
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

                                    <tr>
                                        <td>
                                            <div class="d-flex justify-content-start align-items-center user-name">
                                                <div class="avatar-wrapper">
                                                    <div class="avatar me-3">
                                                        <img src="{{ asset('assets/img/avatars/6.png') }}" alt="Avatar"
                                                            class="rounded">
                                                    </div>
                                                </div>

                                                <div class="d-flex flex-column">
                                                    <a href="#" class="text-body text-truncate">
                                                        <span class="fw-medium">Mutia Wahida Rahmi</span>
                                                    </a>
                                                    <small class="text-muted">mutiawr27@gmail.com</small>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <span class="text-truncate d-flex align-items-center">
                                                <span
                                                    class="badge badge-center rounded-pill bg-label-secondary w-px-30 h-px-30 me-2">
                                                    <i class="ti ti-device-laptop ti-sm"></i>
                                                </span>
                                                Administrator
                                            </span>
                                        </td>

                                        <td>mutia</td>
                                        <td>Pamanukan</td>

                                        <td>
                                            <span class="badge bg-label-warning">Pendding</span>
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
                                </tbody>
                            </table>
                        </div>

                        <div class="row" style="margin-bottom: -30px;">
                            <div class="col-sm-6 col-md-6 hidden-xs">
                                <div>
                                    <button class="dt-button add-new btn btn-sm btn-primary waves-effect waves-light"
                                        type="button">
                                        Add User
                                    </button>

                                    <button type="button" class="btn btn-sm btn-label-secondary">
                                        Showing 1 to 10 of 50 entries
                                    </button>
                                </div>
                            </div>

                            <div class="col-sm-6 col-md-6">
                                <div>
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination pagination-sm justify-content-end">
                                            <li class="page-item prev">
                                                <a class="page-link waves-effect" href="javascript:void(0);">
                                                    <i class="ti ti-chevrons-left ti-xs"></i>
                                                </a>
                                            </li>
                                            <li class="page-item active">
                                                <a class="page-link waves-effect" href="javascript:void(0);">1</a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link waves-effect" href="javascript:void(0);">2</a>
                                            </li>
                                            <li class="page-item next">
                                                <a class="page-link waves-effect" href="javascript:void(0);">
                                                    <i class="ti ti-chevrons-right ti-xs"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--/ User Table -->

        </div>
    </div>
@endsection
