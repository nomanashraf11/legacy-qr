@extends('admin.layout.master')
@section('title', 'Living Legacy | Inquries')
@section('content')
    <div class="content">
        <!-- Start Content-->
        <div class="container-fluid pe-lg-4">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <div class="d-flex align-items-center justify-content-between ">
                            <h4 class="page-title">Inquries Mails</h4>
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
                                    id="inquries_table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Subject</th>
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
@endsection
@push('scripts')
    <script src="{{ asset('js/review.js') }}"></script>
    <script type="text/javascript">
        $(function() {
            $('#inquries_table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                asStripeClasses: false,
                ajax: "{{ route('admin.inquries.mail') }}",
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'subject', name: 'subject' },
                    { data: 'message', name: 'message' },
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
