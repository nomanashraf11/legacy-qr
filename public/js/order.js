var orderId = 0;
$(function () {
    $(document).on("click", ".acceptOrderButton", function () {
        var id = $(this).attr("id");
        $.ajax({
            url: "/accept_order/" + id,
            type: "POST",
            data: {
                _token:
                    $('meta[name="csrf-token"]').attr("content") ||
                    document.querySelector('input[name="_token"]')?.value,
            },
            success: function (response) {
                toastr.options = {
                    progressBar: true,
                    closeButton: true,
                    timeOut: 2000,
                };
                if (response.status === true) {
                    toastr.success(response.message, "Success");
                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                } else {
                    toastr.error(response.message, "Error");
                }
            },
            error: function (xhr) {
                toastr.error(
                    xhr.responseJSON?.message || "Something went wrong",
                    "Error"
                );
            },
        });
    });
    $(document).on("click", ".changeStatusButton", function () {
        orderId = $(this).attr("id");
        $("#deliverModal").modal("show");
    });
    $("#changeStatusForm").on("submit", function (event) {
        event.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: "/mark_as_delivered/" + orderId,
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
                    $("#changeStatusForm")[0].reset();
                    $("#deliverModal").modal("hide");

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

    $(document).on("click", ".changeTrackingDetails", function () {
        orderId = $(this).attr("id");
        $.ajax({
            url: "/get_tracking_details/" + orderId,
            type: "GET",
            success: function (response) {
                $("#trackingModal #tracking_id").val(response.tracking_id);
                $("#trackingModal #tracking_details").val(
                    response.tracking_details
                );
                $("#trackingModal #shipping_carrier").val(
                    response.shipping_carrier || ""
                );
                $("#trackingModal").modal("show");
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
    $("#changeTrackingForm").on("submit", function (event) {
        event.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: "/update_tracking_details/" + orderId,
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
                    $("#changeTrackingForm")[0].reset();
                    $("#trackingModal").modal("hide");

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
});
