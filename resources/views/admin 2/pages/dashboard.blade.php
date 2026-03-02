@extends('admin.layout.master')
@section('title', 'Living Leagacy | Dashboard')

@section('content')
    <div class="content">
        <div class="container-fluid pe-lg-4">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title">Dashboard</h4>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 col-sm-6">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="d-flex justify-content-between ">
                                    <div class="align-self-center">
                                        <i class="mdi mdi-cart-outline primary fs-1 text-info float-left"></i>
                                    </div>
                                    <div class="text-end">
                                        <h3>{{ $orders->count() }}</h3>
                                        <span>Total Orders</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="d-flex justify-content-between ">
                                    <div class="align-self-center">
                                        <i class="mdi mdi-cart-arrow-down primary fs-1 text-info float-left"></i>
                                    </div>
                                    <div class="text-end">
                                        <h3>{{ $orders->where('status', 0)->count() }}</h3>
                                        <span>Pending Orders</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="d-flex justify-content-between ">
                                    <div class="align-self-center">
                                        <i class="mdi mdi-cart-check primary fs-1 text-info float-left"></i>
                                    </div>
                                    <div class="text-end">
                                        <h3>{{ $orders->where('status', 1)->count() }}</h3>
                                        <span>Completed Orders</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
