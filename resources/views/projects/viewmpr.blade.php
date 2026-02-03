@extends('welcome')

@section('content')
<div class="content-wrapper pt-3">
    <style>
        /* Layout Styles */
        .mpr-wrapper { display: flex; gap: 20px; align-items: flex-start; }
        .mpr-left { flex: 1; }
        
        /* Width fixed to 480px */
        .mpr-right { flex: 0 0 480px; max-width: 480px; display: flex; flex-direction: column; gap: 15px; }
        
        .history-scroll-box { max-height: 600px; overflow-y: auto; padding-right: 8px; padding-left: 5px; padding-top: 5px; }
        
        /* History Card Standard */
        .history-item {
            background: #fff; border-left: 4px solid #17a2b8; border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 12px; padding: 15px; 
            transition: all 0.2s; position: relative;
        }
        .history-item:hover { transform: translateX(2px); box-shadow: 0 4px 8px rgba(0,0,0,0.08); }
        
        /* Latest Item Style */
        .history-item.latest {
            border-left: 6px solid #28a745;
            background-color: #fafffb;
            transform: scale(1.01);
            box-shadow: 0 6px 15px rgba(40, 167, 69, 0.15);
            margin-bottom: 20px;
            border: 1px solid #e1e4e8;
            border-left: 6px solid #28a745;
        }
        
        .latest-badge {
            font-size: 0.7rem; letter-spacing: 1px; text-transform: uppercase;
            background: #28a745; color: white; padding: 2px 8px; border-radius: 4px;
            margin-bottom: 8px; display: inline-block; font-weight: bold;
        }

        .history-date { font-size: 0.75rem; color: #6c757d; font-weight: 700; text-transform: uppercase; }
        .history-title { font-weight: 700; color: #343a40; font-size: 0.95rem; margin-bottom: 4px; }
        .history-desc { font-size: 0.85rem; color: #555; white-space: pre-wrap; line-height: 1.5; }
        
        /* Copy Button */
        .copy-btn {
            position: absolute; top: 10px; right: 10px;
            background: #f8f9fa; border: 1px solid #dee2e6; color: #6c757d;
            width: 32px; height: 32px; border-radius: 4px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: 0.2s; z-index: 10;
        }
        .copy-btn:hover { background: #e2e6ea; color: #007bff; border-color: #007bff; }
        .copy-btn:active { transform: scale(0.95); }

        .milestone-context-box { background: #fff; border-top: 4px solid #ffc107; border-radius: 4px; padding: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    </style>

    <section class="content">
        <div class="container-fluid">

            {{-- HEADER: BACK BUTTON LOGIC --}}
            <div class="row mb-3">
                <div class="col-6">
                    @if(Auth::user()->isSORD())
                        <a href="{{ route('sord.inbox') }}" class="btn btn-sm btn-secondary shadow-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Inbox
                        </a>
                    @else
                        <a href="{{ route('projects.show', $project->prj_id) }}" class="btn btn-sm btn-secondary shadow-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Project Details
                        </a>
                    @endif
                </div>
                <div class="col-6 text-right">
                    @if($document)
                        <span class="badge badge-info p-2">Ref: {{ $document->doc_ref_no ?? $document->doc_id }}</span>
                    @endif
                </div>
            </div>

            {{-- STATUS LINE (Simple Text) --}}
            <div class="mb-3">
                @if(isset($isReturned) && $isReturned)
                    <h5 class="text-danger font-weight-bold">
                        <i class="fas fa-exclamation-circle mr-1"></i> Returned by {{ $document->latestVersion->actor->role->rol_desig ?? 'Reviewer' }}: 
                        <span class="bg-warning px-2 rounded text-dark" style="font-size: 0.9em;">"{{ $document->latestVersion->remarks }}"</span>
                    </h5>
                @elseif(!$isEditable && $document)
                    <h5 class="text-info font-weight-bold">
                        <i class="fas fa-lock mr-1"></i> Status: {{ $document->status }} (Currently with {{ $document->currentOwner->role->rol_desig ?? ($document->currentOwner->acc_name ?? 'SORD') }})
                    </h5>
                @endif
            </div>

            <div class="mpr-wrapper">
                
                {{-- LEFT SIDE: FORM --}}
                <div class="mpr-left">
                    <div class="card card-success card-outline shadow">
                        <div class="card-header">
                            <h3 class="card-title text-bold"><i class="fas fa-edit mr-2"></i> Prepare Monthly Report</h3>
                            <div class="card-tools">
                                <span class="badge badge-light border">
                                    Status: {{ $document->status ?? 'New' }}
                                </span>
                            </div>
                        </div>
                        
                        <form action="{{ route('mpr.store', $project->prj_id) }}" method="POST">
                            @csrf
                            <div class="card-body">
                                
                                {{-- Retrieve Latest Data --}}
                                @php
                                    $latestVer = $document ? $document->latestVersion : null;
                                    $latestData = $latestVer ? $latestVer->content_data : [];
                                    $remarks = $latestVer ? $latestVer->remarks : '';

                                    // FIX: If Returned and Content is Empty, fetch from Previous Version (Division's Draft)
                                    if(isset($isReturned) && $isReturned && empty($latestData['mpr_content'])) {
                                        $prevVer = $document->versions->take(2)->last(); 
                                        if($prevVer && $prevVer->ver_id != $latestVer->ver_id) {
                                             $latestData['mpr_content'] = $prevVer->content_data['mpr_content'] ?? '';
                                        }
                                    }
                                @endphp

                                <div class="form-group">
                                    <label>MPR Main Report <span class="text-danger">*</span></label>
                                    <textarea name="mpr_content" class="form-control font-weight-bold" rows="12" 
                                              placeholder="Enter the detailed Monthly Progress Report here..." required
                                              {{ !$isEditable ? 'disabled' : '' }}>{{ $latestData['mpr_content'] ?? '' }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label>Remarks / Issues (Optional)</label>
                                    <textarea name="remarks" class="form-control" rows="3" 
                                              placeholder="Any additional remarks or issues..."
                                              {{ !$isEditable ? 'disabled' : '' }}>{{ $remarks }}</textarea>
                                </div>

                                <div class="alert alert-light border mt-3">
                                   <small class="text-muted">
                                        <i class="fas fa-info-circle mr-1"></i> Current User: 
                                        <strong>{{ Auth::user()->role->rol_desig ?? Auth::user()->acc_username }}</strong>
                                   </small>
                                </div>
                            </div>
                            
                            {{-- FOOTER BUTTONS (Dynamic based on Role) --}}
                            @if($isEditable)
                            <div class="card-footer bg-white border-top">
                                <div class="d-flex justify-content-between align-items-center">
                                    
                                    {{-- Save Draft (Everyone) --}}
                                    <button type="submit" name="action" value="save" class="btn btn-secondary shadow-sm">
                                        <i class="fas fa-save mr-1"></i> Save Draft
                                    </button>

                                    <div class="d-flex gap-2">
                                        {{-- LOGIC: Are we SORD or Division? --}}
                                        @if(Auth::user()->isSORD())
                                            
                                            {{-- SORD Actions --}}
                                            <button type="submit" name="action" value="return" class="btn btn-warning text-white shadow-sm mr-2" 
                                                onclick="return confirm('Return this document to Division for correction?');">
                                                <i class="fas fa-undo-alt mr-1"></i> Return to Division
                                            </button>
                                            <button type="button" class="btn btn-dark shadow-sm" disabled title="Coming Soon">
                                                Forward to MD <i class="fas fa-arrow-right ml-1"></i>
                                            </button>

                                        @else
                                            
                                            {{-- Division Actions --}}
                                            <button type="submit" name="action" value="forward" class="btn btn-success px-4 shadow-sm" 
                                                onclick="return confirm('Are you sure you want to forward this to SORD? You will not be able to edit it until returned.');">
                                                <i class="fas fa-paper-plane mr-1"></i> Forward to SORD
                                            </button>

                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                        </form>
                    </div>
                </div>

               
{{-- RIGHT SIDE HISTORY SECTION KO IS SE REPLACE KAREIN --}}

<div class="mpr-right">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="font-weight-bold m-0 text-dark"><i class="fas fa-history mr-1"></i> Workflow History</h6>
    </div>

    <div class="history-scroll-box">
        @if($document && $document->history->count() > 0)
            @foreach($document->history as $log)
                
                {{-- Dynamic Color based on Action --}}
                @php
                    $borderClass = 'border-left-primary'; // Default Blue
                    $bgClass = 'bg-white';
                    $icon = 'fa-paper-plane';

                    if($log->action_type == 'Returned') {
                        $borderClass = 'border-left-danger'; // Red for Return
                        $bgClass = 'bg-light-danger'; // Light Red BG
                        $icon = 'fa-undo';
                    } elseif($log->action_type == 'Forwarded MD' || $log->action_type == 'Approved') {
                        $borderClass = 'border-left-success'; // Green for Final
                        $bgClass = 'bg-light-success';
                        $icon = 'fa-check-circle';
                    }
                @endphp

                <style>
                    .border-left-primary { border-left: 4px solid #007bff; }
                    .border-left-danger { border-left: 4px solid #dc3545; }
                    .border-left-success { border-left: 4px solid #28a745; }
                    .bg-light-danger { background-color: #fff5f5; }
                    .bg-light-success { background-color: #f0fff4; }
                </style>

                <div class="card shadow-sm mb-2 {{ $borderClass }} {{ $bgClass }}">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between">
                            <span class="text-xs text-muted font-weight-bold text-uppercase">
                                {{ $log->action_type }}
                            </span>
                            <span class="text-xs text-muted">
                                {{ \Carbon\Carbon::parse($log->created_at)->format('d M, h:i A') }}
                            </span>
                        </div>
                        
                        <div class="mt-1">
                            <span class="font-weight-bold text-dark" style="font-size: 0.9rem;">
                                <i class="fas {{ $icon }} mr-1 text-secondary"></i>
                                {{-- FROM: Sender Role --}}
                                {{ $log->sender->role->rol_desig ?? ($log->sender->acc_name ?? 'System') }}
                            </span>
                            
                            <i class="fas fa-arrow-right mx-1 text-muted text-xs"></i>
                            
                            <span class="font-weight-bold text-dark" style="font-size: 0.9rem;">
                                {{-- TO: Receiver Role --}}
                                @if($log->to_user_id == 0)
                                    MD / Final
                                @else
                                    {{ $log->receiver->role->rol_desig ?? 'User' }}
                                @endif
                            </span>
                        </div>

                        @if($log->notes)
                            <div class="mt-2 p-2 bg-white border rounded text-sm text-secondary font-italic">
                                "{{ $log->notes }}"
                            </div>
                        @endif
                    </div>
                </div>

            @endforeach
        @else
            <div class="text-center text-muted py-4">No history found.</div>
        @endif
    </div>
</div>
            </div>
        </div>
    </section>
</div>

{{-- ROBUST COPY SCRIPT --}}
<script>
    function copyToClipboard(btn, elementId) {
        var textToCopy = document.getElementById(elementId).innerText;
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(textToCopy).then(() => { showCopiedFeedback(btn); }).catch(err => { fallbackCopyText(textToCopy, btn); });
        } else { fallbackCopyText(textToCopy, btn); }
    }
    function fallbackCopyText(text, btn) {
        var textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed"; textArea.style.left = "-9999px";
        document.body.appendChild(textArea);
        textArea.focus(); textArea.select();
        try { document.execCommand('copy'); showCopiedFeedback(btn); } catch (err) { alert('Copy failed.'); }
        document.body.removeChild(textArea);
    }
    function showCopiedFeedback(btn) {
        var icon = btn.querySelector('i');
        icon.classList.remove('fa-copy'); icon.classList.add('fa-check'); icon.style.color = '#28a745';
        setTimeout(function() { icon.classList.remove('fa-check'); icon.classList.add('fa-copy'); icon.style.color = ''; }, 2000);
    }
</script>
@endsection