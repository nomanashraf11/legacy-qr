@extends('admin.layout.master')
@section('title', 'Living Legacy')
@section('meta')
    <meta name="description" content="Your meta description goes here">
@endsection
@section('content')
    <div class="content">
        <!-- Start Content-->
        <div class="container-fluid pe-lg-4 ">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title">Availeble QR Codes Batch</h4>
                    </div>
                </div>
            </div>

            <section>
                <div class="pt-4">
                    <div class="card">
                        <div class="card-body">
                            <!-- Filter Section -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="version_type_filter" class="form-label">Filter by Version Type:</label>
                                    <select id="version_type_filter" class="form-select">
                                        <option value="full" selected>Memorial QR Codes</option>
                                        <option value="christmas">Christmas QR Codes</option>
                                    </select>
                                </div>
                            </div>
                            
                            <table
                                class="basic-datatable table table-striped dt-responsive nowrap w-100 no-footer dtr-inline"
                                style="width: 100%" id="batch_datatable_available">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Version Type</th>
                                        <th>Available Qr Codes</th>
                                        <th>zip</th>
                                        <th>xlsx</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
@push('scripts')
    <script type="text/javascript">
        $(function() {
            var table = $('#batch_datatable_available').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                asStripeClasses: false,
                ajax: {
                    url: "{{ route('admin.links') }}",
                    data: function (d) {
                        d.version_type = $('#version_type_filter').val();
                    }
                },
                columns: [{
                        data: 'number',
                        name: 'number',
                        orderable: true, // Set to true to enable sorting
                    }, {
                        data: 'name',
                        name: 'name',

                    },
                    {
                        data: 'version_type',
                        name: 'version_type',
                    },
                    {
                        data: 'qr_codes',
                        name: 'qr_codes',
                    },
                    {
                        data: 'zip',
                        name: 'zip',
                    },
                    {
                        data: 'xlsx',
                        name: 'xlsx',
                    },
                    {
                        data: 'action',
                        name: 'action',
                    },
                ],
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                drawCallback: function() {
                    $(" .pagination").addClass("pagination-rounded")
                }
            });
            
            // Handle filter change
            $('#version_type_filter').on('change', function() {
                table.ajax.reload();
            });
        });
    </script>
@endpush
@push('scripts')
    <script src="{{ asset('js/link.js') }}"></script>
@endpush
