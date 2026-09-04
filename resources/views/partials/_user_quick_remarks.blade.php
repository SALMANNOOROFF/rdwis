@php
    $targetSelector = $targetTextarea ?? '#decisionRemarks';
    $currentUserId = Auth::id() ?? 0;
    $userRemarks = \App\Models\UserQuickRemark::forUser($currentUserId)->get();
@endphp

<div class="user-quick-remarks-wrapper mb-2" data-target="{{ $targetSelector }}" id="uqrContainerWrapper">
    <div class="d-flex align-items-center justify-content-between mb-1.5 flex-wrap gap-1">
        <div class="d-flex align-items-center gap-1">
            <span class="font-weight-bold text-muted" style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.4px;">
                <i class="fas fa-bolt text-warning mr-1"></i> Quick Remarks:
            </span>
            <span id="uqrCountBadge" class="badge badge-light border text-muted" style="font-size: 8.5px; padding: 2px 5px; border-radius: 4px;">
                {{ $userRemarks->count() }}/7
            </span>
        </div>
        <div class="d-flex align-items-center" style="gap: 4px;">
            {{-- Add Button --}}
            <button type="button" class="btn btn-xs btn-outline-primary d-inline-flex align-items-center justify-content-center" 
                    id="btnOpenAddUQR" 
                    style="width: 22px; height: 22px; border-radius: 4px; padding: 0; font-size: 10px;" 
                    title="Add Custom Shortcut (Max 7)"
                    {{ $userRemarks->count() >= 7 ? 'disabled' : '' }}>
                <i class="fas fa-plus"></i>
            </button>
            {{-- Manage (Edit/Delete) Mode Toggle Button --}}
            <button type="button" class="btn btn-xs btn-outline-secondary d-inline-flex align-items-center justify-content-center" 
                    id="btnToggleManageUQR" 
                    style="width: 22px; height: 22px; border-radius: 4px; padding: 0; font-size: 10px;" 
                    title="Manage / Edit / Delete Shortcuts">
                <i class="fas fa-pen"></i>
            </button>
        </div>
    </div>

    {{-- Chips Container --}}
    <div id="uqrChipsList" class="d-flex flex-wrap align-items-center" style="gap: 6px; min-height: 26px;">
        @forelse($userRemarks as $rem)
            <div class="uqr-chip" data-id="{{ $rem->uqr_id }}" data-label="{{ e($rem->uqr_label) }}" data-desc="{{ e($rem->uqr_description) }}">
                <span class="uqr-chip-label" title="{{ $rem->uqr_description }}">{{ $rem->uqr_label }}</span>
                <span class="uqr-manage-actions" style="display: none;">
                    <i class="fas fa-pencil-alt uqr-btn-edit text-primary ml-1.5" title="Edit Shortcut"></i>
                    <i class="fas fa-times uqr-btn-delete text-danger ml-1" title="Delete Shortcut"></i>
                </span>
            </div>
        @empty
            <div id="uqrEmptyPlaceholder" class="text-muted font-italic" style="font-size: 10.5px; opacity: 0.85;">
                No shortcuts added. Click <i class="fas fa-plus text-primary mx-0.5"></i> to create your custom quick remarks.
            </div>
        @endforelse
    </div>
</div>

