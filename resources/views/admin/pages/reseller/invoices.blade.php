@extends('admin.layout.master')
@section('title', 'Living Legacy | Invoices')
@section('content')
    <div class="content">
        <div class="container-fluid pe-lg-4">
            <div class="row mb-4">
                <div class="col-12">
                    <h4 class="page-title mb-1">Invoices</h4>
                    <p class="text-muted mb-0">All your paid orders and invoices</p>
                </div>
            </div>

            <div class="row g-4">
                @forelse($orders as $order)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="card-title mb-1">Invoice #{{ substr($order->uuid, 0, 8) }}</h5>
                                        <p class="text-muted small mb-0">{{ $order->created_at ? $order->created_at->format('M j, Y') : '—' }}</p>
                                    </div>
                                    <span class="badge {{ $order->status == 2 ? 'bg-success' : ($order->status == 1 ? 'bg-info' : 'bg-warning text-dark') }}">
                                        {{ $order->status == 2 ? 'Delivered' : ($order->status == 1 ? 'In Progress' : 'Pending') }}
                                    </span>
                                </div>
                                <p class="text-muted small mb-2">
                                    @if($order->orderItems && $order->orderItems->isNotEmpty())
                                        {{ $order->orderItems->sum('quantity') }} item(s)
                                    @else
                                        {{ $order->qr_codes }} QR Codes
                                    @endif
                                </p>
                                @if($order->tracking_id)
                                <p class="small mb-0 text-primary">
                                    <i class="uil uil-truck me-1"></i>{{ $order->shipping_carrier ?? 'Tracking' }}: {{ $order->tracking_id }}
                                </p>
                                @endif
                                <p class="fw-bold fs-5 text-primary mb-4">${{ number_format($order->amount, 2) }}</p>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('orderDetails', $order->uuid) }}" class="btn btn-sm btn-outline-primary flex-grow-1">View Order</a>
                                    <a href="{{ route('order.invoice.view', $order->uuid) }}" target="_blank" class="btn btn-sm btn-primary">
                                        <i class="uil uil-print me-1"></i>Print
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5 px-4">
                                <div class="rounded-circle bg-light d-inline-flex p-4 mb-3">
                                    <i class="uil uil-receipt display-4 text-muted"></i>
                                </div>
                                <h5 class="mb-2">No invoices yet</h5>
                                <p class="text-muted mb-4">Your paid orders will appear here. Place an order from the Products catalog.</p>
                                <a href="{{ route('reseller.products') }}" class="btn btn-primary px-4">Browse Products</a>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
