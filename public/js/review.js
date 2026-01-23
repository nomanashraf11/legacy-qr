var reviewID = 0;
var email = null;
var nameE = null;
var subject = null;
var data = null;
$(function () {
    $(document).on("click", ".deleteReviewButton", function () {
        reviewID = $(this).attr("id");
        $("#deleteReviewModal").modal("show");
    });
    $("#deleteReviewForm").on("submit", function (event) {
        event.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: "/review/delete/" + reviewID,
            data: formData,
            type: "POST",
            processData: false, // Important: Don't process the data
            contentType: false,
            success: function (response) {
                toastr.options = {
                    progressBar: true,
                    closeButton: true,
                    timeOut: 2000,
                };
                if (response.status === true) {
                    $("#deleteReviewForm")[0].reset();
                    $("#deleteReviewModal").modal("hide");

                    toastr.success(response.message, "Success");
                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                } else {
                    toastr.error(response.message, "Error");
                }
            },
            error: function (errors) {
                const errorMessages = Object.values(
                    errors?.responseJSON?.errors
                ).flat();
                toastr.options = {
                    progressBar: true,
                    closeButton: true,
                };
                for (let i = 0; i < errorMessages.length; i++) {
                    toastr.error(errorMessages[i], "Error");
                }
            },
        });
    });

    $(document).on("click", ".addReview", function () {
        $("#addReviewModal").modal("show");
    });
    $("#addReviewForm").on("submit", function (event) {
        event.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: "/store_review",
            data: formData,
            type: "POST",
            processData: false, // Important: Don't process the data
            contentType: false,
            success: function (response) {
                toastr.options = {
                    progressBar: true,
                    closeButton: true,
                    timeOut: 2000,
                };
                if (response.status === true) {
                    $("#addReviewForm")[0].reset();
                    $("#addReviewModal").modal("hide");

                    toastr.success(response.message, "Success");
                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                } else {
                    toastr.error(response.message, "Error");
                }
            },
            error: function (errors) {
                const errorMessages = Object.values(
                    errors?.responseJSON?.errors
                ).flat();
                toastr.options = {
                    progressBar: true,
                    closeButton: true,
                };
                for (let i = 0; i < errorMessages.length; i++) {
                    toastr.error(errorMessages[i], "Error");
                }
            },
        });
    });



    // $(document).on("click", ".reply", function () {
    //     email = $(this).attr("email");
    //     nameE = $(this).attr("name");
    //     subject = $(this).attr("subject");

    //     $('#replyModal #email').val(email);
    //     $('#replyModal #name').val(nameE);
    //     $('#replyModal #subject').val(subject);
    //     $("#replyModal").modal("show");
    // });
    // $("#replyForm").on("submit", function (event) {
    //     event.preventDefault();
    //     var formData = new FormData(this);
    //     $("#replyModal :input[type='submit']").prop("disabled", true);

    //     $.ajax({
    //         url: "/reply_mail",
    //         data: formData,
    //         type: "POST",
    //         processData: false, // Important: Don't process the data
    //         contentType: false,
    //         success: function (response) {
    //             toastr.options = {
    //                 progressBar: true,
    //                 closeButton: true,
    //                 timeOut: 2000,
    //             };
    //             if (response.status === true) {
    //                 $("#replyForm")[0].reset();
    //                 $("#replyModal").modal("hide");
    //                 $("#replyModal :input[type='submit']").prop("disabled", false);


    //                 toastr.success(response.message, "Success");
    //                 setTimeout(function () {
    //                     location.reload();
    //                 }, 2000);
    //             } else {
    //                 toastr.error(response.message, "Error");
    //             }
    //         },
    //         error: function (errors) {
    //             const errorMessages = Object.values(
    //                 errors?.responseJSON?.errors
    //             ).flat();
    //             toastr.options = {
    //                 progressBar: true,
    //                 closeButton: true,
    //             };
    //             for (let i = 0; i < errorMessages.length; i++) {
    //                 toastr.error(errorMessages[i], "Error");
    //             }
    //         },
    //     });
    // });

    $(document).on("click", ".registerReseller", function () {
        nameE = $(this).attr("name");
        email = $(this).attr("email");
        website = $(this).attr("website");
        phone = $(this).attr("phone");
        address = $(this).attr("address");

        $("#addSellerModal #name").val(nameE);
        $("#addSellerModal #email").val(email);
        $("#addSellerModal #website").val(website);
        $("#addSellerModal #address").val(address);
        $("#addSellerModal #phone").val(phone);

        $("#addSellerModal").modal("show");
    });



    $("#addSellerForm").on("submit", function (event) {
        event.preventDefault();
        $("#addSellerModal :input[type='submit']").prop("disabled", true);

        var formData = new FormData(this);
        $.ajax({
            url: "/create_re_sellars",
            data: formData,
            type: "POST",
            processData: false, // Important: Don't process the data
            contentType: false,
            success: function (response) {
                toastr.options = {
                    progressBar: true,
                    closeButton: true,
                    timeOut: 2000,
                };
                if (response.status === true) {
                    $("#addSellerModal :input[type='submit']").prop("disabled", true);

                    $("#addSellerForm")[0].reset();
                    $("#addSellerModal").modal("hide");

                    toastr.success(response.message, "Success");
                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                } else {
                    $("#addSellerModal :input[type='submit']").prop("disabled", false);

                    toastr.error(response.message, "Error");
                }
            },
            error: function (errors) {
                $("#addSellerModal :input[type='submit']").prop("disabled", false);

                const errorMessages = Object.values(
                    errors?.responseJSON?.errors
                ).flat();
                toastr.options = {
                    progressBar: true,
                    closeButton: true,
                };
                for (let i = 0; i < errorMessages.length; i++) {
                    toastr.error(errorMessages[i], "Error");
                }
            },
        });
    });
});
