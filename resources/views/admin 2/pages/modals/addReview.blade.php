<div class="modal fade" id="addReviewModal" tabindex="-1" aria-labelledby="addReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="addReviewForm" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addReviewModalLabel">Add Review</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="">Name: </label>
                        <input type="text" class="form-control w-100" name="name" id="name">
                    </div>
                    <div class="mb-3">
                        <label for="">Title: </label>
                        <input type="text" class="form-control w-100" name="title" id="title">
                    </div>
                    <div class="mb-1">
                        <label for="">Description: </label>
                        <input type="text" class="form-control w-100" name="description" id="description">
                    </div>
                    <div class="mb-1">
                        <label for="">Image: </label>
                        <input type="file" class="form-control w-100" name="image" id="image">
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
