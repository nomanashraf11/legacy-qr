<div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="replyForm" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="replyModalLabel">Reply</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <label for="">To: </label>
                        <input type="email" class="form-control" name="email" id="email">
                    </div>
                    <div class="row">
                        <label for="">Subject: </label>
                        <input type="text" class="form-control" name="subject" id="subject">
                    </div>
                    <div class="row">
                        <label for="">Name: </label>
                        <input type="text" class="form-control" name="name" id="name">
                    </div>
                    <div class="row">
                        <label for="">Message: </label>
                        <textarea name="message" id="message" cols="30" rows="10" class="form-control">
                        </textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="banUser">Send Reply</button>
                </div>
            </div>
        </form>
    </div>
</div>
