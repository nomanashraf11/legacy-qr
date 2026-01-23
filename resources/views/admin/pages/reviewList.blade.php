@extends('admin.layout.master')
@section('title', 'Living Legacy | Reviews')
@section('content')
    <div class="content">
        <!-- Start Content-->
        <div class="container-fluid pe-lg-4">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <div class="d-flex align-items-center justify-content-between">
                            <h4 class="page-title">Reviews</h4>
                            <button class="btn btn-primary addReview">Add Review</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tableContainer">
                <section class="mt-1">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <table
                                    class="basic-datatable table table-striped dt-responsive nowrap w-100 no-footer dtr-inline"
                                    id="reviews_table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Title</th>
                                            <th>Description</th>
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
    </div>
    @include('admin.pages.modals.addReview')
    @include('admin.pages.modals.deleteReview')
@endsection
@push('scripts')
    <script src="{{ asset('js/review.js') }}"></script>
    <script type="text/javascript">
        $(function() {
            $('#reviews_table').DataTable({

                processing: true,
                serverSide: true,
                responsive: true,
                asStripeClasses: false,
                ajax: "{{ route('admin.reviews') }}",
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },

                    {
                        data: 'action',
                        name: 'action',
                        orderable: true,
                        searchable: true
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
