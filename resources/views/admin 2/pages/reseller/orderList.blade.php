@extends('admin.layout.master')
@section('content')
    <div class="content">
        <!-- Start Content-->
        <div class="container-fluid pe-lg-4">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title">My Orders</h4>
                    </div>
                </div>
            </div>
            <section>
                <div class="card">
                    <div class="card-body">
                        <table class="basic-datatable table table-striped dt-responsive nowrap w-100 no-footer dtr-inline"
                            style="width: 100%" id="my_orders_datatable">
                            <thead>
                                <tr>
                                    <th>Qr-Codes</th>
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
                columns: [{
                        data: 'qr_codes',
                        name: 'qr_codes'
                    }, {
                        data: 'amount',
                        name: 'amount',
                    },
                    {
                        data: 'status',
                        name: 'status',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: true,
                        searchable: true
                    }
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
                columnDefs: [{
                    targets: 1, // Index of the 'amount' column
                    type: 'num-fmt', // Set column type to numeric format
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return parseFloat(data).toFixed(2);
                        }
                        return data;
                    }
                }]
            });
        });
    </script>
@endpush
@push('scripts')
    <script src="{{ asset('js/link.js') }}"></script>
@endpush