<style>
.uqr-chip {
    font-size: 10.5px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 12px;
    background: #f1f5f9;
    color: #334155;
    border: 1px solid #cbd5e1;
    cursor: pointer;
    transition: all .2s;
    display: inline-flex;
    align-items: center;
    user-select: none;
    line-height: 1.35;
}
.uqr-chip:hover:not(.manage-mode) {
    background: var(--rd-accent, #5F7858);
    color: #ffffff !important;
    border-color: var(--rd-accent, #5F7858);
}
.uqr-chip.manage-mode {
    background: #fffbeb;
    border-color: #f59e0b;
    color: #92400e;
    cursor: default;
}
.uqr-manage-actions i {
    cursor: pointer;
    padding: 2px;
    font-size: 9.5px;
    transition: transform 0.15s;
}
.uqr-manage-actions i:hover {
    transform: scale(1.25);
}
</style>

{{-- Interactive Modal for Add / Edit User Quick Remark (Appended to body in JS) --}}
<div class="modal fade" id="modalUserQuickRemark" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 440px;">
        <div class="modal-content" style="border-radius: 10px; border: 1px solid #cbd5e1; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            <div class="modal-header py-2.5 px-3" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <h6 class="modal-title font-weight-bold rajdhani text-dark mb-0" id="modalUQRTitle" style="font-size: 13.5px; letter-spacing: 0.5px;">
                    <i class="fas fa-bolt text-warning mr-1.5"></i> <span id="modalUQRModeText">Add Custom Shortcut</span>
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="outline: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div class="modal-body p-3">
                <input type="hidden" id="uqrIdInput" value="">
                
                {{-- Label Input --}}
                <div class="form-group mb-2.5">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="font-weight-bold text-muted small mb-0" style="font-size: 10.5px; text-transform: uppercase;">
                            Shortcut Label: <span class="text-danger">*</span>
                        </label>
                        <span id="uqrWordCountDisplay" class="small text-muted" style="font-size: 10px;">
                            <span id="uqrWordsUsed" class="font-weight-bold">0</span>/6 words
                        </span>
                    </div>
                    <input type="text" id="uqrLabelInput" class="form-control" maxlength="100" style="font-size: 12.5px; border-radius: 6px; border-color: #cbd5e1;" required>
                    <small id="uqrWordCountWarn" class="text-danger font-weight-bold d-none" style="font-size: 9.5px;">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Label cannot exceed 6 words.
                    </small>
                </div>

                {{-- Description / Full Content Input --}}
                <div class="form-group mb-1">
                    <label class="font-weight-bold text-muted small mb-1" style="font-size: 10.5px; text-transform: uppercase;">
                        Full Remark / Note Content: <span class="text-danger">*</span>
                    </label>
                    <textarea id="uqrDescInput" class="form-control" rows="3" style="font-size: 12px; border-radius: 6px; border-color: #cbd5e1;" required></textarea>
                    <small class="text-muted" style="font-size: 9.5px;">
                        Clicking the shortcut chip will paste this exact text into the remarks textarea.
                    </small>
                </div>
            </div>
            <div class="modal-footer py-2 px-3" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
                <button type="button" class="btn btn-light border btn-sm font-weight-bold" data-dismiss="modal" style="border-radius: 6px; font-size: 11.5px;">Cancel</button>
                <button type="button" id="btnSaveUQR" class="btn btn-primary btn-sm font-weight-bold rajdhani" style="border-radius: 6px; font-size: 12px; background: var(--rd-accent, #5F7858) !important; border-color: var(--rd-accent, #5F7858) !important;">
                    <i class="fas fa-check mr-1"></i> SAVE SHORTCUT
                </button>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
$(document).ready(function() {
    // Ensure modal is moved to body to prevent nested form submissions
    if ($('#modalUserQuickRemark').parent().is('body') === false) {
        $('body').append($('#modalUserQuickRemark'));
    }

    let isManageMode = false;

    function countWords(str) {
        if (!str) return 0;
        return str.trim().split(/\s+/).filter(Boolean).length;
    }

    function updateWordCounter() {
        const val = $('#uqrLabelInput').val() || '';
        const count = countWords(val);
        $('#uqrWordsUsed').text(count);

        if (count > 6) {
            $('#uqrWordCountDisplay').removeClass('text-muted').addClass('text-danger font-weight-bold');
            $('#uqrWordCountWarn').removeClass('d-none');
            $('#uqrLabelInput').addClass('is-invalid');
            $('#btnSaveUQR').prop('disabled', true);
        } else {
            $('#uqrWordCountDisplay').removeClass('text-danger font-weight-bold').addClass('text-muted');
            $('#uqrWordCountWarn').addClass('d-none');
            $('#uqrLabelInput').removeClass('is-invalid');
            $('#btnSaveUQR').prop('disabled', false);
        }
    }

    $('#uqrLabelInput').on('input change keyup', updateWordCounter);

    // Open Add Modal
    $(document).on('click', '#btnOpenAddUQR', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('#modalUQRModeText').text('Add Custom Shortcut');
        $('#uqrIdInput').val('');
        $('#uqrLabelInput').val('');
        $('#uqrDescInput').val('');
        updateWordCounter();
        $('#modalUserQuickRemark').modal('show');
    });

    // Toggle Manage Mode (Pencil button)
    $(document).on('click', '#btnToggleManageUQR', function(e) {
        e.preventDefault();
        e.stopPropagation();
        isManageMode = !isManageMode;
        if (isManageMode) {
            $(this).addClass('active btn-warning text-dark').removeClass('btn-outline-secondary');
            $('.uqr-chip').addClass('manage-mode');
            $('.uqr-manage-actions').show();
        } else {
            $(this).removeClass('active btn-warning text-dark').addClass('btn-outline-secondary');
            $('.uqr-chip').removeClass('manage-mode');
            $('.uqr-manage-actions').hide();
        }
    });

    // Click Chip to Insert Remark into Target Textarea
    $(document).on('click', '.uqr-chip', function(e) {
        if (isManageMode) return; // In manage mode, clicking chip does nothing
        if ($(e.target).closest('.uqr-manage-actions').length > 0) return;

        const desc = $(this).attr('data-desc') || '';
        const targetSelector = $('#uqrContainerWrapper').attr('data-target') || '#decisionRemarks';
        const $target = $(targetSelector);

        if ($target.length) {
            $target.val(desc).trigger('input').trigger('change');
            if (typeof Swal !== 'undefined' && Swal.mixin) {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true
                });
                Toast.fire({
                    icon: 'success',
                    title: 'Remark applied'
                });
            }
        }
    });

    // Edit Chip Button
    $(document).on('click', '.uqr-btn-edit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const $chip = $(this).closest('.uqr-chip');
        const id = $chip.attr('data-id');
        const label = $chip.attr('data-label');
        const desc = $chip.attr('data-desc');

        $('#modalUQRModeText').text('Edit Shortcut');
        $('#uqrIdInput').val(id);
        $('#uqrLabelInput').val(label);
        $('#uqrDescInput').val(desc);
        updateWordCounter();
        $('#modalUserQuickRemark').modal('show');
    });

    // Delete Chip Button
    $(document).on('click', '.uqr-btn-delete', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const $chip = $(this).closest('.uqr-chip');
        const id = $chip.attr('data-id');
        const label = $chip.attr('data-label') || 'shortcut';

        if (confirm(`Are you sure you want to delete the "${label}" shortcut?`)) {
            $.ajax({
                url: `/user-quick-remarks/${id}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
                },
                success: function(resp) {
                    $chip.fadeOut(200, function() {
                        $(this).remove();
                        const currentChips = $('.uqr-chip').length;
                        $('#uqrCountBadge').text(`${currentChips}/7`);
                        $('#btnOpenAddUQR').prop('disabled', currentChips >= 7);
                        if (currentChips === 0) {
                            $('#uqrChipsList').html('<div id="uqrEmptyPlaceholder" class="text-muted font-italic" style="font-size: 10.5px; opacity: 0.85;">No shortcuts added. Click <i class="fas fa-plus text-primary mx-0.5"></i> to create your custom quick remarks.</div>');
                        }
                    });
                },
                error: function(xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to delete shortcut.';
                    alert(msg);
                }
            });
        }
    });

    // Save Shortcut via Pure AJAX
    function handleSaveUQR(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        const id = $('#uqrIdInput').val();
        const label = ($('#uqrLabelInput').val() || '').trim();
        const desc = ($('#uqrDescInput').val() || '').trim();

        if (countWords(label) > 6) {
            alert('Label cannot exceed 6 words.');
            return;
        }
        if (!label || !desc) {
            alert('Please enter both shortcut label and remark description.');
            return;
        }

        const isEdit = !!id;
        const url = isEdit ? `/user-quick-remarks/${id}` : '/user-quick-remarks';
        const method = isEdit ? 'PUT' : 'POST';

        $('#btnSaveUQR').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> SAVING...');

        $.ajax({
            url: url,
            type: method,
            data: {
                label: label,
                description: desc,
                _token: $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
            },
            success: function(resp) {
                $('#modalUserQuickRemark').modal('hide');
                $('#btnSaveUQR').prop('disabled', false).html('<i class="fas fa-check mr-1"></i> SAVE SHORTCUT');

                const r = resp.remark;
                if (isEdit) {
                    const $chip = $(`.uqr-chip[data-id="${r.uqr_id}"]`);
                    $chip.attr('data-label', r.uqr_label).attr('data-desc', r.uqr_description);
                    $chip.find('.uqr-chip-label').text(r.uqr_label).attr('title', r.uqr_description);
                } else {
                    $('#uqrEmptyPlaceholder').remove();
                    const manageClass = isManageMode ? 'manage-mode' : '';
                    const manageDisplay = isManageMode ? 'inline' : 'none';
                    const newChipHtml = `
                        <div class="uqr-chip ${manageClass}" data-id="${r.uqr_id}" data-label="${r.uqr_label}" data-desc="${r.uqr_description}">
                            <span class="uqr-chip-label" title="${r.uqr_description}">${r.uqr_label}</span>
                            <span class="uqr-manage-actions" style="display: ${manageDisplay};">
                                <i class="fas fa-pencil-alt uqr-btn-edit text-primary ml-1.5" title="Edit Shortcut"></i>
                                <i class="fas fa-times uqr-btn-delete text-danger ml-1" title="Delete Shortcut"></i>
                            </span>
                        </div>
                    `;
                    $('#uqrChipsList').append(newChipHtml);
                }

                const currentChips = $('.uqr-chip').length;
                $('#uqrCountBadge').text(`${currentChips}/7`);
                $('#btnOpenAddUQR').prop('disabled', currentChips >= 7);

                if (typeof Swal !== 'undefined' && Swal.mixin) {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: isEdit ? 'Shortcut updated' : 'Shortcut saved'
                    });
                }
            },
            error: function(xhr) {
                $('#btnSaveUQR').prop('disabled', false).html('<i class="fas fa-check mr-1"></i> SAVE SHORTCUT');
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to save shortcut.';
                alert(msg);
            }
        });
    }

    $(document).on('click', '#btnSaveUQR', handleSaveUQR);
});
</script>
@endpush
@endonce
