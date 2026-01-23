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
                        <h4 class="page-title">Batch Management</h4>
                    </div>
                </div>
            </div>
            <form method="post" id="generateCode">
                @csrf
                <div class="row">
                    <div class="col">
                        <input type="text" required name="name" id="name" class="form-control"
                            placeholder="Enter Name of batch">
                    </div>
                    <div class="col">
                        <input type="number" required name="number" id="number" class="form-control"
                            placeholder="Enter number of QrCodes to be generated">
                    </div>
                    <div class="col">
                        <select name="version_type" id="version_type" class="form-select" required>
                            <option value="full">Full Version</option>
                            <option value="christmas">Christmas Version</option>
                        </select>
                    </div>
                    <div class="col">
                        <button type="submit" class="btn btn-primary">Create QrCodes</button>
                    </div>
                </div>
            </form>
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
                                style="width: 100%" id="batch_datatable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Version Type</th>
                                        <th>Qr Codes</th>
                                        <th>zip</th>
                                        <th>xlsx</th>
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
            var table = $('#batch_datatable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                asStripeClasses: false,
                ajax: {
                    url: "{{ route('admin.batches') }}",
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
