@extends('admin.layout.master')
@section('title', 'Living Legacy | Users | Local Users')
@section('content')
    <div class="content">
        <!-- Start Content-->
        <div class="container-fluid pe-lg-4">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title">User Management</h4>
                    </div>
                </div>
            </div>

            <div class="tableContainer">
                <section>
                    <div class="">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <table
                                        class="basic-datatable table table-striped dt-responsive nowrap w-100 no-footer dtr-inline"
                                        id="local_users_table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Role</th>
                                                <th>Email</th>
                                                <th>Status</th>
                                                <th>Created At</th>
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
                </section>
            </div>
        </div>
    </div>
    @include('admin.pages.modals.banUser')
    @include('admin.pages.modals.deleteUser')
@endsection
@push('scripts')
    <script src="{{ asset('js/user.js') }}"></script>
    <script type="text/javascript">
        $(function () {
            $('#local_users_table').DataTable({

                processing: true,
                serverSide: true,
                responsive: true,
                asStripeClasses: false,
                ajax: "{{ route('users.list.local') }}",
                columns: [{
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'role',
                    name: 'role'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'status',
                    name: 'status'
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
                },
                ],
                order: [[4, 'desc']],
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
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
        });
    </script>
@endpush