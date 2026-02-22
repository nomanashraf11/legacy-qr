@extends('admin.layout.master')
@section('title', 'Living Legacy | Orders')
@section('content')
    <div class="content">
        <!-- Start Content-->
        <div class="container-fluid pe-lg-4">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title">Orders</h4>
                        <p class="text-muted mb-0">View and dispatch reseller orders</p>
                    </div>
                </div>
            </div>
            <section>
                <div class="card">
                    <div class="card-body">
                        <table class="basic-datatable table table-striped dt-responsive nowrap w-100 no-footer dtr-inline"
                            style="width: 100%" id="orders_datatable">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Reseller</th>
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
            </section>
        </div>
    </div>
    @include('admin.pages.modals.delivered')
@endsection
@push('scripts')
    <script type="text/javascript">
        $(function() {
            var table = $('#orders_datatable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                asStripeClasses: false,
                ajax: "{{ route('admin.orders') }}",
                columns: [
                    { data: 'order_number', name: 'order_number' },
                    { data: 'order_date', name: 'created_at' },
                    { data: 'name', name: 'name' },
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
    <script src="{{ asset('js/order.js') }}"></script>
@endpush
