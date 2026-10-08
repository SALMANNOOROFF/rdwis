@php
    $cType = $caseType ?? 'purchase';
    $cId = (int)($caseId ?? 0);
    $target = $targetTextarea ?? '#inlineRemarks';
    $currUserAccId = (int)(Auth::user()?->acc_id ?? Auth::id() ?? 0);
    $existingDraft = $currUserAccId ? \App\Models\UserCaseDraftRemark::getDraft($currUserAccId, $cType, $cId) : null;
    $hasDraft = !empty(trim($existingDraft ?? ''));
    $uniqueUid = 'dr_' . $cType . '_' . $cId . '_' . substr(md5($target), 0, 4);
@endphp

<div class="draft-remarks-container d-inline-flex align-items-center" 
     id="{{ $uniqueUid }}" 
     data-case-type="{{ $cType }}" 
     data-case-id="{{ $cId }}" 
     data-target="{{ $target }}" 
     data-initial-draft="{{ e($existingDraft ?? '') }}" 
     style="gap: 6px;">

    {{-- Save Draft Remarks Button (Hidden initially until user adds/changes text) --}}
    <button type="button" 
            class="btn btn-xs btn-outline-info font-weight-bold btn-save-draft rajdhani" 
            id="{{ $uniqueUid }}_btnSave" 
            style="display: none; font-size: 11px; padding: 2px 9px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); transition: all 0.2s;" 
            title="Save your remarks as a private draft (visible only to you until sent)">
        <i class="fas fa-save mr-1"></i> SAVE DRAFT REMARKS
    </button>

    {{-- Draft Status Badge (Shows when a saved draft exists) --}}
    <div class="draft-status-badge d-inline-flex align-items-center" 
         id="{{ $uniqueUid }}_badge" 
         style="{{ $hasDraft ? 'display: inline-flex;' : 'display: none;' }} gap: 4px;">
        <span class="badge badge-warning text-dark font-weight-bold rajdhani px-2 py-0.5" 
              id="{{ $uniqueUid }}_badgeText" 
              style="font-size: 10.5px; border: 1px solid #f59e0b; background: #fef3c7; color: #92400e !important; letter-spacing: 0.3px;" 
              title="This draft is private to your account and not yet visible to other users">
            <i class="fas fa-file-alt mr-1"></i> DRAFT (PRIVATE TO YOU)
        </span>
        <button type="button" 
                class="btn btn-xs btn-outline-danger py-0 px-1.5 btn-discard-draft" 
                id="{{ $uniqueUid }}_btnDiscard" 
                style="font-size: 9.5px; height: 18px; border-radius: 3px; line-height: 1;" 
                title="Discard this draft remarks">
            <i class="fas fa-times mr-0.5"></i> Clear
        </button>
    </div>
</div>

<script>
(function() {
    function initDraftRemarks_{{ $uniqueUid }}() {
        const wrap = document.getElementById('{{ $uniqueUid }}');
        if (!wrap) return;

        const caseType = wrap.getAttribute('data-case-type');
        const caseId = wrap.getAttribute('data-case-id');
        const targetSelector = wrap.getAttribute('data-target');
        const textarea = document.querySelector(targetSelector);
        if (!textarea) return;

        const btnSave = document.getElementById('{{ $uniqueUid }}_btnSave');
        const badge = document.getElementById('{{ $uniqueUid }}_badge');
        const badgeText = document.getElementById('{{ $uniqueUid }}_badgeText');
        const btnDiscard = document.getElementById('{{ $uniqueUid }}_btnDiscard');

        let savedDraftContent = wrap.getAttribute('data-initial-draft') || '';

        // Prepopulate textarea if draft exists and textarea is currently empty
        if (savedDraftContent && !textarea.value.trim()) {
            textarea.value = savedDraftContent;
            // Trigger input event so any character counters or button states update
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
        }

        // Monitor textarea input: show Save Draft button when user modifies remarks
        function checkDirty() {
            const currentVal = textarea.value.trim();
            if (currentVal.length > 0 && currentVal !== savedDraftContent.trim()) {
                btnSave.style.display = 'inline-flex';
            } else {
                btnSave.style.display = 'none';
            }
        }

        textarea.addEventListener('input', checkDirty);
        textarea.addEventListener('change', checkDirty);
        textarea.addEventListener('keyup', checkDirty);

        // Save Draft Click Handler
        if (btnSave) {
            btnSave.addEventListener('click', function(e) {
                e.preventDefault();
                const content = textarea.value;
                const originalHtml = btnSave.innerHTML;
                btnSave.disabled = true;
                btnSave.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Saving...';

                fetch('{{ route("draft-remarks.save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        case_type: caseType,
                        case_id: caseId,
                        remarks: content
                    })
                })
                .then(res => res.json())
                .then(data => {
                    btnSave.disabled = false;
                    btnSave.innerHTML = originalHtml;
                    if (data.status === 'success') {
                        savedDraftContent = content;
                        btnSave.style.display = 'none';
                        badge.style.display = 'inline-flex';
                        const timeStr = data.saved_at ? ' (' + data.saved_at + ')' : '';
                        badgeText.innerHTML = '<i class="fas fa-check mr-1 text-success"></i> DRAFT SAVED' + timeStr;
                        badgeText.style.background = '#dcfce7';
                        badgeText.style.borderColor = '#86efac';
                        badgeText.style.color = '#166534';
                        
                        // Revert badge styling to warning after 2.5s
                        setTimeout(() => {
                            badgeText.innerHTML = '<i class="fas fa-file-alt mr-1"></i> DRAFT (PRIVATE TO YOU)';
                            badgeText.style.background = '#fef3c7';
                            badgeText.style.borderColor = '#f59e0b';
                            badgeText.style.color = '#92400e';
                        }, 2500);

                        if (typeof toastr !== 'undefined') {
                            toastr.success('Draft remarks saved privately to your account.', 'Draft Saved');
                        }
                    } else {
                        alert(data.message || 'Failed to save draft.');
                    }
                })
                .catch(err => {
                    btnSave.disabled = false;
                    btnSave.innerHTML = originalHtml;
                    console.error('Draft save error:', err);
                    alert('Error saving draft. Please try again.');
                });
            });
        }

        // Discard Draft Click Handler
        if (btnDiscard) {
            btnDiscard.addEventListener('click', function(e) {
                e.preventDefault();
                if (!confirm('Are you sure you want to discard your private draft remarks?')) {
                    return;
                }

                btnDiscard.disabled = true;
                btnDiscard.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                fetch('{{ route("draft-remarks.clear") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        case_type: caseType,
                        case_id: caseId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    btnDiscard.disabled = false;
                    btnDiscard.innerHTML = '<i class="fas fa-times mr-0.5"></i> Clear';
                    if (data.status === 'success') {
                        savedDraftContent = '';
                        textarea.value = '';
                        textarea.dispatchEvent(new Event('input', { bubbles: true }));
                        badge.style.display = 'none';
                        btnSave.style.display = 'none';
                        if (typeof toastr !== 'undefined') {
                            toastr.info('Draft remarks discarded.', 'Cleared');
                        }
                    }
                })
                .catch(err => {
                    btnDiscard.disabled = false;
                    btnDiscard.innerHTML = '<i class="fas fa-times mr-0.5"></i> Clear';
                    console.error('Draft clear error:', err);
                });
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDraftRemarks_{{ $uniqueUid }});
    } else {
        initDraftRemarks_{{ $uniqueUid }}();
    }
})();
</script>
