@extends('admin.layout.master')
@section('title', 'Living Legacy | Mails')
@section('content')
    <div class="content">
        <!-- Start Content-->
        <div class="container-fluid pe-lg-4">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <div class="d-flex align-items-center justify-content-between ">
                            <h4 class="page-title">Reseller Account Creation</h4>
                            {{-- <a class="btn btn-primary addSeller">Create Seller Account</a> --}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="tableContainer">
                <section>
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <table
                                    class="basic-datatable table table-striped dt-responsive nowrap w-100 no-footer dtr-inline"
                                    id="mails_table">
                                    <thead>
                                        <tr>
                                            <th>First Name</th>
                                            <th>Last Name</th>
                                            <th>Email</th>
                                            <th>Website</th>
                                            <th>Action</th>
                                            <th>Phone</th>
                                            <th>Message</th>
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
    </div>
    @include('admin.pages.modals.addSeller')
@endsection
@push('scripts')
    <script src="{{ asset('js/review.js') }}"></script>
    <script type="text/javascript">
        $(function() {
            $('#mails_table').DataTable({

                processing: true,
                serverSide: true,
                responsive: true,
                asStripeClasses: false,
                ajax: "{{ route('admin.contact.mail') }}",
                columns: [{
                        data: 'first_name',
                        name: 'first_name'
                    },
                    {
                        data: 'last_name',
                        name: 'last_name'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'website',
                        name: 'website'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'phone',
                        name: 'phone'
                    },
                    {
                        data: 'message',
                        name: 'message',
                    },

                ],
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
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
        });
    </script>
@endpush
