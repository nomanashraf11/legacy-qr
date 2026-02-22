@extends('admin.layout.master')
@section('title', 'Living Legacy | Invoices')
@section('content')
    <div class="content">
        <div class="container-fluid pe-lg-4">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title">Invoices</h4>
                        <p class="text-muted mb-0">All your paid orders and invoices</p>
                    </div>
                </div>
            </div>

            <div class="row">
                @forelse($orders as $order)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="card-title mb-1">Invoice #{{ substr($order->uuid, 0, 8) }}</h5>
                                        <p class="text-muted small mb-0">{{ $order->created_at ? $order->created_at->format('M j, Y') : '—' }}</p>
                                    </div>
                                    <span class="badge {{ $order->status == 1 ? 'bg-success' : 'bg-warning' }}">
                                        {{ $order->status == 1 ? 'Delivered' : 'Pending' }}
                                    </span>
                                </div>
                                <p class="mb-2">
                                    @if($order->orderItems && $order->orderItems->isNotEmpty())
                                        {{ $order->orderItems->sum('quantity') }} item(s)
                                    @else
                                        {{ $order->qr_codes }} QR Codes
                                    @endif
                                </p>
                                <p class="fw-bold text-primary mb-3">${{ number_format($order->amount, 2) }}</p>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('orderDetails', $order->uuid) }}" class="btn btn-sm btn-outline-primary">View Order</a>
                                    <a href="{{ route('order.invoice.view', $order->uuid) }}" target="_blank" class="btn btn-sm btn-primary">
                                        <i class="uil uil-print me-1"></i>Print Invoice
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="uil uil-receipt display-4 text-muted mb-3"></i>
                                <h5>No invoices yet</h5>
                                <p class="text-muted">Your paid orders will appear here. Place an order from the Products catalog.</p>
                                <a href="{{ route('reseller.products') }}" class="btn btn-primary">Browse Products</a>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
