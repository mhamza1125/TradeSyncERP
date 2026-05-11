{{-- Dashboard Page - Extends the master layout --}}
@extends('index')

@section('title', 'Dashboard - TradeSyncERP')

@section('content')
    <div class="nxl-content">
        <!-- [ page-header ] start -->
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Dashboard</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">Dashboard</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <div class="page-header-right-items">
                    <div class="d-flex d-md-none">
                        <a href="javascript:void(0)" class="pin-content" data-bs-toggle="dropdown">
                            <i class="feather-more-vertical"></i>
                        </a>
                    </div>
                    <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                        <div class="dropdown">
                            <a class="btn btn-icon btn-light-brand" data-bs-toggle="dropdown" data-bs-offset="0, 10" data-bs-auto-close="outside">
                                <i class="feather-paperclip"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="javascript:void(0);" class="dropdown-item">
                                    <i class="feather-copy me-3"></i>
                                    <span>Copy Link</span>
                                </a>
                                <a href="javascript:void(0);" class="dropdown-item">
                                    <i class="feather-file me-3"></i>
                                    <span>Export as CSV</span>
                                </a>
                                <a href="javascript:void(0);" class="dropdown-item">
                                    <i class="feather-file-text me-3"></i>
                                    <span>Export as PDF</span>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="javascript:void(0);" class="dropdown-item">
                                    <i class="feather-printer me-3"></i>
                                    <span>Print</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- [ page-header ] end -->

        <!-- [ Main Content ] start -->
        <div class="main-content">
            <div class="row">
                <!-- [ Statistics Cards ] start -->
                <div class="col-xxl-3 col-md-6">
                    <div class="card stretch stretch-full">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-text avatar-lg bg-soft-primary text-primary border-soft-primary rounded">
                                        <i class="feather-users"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">Total Customers</div>
                                        <div class="fs-12 text-muted">Active accounts</div>
                                    </div>
                                </div>
                                <div class="badge bg-soft-primary text-primary">
                                    <i class="feather-trending-up fs-11 me-1"></i>
                                    <span>12.5%</span>
                                </div>
                            </div>
                            <div class="pt-4">
                                <h2 class="fs-4 fw-bold text-dark mb-1">2,456</h2>
                                <div class="fs-12 text-muted">
                                    <span class="text-success fw-semibold">+124</span> new this month
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="card stretch stretch-full">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-text avatar-lg bg-soft-success text-success border-soft-success rounded">
                                        <i class="feather-dollar-sign"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">Total Revenue</div>
                                        <div class="fs-12 text-muted">Monthly income</div>
                                    </div>
                                </div>
                                <div class="badge bg-soft-success text-success">
                                    <i class="feather-trending-up fs-11 me-1"></i>
                                    <span>8.3%</span>
                                </div>
                            </div>
                            <div class="pt-4">
                                <h2 class="fs-4 fw-bold text-dark mb-1">$48,250</h2>
                                <div class="fs-12 text-muted">
                                    <span class="text-success fw-semibold">+$5,200</span> from last month
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="card stretch stretch-full">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-text avatar-lg bg-soft-warning text-warning border-soft-warning rounded">
                                        <i class="feather-shopping-cart"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">Total Orders</div>
                                        <div class="fs-12 text-muted">Completed orders</div>
                                    </div>
                                </div>
                                <div class="badge bg-soft-warning text-warning">
                                    <i class="feather-trending-down fs-11 me-1"></i>
                                    <span>3.2%</span>
                                </div>
                            </div>
                            <div class="pt-4">
                                <h2 class="fs-4 fw-bold text-dark mb-1">1,890</h2>
                                <div class="fs-12 text-muted">
                                    <span class="text-danger fw-semibold">-32</span> from last month
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="card stretch stretch-full">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-text avatar-lg bg-soft-danger text-danger border-soft-danger rounded">
                                        <i class="feather-briefcase"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">Active Projects</div>
                                        <div class="fs-12 text-muted">In progress</div>
                                    </div>
                                </div>
                                <div class="badge bg-soft-danger text-danger">
                                    <i class="feather-trending-up fs-11 me-1"></i>
                                    <span>5.7%</span>
                                </div>
                            </div>
                            <div class="pt-4">
                                <h2 class="fs-4 fw-bold text-dark mb-1">64</h2>
                                <div class="fs-12 text-muted">
                                    <span class="text-success fw-semibold">+8</span> new this month
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ Statistics Cards ] end -->

                <!-- [ Recent Activity ] start -->
                <div class="col-xxl-8">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title">Recent Activity</h5>
                            <div class="card-header-action">
                                <div class="dropdown">
                                    <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" data-bs-toggle="dropdown" data-bs-offset="0, 10">
                                        <i class="feather-more-vertical"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a href="javascript:void(0);" class="dropdown-item"><i class="feather-eye me-3"></i><span>View Details</span></a>
                                        <a href="javascript:void(0);" class="dropdown-item"><i class="feather-download me-3"></i><span>Download Report</span></a>
                                        <div class="dropdown-divider"></div>
                                        <a href="javascript:void(0);" class="dropdown-item"><i class="feather-refresh-cw me-3"></i><span>Refresh</span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body custom-card-action">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="dashboard-recent-activity">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>Order ID</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="avatar-image avatar-md">
                                                        <img src="{{ asset('assets/images/avatar/1.png') }}" alt="" class="img-fluid">
                                                    </div>
                                                    <a href="javascript:void(0);">
                                                        <span class="d-block">Alexandra Della</span>
                                                        <span class="fs-12 d-block fw-normal text-muted">alex@example.com</span>
                                                    </a>
                                                </div>
                                            </td>
                                            <td>#ORD-1234</td>
                                            <td>May 10, 2026</td>
                                            <td class="fw-semibold text-dark">$1,250.00</td>
                                            <td><span class="badge bg-soft-success text-success">Completed</span></td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="avatar-image avatar-md">
                                                        <img src="{{ asset('assets/images/avatar/2.png') }}" alt="" class="img-fluid">
                                                    </div>
                                                    <a href="javascript:void(0);">
                                                        <span class="d-block">Green Cute</span>
                                                        <span class="fs-12 d-block fw-normal text-muted">green@example.com</span>
                                                    </a>
                                                </div>
                                            </td>
                                            <td>#ORD-1235</td>
                                            <td>May 09, 2026</td>
                                            <td class="fw-semibold text-dark">$890.00</td>
                                            <td><span class="badge bg-soft-warning text-warning">Pending</span></td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="avatar-image avatar-md">
                                                        <img src="{{ asset('assets/images/avatar/3.png') }}" alt="" class="img-fluid">
                                                    </div>
                                                    <a href="javascript:void(0);">
                                                        <span class="d-block">Malanie Hanvey</span>
                                                        <span class="fs-12 d-block fw-normal text-muted">malanie@example.com</span>
                                                    </a>
                                                </div>
                                            </td>
                                            <td>#ORD-1236</td>
                                            <td>May 08, 2026</td>
                                            <td class="fw-semibold text-dark">$2,340.00</td>
                                            <td><span class="badge bg-soft-success text-success">Completed</span></td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="avatar-image avatar-md">
                                                        <img src="{{ asset('assets/images/avatar/4.png') }}" alt="" class="img-fluid">
                                                    </div>
                                                    <a href="javascript:void(0);">
                                                        <span class="d-block">Kenneth Hune</span>
                                                        <span class="fs-12 d-block fw-normal text-muted">kenneth@example.com</span>
                                                    </a>
                                                </div>
                                            </td>
                                            <td>#ORD-1237</td>
                                            <td>May 07, 2026</td>
                                            <td class="fw-semibold text-dark">$560.00</td>
                                            <td><span class="badge bg-soft-danger text-danger">Cancelled</span></td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="avatar-image avatar-md">
                                                        <img src="{{ asset('assets/images/avatar/5.png') }}" alt="" class="img-fluid">
                                                    </div>
                                                    <a href="javascript:void(0);">
                                                        <span class="d-block">Archie Cantones</span>
                                                        <span class="fs-12 d-block fw-normal text-muted">archie@example.com</span>
                                                    </a>
                                                </div>
                                            </td>
                                            <td>#ORD-1238</td>
                                            <td>May 06, 2026</td>
                                            <td class="fw-semibold text-dark">$3,100.00</td>
                                            <td><span class="badge bg-soft-primary text-primary">Processing</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ Recent Activity ] end -->

                <!-- [ Quick Stats Sidebar ] start -->
                <div class="col-xxl-4">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title">Quick Overview</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-text rounded bg-soft-primary text-primary">
                                        <i class="feather-check-circle"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">Completed Tasks</div>
                                        <div class="fs-12 text-muted">Last 30 days</div>
                                    </div>
                                </div>
                                <div class="fs-4 fw-bold text-dark">245</div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-text rounded bg-soft-warning text-warning">
                                        <i class="feather-clock"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">Pending Tasks</div>
                                        <div class="fs-12 text-muted">Needs attention</div>
                                    </div>
                                </div>
                                <div class="fs-4 fw-bold text-dark">32</div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-text rounded bg-soft-success text-success">
                                        <i class="feather-file-text"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">Invoices Sent</div>
                                        <div class="fs-12 text-muted">This month</div>
                                    </div>
                                </div>
                                <div class="fs-4 fw-bold text-dark">89</div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-text rounded bg-soft-danger text-danger">
                                        <i class="feather-alert-triangle"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">Overdue Items</div>
                                        <div class="fs-12 text-muted">Requires action</div>
                                    </div>
                                </div>
                                <div class="fs-4 fw-bold text-dark">12</div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-text rounded bg-soft-teal text-teal">
                                        <i class="feather-message-square"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">Support Tickets</div>
                                        <div class="fs-12 text-muted">Open tickets</div>
                                    </div>
                                </div>
                                <div class="fs-4 fw-bold text-dark">18</div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ Quick Stats Sidebar ] end -->
            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/dashboard-init.min.js') }}"></script>
@endpush
