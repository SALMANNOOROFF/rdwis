@php
    /**
     * Reusable Attachments Widget
     *
     * Expected variables:
     * @var string $module 'prj' | 'pur' | 'emp' | 'ctc' | 'aud' | 'ina'
     * @var int|string $objectId Parent record ID
     * @var string|null $title Widget title (default: 'Attachments')
     * @var array|null $defaultSlots Array of default document type strings
     * @var \Illuminate\Support\Collection|array|null $attachments Existing attachments collection
     * @var bool|null $canEdit General edit flag (default: true)
     * @var bool|null $canUpload Whether current user can upload
     * @var bool|null $canDelete Whether current user can delete (Permanently disabled: no delete for anyone)
     */
    $module = $module ?? 'prj';
    $title = $title ?? 'Attachments';
    $canEdit = $canEdit ?? true;
    $widgetId = 'att_widget_' . $module . '_' . $objectId . '_' . \Illuminate\Support\Str::random(4);

    // Upload permission: Any authenticated user can upload
    $canUpload = $canUpload ?? (Auth::check() && $canEdit);

    // Default slots per module if not provided
    if (!isset($defaultSlots)) {
        $defaultSlots = match ($module) {
            'prj' => ['Project Proposal', 'URD', 'Work Order', 'PPF'],
            'pur' => ['Purchase Case', 'Quotation Document', 'Market Research Report', 'Financial Status', 'Minute'],
            'emp' => ['Appointment Letter', 'Form', 'CV', 'Minute'],
            'ctc' => ['CV', 'Approval', 'Form', 'Minute'],
            'aud' => ['Data Revision Case', 'Minute'],
            default => ['Document 1', 'Document 2'],
        };
    }

    // Standardize attachments to collection
    $attachmentCollection = collect($attachments ?? []);

    // Helper to get pk and path keys based on module
    $pkKey = match ($module) {
        'prj' => 'jat_id',
        'pur' => 'pat_id',
        'emp' => 'eat_id',
        'ctc' => 'cat_id',
        'aud' => 'aat_id',
        'ina' => 'iat_id',
        default => 'id',
    };
    $typeKey = match ($module) {
        'prj' => 'jat_type',
        'pur' => 'pat_type',
        'emp' => 'eat_type',
        'ctc' => 'cat_type',
        'aud' => 'aat_type',
        'ina' => 'iat_type',
        default => 'type',
    };
    $pathKey = match ($module) {
        'prj' => 'jat_path',
        'pur' => 'pat_path',
        'emp' => 'eat_path',
        'ctc' => 'cat_path',
        'aud' => 'aat_path',
        'ina' => 'iat_path',
        default => 'path',
    };

    // Calculate uploaded count
    $totalUploaded = $attachmentCollection->filter(fn($a) => !empty(data_get($a, $pathKey)))->count();
@endphp

