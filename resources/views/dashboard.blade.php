@extends('layouts.app')
@section('title', 'Dashboard')

@if (Auth::user()->roles->pluck('name')[0] ?? '')
    @section('content')
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">

                <!-- View sales -->
                <div class="col-xl-4 mb-4 col-lg-5 col-12">
                    <div class="card">
                        <div class="d-flex align-items-end row">
                            <div class="col-7">
                                <div class="card-body text-nowrap">
                                    <h5 class="card-title mb-0 text-capitalize">Selamat {{ Auth::user()->username }}!
                                        🎉
                                    </h5>
                                    <p class="mb-4">Realisasi kredit hari ini</p>
                                    <h4 class="text-primary mb-1">Rp. 1.086.200.000</h4>
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
                                            <i class="ti ti-users ti-sm"></i>
                                        </div>
                                        <div class="card-info">
                                            <h5 class="mb-0">1772</h5>
                                            <small>Pengajuan</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="d-flex align-items-center">
                                        <div class="badge rounded-pill bg-label-success me-3 p-2">
                                            <i class="ti ti-discount-check-filled ti-sm"></i>
                                        </div>
                                        <div class="card-info">
                                            <h5 class="mb-0">1034</h5>
                                            <small>Realisasi</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="d-flex align-items-center">
                                        <div class="badge rounded-pill bg-label-warning me-3 p-2">
                                            <i class="ti ti-bell-cancel ti-sm"></i>
                                        </div>
                                        <div class="card-info">
                                            <h5 class="mb-0">148</h5>
                                            <small>Pembatalan</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3 col-6">
                                    <div class="d-flex align-items-center">
                                        <div class="badge rounded-pill bg-label-danger me-3 p-2">
                                            <i class="ti ti-ban ti-sm"></i>
                                        </div>
                                        <div class="card-info">
                                            <h5 class="mb-0">239</h5>
                                            <small>Penolakan</small>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <!--/ Statistics -->

            </div>

            <div class="row">
                <div class="col">
                    <div class="card mb-3">
                        <div class="card-header pt-2">
                            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#berkas-kurang"
                                        role="tab" aria-selected="true">
                                        Berkas Kurang
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#realisasi" role="tab"
                                        aria-selected="false" tabindex="-1">Realisasi Hari Ini</button>
                                </li>
                            </ul>
                        </div>

                        <div class="tab-content">
                            <div class="tab-pane fade active show" id="berkas-kurang" role="tabpanel">

                                <div class="dataTables_wrapper dt-bootstrap5 no-footer border-bottom"
                                    style="margin-top:-20px;">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-3">
                                            <div class="dataTables_length">
                                                <button type="button" class="btn btn-primary waves-effect waves-light"
                                                    data-bs-toggle="modal" data-bs-target="#addUser"> Export </button>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-9">
                                            <div
                                                class="dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-md-end justify-content-center flex-wrap">
                                                <div>
                                                    <form action="https://www.sipebri.test/admin/user" method="GET">
                                                        <div class="dataTables_filter">
                                                            <label>Search
                                                                <input type="search" class="form-control" name="keyword"
                                                                    value="" placeholder="Search..">
                                                            </label>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive text-nowrap">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th class="fw-bold">Realisasi</th>
                                                <th class="fw-bold">Nama Debitur</th>
                                                <th class="fw-bold">Surveyor</th>
                                                <th class="fw-bold">Kantor</th>
                                                <th class="fw-bold">Kekurangan</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                            <tr class="table-danger">
                                                <td>25 JANUARI 2024</td>
                                                <td>ZULFADLI RIZAL</td>
                                                <td>JAELANI</td>
                                                <td>PAMANUKAN</td>
                                                <td>KK Asli, Ijazah SMA</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                            </div>

                            <div class="tab-pane fade" id="realisasi" role="tabpanel">

                                <div class="dataTables_wrapper dt-bootstrap5 no-footer border-bottom"
                                    style="margin-top:-20px;">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-3">
                                            <div class="dataTables_length">
                                                <button type="button" class="btn btn-primary waves-effect waves-light"
                                                    data-bs-toggle="modal" data-bs-target="#addUser"> Export </button>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-9">
                                            <div
                                                class="dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-md-end justify-content-center flex-wrap">
                                                <div>
                                                    <form action="#" method="GET">
                                                        <div class="dataTables_filter">
                                                            <label>Search
                                                                <input type="search" class="form-control" name="keyword"
                                                                    value="" placeholder="Search..">
                                                            </label>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive text-nowrap">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th class="fw-bold">Kode</th>
                                                <th class="fw-bold">No. SPK</th>
                                                <th class="fw-bold">Nama Debitur</th>
                                                <th class="fw-bold">Kantor</th>
                                                <th class="fw-bold">Plafon</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                            <tr class="table-success">
                                                <td>00341306</td>
                                                <td>0112/KPS/I/2024</td>
                                                <td>DHIAH RATNA SARASWATI</td>
                                                <td>PAMANUKAN</td>
                                                <td>500.000.000</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    @endsection
@endif
