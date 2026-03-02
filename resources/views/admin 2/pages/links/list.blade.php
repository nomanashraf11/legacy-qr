@extends('admin.layout.master')
@section('title', 'Living Legacy | QR Codes | Available')
@section('content')
    <div class="content">
        <!-- Start Content-->
        <div class="container-fluid pe-lg-4 ">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title">Available QrCodes Management</h4>
                    </div>
                </div>
            </div>

            <section>
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
                        
                        <table class="basic-datatable table table-striped dt-responsive nowrap w-100 no-footer dtr-inline"
                            style="width: 100%" id="links_datatable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>QR_ID</th>
                                    <th>Status</th>
                                    <th>QrCode</th>
                                    <th>Batch</th>
                                    <th>Version Type</th>
                                    <th>Link</th>
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
            var table = $('#links_datatable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                asStripeClasses: false,
                ajax: {
                    url: "{{ route('admin.available.links.list', ['uuid' => $uuid]) }}",
                    data: function (d) {
                        d.version_type = $('#version_type_filter').val();
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    }, {
                        data: 'uuid',
                        name: 'uuid'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    }, {
                        data: 'qr_code',
                        name: 'qr_code',
                    },
                    {
                        data: 'batch',
                        name: 'batch',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'version_type',
                        name: 'version_type',
                    },
                    {
                        data: 'link',
                        name: 'link',
                    },
                    {
                        data: 'action',
                        name: 'action',
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
