<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 text-uppercase"><i class="<?php echo $current_module['icon'] ?>"></i> <?php echo $current_module['sub_level1'] ?> </h5>
                    <div class="page-title-right"></div>
                </div>
            </div>
        </div>

        <form id="frmEntry">
            <input type="hidden" name="<?php echo $pfield ?>" value="<?php echo encrypter_encrypt($rec->{$pfield}) ?>">
            <div class="card mb-2">
                <div class="card-header d-flex py-2 px-1 justify-content-between" id="tooltip-container">
                    <h5 class="mb-0 text-uppercase">Edit</h5>
                    <small class="mb-0"><span class="text-danger">*</span> Indicates Required</small>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0 align-middle">
                        <tr>
                            <td class="text-end bg-light wx-100">Cloudattachments <small class="text-danger">*</small></td>
                            <td>
                                <textarea name="cloud attachments" id="" class="form-control form-control-sm wx-400" title="Cloudattachments" required><?php echo $rec->cloud attachments ?></textarea>
                            </td>
                        </tr>

                        <tr>
                            <td class="text-end bg-light wx-100">Status</td>
                            <td>
                                <select name="status" id="" class="form-select form-select-sm select2 wx-120" title="Status">
                                    <?php foreach ($status_map as $key => $value) { ?>
                                        <option value="<?php echo $key ?>" <?php echo $rec->status == $key ? 'selected' : '' ?>><?php echo $value['text'] ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-rounded btn-sm" id="cmdUpdate">
                        <i class="fa fa-save"></i> Update
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded btn-sm" id="cmdCancel">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- JS Functions -->
<script>
    // Initialize date picker
    flatpickr('.flatpickr-basic', {
        dateFormat: 'm/d/Y'
    });

    // ==========================
    // Save Button Handler
    // ==========================
    $('#cmdUpdate').on('click', function() {
        if (!check_fields('#frmEntry')) return;

        const btn = $(this);
        const btnText = btn.html();
        const formData = new FormData($('#frmEntry')[0]);

        Swal.fire({
            title: 'Are you sure?',
            text: 'You are going to save this data. Do you wish to proceed?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: "#4D62C5",
            cancelButtonColor: "#636678",
            processData: false,
            contentType: false,
            confirmButtonText: "Yes"
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: "<?php echo $controller_page . '/update' ?>",
                    data: formData,
                    dataType: "json",
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        btn.html(`<div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                      </div>`);
                    },
                    success: function(response) {
                        Swal.fire({
                            title: response.title,
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: "Ok"
                        }).then(() => {
                            window.location.replace(response.data.url);
                        });
                    },
                    error: function(xhr) {
                        if (xhr.responseJSON) {
                            Swal.fire(xhr.responseJSON.title, xhr.responseJSON.message, "error");
                        } else {
                            Swal.fire("Error", xhr.status + ": " + xhr.statusText, "error");
                        }
                    },
                    complete: function() {
                        btn.html(btnText);
                    }
                });
            }
        });
    });

    // ==========================
    // Cancel Button
    // ==========================
    $('#cmdCancel').on('click', function() {
        cancel_confirmation('<?php echo $controller_page . '/view/' . encrypter_encrypt($rec->{$pfield}) ?>');
    });
</script>
