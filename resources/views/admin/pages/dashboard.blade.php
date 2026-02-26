@extends('admin.layout.master')
@section('title', 'Living Legacy | Dashboard')
@section('content')
    <div class="content">
        <div class="container-fluid pe-lg-4">
            <div class="row mb-4">
                <div class="col-12">
                    <h4 class="page-title mb-1">Dashboard</h4>
                    <p class="text-muted mb-0">Overview of your reseller activity</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted small text-uppercase mb-1">Total Orders</p>
                                    <h3 class="mb-0">{{ $orders->count() }}</h3>
                                </div>
                                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                    <i class="uil uil-shopping-bag fs-2 text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted small text-uppercase mb-1">Pending</p>
                                    <h3 class="mb-0">{{ $orders->where('status', 0)->count() }}</h3>
                                </div>
                                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                                    <i class="uil uil-clock fs-2 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted small text-uppercase mb-1">Delivered</p>
                                    <h3 class="mb-0">{{ $orders->where('status', 1)->count() }}</h3>
                                </div>
                                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                    <i class="uil uil-check-circle fs-2 text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
