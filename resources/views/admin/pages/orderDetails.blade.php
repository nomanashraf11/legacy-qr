@extends('admin.layout.master')
@section('title', 'Living Leagacy | Dashboard')

@section('content')
    <div class="content">
        <div class="container-fluid pe-lg-4">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title">Order Details</h4>
                    </div>
                    <div class="mt-2">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Status:</strong>
                                            @if($order->status == \App\Models\Order::STATUS_PENDING)
                                                <span class="badge bg-warning">Pending</span>
                                            @elseif($order->status == \App\Models\Order::STATUS_IN_PROGRESS)
                                                <span class="badge bg-info">In Progress</span>
                                            @else
                                                <span class="badge bg-success">Delivered</span>
                                            @endif
                                            @role('admin')
                                                @if($order->status != \App\Models\Order::STATUS_DELIVERED)
                                                    <a class="changeTrackingDetails ms-3 pointer" id={{ $order->uuid }} title="Add / Edit Shipping">
                                                        <i class="mdi mdi-pencil-outline fs-5"></i>
                                                    </a>
                                                @endif
                                            @endrole
                                        </p>
                                        <p><strong>Carrier:</strong> {{ $order->shipping_carrier ?? '—' }}</p>
                                        <p><strong>Tracking ID:</strong> {{ $order->tracking_id ?? '—' }}</p>
                                        <p><strong>Quantity:</strong> {{ $order->qr_codes }}</p>
                                        <p><strong>Amount:</strong> ${{ number_format($order->amount, 2) }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        @php
                                            $resel = $order->reSeller;
                                        @endphp
                                        @if($resel && $resel->user)
                                        <p><strong>Name:</strong> {{ $resel->user->name }}</p>
                                        <p><strong>Phone:</strong> {{ $resel->phone ?? '—' }}</p>
                                        <p><strong>Shipping Address:</strong> {{ $resel->shipping_address ?? '—' }}</p>
                                        @endif
                                    </div>
                                </div>
                                @if($order->orderItems && $order->orderItems->isNotEmpty())
                                    <hr>
                                    <h6 class="mb-3">Order Items</h6>
                                    <table class="table table-sm">
                                        <thead><tr><th>Product</th><th>SKU</th><th class="text-end">Qty</th><th class="text-end">Price</th><th class="text-end">Subtotal</th></tr></thead>
                                        <tbody>
                                            @foreach($order->orderItems as $oi)
                                                @php $product = $oi->product; @endphp
                                                <tr>
                                                    <td>{{ $product ? $product->name : '—' }}</td>
                                                    <td>{{ $product ? $product->sku : '—' }}</td>
                                                    <td class="text-end">{{ $oi->quantity }}</td>
                                                    <td class="text-end">${{ number_format($oi->price, 2) }}</td>
                                                    <td class="text-end">${{ number_format($oi->price * $oi->quantity, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                                <div class="mt-4 pt-3 border-top d-flex flex-wrap gap-2">
                                    <a href="{{ route('order.invoice.view', $order->uuid) }}" target="_blank" class="btn btn-outline-primary">
                                        <i class="uil uil-print me-1"></i>View / Print Invoice
                                    </a>
                                    @role('admin')
                                    @if($order->status != \App\Models\Order::STATUS_DELIVERED)
                                        @if(!$order->accepted_at)
                                            <button type="button" class="btn btn-primary acceptOrderButton" id="{{ $order->uuid }}">
                                                <i class="uil uil-check-circle me-1"></i>Accept Order
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-outline-primary changeTrackingDetails" id="{{ $order->uuid }}">
                                            <i class="uil uil-truck me-1"></i>Add Shipping Info
                                        </button>
                                        <button type="button" class="btn btn-success changeStatusButton" id="{{ $order->uuid }}">
                                            <i class="uil uil-check me-1"></i>Mark as Delivered
                                        </button>
                                    @endif
                                    @endrole
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- <div class="col-md-6 ">
                        <div class="card">
                            <div class="card-body">
                                <p>Tracking ID: {{ $order->tracking_id }}</p>
                                <p>Status : {{ $order->tracking_details }}
                                    @role('admin')
                                        <a class="changeTrackingDetails" id={{ $order->uuid }}>edit</a>
                                    @endrole
                                </p>
                                <p>Quantity : {{ $order->qr_codes }}</p>
                                <p>Amount : ${{ number_format($order->amount, 2) }}</p>
                                @php
                                    // dd($order->re_seller_id);
                                    $resel = App\Models\ReSeller::where('id', $order->re_seller_id)->first();
                                @endphp
                                <p>Name : {{ $resel->user->name }}</p>
                                <p>Phone : {{ $resel->phone }}</p>
                                <p>Shipping Address : {{ $resel->shipping_address }}</p>
                            </div>
                        </div>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
    @include('admin.pages.modals.editTrackingDetails')
    @include('admin.pages.modals.delivered')
@endsection
@push('scripts')
    <script src="{{ asset('js/order.js') }}"></script>
@endpush