<div class="rdwis-attachment-widget card border shadow-sm mb-3" id="{{ $widgetId }}" style="border-radius: 8px; overflow: hidden; background: #ffffff;">
    {{-- Header --}}
    <div class="card-header d-flex justify-content-between align-items-center py-2 px-3" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
        <div class="d-flex align-items-center">
            <i class="fas fa-paperclip text-primary mr-2" style="font-size: 14px;"></i>
            <span class="font-weight-bold text-dark" style="font-size: 14px; letter-spacing: 0.3px;">{{ $title }}</span>
            <span class="badge badge-secondary badge-pill ml-2" style="font-size: 11px; font-weight: 500;">{{ $totalUploaded }}</span>
        </div>
        @if($canUpload)
            <button type="button" class="btn btn-sm btn-outline-primary" style="padding: 2px 8px; font-size: 12px; border-radius: 4px; line-height: 1.2;" data-toggle="modal" data-target="#modal_add_{{ $widgetId }}" title="Add New Attachment">
                <i class="fas fa-plus"></i>
            </button>
        @endif
    </div>

    {{-- Slots List --}}
    <div class="list-group list-group-flush" style="font-size: 13px;">
        {{-- 1. Default Standard Slots --}}
        @foreach($defaultSlots as $slotIndex => $slotName)
            @php
                $existing = $attachmentCollection->first(function($item) use ($typeKey, $slotName) {
                    return strcasecmp(trim(data_get($item, $typeKey) ?? ''), trim($slotName)) === 0;
                });
                $filePath = $existing ? data_get($existing, $pathKey) : null;
                $slotId = $existing ? data_get($existing, $pkKey) : null;
                $hasFile = !empty($filePath);
                $fileUrl = $hasFile ? \App\Facades\FileStorage::url($filePath) : null;
                $inputId = "file_input_{$widgetId}_{$slotIndex}";
                $canManageThis = $hasFile && \App\Http\Controllers\AttachmentController::canManageAttachment(Auth::user(), $module, $objectId, $existing);
                $replaceInputId = "replace_input_{$widgetId}_{$slotIndex}_{$slotId}";
            @endphp

            <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 border-bottom rdwis-slot-item" id="slot_item_{{ $widgetId }}_{{ $slotIndex }}" data-slot-name="{{ $slotName }}" style="background: {{ $hasFile ? '#f0fdf4' : '#ffffff' }}; transition: background 0.15s ease;">
                <div class="d-flex align-items-center overflow-hidden mr-2">
                    <i class="fas {{ $hasFile ? 'fa-check-circle text-success' : 'fa-file-alt text-muted' }} mr-2" style="font-size: 13px; width: 16px;"></i>
                    <span class="text-truncate {{ $hasFile ? 'font-weight-bold text-dark' : 'text-secondary' }}" title="{{ $slotName }}" style="font-size: 13px;">
                        {{ $slotName }}
                    </span>
                </div>

                <div class="d-flex align-items-center rdwis-slot-actions" style="gap: 4px;">
                    @if($hasFile)
                        {{-- Live View Button --}}
                        <a href="{{ $fileUrl }}" onclick="window.openLiveDocument('{{ $fileUrl }}', '{{ addslashes($slotName) }}'); return false;" class="rd-live-file-view btn btn-xs btn-outline-success shadow-none font-weight-bold" style="padding: 3px 8px; font-size: 11px; border-radius: 4px;" title="Live View {{ $slotName }}">
                            <i class="fas fa-eye mr-1"></i> View
                        </a>

                        @if($canManageThis)
                            {{-- Replace Button --}}
                            <label for="{{ $replaceInputId }}" class="btn btn-xs btn-outline-warning shadow-none font-weight-bold mb-0" style="padding: 3px 8px; font-size: 11px; border-radius: 4px; cursor: pointer;" title="Replace {{ $slotName }}">
                                <i class="fas fa-sync-alt mr-1"></i> Replace
                            </label>
                            <input type="file" id="{{ $replaceInputId }}" class="d-none" onchange="window.rdwisReplaceSlotAttachment(this, '{{ $module }}', '{{ $slotId }}', '{{ addslashes($slotName) }}', '{{ $widgetId }}')">

                            {{-- Delete Button --}}
                            <button type="button" class="btn btn-xs btn-outline-danger shadow-none font-weight-bold" style="padding: 3px 8px; font-size: 11px; border-radius: 4px;" onclick="window.rdwisDeleteSlotAttachment('{{ $module }}', '{{ $slotId }}', '{{ addslashes($slotName) }}', this, '{{ $widgetId }}', '{{ $inputId }}', false, '{{ $slotIndex }}')" title="Delete {{ $slotName }}">
                                <i class="fas fa-trash-alt mr-1"></i> Delete
                            </button>
                        @endif
                    @else
                        {{-- Upload Button for Empty Slot (No page reload: smooth AJAX upload) --}}
                        @if($canUpload)
                            <label for="{{ $inputId }}" class="btn btn-xs btn-light border mb-0 shadow-none text-muted upload-slot-btn" style="padding: 4px 10px; font-size: 12px; cursor: pointer; border-radius: 4px; background: #f1f5f9; border-color: #cbd5e1 !important;" title="Click to Upload {{ $slotName }}">
                                <i class="fas fa-upload text-primary mr-1"></i> Upload
                            </label>
                            <form action="{{ route('universal.attachment.upload') }}" method="POST" enctype="multipart/form-data" class="d-none" id="form_{{ $inputId }}">
                                @csrf
                                <input type="hidden" name="module" value="{{ $module }}">
                                <input type="hidden" name="object_id" value="{{ $objectId }}">
                                <input type="hidden" name="doc_type" value="{{ $slotName }}">
                                <input type="file" id="{{ $inputId }}" name="file" onchange="window.rdwisUploadSlotFile(this, '{{ $widgetId }}', '{{ addslashes($slotName) }}', '{{ $slotIndex }}')">
                            </form>
                        @else
                            <span class="badge badge-light text-muted" style="font-size: 11px;">Not Uploaded</span>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach

        {{-- 2. Custom Additional Attachments (uploaded via +) --}}
        @php
            $customAttachments = $attachmentCollection->reject(function($item) use ($defaultSlots, $typeKey) {
                $t = trim(data_get($item, $typeKey) ?? '');
                foreach ($defaultSlots as $ds) {
                    if (strcasecmp(trim($ds), $t) === 0) return true;
                }
                return false;
            });
        @endphp

        @foreach($customAttachments as $cIndex => $customAtt)
            @php
                $slotName = data_get($customAtt, $typeKey) ?: 'Additional Attachment';
                $filePath = data_get($customAtt, $pathKey);
                $slotId = data_get($customAtt, $pkKey);
                $hasFile = !empty($filePath);
                $fileUrl = $hasFile ? \App\Facades\FileStorage::url($filePath) : null;
                $canManageThis = $hasFile && \App\Http\Controllers\AttachmentController::canManageAttachment(Auth::user(), $module, $objectId, $customAtt);
                $replaceInputId = "replace_input_{$widgetId}_cust_{$slotId}";
            @endphp
            <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 border-bottom rdwis-slot-item" style="background: #f8fafc;">
                <div class="d-flex align-items-center overflow-hidden mr-2">
                    <i class="fas fa-paperclip text-info mr-2" style="font-size: 13px; width: 16px;"></i>
                    <span class="text-truncate font-weight-bold text-dark" title="{{ $slotName }}" style="font-size: 13px;">
                        {{ $slotName }}
                    </span>
                </div>
                <div class="d-flex align-items-center rdwis-slot-actions" style="gap: 4px;">
                    @if($hasFile)
                        <a href="{{ $fileUrl }}" onclick="window.openLiveDocument('{{ $fileUrl }}', '{{ addslashes($slotName) }}'); return false;" class="rd-live-file-view btn btn-xs btn-outline-info shadow-none font-weight-bold" style="padding: 3px 8px; font-size: 11px; border-radius: 4px;" title="Live View {{ $slotName }}">
                            <i class="fas fa-eye mr-1"></i> View
                        </a>

                        @if($canManageThis)
                            {{-- Replace Button --}}
                            <label for="{{ $replaceInputId }}" class="btn btn-xs btn-outline-warning shadow-none font-weight-bold mb-0" style="padding: 3px 8px; font-size: 11px; border-radius: 4px; cursor: pointer;" title="Replace {{ $slotName }}">
                                <i class="fas fa-sync-alt mr-1"></i> Replace
                            </label>
                            <input type="file" id="{{ $replaceInputId }}" class="d-none" onchange="window.rdwisReplaceSlotAttachment(this, '{{ $module }}', '{{ $slotId }}', '{{ addslashes($slotName) }}', '{{ $widgetId }}')">

                            {{-- Delete Button --}}
                            <button type="button" class="btn btn-xs btn-outline-danger shadow-none font-weight-bold" style="padding: 3px 8px; font-size: 11px; border-radius: 4px;" onclick="window.rdwisDeleteSlotAttachment('{{ $module }}', '{{ $slotId }}', '{{ addslashes($slotName) }}', this, '{{ $widgetId }}', null, true, null)" title="Delete {{ $slotName }}">
                                <i class="fas fa-trash-alt mr-1"></i> Delete
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach

        {{-- HR Policy Forms Section (Policy 2026) --}}
        @if($module === 'ctc')
            @include('hrforms.attachments-forms-section', ['caseId' => $objectId])
        @endif
    </div>
