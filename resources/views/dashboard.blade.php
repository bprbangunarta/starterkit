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
                                <h5 class="card-title mb-0">Congratulations John! 🎉</h5>
                                <p class="mb-4">Best seller of the month</p>
                                <h4 class="text-primary mb-1">$48.9k</h4>
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

            <div class="card mb-4">
                <div class="table-responsive mb-3">
                    <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer">
                        <div class="card-header py-sm-0">
                            <div class="head-label text-center">
                                <h5 class="card-title mb-0 text-nowrap">Course you are taking</h5>
                            </div>
                            <div id="DataTables_Table_0_filter" class="dataTables_filter"><label><input type="search"
                                        class="form-control" placeholder="Course Name"
                                        aria-controls="DataTables_Table_0"></label></div>
                        </div>
                        <table class="table datatables-academy-course dataTable no-footer dtr-column"
                            id="DataTables_Table_0" aria-describedby="DataTables_Table_0_info">
                            <thead class="border-top">
                                <tr>
                                    <th class="control sorting_disabled dtr-hidden" rowspan="1" colspan="1"
                                        style="width: 47.5156px; display: none;" aria-label=""></th>
                                    <th class="sorting_disabled dt-checkboxes-cell dt-checkboxes-select-all" rowspan="1"
                                        colspan="1" style="width: 47.5156px;" data-col="1" aria-label=""><input
                                            type="checkbox" class="form-check-input"></th>
                                    <th class="sorting sorting_desc" tabindex="0" aria-controls="DataTables_Table_0"
                                        rowspan="1" colspan="1" style="width: 287.062px;" aria-sort="descending"
                                        aria-label="Course Name: activate to sort column ascending">Course Name</th>
                                    <th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1"
                                        colspan="1" style="width: 131.906px;"
                                        aria-label="Time: activate to sort column ascending">Time</th>
                                    <th class="w-25 sorting" tabindex="0" aria-controls="DataTables_Table_0"
                                        rowspan="1" colspan="1" style="width: 297px;"
                                        aria-label="Progress: activate to sort column ascending">Progress</th>
                                    <th class="w-25 sorting" tabindex="0" aria-controls="DataTables_Table_0"
                                        rowspan="1" colspan="1" style="width: 297px;"
                                        aria-label="Status: activate to sort column ascending">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="odd">
                                    <td valign="top" colspan="5" class="dataTables_empty">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="row mx-4">
                            <div class="col-sm-6 col-12 text-center text-xl-start pb-2 pb-xl-0 px-0">
                                <div class="dataTables_info" id="DataTables_Table_0_info" role="status"
                                    aria-live="polite">Showing 0 to 0 of 0 entries</div>
                            </div>
                            <div class="col-sm-6 col-12 d-flex justify-content-center justify-content-xl-end px-0">
                                <div class="dataTables_paginate paging_simple_numbers" id="DataTables_Table_0_paginate">
                                    <ul class="pagination">
                                        <li class="paginate_button page-item previous disabled"
                                            id="DataTables_Table_0_previous"><a aria-controls="DataTables_Table_0"
                                                aria-disabled="true" role="link" data-dt-idx="previous"
                                                tabindex="0" class="page-link">Previous</a></li>
                                        <li class="paginate_button page-item next disabled" id="DataTables_Table_0_next">
                                            <a aria-controls="DataTables_Table_0" aria-disabled="true" role="link"
                                                data-dt-idx="next" tabindex="0" class="page-link">Next</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
