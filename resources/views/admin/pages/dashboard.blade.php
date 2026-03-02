@extends('admin.layout.master')
@section('title', 'Living Legacy | Dashboard')
@section('content')
    <div class="content">
        <div class="container-fluid pe-lg-4">
            {{-- Welcome banner --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 overflow-hidden" style="background: linear-gradient(135deg, #1a365d 0%, #2c5282 100%);">
                        <div class="card-body p-4 p-md-5">
                            <div class="row align-items-center">
                                <div class="col-12 col-md-8">
                                    <h4 class="text-white mb-1 fw-semibold">Welcome back, {{ Auth::user()->name }}!</h4>
                                    <p class="text-white-50 mb-0 mb-md-0">Track your orders and manage your Partner Portal activity.</p>
                                </div>
                                <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
                                    <a href="{{ route('reseller.products') }}" class="btn btn-light px-4 py-2 shadow-sm">
                                        <i class="mdi mdi-cart-outline me-2 fs-5"></i>Browse Products
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stats cards --}}
            <div class="row g-4 mb-4">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100 overflow-hidden">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="flex-grow-1">
                                    <p class="text-muted small text-uppercase fw-medium mb-1">Total Orders</p>
                                    <h2 class="mb-0 fw-bold">{{ $totalOrders }}</h2>
                                    <p class="text-muted small mb-0 mt-1">All time</p>
                                </div>
                                <div class="rounded-3 p-3 flex-shrink-0" style="background: rgba(13, 102, 166, 0.12);">
                                    <i class="mdi mdi-shopping-outline fs-1" style="color: #0d66a6;"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 py-2 px-4">
                            <a href="{{ route('myOrders') }}" class="text-primary small text-decoration-none fw-medium">View orders <i class="mdi mdi-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100 overflow-hidden">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="flex-grow-1">
                                    <p class="text-muted small text-uppercase fw-medium mb-1">Pending Orders</p>
                                    <h2 class="mb-0 fw-bold">{{ $pendingOrders }}</h2>
                                    <p class="text-muted small mb-0 mt-1">In progress</p>
                                </div>
                                <div class="rounded-3 p-3 flex-shrink-0" style="background: rgba(255, 171, 0, 0.12);">
                                    <i class="mdi mdi-package-variant fs-1" style="color: #e67e22;"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 py-2 px-4">
                            <a href="{{ route('myOrders') }}" class="text-primary small text-decoration-none fw-medium">View orders <i class="mdi mdi-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100 overflow-hidden">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="flex-grow-1">
                                    <p class="text-muted small text-uppercase fw-medium mb-1">Outstanding Balance</p>
                                    <h2 class="mb-0 fw-bold">${{ number_format($outstandingBalance, 2) }}</h2>
                                    <p class="text-muted small mb-0 mt-1">{{ $pendingOrders }} invoice(s)</p>
                                </div>
                                <div class="rounded-3 p-3 flex-shrink-0" style="background: rgba(34, 197, 94, 0.12);">
                                    <i class="mdi mdi-currency-usd fs-1" style="color: #16a34a;"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 py-2 px-4">
                            <a href="{{ route('reseller.invoices') }}" class="text-primary small text-decoration-none fw-medium">View invoices <i class="mdi mdi-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100 overflow-hidden">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="flex-grow-1">
                                    <p class="text-muted small text-uppercase fw-medium mb-1">Active Shipments</p>
                                    <h2 class="mb-0 fw-bold">{{ $activeShipments }}</h2>
                                    <p class="text-muted small mb-0 mt-1">In transit</p>
                                </div>
                                <div class="rounded-3 p-3 flex-shrink-0" style="background: rgba(59, 130, 246, 0.12);">
                                    <i class="mdi mdi-truck-delivery-outline fs-1" style="color: #2563eb;"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 py-2 px-4">
                            <a href="{{ route('myOrders') }}" class="text-primary small text-decoration-none fw-medium">Track shipments <i class="mdi mdi-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Orders + Pending Invoices --}}
            <div class="row g-4">
                <div class="col-12 col-xl-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 d-flex align-items-center">
                                <i class="mdi mdi-clipboard-list-outline me-2 text-primary"></i>Recent Orders
                            </h5>
                            <a href="{{ route('myOrders') }}" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body pt-0">
                            @if($recentOrders->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <tbody>
                                            @foreach($recentOrders as $order)
                                                @php
                                                    $statusBadge = match($order->status) {
                                                        0 => ['class' => 'warning', 'label' => 'Pending'],
                                                        1 => ['class' => 'info', 'label' => 'In Progress'],
                                                        default => ['class' => 'success', 'label' => 'Delivered'],
                                                    };
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('orderDetails', $order->uuid) }}" class="text-decoration-none text-body fw-medium">#{{ $order->id }}</a>
                                                        <br><span class="text-muted small">{{ $order->created_at->format('M j, Y') }}</span>
                                                    </td>
                                                    <td class="text-end">
                                                        <span class="badge bg-{{ $statusBadge['class'] }}">{{ $statusBadge['label'] }}</span>
                                                    </td>
                                                    <td class="text-end fw-semibold">${{ number_format($order->amount, 2) }}</td>
                                                    <td class="text-end">
                                                        <a href="{{ route('orderDetails', $order->uuid) }}" class="btn btn-sm btn-outline-primary py-1 px-2">
                                                            <i class="mdi mdi-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="mdi mdi-cart-outline text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2 mb-2">No orders yet</p>
                                    <a href="{{ route('reseller.products') }}" class="btn btn-primary btn-sm">Browse Products</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 d-flex align-items-center">
                                <i class="mdi mdi-receipt-text-outline me-2 text-primary"></i>Pending Invoices
                            </h5>
                            <a href="{{ route('reseller.invoices') }}" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body pt-0">
                            @if($pendingInvoicesCount > 0)
                                <div class="d-flex align-items-center p-3 rounded-3 mb-3" style="background: rgba(255, 171, 0, 0.08);">
                                    <div class="rounded-circle p-2 me-3" style="background: rgba(230, 126, 34, 0.15);">
                                        <i class="mdi mdi-clock-outline fs-4" style="color: #e67e22;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 fw-medium">{{ $pendingInvoicesCount }} pending invoice(s)</p>
                                        <p class="text-muted small mb-0">Awaiting fulfillment</p>
                                    </div>
                                    <a href="{{ route('myOrders') }}" class="btn btn-primary btn-sm">View Orders</a>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div class="rounded-circle d-inline-flex p-3 mb-2" style="background: rgba(34, 197, 94, 0.12);">
                                        <i class="mdi mdi-check-circle-outline text-success" style="font-size: 2.5rem;"></i>
                                    </div>
                                    <p class="text-muted mb-0">No pending invoices</p>
                                    <p class="text-success small mb-0">You're all caught up!</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