</div>

{{-- Modal for Add (+) Button (Placed OUTSIDE the card and teleported to document.body so it is never trapped under backdrops) --}}
@if($canUpload)
<div class="modal fade rd-upload-modal" id="modal_add_{{ $widgetId }}" tabindex="-1" role="dialog" aria-labelledby="modalLabel_{{ $widgetId }}" aria-hidden="true" style="z-index: 1065;">
    <div class="modal-dialog modal-dialog-centered" role="document" style="z-index: 1066;">
        <div class="modal-content" style="border-radius: 8px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.3); background: #ffffff;">
            <div class="modal-header py-3 px-4" style="background: #1e293b; color: #ffffff;">
                <h6 class="modal-title font-weight-bold mb-0 rajdhani" id="modalLabel_{{ $widgetId }}" style="letter-spacing: 0.5px;">
                    <i class="fas fa-file-upload mr-2 text-primary"></i> ATTACH DOCUMENT TO {{ strtoupper($module === 'pur' ? 'PURCHASE CASE' : ($module === 'ctc' ? 'CONTRACT CASE' : ($module === 'prj' ? 'PROJECT' : 'CASE'))) }}
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.8; outline: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('universal.attachment.upload') }}" method="POST" enctype="multipart/form-data" onsubmit="window.rdwisUploadCustomAttachment(event, this, '{{ $widgetId }}')">
                @csrf
                <input type="hidden" name="module" value="{{ $module }}">
                <input type="hidden" name="object_id" value="{{ $objectId }}">
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark" style="font-size: 13px;">DOCUMENT TITLE / NAME <span class="text-danger">*</span></label>
                        <input type="text" name="doc_type" class="form-control form-control-sm" placeholder="e.g., Justification Note, CNIC Copy, Degree" required style="border-radius: 4px;">
                    </div>
                    <div class="form-group mb-2">
                        <label class="font-weight-bold text-dark" style="font-size: 13px;">Select File <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control-file border p-2" style="border-radius: 4px; background: #f8fafc; font-size: 12px;" required>
                        <small class="text-muted d-block mt-1">Supported formats: PDF, JPG, PNG, DOC, DOCX, XLS, XLSX (Max: 20MB)</small>
                    </div>
                </div>
                <div class="modal-footer py-2 px-4 bg-light border-top">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary px-3 font-weight-bold">
                        <i class="fas fa-upload mr-1"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.rd-upload-modal {
    z-index: 1065 !important;
}
.rd-upload-modal + .modal-backdrop {
    z-index: 1060 !important;
}
</style>

