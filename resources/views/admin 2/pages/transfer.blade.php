@extends('admin.layout.master')
@section('content')
    <div class="content">
        <div class="container-fluid pe-lg-4">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title">Transfer Page</h4>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <form id="transferDataForm" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <label for="old">Old QR Code</label>
                                <input type="text" class="form-control" name="old">
                            </div>
                            <div class="col-md-6">
                                <label for="new">New QR Code</label>
                                <input type="text" class="form-control" name="new">
                            </div>
                        </div>
                        <div class="text-center">
                            <button class="mt-3 btn btn-primary" type="submit">Transfer Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/link.js') }}"></script>
@endpush
