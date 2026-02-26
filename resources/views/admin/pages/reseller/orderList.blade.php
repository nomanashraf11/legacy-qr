@extends('admin.layout.master')
@section('title', 'Living Legacy | My Orders')
@section('content')
    <div class="content">
        <div class="container-fluid pe-lg-4">
            <div class="row mb-4">
                <div class="col-12">
                    <h4 class="page-title mb-1">My Orders</h4>
                    <p class="text-muted mb-0">View and track your orders</p>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <table class="basic-datatable table table-hover dt-responsive nowrap w-100 no-footer dtr-inline" style="width: 100%" id="my_orders_datatable">
                        <thead class="table-light">
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script type="text/javascript">
        $(function() {
            var table = $('#my_orders_datatable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                asStripeClasses: false,
                ajax: "{{ route('myOrders') }}",
                columns: [
                    { data: 'order_number', name: 'order_number' },
                    { data: 'order_date', name: 'created_at' },
                    { data: 'items', name: 'items' },
                    { data: 'amount_fmt', name: 'amount' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                drawCallback: function() {
                    $(" .pagination").addClass("pagination-rounded")
                },
                order: [[1, 'desc']]
            });
        });
    </script>
@endpush
@push('scripts')
    <script src="{{ asset('js/link.js') }}"></script>
@endpush