<script>
(function() {
    function moveUploadModalToBody() {
        var m = document.getElementById('modal_add_{{ $widgetId }}');
        if (m && m.parentElement !== document.body) {
            document.body.appendChild(m);
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', moveUploadModalToBody);
    } else {
        moveUploadModalToBody();
    }
    if (window.jQuery) {
        $(document).ready(moveUploadModalToBody);
        $(document).on('show.bs.modal', '#modal_add_{{ $widgetId }}', moveUploadModalToBody);
        $(document).on('hidden.bs.modal', '#modal_add_{{ $widgetId }}', function() {
            if ($('.modal.show').length === 0) {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('padding-right', '');
            }
        });
    }
})();
</script>
@endif

<script>
if (!window.rdwisAttachmentUploaderInitialized) {
    window.rdwisAttachmentUploaderInitialized = true;

    // Helper: Replace Slot Attachment
    window.rdwisReplaceSlotAttachment = function(inputEl, module, id, docType, widgetId) {
        if (!inputEl.files || !inputEl.files[0]) return;
        const file = inputEl.files[0];
        const listItem = inputEl.closest('.rdwis-slot-item');
        const replaceBtn = listItem ? listItem.querySelector('label[for="' + inputEl.id + '"]') : null;

        const doUpload = () => {
            const originalBtnHtml = replaceBtn ? replaceBtn.innerHTML : '';
            if (replaceBtn) {
                replaceBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1" role="status"></span>';
                replaceBtn.style.pointerEvents = 'none';
            }

            const formData = new FormData();
            formData.append('file', file);
            formData.append('doc_type', docType);
            formData.append('_token', '{{ csrf_token() }}');

            fetch(`/universal-attachment/${module}/${id}/replace`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (replaceBtn) {
                    replaceBtn.innerHTML = originalBtnHtml;
                    replaceBtn.style.pointerEvents = 'auto';
                }
                inputEl.value = '';

                if (data.success) {
                    if (listItem) {
                        const viewBtn = listItem.querySelector('.rd-live-file-view');
                        if (viewBtn) {
                            viewBtn.href = data.url;
                            viewBtn.setAttribute('onclick', `window.openLiveDocument('${data.url}', '${docType.replace(/'/g, "\\'")}'); return false;`);
                        }
                    }

                    if (window.Swal) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: data.message || `"${docType}" replaced successfully!`,
                            showConfirmButton: false,
                            timer: 3000
                        });
                    } else {
                        alert(`"${docType}" replaced successfully!`);
                    }
                } else {
                    throw new Error(data.message || 'Replace failed');
                }
            })
            .catch(err => {
                if (replaceBtn) {
                    replaceBtn.innerHTML = originalBtnHtml;
                    replaceBtn.style.pointerEvents = 'auto';
                }
                inputEl.value = '';
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Replace Failed',
                        text: err.message || 'Could not replace file. You may not have permission.'
                    });
                } else {
                    alert('Error: ' + err.message);
                }
            });
        };

        if (window.Swal) {
            Swal.fire({
                title: 'Replace Attachment?',
                text: `Are you sure you want to replace "${docType}" with "${file.name}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Yes, Replace',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    doUpload();
                } else {
                    inputEl.value = '';
                }
            });
        } else {
            if (confirm(`Are you sure you want to replace "${docType}" with "${file.name}"?`)) {
                doUpload();
            } else {
                inputEl.value = '';
            }
        }
    };

    // Helper: Delete Slot Attachment
    window.rdwisDeleteSlotAttachment = function(module, id, docType, btnEl, widgetId, inputId, isCustom, slotIndex) {
        const doDelete = () => {
            const originalBtnHtml = btnEl.innerHTML;
            btnEl.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
            btnEl.disabled = true;

            fetch(`/universal-attachment/${module}/${id}/delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ _token: '{{ csrf_token() }}' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const widget = document.getElementById(widgetId);
                    if (widget) {
                        const badge = widget.querySelector('.card-header .badge');
                        if (badge) {
                            badge.textContent = Math.max(0, parseInt(badge.textContent || '1', 10) - 1);
                        }
                    }

                    if (isCustom) {
                        btnEl.closest('.rdwis-slot-item').remove();
                    } else {
                        // Revert standard slot to empty upload state
                        const listItem = btnEl.closest('.rdwis-slot-item');
                        if (listItem) {
                            listItem.style.background = '#ffffff';
                            const iconEl = listItem.querySelector('.fa-check-circle');
                            if (iconEl) {
                                iconEl.className = 'fas fa-file-alt text-muted mr-2';
                            }
                            const titleEl = listItem.querySelector('.text-truncate');
                            if (titleEl) {
                                titleEl.className = 'text-truncate text-secondary';
                            }
                            const actionsEl = listItem.querySelector('.rdwis-slot-actions');
                            if (actionsEl) {
                                actionsEl.innerHTML = `
                                    <label for="${inputId}" class="btn btn-xs btn-light border mb-0 shadow-none text-muted upload-slot-btn" style="padding: 4px 10px; font-size: 12px; cursor: pointer; border-radius: 4px; background: #f1f5f9; border-color: #cbd5e1 !important;" title="Click to Upload ${docType}">
                                        <i class="fas fa-upload text-primary mr-1"></i> Upload
                                    </label>
                                    <form action="{{ route('universal.attachment.upload') }}" method="POST" enctype="multipart/form-data" class="d-none" id="form_${inputId}">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" name="module" value="${module}">
                                        <input type="hidden" name="object_id" value="{{ $objectId }}">
                                        <input type="hidden" name="doc_type" value="${docType}">
                                        <input type="file" id="${inputId}" name="file" onchange="window.rdwisUploadSlotFile(this, '${widgetId}', '${docType}', '${slotIndex}')">
                                    </form>
                                `;
                            }
                        }
                    }

                    if (window.Swal) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: data.message || `"${docType}" deleted successfully.`,
                            showConfirmButton: false,
                            timer: 3000
                        });
                    } else {
                        alert(`"${docType}" deleted successfully.`);
                    }
                } else {
                    throw new Error(data.message || 'Delete failed');
                }
            })
            .catch(err => {
                btnEl.innerHTML = originalBtnHtml;
                btnEl.disabled = false;
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Delete Failed',
                        text: err.message || 'Could not delete attachment. You may not have permission.'
                    });
                } else {
                    alert('Error: ' + err.message);
                }
            });
        };

        if (window.Swal) {
            Swal.fire({
                title: 'Delete Attachment?',
                text: `Are you sure you want to delete "${docType}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    doDelete();
                }
            });
        } else {
            if (confirm(`Are you sure you want to delete "${docType}"?`)) {
                doDelete();
            }
        }
    };

    window.rdwisUploadSlotFile = function(inputEl, widgetId, docType, slotIndex) {
        if (!inputEl.files || !inputEl.files[0]) return;
        const file = inputEl.files[0];
        const form = inputEl.closest('form');
        const listItem = inputEl.closest('.rdwis-slot-item');
        const uploadBtn = listItem ? listItem.querySelector('.upload-slot-btn') : null;
        
        const originalBtnHtml = uploadBtn ? uploadBtn.innerHTML : '';
        if (uploadBtn) {
            uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm text-primary mr-1" role="status"></span> Uploading...';
            uploadBtn.style.pointerEvents = 'none';
        }

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const slotId = data.slot_id || '';
                const replaceInputId = `replace_input_${widgetId}_${slotIndex}_${slotId}`;
                const module = '{{ $module }}';

                // Update list item to uploaded state with View, Replace & Delete buttons
                if (listItem) {
                    listItem.style.background = '#f0fdf4';
                    const iconEl = listItem.querySelector('.fa-file-alt');
                    if (iconEl) {
                        iconEl.className = 'fas fa-check-circle text-success mr-2';
                    }
                    const titleEl = listItem.querySelector('.text-secondary');
                    if (titleEl) {
                        titleEl.classList.remove('text-secondary');
                        titleEl.classList.add('font-weight-bold', 'text-dark');
                    }
                    const actionContainer = listItem.querySelector('.rdwis-slot-actions');
                    if (actionContainer) {
                        actionContainer.innerHTML = `
                            <a href="${data.url}" onclick="window.openLiveDocument('${data.url}', '${docType}'); return false;" class="rd-live-file-view btn btn-xs btn-outline-success shadow-none font-weight-bold" style="padding: 3px 8px; font-size: 11px; border-radius: 4px;" title="Live View ${docType}">
                                <i class="fas fa-eye mr-1"></i> View
                            </a>
                            <label for="${replaceInputId}" class="btn btn-xs btn-outline-warning shadow-none font-weight-bold mb-0" style="padding: 3px 8px; font-size: 11px; border-radius: 4px; cursor: pointer;" title="Replace ${docType}">
                                <i class="fas fa-sync-alt mr-1"></i> Replace
                            </label>
                            <input type="file" id="${replaceInputId}" class="d-none" onchange="window.rdwisReplaceSlotAttachment(this, '${module}', '${slotId}', '${docType}', '${widgetId}')">
                            <button type="button" class="btn btn-xs btn-outline-danger shadow-none font-weight-bold" style="padding: 3px 8px; font-size: 11px; border-radius: 4px;" onclick="window.rdwisDeleteSlotAttachment('${module}', '${slotId}', '${docType}', this, '${widgetId}', '${inputEl.id}', false, '${slotIndex}')" title="Delete ${docType}">
                                <i class="fas fa-trash-alt mr-1"></i> Delete
                            </button>
                        `;
                    }
                }

                // Increment widget count badge
                const widget = document.getElementById(widgetId);
                if (widget) {
                    const badge = widget.querySelector('.card-header .badge');
                    if (badge) {
                        badge.textContent = parseInt(badge.textContent || '0', 10) + 1;
                    }
                }

                // If financial view table is on page, append row smoothly
                if (window.rdwisAppendFinTableRow) {
                    window.rdwisAppendFinTableRow(docType, file.name, data.url);
                }

                if (window.Swal) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message || 'File uploaded successfully!',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            } else {
                throw new Error(data.message || 'Upload failed');
            }
        })
        .catch(err => {
            if (uploadBtn) {
                uploadBtn.innerHTML = originalBtnHtml;
                uploadBtn.style.pointerEvents = 'auto';
            }
            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'Upload Error',
                    text: err.message || 'Could not upload file. Please try again.'
                });
            } else {
                alert('Upload failed: ' + (err.message || 'Please try again.'));
            }
        });
    };

    window.rdwisUploadCustomAttachment = function(event, form, widgetId) {
        event.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1" role="status"></span> Uploading...';
            submitBtn.disabled = true;
        }

        const formData = new FormData(form);
        const docType = form.querySelector('input[name="doc_type"]')?.value || 'Document';
        const fileInput = form.querySelector('input[name="file"]');
        const fileName = fileInput?.files?.[0]?.name || 'Document File';

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (submitBtn) {
                submitBtn.innerHTML = originalBtnHtml;
                submitBtn.disabled = false;
            }

            if (data.success) {
                const modal = form.closest('.modal');
                if (modal && window.$) {
                    $(modal).modal('hide');
                }
                form.reset();

                const slotId = data.slot_id || '';
                const replaceInputId = `replace_input_${widgetId}_cust_${slotId}`;
                const module = '{{ $module }}';

                const widget = document.getElementById(widgetId);
                if (widget) {
                    const listGroup = widget.querySelector('.list-group');
                    if (listGroup) {
                        const newItem = document.createElement('div');
                        newItem.className = 'list-group-item d-flex justify-content-between align-items-center py-2 px-3 border-bottom rdwis-slot-item';
                        newItem.style.background = '#f8fafc';
                        newItem.innerHTML = `
                            <div class="d-flex align-items-center overflow-hidden mr-2">
                                <i class="fas fa-paperclip text-info mr-2" style="font-size: 13px; width: 16px;"></i>
                                <span class="text-truncate font-weight-bold text-dark" title="${docType}" style="font-size: 13px;">
                                    ${docType}
                                </span>
                            </div>
                            <div class="d-flex align-items-center rdwis-slot-actions" style="gap: 4px;">
                                <a href="${data.url}" onclick="window.openLiveDocument('${data.url}', '${docType}'); return false;" class="rd-live-file-view btn btn-xs btn-outline-info shadow-none font-weight-bold" style="padding: 3px 8px; font-size: 11px; border-radius: 4px;" title="Live View ${docType}">
                                    <i class="fas fa-eye mr-1"></i> View
                                </a>
                                <label for="${replaceInputId}" class="btn btn-xs btn-outline-warning shadow-none font-weight-bold mb-0" style="padding: 3px 8px; font-size: 11px; border-radius: 4px; cursor: pointer;" title="Replace ${docType}">
                                    <i class="fas fa-sync-alt mr-1"></i> Replace
                                </label>
                                <input type="file" id="${replaceInputId}" class="d-none" onchange="window.rdwisReplaceSlotAttachment(this, '${module}', '${slotId}', '${docType}', '${widgetId}')">
                                <button type="button" class="btn btn-xs btn-outline-danger shadow-none font-weight-bold" style="padding: 3px 8px; font-size: 11px; border-radius: 4px;" onclick="window.rdwisDeleteSlotAttachment('${module}', '${slotId}', '${docType}', this, '${widgetId}', null, true, null)" title="Delete ${docType}">
                                    <i class="fas fa-trash-alt mr-1"></i> Delete
                                </button>
                            </div>
                        `;
                        listGroup.appendChild(newItem);
                    }

                    const badge = widget.querySelector('.card-header .badge');
                    if (badge) {
                        badge.textContent = parseInt(badge.textContent || '0', 10) + 1;
                    }
                }

                // If financial view table is on page, append row smoothly
                if (window.rdwisAppendFinTableRow) {
                    window.rdwisAppendFinTableRow(docType, fileName, data.url);
                }

                if (window.Swal) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message || 'Attachment uploaded successfully!',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            } else {
                throw new Error(data.message || 'Upload failed');
            }
        })
        .catch(err => {
            if (submitBtn) {
                submitBtn.innerHTML = originalBtnHtml;
                submitBtn.disabled = false;
            }
            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'Upload Error',
                    text: err.message || 'Could not upload attachment.'
                });
            } else {
                alert('Upload failed: ' + (err.message || 'Please try again.'));
            }
        });
    };

    // Dropdown helpers for Minute Header attachment dropdowns
    window.rdwisDropdownReplace = function(module, id, docType, inputEl) {
        if (!inputEl.files || !inputEl.files[0]) return;
        const file = inputEl.files[0];

        const doUpload = () => {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('doc_type', docType);
            formData.append('_token', '{{ csrf_token() }}');

            fetch(`/universal-attachment/${module}/${id}/replace`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                inputEl.value = '';
                if (data.success) {
                    if (window.Swal) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: data.message || `"${docType}" replaced successfully!`,
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    throw new Error(data.message || 'Replace failed');
                }
            })
            .catch(err => {
                inputEl.value = '';
                if (window.Swal) {
                    Swal.fire({ icon: 'error', title: 'Replace Failed', text: err.message });
                } else {
                    alert('Error: ' + err.message);
                }
            });
        };

        if (window.Swal) {
            Swal.fire({
                title: 'Replace Document?',
                text: `Are you sure you want to replace "${docType}" with "${file.name}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Yes, Replace',
                cancelButtonText: 'Cancel'
            }).then(r => {
                if (r.isConfirmed) doUpload();
                else inputEl.value = '';
            });
        } else {
            if (confirm(`Are you sure you want to replace "${docType}" with "${file.name}"?`)) doUpload();
            else inputEl.value = '';
        }
    };

    window.rdwisDropdownDelete = function(module, id, docType, btnEl) {
        const doDelete = () => {
            const originalBtnHtml = btnEl.innerHTML;
            btnEl.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
            btnEl.disabled = true;

            fetch(`/universal-attachment/${module}/${id}/delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ _token: '{{ csrf_token() }}' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const row = btnEl.closest('.d-flex.justify-content-between');
                    if (row) row.remove();
                    if (window.Swal) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: data.message || `"${docType}" deleted.`,
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                } else {
                    throw new Error(data.message || 'Delete failed');
                }
            })
            .catch(err => {
                btnEl.innerHTML = originalBtnHtml;
                btnEl.disabled = false;
                if (window.Swal) {
                    Swal.fire({ icon: 'error', title: 'Delete Failed', text: err.message });
                } else {
                    alert('Error: ' + err.message);
                }
            });
        };

        if (window.Swal) {
            Swal.fire({
                title: 'Delete Attachment?',
                text: `Are you sure you want to delete "${docType}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel'
            }).then(r => {
                if (r.isConfirmed) doDelete();
            });
        } else {
            if (confirm(`Are you sure you want to delete "${docType}"?`)) doDelete();
        }
    };

    window.rdwisAppendFinTableRow = function(docType, fileName, fileUrl) {
        const finTable = document.querySelector('#finProjectAttachmentsTable tbody');
        if (!finTable) return;
        
        // Remove empty state row if present
        const emptyRow = finTable.querySelector('tr td[colspan="5"]');
        if (emptyRow) {
            emptyRow.closest('tr').remove();
        }

        const rowCount = finTable.querySelectorAll('tr').length + 1;
        const now = new Date();
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        const day = String(now.getDate()).padStart(2, '0');
        const month = months[now.getMonth()];
        const year = now.getFullYear();
        const dateStr = `${day} ${month} ${year}`;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="pl-3 font-weight-bold text-primary">${rowCount}</td>
            <td class="font-weight-bold text-dark">
                <i class="fas fa-file-alt text-info fa-lg mr-2"></i>
                ${docType}
            </td>
            <td class="text-muted font-weight-bold font-mono" style="font-size: 0.82rem;">
                ${fileName}
            </td>
            <td class="text-dark font-weight-bold" style="font-size: 0.85rem;">
                ${dateStr}
            </td>
            <td class="pr-3 text-center">
                <a href="${fileUrl}" onclick="window.openLiveDocument('${fileUrl}', '${docType}'); return false;" class="rd-live-file-view btn btn-xs btn-primary font-weight-bold px-2.5 py-1 rounded shadow-sm" title="View Document Live">
                    <i class="fas fa-eye mr-1"></i> View File
                </a>
            </td>
        `;
        finTable.appendChild(tr);
    };
}
</script>
