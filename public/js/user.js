var userId = 0;
$(function () {
    $(document).on("click", ".blockUserButton", function () {
        userId = $(this).attr("id");
        $("#banUserModal").modal("show");
    });
    $("#banUserForm").on("submit", function (event) {
        event.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: "/ban_user/" + userId,
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
                    $("#banUserForm")[0].reset();
                    $("#banUserModal").modal("hide");

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

    $(document).on("click", ".deleteUserButton", function () {
        userId = $(this).attr("id");
        $("#deleteUserModal").modal("show");
    });
    $("#deleteUserForm").on("submit", function (event) {
        event.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: "/delete-user/" + userId,
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
                    // $("#deleteUserModal")[0].reset();
                    $("#deleteUserModal").modal("hide");

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

    // $(document).on("click", ".addSeller", function () {
    //     $("#addSellerModal").modal("show");
    // });
    // $("#addSellerForm").on("submit", function (event) {
    //     event.preventDefault();
    //     $("#addSellerModal :input[type='submit']").prop("disabled", true);

    //     var formData = new FormData(this);
    //     $.ajax({
    //         url: "/create_re_sellars",
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
    //                 $("#addSellerModal :input[type='submit']").prop("disabled", true);

    //                 $("#addSellerForm")[0].reset();
    //                 $("#addSellerModal").modal("hide");

    //                 toastr.success(response.message, "Success");
    //                 setTimeout(function () {
    //                     location.reload();
    //                 }, 2000);
    //             } else {
    //                 $("#addSellerModal :input[type='submit']").prop("disabled", true);

    //                 toastr.error(response.message, "Error");
    //             }
    //         },
    //         error: function (errors) {
    //             $("#addSellerModal :input[type='submit']").prop("disabled", true);

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


});
