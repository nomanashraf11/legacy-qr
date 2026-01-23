@extends('admin.layout.master')
@section('title', 'Living Legacy | QR Codes | Linked')
@section('content')
    <div class="content">
        <!-- Start Content-->
        <div class="container-fluid pe-lg-4 ">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title">Linked QrCode Management</h4>
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
                                    <option value="all">All Versions</option>
                                    <option value="christmas">Christmas QR Codes</option>
                                    <option value="full">Memorial QR Codes</option>
                                </select>
                            </div>
                        </div>
                        
                        <table
                            class="basic-datatable w-100 table table-striped dt-responsive nowrap w-100 no-footer dtr-inline"
                            id="links_linked_datatable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>QR_ID</th>
                                    <th>Batch</th>
                                    <th>User</th>
                                    <th>Version Type</th>
                                    <th>Link</th>
                                    <th>Created At</th>
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
        $(function () {
            var table = $('#links_linked_datatable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                asStripeClasses: false,
                ajax: {
                    url: "{{ route('admin.links.linked') }}",
                    data: function (d) {
                        d.version_type = $('#version_type_filter').val();
                        console.log('AJAX Data being sent:', d);
                    }
                },
                columns: [{
                    data: 'id',
                    name: 'id',
                }, {
                    data: 'uuid',
                    name: 'uuid',
                },
                {
                    data: 'batch',
                    name: 'batch',
                },
                {
                    data: 'user',
                    name: 'user',
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
                    data: 'created_at',
                    name: 'created_at',
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: true,
                    searchable: true
                }
                ],
                order: [[6, 'desc']],
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                drawCallback: function () {
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