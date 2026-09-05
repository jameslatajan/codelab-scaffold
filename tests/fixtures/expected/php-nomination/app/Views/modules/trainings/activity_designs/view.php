<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 text-uppercase"><i class="<?php echo $current_module['icon'] ?>"></i> <?php echo $current_module['sub_level1'] ?> </h5>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="me-1">
                                <a href="<?php echo $controller_page ?>" class="btn btn-secondary btn-rounded waves-effect btn-sm"><i class="fas fa-arrow-left me-1"></i> Back to List</a>
                            </li>
                            <li class="">
                                <a href="<?php echo $controller_page . '/create' ?>" type="submit" class="btn btn-primary btn-rounded btn-sm waves-effect waves-light btn-sm" id="filter"><i class="fa fa-plus"></i> Add <?php echo $current_module['sub_level1'] ?> </a>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <form id="frmEntry">
                    <input type="hidden" name="<?php echo $pfield ?>" value="<?php echo $encrypter->encode($rec->{$pfield}) ?>">
                </form>
                <!-- Main Document Table -->
                <div class="card mb-2">
                    <div class="card-header d-flex align-items-center justify-content-between p-1" id="tooltip-container">
                        <small class="mb-0 fw-bold text-uppercase">View
                            <span class="text-white text-uppercase rounded-pill bg-<?php echo $status_map[$rec->status]['color'] ?>"><?php echo $status_map[$rec->status]['text'] ?>
                        </small>
                        <div class="buttons">
                            <?php if ((int) $rec->status === 1 && !empty($roles['review'])) { ?>
                                <button type="button" id="review" class="btn btn-info btn-rounded btn-sm">
                                    <i class="fas fa-check"></i> Review
                                </button>
                            <?php } ?>
                            <?php if ((int) $rec->status === 2 && !empty($roles['approve'])) { ?>
                                <button type="button" id="approve" class="btn btn-info btn-rounded btn-sm">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                            <?php } ?>
                            <?php if (in_array((int) $rec->status, [1, 2], true) && !empty($roles['decline'])) { ?>
                                <button type="button" id="decline" class="btn btn-danger btn-rounded btn-sm">
                                    <i class="fas fa-times"></i> Decline
                                </button>
                            <?php } ?>
                            <?php if ((int) $rec->status === 1 && !empty($roles['edit'])) { ?>
                                <a href="<?php echo $controller_page . '/edit/' . $encrypter->encode($rec->{$pfield}) ?>" type="submit" class="btn btn-light btn-rounded btn-sm" id="filter"><i class="fa fa-edit"></i></a>
                            <?php } ?>
                            <!-- <?php if (in_array((int) $rec->status, [1, 2, 3], true) && !empty($roles['confirm'])) { ?>
                                <button type="button" id="cancel" class="btn btn-light btn-rounded btn-sm">
                                    <i class="fa fa-ban"></i>
                                </button>
                            <?php } ?> -->
                            <button type="button" id="recordlog" class="btn btn-light text-primary btn-rounded waves-effect btn-sm" data-bs-container="#tooltip-container" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Logs" data-bs-original-title="Logs" onclick="popUp('<?php echo $logUrl; ?>', 1000, 1000)"><i class="fas fa-server"></i></button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0 align-middle">
                            <tr>
                                <td class="text-end bg-light wx-100">Activity: </td>
                                <td class="wx-400">
                                    <?php echo $rec->activity  ?>
                                </td>
                                <td class="text-end bg-light wx-100">Category: </td>
                                <td>
                                    <?php echo $nomination_type_map[$rec->nominationType]['text'] ?? '-' ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-end bg-light">Activity Date</td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($rec->date))  ?>
                                </td>
                                <td class="text-end bg-light">Venue: </td>
                                <td>
                                    <?php echo $rec->venue  ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-end bg-light">Budget: </td>
                                <td>
                                    <?php echo number_format($rec->budget, 2)  ?>
                                </td>
                                <td class="text-end bg-light">Participants: </td>
                                <td>
                                    <?php echo (int) $rec->participantCount ?>
                                </td>
                            </tr>
                        </table>
                        <!-- Attachments -->
                        <?php if ($rec->files) { ?>
                            <?php $files = array_values(array_filter(array_map('trim', explode(',', $rec->files ?? '')))); ?>
                            <div class="attachments-dropzone mb-3 px-3 py-2 mx-2" style="border:2px dashed #cbd5e1; border-radius:.5rem">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="mb-0 fw-semibold">Attachments (<?php echo count($files); ?>)</h5>
                                    <?php if (empty($files)) { ?><span class="text-muted small">No attachments</span><?php } ?>
                                </div>
                                <div class="dz-previews d-flex flex-wrap gap-3">
                                    <?php
                                    if (!empty($files)) {
                                        foreach ($files as $idx => $fname) {
                                            $url     = base_url($dir . md5($rec->actID) . '/' . $fname);
                                            $ext     = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
                                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
                                            $isPdf   = ($ext === 'pdf');
                                            $icon    = 'bi-file-earmark';


                                            if ($isImage) $icon                                  = 'bi-image';
                                            else if ($isPdf) $icon                                 = 'bi-file-earmark-pdf';
                                            else if (in_array($ext, ['doc', 'docx'])) $icon        = 'bi-file-earmark-word';
                                            else if (in_array($ext, ['xls', 'xlsx', 'csv'])) $icon = 'bi-file-earmark-excel';
                                            else if (in_array($ext, ['ppt', 'pptx'])) $icon        = 'bi-file-earmark-ppt';
                                            else if (in_array($ext, ['zip', 'rar', '7z'])) $icon   = 'bi-file-earmark-zip';

                                            $iconSvg = null;
                                            if ($isPdf) $iconSvg = base_url('assets/images/icons/pdf.svg');
                                            elseif (in_array($ext, ['doc', 'docx'])) $iconSvg = base_url('assets/images/icons/word.svg');
                                            elseif (in_array($ext, ['xls', 'xlsx', 'csv'])) $iconSvg = base_url('assets/images/icons/excel.svg');
                                    ?>
                                            <div class="dz-preview dz-file-preview card shadow-sm attachment-card" data-url="<?php echo htmlspecialchars($url, ENT_QUOTES) ?>" data-name="<?php echo htmlspecialchars($fname, ENT_QUOTES) ?>" style="width: 200px; cursor: pointer;" onclick="previewAttachment(this.dataset.url, this.dataset.name)">
                                                <div class="dz-image rounded-top <?php echo $isImage ? '' : 'bg-light d-flex align-items-center justify-content-center'; ?>" style="height: 120px; <?php echo $isImage ? 'background:url(' . htmlspecialchars($url) . ') center/cover no-repeat;' : '' ?>">
                                                    <?php if (!$isImage) { ?>
                                                        <?php if ($iconSvg) { ?>
                                                            <img src="<?php echo $iconSvg ?>" alt="<?php echo $ext ?>" style="width: 64px; height: 64px;">
                                                        <?php } else { ?>
                                                            <i class="bi <?php echo $icon ?> fs-1 text-primary"></i>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </div>
                                                <?php if ($isImage) { ?>
                                                    <div class="dz-details p-2" onclick="event.stopPropagation(); previewAttachment('<?php echo htmlspecialchars($url, ENT_QUOTES) ?>', '<?php echo htmlspecialchars($fname, ENT_QUOTES) ?>')">
                                                        <div class="dz-filename text-truncate" title="<?php echo htmlspecialchars($fname) ?>"><?php echo htmlspecialchars($fname) ?></div>
                                                        <div class="dz-size text-muted small text-uppercase"><?php echo htmlspecialchars($ext) ?></div>
                                                    </div>
                                                <?php } elseif ($isPdf) { ?>
                                                    <div class="dz-details p-2" style="cursor:pointer" onclick="event.stopPropagation(); previewAttachment('<?php echo htmlspecialchars($url, ENT_QUOTES) ?>', '<?php echo htmlspecialchars($fname, ENT_QUOTES) ?>')">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div class="overflow-hidden" style="max-width: 85%;">
                                                                <div class="dz-filename text-truncate" title="<?php echo htmlspecialchars($fname) ?>"><?php echo htmlspecialchars($fname) ?></div>
                                                                <div class="dz-size text-muted small text-uppercase"><?php echo htmlspecialchars($ext) ?></div>
                                                            </div>
                                                            <i class="bi bi-eye fs-4 text-secondary" title="View"></i>
                                                        </div>
                                                    </div>
                                                <?php } else { ?>
                                                    <div class="dz-details p-2">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div class="overflow-hidden" style="max-width: 85%;">
                                                                <div class="dz-filename text-truncate" title="<?php echo htmlspecialchars($fname) ?>"><?php echo htmlspecialchars($fname) ?></div>
                                                                <div class="dz-size text-muted small text-uppercase"><?php echo htmlspecialchars($ext) ?></div>
                                                            </div>
                                                            <i class="bi bi-eye fs-4 text-secondary" title="View"></i>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                    <?php
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <!-- Attachment Preview Modal -->
                    <div class="modal fade" id="attachmentPreviewModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header p-1">
                                    <h5 class="modal-title" id="attachmentPreviewTitle">Attachment Preview</h5>
                                    <button type="button" class="btn-close p-3" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-0" id="attachmentPreviewBody">
                                    <div class="text-center text-muted">Loading preview...</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Participants List -->
                    <div class="card-body border-top p-1">
                        <h6 class="font-size-14 mb-3"><i class="fas fa-list"></i> Participants</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-striped table-hover mb-0">
                                <thead class="header-bg">
                                    <tr>
                                        <th class="text-center wx-50">#</th>
                                        <th class="wx-100">ID No.</th>
                                        <th class="wx-300">Name</th>
                                        <th class="wx-300">Division/Section/Unit</th>
                                        <th class="wx-100">Employee Type</th>
                                        <th>Job Title</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($participants)) { ?>
                                        <?php foreach ($participants as $i => $p) { ?>
                                            <tr>
                                                <td class="text-center"><?php echo $i + 1; ?></td>
                                                <td><?php echo $p['idNo']; ?></td>
                                                <td><?php echo $p['name']; ?></td>
                                                <td><?php echo $p['divisionName'] ?: ($p['sectionName'] ?: $p['unitName']); ?></td>
                                                <td><?php echo $p['employeeType']; ?></td>
                                                <td><?php echo $p['jobTitle']; ?></td>
                                            </tr>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No participants found</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-body border-top">
                        <h6 class="font-size-14"><i class="fa fa-users"></i> Users</h6>
                        <?php echo $this->include('components/transaction_status'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function popUp(url, w, h) {
        var width = w || 1000;
        var height = h || 600;
        var dualLeft = (window.screenLeft !== undefined) ? window.screenLeft : screen.left;
        var dualTop = (window.screenTop !== undefined) ? window.screenTop : screen.top;
        var winWidth = window.innerWidth || document.documentElement.clientWidth || screen.width;
        var winHeight = window.innerHeight || document.documentElement.clientHeight || screen.height;
        var left = ((winWidth - width) / 2) + dualLeft;
        var top = ((winHeight - height) / 2) + dualTop;
        var features = 'width=' + width + ',height=' + height + ',left=' + left + ',top=' + top + ',resizable=yes,scrollbars=yes';
        var popup = window.open(url, '_blank', features);
        if (popup && popup.focus) popup.focus();
    }

    $('#cancel').on('click', function() {
        let btn = $(this);
        let btnText = $(this).html();
        let data = $('#frmEntry').serialize();

        Swal.fire({
            title: 'Are you sure?',
            html: 'You are going to <strong>Cancel</strong> this data. Do you wish to proceed?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: "#4D62C5",
            cancelButtonColor: "#636678",
            confirmButtonText: "Yes"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: "<?php echo $controller_page . '/cancel' ?>",
                    data: data,
                    dataType: "json",
                    beforeSend: function() {
                        let html = `
                        <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...
                        </div>`;
                        $(btn).html(html);
                    },
                    success: function(response) {
                        Swal.fire({
                            title: response.title,
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: "Ok"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.replace(response.data.url)
                            }
                        })
                    },
                    error: function(xhr) {
                        if (xhr.responseJSON) {
                            Swal.fire(xhr.responseJSON.title, xhr.responseJSON.message, "error");
                        } else {
                            Swal.fire(xhr.status, xhr.status, "error");
                        }

                        $(btn).html(btnText);
                    }
                });
            }
        });
    });

    $('#review').on('click', function() {
        let btn = $(this);
        let btnText = $(this).html();
        let data = $('#frmEntry').serialize();

        Swal.fire({
            title: 'Are you sure?',
            html: 'You are going to <strong>Review</strong> this data. Do you wish to proceed?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: "#4D62C5",
            cancelButtonColor: "#636678",
            confirmButtonText: "Yes"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: "<?php echo $controller_page . '/review' ?>",
                    data: data,
                    dataType: "json",
                    beforeSend: function() {
                        let html = `
                        <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...
                        </div>`;
                        $(btn).html(html);
                    },
                    success: function(response) {
                        Swal.fire({
                            title: response.title,
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: "Ok"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.replace(response.data.url)
                            }
                        })
                    },
                    error: function(xhr) {
                        if (xhr.responseJSON) {
                            Swal.fire(xhr.responseJSON.title, xhr.responseJSON.message, "error");
                        } else {
                            Swal.fire(xhr.status, xhr.status, "error");
                        }

                        $(btn).html(btnText);
                    }
                });
            }
        });

    });

    $('#approve').on('click', function() {
        let btn = $(this);
        let btnText = $(this).html();
        let data = $('#frmEntry').serialize();

        Swal.fire({
            title: 'Are you sure?',
            html: 'You are going to <strong>Approve</strong> this data. Do you wish to proceed?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: "#4D62C5",
            cancelButtonColor: "#636678",
            confirmButtonText: "Yes"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: "<?php echo $controller_page . '/approve' ?>",
                    data: data,
                    dataType: "json",
                    beforeSend: function() {
                        let html = `
                        <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...
                        </div>`;
                        $(btn).html(html);
                    },
                    success: function(response) {
                        Swal.fire({
                            title: response.title,
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: "Ok"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.replace(response.data.url)
                            }
                        })
                    },
                    error: function(xhr) {
                        if (xhr.responseJSON) {
                            Swal.fire(xhr.responseJSON.title, xhr.responseJSON.message, "error");
                        } else {
                            Swal.fire(xhr.status, xhr.status, "error");
                        }

                        $(btn).html(btnText);
                    }
                });
            }
        });
    });

    $('#decline').on('click', function() {
        let btn = $(this);
        let btnText = $(this).html();
        let data = $('#frmEntry').serialize();

        Swal.fire({
            title: 'Are you sure?',
            html: 'You are going to <strong>Decline</strong> this data. Do you wish to proceed?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: "#4D62C5",
            cancelButtonColor: "#636678",
            confirmButtonText: "Yes"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: "<?php echo $controller_page . '/decline' ?>",
                    data: data,
                    dataType: "json",
                    beforeSend: function() {
                        let html = `
                        <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...
                        </div>`;
                        $(btn).html(html);
                    },
                    success: function(response) {
                        Swal.fire({
                            title: response.title,
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: "Ok"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.replace(response.data.url)
                            }
                        })
                    },
                    error: function(xhr) {
                        if (xhr.responseJSON) {
                            Swal.fire(xhr.responseJSON.title, xhr.responseJSON.message, "error");
                        } else {
                            Swal.fire(xhr.status, xhr.status, "error");
                        }

                        $(btn).html(btnText);
                    }
                });
            }
        });
    });

    /**
     * Preview an attachment in a Bootstrap modal. Supports images and PDFs.
     * @param {string} url - The absolute URL to the attachment resource.
     * @param {string} filename - The attachment file name, used for title and type detection.
     */
    function previewAttachment(url, filename) {
        $('#attachmentPreviewTitle').text(filename || 'Attachment Preview');
        const parts = (filename || '').split('.');
        const ext = parts.length ? parts.pop().toLowerCase() : '';
        let html = '';
        if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext)) {
            // change modal to big
            $('.modal-dialog').removeClass('modal-sm');
            $('.modal-dialog').addClass('modal-xl');
            html = '<img src="' + url + '" alt="' + (filename || '') + '" class="img-fluid rounded border w-100"/>';
        } else if (ext === 'pdf') {
            // change modal to big
            $('.modal-dialog').removeClass('modal-sm');
            $('.modal-dialog').addClass('modal-xl');
            html = '<iframe src="' + url + '" class="w-100" style="height:80vh;border:1px solid #dee2e6;border-radius:.25rem;"></iframe>';
        } else {
            // change modal to small
            $('.modal-dialog').removeClass('modal-xl');
            $('.modal-dialog').addClass('modal-sm');
            html = '<div class="text-center w-100"><a href="' + url + '" target="_blank" class="btn btn-primary my-5"><i class="fa fa-download"></i> Download</a></div>';
        }
        $('#attachmentPreviewBody').html(html);
        const modalEl = document.getElementById('attachmentPreviewModal');
        const isShown = modalEl.classList.contains('show');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        if (!isShown) modal.show();
    }

    /**
     * Copy an attachment link to the clipboard using Clipboard API with a fallback.
     * @param {string} url - The absolute URL to copy.
     */
    function copyAttachmentLink(url) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(function() {
                Swal.fire('Copied!', 'Attachment link copied to clipboard.', 'success');
            }).catch(function() {
                fallbackCopy(url);
            });
        } else {
            fallbackCopy(url);
        }
    }

    /**
     * Fallback copy function using a temporary input element.
     * @param {string} text - The text to copy to clipboard.
     */
    function fallbackCopy(text) {
        const temp = document.createElement('input');
        temp.value = text;
        document.body.appendChild(temp);
        temp.select();
        try {
            document.execCommand('copy');
            Swal.fire('Copied!', 'Attachment link copied to clipboard.', 'success');
        } catch (e) {
            Swal.fire('Error', 'Unable to copy link. Please copy manually.', 'error');
        }
        document.body.removeChild(temp);
    }
</script>