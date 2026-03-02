<div class="modal fade" id="trackingModal" tabindex="-1" aria-labelledby="trackingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="changeTrackingForm" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="trackingModalLabel">Change Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body mb-1">
                    <div>
                        <label for="tracking_id">Tracking ID</label>
                        <input type="text" class="form-control" placeholder="Enter order tracking id"
                            name="tracking_id" id="tracking_id">
                    </div>
                    <div class="mt-3">
                        <label for="tracking_details">Status</label>
                        <input type="text" class="form-control" name="tracking_details" id="tracking_details">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="banUser">Save</button>
                </div>
            </div>
        </form>

    </div>
</div>
