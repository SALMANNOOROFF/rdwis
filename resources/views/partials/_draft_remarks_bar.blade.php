@php
    $cType = $caseType ?? 'purchase';
    $cId = (int)($caseId ?? 0);
    $target = $targetTextarea ?? '#inlineRemarks';
    $currUserAccId = (int)(Auth::user()?->acc_id ?? Auth::id() ?? 0);
    $existingDraft = $currUserAccId ? \App\Models\UserCaseDraftRemark::getDraft($currUserAccId, $cType, $cId) : null;
    $uniqueUid = 'dr_' . $cType . '_' . $cId . '_' . substr(md5($target), 0, 4);
@endphp

<div class="draft-remarks-container d-inline-flex align-items-center" 
     id="{{ $uniqueUid }}" 
     data-case-type="{{ $cType }}" 
     data-case-id="{{ $cId }}" 
     data-target="{{ $target }}" 
     data-initial-draft="{{ e($existingDraft ?? '') }}" 
     style="gap: 5px;">

    {{-- Save Draft Button (Only visible when user writes/edits remarks) --}}
    <button type="button" 
            class="btn btn-xs btn-outline-primary font-weight-bold btn-save-draft rajdhani" 
            id="{{ $uniqueUid }}_btnSave" 
            style="display: none; font-size: 11px; padding: 2px 8px; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" 
            title="Save your remarks as draft">
        <i class="fas fa-save mr-1"></i> Save Draft
    </button>

    {{-- Saved Status Indicator --}}
    <span id="{{ $uniqueUid }}_status" 
          class="badge badge-success px-2 py-0.5 rajdhani font-weight-bold" 
          style="display: none; font-size: 10.5px; border-radius: 4px;">
        <i class="fas fa-check mr-1"></i> Saved
    </span>

    {{-- Clear Button (Only visible when remarks exist) --}}
    <button type="button" 
            class="btn btn-xs btn-outline-danger font-weight-bold btn-clear-draft rajdhani" 
            id="{{ $uniqueUid }}_btnClear" 
            style="display: none; font-size: 11px; padding: 2px 8px; border-radius: 4px;" 
            title="Clear remarks">
        <i class="fas fa-times mr-1"></i> Clear
    </button>
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
        const btnClear = document.getElementById('{{ $uniqueUid }}_btnClear');
        const statusSpan = document.getElementById('{{ $uniqueUid }}_status');

        let savedDraftContent = wrap.getAttribute('data-initial-draft') || '';

        // Prepopulate textarea if draft exists and textarea is currently empty
        if (savedDraftContent && !textarea.value.trim()) {
            textarea.value = savedDraftContent;
        }

        // Update button visibility based on textarea content:
        // - If empty: ALL buttons hidden (wesy hi na aya hua hu)
        // - If has text:
        //     - Clear button is shown
        //     - If text differs from savedDraftContent, Save Draft button is shown
        function updateVisibility() {
            const currentVal = textarea.value.trim();
            if (currentVal.length === 0) {
                btnSave.style.display = 'none';
                btnClear.style.display = 'none';
                statusSpan.style.display = 'none';
            } else {
                btnClear.style.display = 'inline-flex';
                if (currentVal !== savedDraftContent.trim()) {
                    btnSave.style.display = 'inline-flex';
                    statusSpan.style.display = 'none';
                } else {
                    btnSave.style.display = 'none';
                }
            }
        }

        textarea.addEventListener('input', updateVisibility);
        textarea.addEventListener('change', updateVisibility);
        textarea.addEventListener('keyup', updateVisibility);

        // Initial check
        updateVisibility();

        // Save Draft Click
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
                        statusSpan.style.display = 'inline-flex';
                        statusSpan.innerHTML = '<i class="fas fa-check mr-1"></i> Saved';
                        setTimeout(() => {
                            statusSpan.style.display = 'none';
                        }, 2000);
                    }
                })
                .catch(err => {
                    btnSave.disabled = false;
                    btnSave.innerHTML = originalHtml;
                    console.error('Draft save error:', err);
                });
            });
        }

        // Clear Click
        if (btnClear) {
            btnClear.addEventListener('click', function(e) {
                e.preventDefault();
                textarea.value = '';
                savedDraftContent = '';
                updateVisibility();

                // Clear from backend
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
                }).catch(err => console.error('Draft clear error:', err));
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
