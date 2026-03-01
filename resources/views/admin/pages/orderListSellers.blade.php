@extends('admin.layout.master')
@section('title', 'Living Legacy | Orders')

@section('content')
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title">Orders</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <table id="selection-datatable"
                        class="lookBuilderTable table table-striped dt-responsive nowrap w-100 align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Quantity</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                <tr>
                                    <td>{{ $order->reSeller->user->name }}</td>
                                    <td>{{ $order->qr_codes }}</td>
                                    <td>{{ number_format($order->amount, 2) }}</td>
                                    <td>
                                        @if ($order->status == 0)
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif ($order->status == 1)
                                            <span class="badge bg-info">In Progress</span>
                                        @else
                                            <span class="badge bg-success">Delivered</span>
                                        @endif
                                    </td>

                                    <td>
                                        <a href="{{ route('orderDetails', $order->uuid) }}">
                                            <i class="mdi mdi-eye fs-3"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>

@endsection
