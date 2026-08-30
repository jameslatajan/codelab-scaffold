<?php
$sizeToBytes = static function (string $value): int {
    $value = trim($value);
    if ($value === '') {
        return 0;
    }

    $unit = strtolower(substr($value, -1));
    $bytes = (int) $value;

    switch ($unit) {
        case 'g':
            $bytes *= 1024;
        case 'm':
            $bytes *= 1024;
        case 'k':
            $bytes *= 1024;
    }

    return $bytes;
};

$uploadMaxSize = (string) ini_get('upload_max_filesize');
$postMaxSize = (string) ini_get('post_max_size');
$uploadMaxBytes = $sizeToBytes($uploadMaxSize);
$postMaxBytes = $sizeToBytes($postMaxSize);
?>

<style>
    #dropzone {
        border: 2px dashed #adb5bd;
        border-radius: 1rem;
        background-color: #f8f9fa;
        text-align: center;
        transition: all 0.25s ease;
        cursor: pointer;
        position: relative;
        min-height: 160px;
    }

    #dropzone.dragover {
        border-color: #0d6efd;
        background-color: #eaf2ff;
        transform: scale(1.01);
    }

    #fileList {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 1rem;
        max-height: 240px;
        overflow-y: auto;
        padding: 0.5rem;
        margin-top: 1rem;
        height: 150px;
    }

    .file-preview {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        padding: 0.5rem;
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .file-preview:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .file-preview img {
        max-width: 100%;
        height: 70px;
        object-fit: cover;
        border-radius: 0.5rem;
        background-color: #f8f9fa;
    }

    .file-name {
        font-size: 0.8rem;
        color: #495057;
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
        margin-top: 0.25rem;
        padding: 0 0.25rem;
        height: 20px;
    }

    .remove-file {
        position: absolute;
        top: 6px;
        right: 6px;
        border: none;
        background: rgba(255, 255, 255, 0.9);
        color: #dc3545;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .remove-file:hover {
        background: #dc3545;
        color: #fff;
    }
</style>

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 text-uppercase"><i class="<?php echo $current_module['icon'] ?>"></i> <?php echo $current_module['sub_level1'] ?></h5>
                    <div class="page-title-right"></div>
                </div>
            </div>
        </div>
        <form id="frmEntry" enctype="multipart/form-data">
            <input type="hidden" name="<?php echo $pfield ?>" value="<?php echo $encrypter->encode($rec->{$pfield}) ?>">
            <div class="col-12">
                <div class="card mb-2">
                    <div class="card-header d-flex py-2 px-1 justify-content-between" id="tooltip-container">
                        <h5 class="mb-0 text-uppercase">Replace Attachments</h5>
                        <small class="mb-0"><span class="text-danger">*</span> Indicates Required</small>
                    </div>

                    <div class="card-body p-1">
                        <table class="table table-sm mb-0 align-middle">
                            <tr>
                                <td class="text-end bg-light wx-100">Activity No.</td>
                                <td><?php echo $rec->activityNo ?></td>
                            </tr>
                            <tr>
                                <td class="text-end bg-light wx-100">Activity</td>
                                <td><?php echo $rec->activity ?></td>
                            </tr>
                            <tr>
                                <td class="text-end bg-light">Category</td>
                                <td><?php echo $nomination_type_map[$rec->nominationType]['text'] ?? '-' ?></td>
                            </tr>
                            <tr>
                                <td class="text-end bg-light">Date</td>
                                <td><?php echo !empty($rec->date) ? date('M d, Y', strtotime($rec->date)) : '-' ?></td>
                            </tr>
                            <tr>
                                <td class="text-end bg-light">Venue</td>
                                <td><?php echo $rec->venue ?></td>
                            </tr>
                            <tr>
                                <td class="text-end bg-light">Budget</td>
                                <td><?php echo number_format((float) $rec->budget, 2) ?></td>
                            </tr>
                        </table>
                        <h5 class="text-center mb-3 fw-semibold">Upload Files</h5>
                        <small class="text-center text-secondary">
                            <span class="text-danger">*</span> Maximum file size: <?php echo esc($uploadMaxSize) ?> per file. Total request limit: <?php echo esc($postMaxSize) ?>.
                        </small>
                        <div id="uploadForm" class="m-2">
                            <div id="dropzone" class="text-muted p-2">
                                <i class="bi bi-cloud-arrow-up display-5 d-block mb-2 text-primary"></i>
                                <p class="mb-1 fw-medium">Drag & drop your files here</p>
                                <p class="small text-secondary mb-0">
                                    or <span class="text-primary fw-semibold">browse</span> from your device
                                </p>
                                <input id="fileInput" type="file" multiple hidden>
                                <div id="fileList" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex gap-2">
                        <button type="button" class="btn btn-primary btn-rounded btn-sm" id="cmdSave">
                            <i class="fa fa-save"></i> Save
                        </button>
                        <button type="button" class="btn btn-secondary btn-rounded btn-sm" id="cmdCancel">
                            <i class="fa fa-times"></i> Cancel
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const uploadMaxBytes = <?php echo (int) $uploadMaxBytes; ?>;
    const postMaxBytes = <?php echo (int) $postMaxBytes; ?>;
    const uploadMaxLabel = <?php echo json_encode($uploadMaxSize); ?>;
    const postMaxLabel = <?php echo json_encode($postMaxSize); ?>;

    let files = [];
    const dropzone = $('#dropzone');
    const fileInput = $('#fileInput');
    const fileList = $('#fileList');

    $(document).ready(function() {
        renderFileList();
        initFileHandlers();
    });

    function initFileHandlers() {
        dropzone.on('click', () => fileInput[0].click());

        fileInput.on('change', function(e) {
            handleFiles(e.target.files);
            fileInput.val('');
        });

        dropzone.on('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.addClass('dragover');
        });

        dropzone.on('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.removeClass('dragover');
        });

        dropzone.on('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.removeClass('dragover');
            handleFiles(e.originalEvent.dataTransfer.files);
        });

        fileList.on('click', '.remove-file', function(e) {
            e.stopPropagation();
            const index = $(this).data('index');
            files.splice(index, 1);
            renderFileList();
        });
    }

    function handleFiles(selectedFiles) {
        const incoming = Array.from(selectedFiles);
        const merged = files.concat(incoming);

        if (merged.length > 10) {
            Swal.fire('Upload Files', 'Please upload at most 10 files.', 'warning');
            return;
        }

        const totalBytes = merged.reduce((sum, file) => sum + file.size, 0);
        if (totalBytes > postMaxBytes) {
            Swal.fire('Upload Files', `Total upload size exceeds ${postMaxLabel}.`, 'warning');
            return;
        }

        for (const file of incoming) {
            if (uploadMaxBytes > 0 && file.size > uploadMaxBytes) {
                Swal.fire('Upload Files', `${file.name} exceeds ${uploadMaxLabel}.`, 'warning');
                return;
            }
        }

        files = merged;
        renderFileList();
    }

    function renderFileList() {
        if (files.length === 0) {
            fileList.html('<div class="text-muted small d-flex align-items-center justify-content-center">No files selected</div>');
            return;
        }

        const html = files.map((file, index) => {
            const isImage = file.type.startsWith('image/');
            const preview = isImage ? `<img src="${URL.createObjectURL(file)}" alt="${file.name}">` : '<div class="py-4"><i class="bi bi-file-earmark fs-1 text-primary"></i></div>';

            return `
                <div class="file-preview">
                    <button type="button" class="remove-file" data-index="${index}"><i class="fa fa-times"></i></button>
                    ${preview}
                    <div class="file-name" title="${file.name}">${file.name}</div>
                </div>
            `;
        }).join('');

        fileList.html(html);
    }

    $('#cmdSave').on('click', function() {
        if (files.length === 0) {
            Swal.fire('Replace Attachments', 'Please upload at least one file.', 'warning');
            return;
        }

        const btn = $(this);
        const btnText = btn.html();
        const formData = new FormData($('#frmEntry')[0]);

        files.forEach(file => {
            formData.append('files[]', file);
        });

        Swal.fire({
            title: 'Are you sure?',
            text: 'You are going to replace the activity attachments. Do you wish to proceed?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4D62C5',
            cancelButtonColor: '#636678',
            confirmButtonText: 'Yes'
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url: '<?php echo $controller_page . '/replace_attachments_save' ?>',
                    data: formData,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        btn.html(`<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>`);
                    },
                    success: function(response) {
                        Swal.fire(response.title, response.message, 'success').then(() => {
                            window.location.replace(response.data.url);
                        });
                    },
                    error: function(xhr) {
                        if (xhr.responseJSON) {
                            Swal.fire(xhr.responseJSON.title, xhr.responseJSON.message, 'error');
                        } else {
                            Swal.fire('Error', `${xhr.status}: ${xhr.statusText}`, 'error');
                        }
                    },
                    complete: function() {
                        btn.html(btnText);
                    }
                });
            }
        });
    });

    $('#cmdCancel').on('click', function() {
        cancel_confirmation('<?php echo $controller_page . '/view/' . $encrypter->encode($rec->{$pfield}); ?>');
    });
</script>
