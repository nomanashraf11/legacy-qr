<div class="modal fade" id="addSellerModal" tabindex="-1" aria-labelledby="addSellerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="addSellerForm" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSellerModalLabel">Add Seller</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-1">
                        <label for="name" class="col-sm-3 col-form-label">Name:</label>
                        <input type="text" placeholder="Enter name" class="form-control" name="name"
                            id="name">
                    </div>
                    <div class="mb-3">
                        <label for="name" class="col-sm-3 col-form-label">Email:</label>
                        <input type="email" readonly placeholder='name@company.com' class="form-control"
                            name="email" id="email">
                    </div>
                    <div class="mb-3">
                        <label for="name" class="col-sm-3 col-form-label">Website:</label>
                        <input type="text" class="form-control" name="website" id="website">
                    </div>
                    <div class="mb-3">
                        <label for="name" class="col-sm-3 col-form-label">Phone:</label>
                        <input type="text" class="form-control" name="phone" id="phone">
                    </div>
                    <div class="mb-3">
                        <label for="name" class="col-sm-3 col-form-label">Address:</label>
                        <input type="text" class="form-control" name="address" id="address">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create Seller Account</button>
                </div>
            </div>
        </form>
    </div>
</div>
