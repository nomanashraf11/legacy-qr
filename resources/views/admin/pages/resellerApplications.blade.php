@extends('admin.layout.master')
@section('title', 'Living Legacy | Reseller Applications')
@section('content')
    <div class="content">
        <div class="container-fluid pe-lg-4">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between">
                        <h4 class="page-title mb-0">Reseller Applications</h4>
                        <p class="text-muted mb-0"><strong id="pendingCount">{{ $pendingCount }}</strong> applications pending review</p>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <input type="text" id="searchApplications" class="form-control" placeholder="Search applications...">
                </div>
                <div class="col-md-3">
                    <select id="filterStatus" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>

            <div class="row" id="applicationsList">
                @forelse($applications as $app)
                    @php
                        $location = trim(implode(', ', array_filter([$app->city, $app->state])));
                        $statusClass = match ($app->status) {
                            'approved' => 'success',
                            'rejected' => 'danger',
                            default => 'warning',
                        };
                    @endphp
                    <div class="col-12 col-lg-6 col-xl-4 application-card" data-search="{{ strtolower($app->business_name . ' ' . $app->full_name . ' ' . $app->email) }}" data-status="{{ $app->status }}">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0">{{ $app->business_name ?: '—' }}</h5>
                                    <span class="badge bg-{{ $statusClass }}">{{ ucfirst($app->status) }}</span>
                                </div>
                                <p class="text-muted mb-1">{{ $app->full_name }}</p>
                                <p class="text-muted mb-1 small"><i class="mdi mdi-email-outline me-1"></i>{{ $app->email }}</p>
                                @if($location)
                                    <p class="text-muted mb-2 small"><i class="mdi mdi-map-marker-outline me-1"></i>{{ $location }}</p>
                                @endif
                                <p class="text-muted mb-3 small">{{ $app->created_at ? $app->created_at->format('M j, Y') : '—' }}</p>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary viewApplicationBtn" data-id="{{ $app->id }}">
                                        <i class="mdi mdi-eye me-1"></i>View
                                    </button>
                                    @if($app->status === 'pending')
                                        <button type="button" class="btn btn-sm btn-success approveResellerApplication" data-url="{{ route('admin.reseller.application.approve', $app->id) }}">
                                            <i class="mdi mdi-check-circle me-1"></i>Approve
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger rejectResellerApplication" data-url="{{ route('admin.reseller.application.reject', $app->id) }}">
                                            <i class="mdi mdi-close-circle me-1"></i>Reject
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info">No applications yet.</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewApplicationModal" tabindex="-1" aria-labelledby="viewApplicationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewApplicationModalLabel">Application Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="viewApplicationContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
                    </div>
                </div>
                <div class="modal-footer" id="viewApplicationFooter" style="display: none;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success approveFromModal" style="display: none;">
                        <i class="mdi mdi-check-circle me-1"></i>Approve
                    </button>
                    <button type="button" class="btn btn-danger rejectFromModal" style="display: none;">
                        <i class="mdi mdi-close-circle me-1"></i>Reject
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(function() {
            var detailUrl = "{{ url('reseller-applications') }}";

            function filterApplications() {
                var search = $('#searchApplications').val().toLowerCase();
                var status = $('#filterStatus').val();
                $('.application-card').each(function() {
                    var $card = $(this);
                    var matchesSearch = !search || $card.data('search').indexOf(search) >= 0;
                    var matchesStatus = !status || $card.data('status') === status;
                    $card.toggle(matchesSearch && matchesStatus);
                });
            }

            $('#searchApplications').on('keyup', filterApplications);
            $('#filterStatus').on('change', filterApplications);

            $(document).on("click", ".viewApplicationBtn", function() {
                var id = $(this).data('id');
                var $modal = $('#viewApplicationModal');
                var $content = $('#viewApplicationContent');
                var $footer = $('#viewApplicationFooter');
                $content.html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>');
                $footer.hide();
                $modal.modal('show');

                $.get(detailUrl + '/' + id)
                    .done(function(app) {
                        var location = [app.city, app.state].filter(Boolean).join(', ');
                        var html = '<div class="row">' +
                            '<div class="col-md-6 mb-3"><label class="text-muted small">Business Name</label><p class="mb-0">' + (app.business_name || '—') + '</p></div>' +
                            '<div class="col-md-6 mb-3"><label class="text-muted small">Business Category</label><p class="mb-0">' + (app.business_category || '—') + '</p></div>' +
                            '<div class="col-md-6 mb-3"><label class="text-muted small">Years in Business</label><p class="mb-0">' + (app.years_in_business || '—') + '</p></div>' +
                            '<div class="col-md-6 mb-3"><label class="text-muted small">Website</label><p class="mb-0">' + (app.website ? '<a href="' + app.website + '" target="_blank">' + app.website + '</a>' : '—') + '</p></div>' +
                            '<div class="col-12 mb-3"><label class="text-muted small">Street Address</label><p class="mb-0">' + (app.street_address || '—') + '</p></div>' +
                            '<div class="col-md-4 mb-3"><label class="text-muted small">City</label><p class="mb-0">' + (app.city || '—') + '</p></div>' +
                            '<div class="col-md-4 mb-3"><label class="text-muted small">State</label><p class="mb-0">' + (app.state || '—') + '</p></div>' +
                            '<div class="col-md-4 mb-3"><label class="text-muted small">ZIP</label><p class="mb-0">' + (app.zip_code || '—') + '</p></div>' +
                            '<div class="col-md-6 mb-3"><label class="text-muted small">Business Phone</label><p class="mb-0">' + (app.business_phone || '—') + '</p></div>' +
                            '<div class="col-12 mb-3"><label class="text-muted small">Full Name</label><p class="mb-0">' + (app.full_name || '—') + '</p></div>' +
                            '<div class="col-md-6 mb-3"><label class="text-muted small">Email</label><p class="mb-0"><a href="mailto:' + app.email + '">' + app.email + '</a></p></div>' +
                            '<div class="col-md-6 mb-3"><label class="text-muted small">Phone</label><p class="mb-0">' + (app.phone || '—') + '</p></div>' +
                            '<div class="col-md-6 mb-3"><label class="text-muted small">Estimated Monthly Volume</label><p class="mb-0">' + (app.estimated_monthly_volume || '—') + '</p></div>' +
                            '<div class="col-md-6 mb-3"><label class="text-muted small">How did you hear about us?</label><p class="mb-0">' + (app.hear_about_us || '—') + '</p></div>' +
                            '<div class="col-12 mb-3"><label class="text-muted small">Additional Notes</label><p class="mb-0">' + (app.additional_notes || '—') + '</p></div>' +
                            '<div class="col-12"><label class="text-muted small">Applied</label><p class="mb-0">' + (app.created_at ? new Date(app.created_at).toLocaleString() : '—') + '</p></div>' +
                            '</div>';
                        $content.html(html);

                        if (app.status === 'pending') {
                            $footer.show();
                            $('.approveFromModal, .rejectFromModal').show().off('click').on('click', function() {
                                var isApprove = $(this).hasClass('approveFromModal');
                                var baseUrl = "{{ url('reseller-applications') }}";
                                var url = isApprove ? baseUrl + "/" + id + "/approve" : baseUrl + "/" + id + "/reject";
                                $.post(url, { _token: "{{ csrf_token() }}" })
                                    .done(function(r) {
                                        toastr.success(r.message, "Success");
                                        $modal.modal('hide');
                                        location.reload();
                                    })
                                    .fail(function(xhr) {
                                        toastr.error(xhr.responseJSON?.message || 'Something went wrong', "Error");
                                    });
                            });
                        }
                    })
                    .fail(function() {
                        $content.html('<div class="alert alert-danger">Could not load application details.</div>');
                    });
            });

            $(document).on("click", ".approveResellerApplication", function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                if (!confirm('Approve this application and create reseller account?')) return;
                $.post(url, { _token: "{{ csrf_token() }}" })
                    .done(function(r) {
                        toastr.success(r.message, "Success");
                        location.reload();
                    })
                    .fail(function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Something went wrong', "Error");
                    });
            });

            $(document).on("click", ".rejectResellerApplication", function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                if (!confirm('Reject this application?')) return;
                $.post(url, { _token: "{{ csrf_token() }}" })
                    .done(function(r) {
                        toastr.success(r.message, "Success");
                        location.reload();
                    })
                    .fail(function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Something went wrong', "Error");
                    });
            });
        });
    </script>
@endpush
