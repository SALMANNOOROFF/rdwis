@extends('welcome')

@section('content')
<div class="content-wrapper pt-3 pb-4">
    
    {{-- Top Header --}}
    <div class="content-header pb-2">
        <div class="container-fluid">
            <div class="row align-items-center mb-2">
                <div class="col-12 d-flex justify-content-between align-items-center flex-wrap" style="gap: 15px;">
                    <h1 class="m-0 font-weight-bold" style="color: var(--rd-accent); font-family: 'Rajdhani', sans-serif;">
                        <i class="fas fa-file-signature mr-2"></i> Review MPR: {{ $document->project->prj_code ?? 'N/A' }}
                    </h1>
                    <div class="d-flex gap-2">
                        <a href="{{ route('sord.all_projects') }}" class="btn btn-outline-secondary btn-sm rounded-pill shadow-sm px-3">
                            <i class="fas fa-arrow-left mr-1"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            
            {{-- ALERTS --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible shadow-sm">
                    <button class="close" data-dismiss="alert">&times;</button>
                    <h5><i class="icon fas fa-check"></i> Success!</h5>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible shadow-sm">
                    <button class="close" data-dismiss="alert">&times;</button>
                    <h5><i class="icon fas fa-ban"></i> Error!</h5>
                    {{ session('error') }}
                </div>
            @endif

            <div class="row">
                {{-- /// LEFT COLUMN: Document & Actions /// --}}
                <div class="col-md-8">
                    
                    {{-- 1. Notification Badge / Header --}}
                    @php
                        $divisionName = $document->creator->unit->unt_name ?? 'Unknown Division';
                        $creatorName = $document->creator->acc_username ?? 'Unknown User';
                    @endphp
                    <div class="alert alert-primary shadow-sm mb-3 border-0">
                        <div class="d-flex align-items-center flex-wrap" style="gap: 15px;">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-paper-plane fa-2x mr-3 opacity-50"></i>
                                <div>
                                    <h6 class="mb-0 font-weight-bold">Submitted by: <span class="text-white">{{ $divisionName }}</span></h6>
                                    <small class="text-white opacity-75">Sent by user: <strong>{{ $creatorName }}</strong></small>
                                </div>
                            </div>
                            <div class="ml-auto-lg">
                                <span class="badge badge-light px-3 py-2 shadow-sm rounded-pill text-primary" style="font-size: 0.85rem;">
                                    <i class="fas fa-file-invoice mr-1"></i> {{ $document->project->prj_title ?? 'Project' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- 2. MPR Content Area (Editable) --}}
                    <div class="card shadow-sm border-0 mb-3 rounded-lg" style="background: var(--rd-surface);">
                        <div class="card-header border-bottom-0 pt-3 pb-0">
                            <h5 class="card-title font-weight-bold" style="color: var(--rd-text1);">
                                <i class="fas fa-stream text-primary mr-2"></i> MPR Report Content
                            </h5>
                        </div>
                        <div class="card-body position-relative pt-1">
                            @if($isEditable)
                            <button type="button" class="btn btn-sm btn-outline-primary shadow-sm rounded-circle position-absolute" id="editMprBtn" title="Edit Content" style="width: 32px; height: 32px; right: 20px; top: -5px; z-index: 10;">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            @endif
                            @php
                                $dData = $latestVersion->content_data ?? [];
                                $content = $dData['mpr_content'] ?? ($dData['physical_progress'] ?? 'No text available.');
                                $mprMonth = $dData['mpr_month'] ?? 'N/A';
                            @endphp
                            
                            <div class="mb-3 p-2 bg-white rounded shadow-sm d-inline-block border">
                                <span class="font-weight-bold text-dark"><i class="fas fa-calendar-alt text-primary mr-1"></i> Report Month:</span> 
                                <span class="badge badge-primary px-2 py-1 ml-1" style="font-size: 0.95rem;">
                                    {{ $mprMonth != 'N/A' ? \Carbon\Carbon::parse($mprMonth)->format('F, Y') : 'N/A' }}
                                </span>
                            </div>

                            {{-- View Mode --}}
                            <div id="mprViewMode" class="p-0 border-0" style="color: var(--rd-text1); min-height: 150px; font-size: 1.05rem; white-space: pre-wrap; line-height: 1.6;">{{ $content }}</div>

                            {{-- Edit Mode Form --}}
                            <form action="{{ route('sord.action') }}" method="POST" id="mprEditForm" style="display: none;">
                                @csrf
                                <input type="hidden" name="doc_id" value="{{ $document->doc_id }}">
                                <input type="hidden" name="action" value="save"> {{-- Save action for edit --}}

                                <textarea name="mpr_content" id="mprTextarea" class="form-control mb-3 shadow-sm" rows="12" style="font-size: 1.05rem; width: 100%;">{{ $content }}</textarea>
                                
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-secondary btn-sm mr-2 rounded-pill px-3" id="cancelEditBtn">Cancel</button>
                                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm"><i class="fas fa-save mr-1"></i> Save Changes</button>
                                </div>
                            </form>
                            
                            {{-- Director/Division Comments --}}
                            @php
                                $divRemarks = $divisionVersion->remarks ?? '';
                            @endphp
                            @if($divRemarks)
                            <div class="mt-4 p-3 rounded" style="background: var(--rd-warning-soft); border-left: 4px solid var(--rd-warning);">
                                <h6 class="font-weight-bold" style="color: var(--rd-text1);"><i class="fas fa-comment-dots text-warning mr-2"></i> Division / Director's Comments:</h6>
                                <p class="mb-0 text-muted font-italic" style="font-size: 0.95rem;">"{{ $divRemarks }}"</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- 3. Action Buttons --}}
                    @if($isEditable)
                    <div class="card border-0 shadow-sm rounded-lg" style="background: var(--rd-surface);">
                        <div class="card-body py-3 d-flex justify-content-between align-items-center flex-wrap" style="gap: 15px;">
                            
                            {{-- Return Button (Opens Modal) --}}
                            <button type="button" class="btn btn-danger rounded-pill px-4 shadow-sm font-weight-bold" data-toggle="modal" data-target="#returnModal">
                                <i class="fas fa-undo mr-2"></i> Return
                            </button>

                            <div class="d-flex gap-2 flex-wrap">
                                {{-- Finalize Button --}}
                                <form action="{{ route('sord.action') }}" method="POST" class="m-0">
                                    @csrf
                                    <input type="hidden" name="doc_id" value="{{ $document->doc_id }}">
                                    <input type="hidden" name="action" value="forward">
                                    <button type="submit" class="btn btn-success rounded-pill px-4 shadow font-weight-bold" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-check-circle mr-2"></i> Finalize
                                    </button>
                                </form>

                                {{-- Forward to MD (Disabled) --}}
                                <button type="button" class="btn btn-secondary rounded-pill px-4 shadow font-weight-bold" disabled title="Pending feature">
                                    Forward to MD <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>

                        </div>
                    </div>
                    @else
                        <div class="alert alert-info shadow-sm rounded-pill text-center mt-3">
                            <i class="fas fa-lock mr-2"></i> This document is currently <strong>{{ $document->status }}</strong> and locked for actions.
                        </div>
                    @endif

                </div>

                {{-- /// RIGHT COLUMN: EXACT SAME HISTORY FROM DIVISION /// --}}
                <div class="col-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="font-weight-bold m-0 text-white"><i class="fas fa-history mr-1"></i> Workflow History</h5>
                    </div>

                    <div class="history-scroll-box" style="max-height: 600px; overflow-y: auto; padding-right: 8px;">
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
                                    } elseif($log->action_type == 'Finalized' || $log->action_type == 'Forwarded to MD') {
                                        $borderClass = 'border-left-success'; // Green for Final
                                        $bgClass = 'bg-light-success';
                                        $icon = 'fa-check-circle';
                                    }
                                @endphp

                                <style>
                                    .border-left-primary { border-left: 4px solid var(--rd-accent); }
                                    .border-left-danger { border-left: 4px solid var(--rd-danger); }
                                    .border-left-success { border-left: 4px solid var(--rd-success); }
                                    .bg-light-danger { background-color: var(--rd-danger-soft); }
                                    .bg-light-success { background-color: var(--rd-success-soft); }
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
                                                {{ $log->fromUser->role->rol_desig ?? ($log->fromUser->acc_username ?? 'System') }}
                                            </span>
                                            
                                            <i class="fas fa-arrow-right mx-1 text-muted text-xs"></i>
                                            
                                            <span class="font-weight-bold text-dark" style="font-size: 0.9rem;">
                                                {{-- TO: Receiver Role --}}
                                                @if($log->to_user_id == 0 || $log->action_type == 'Finalized')
                                                    MD / Final
                                                @else
                                                    {{ $log->toUser->role->rol_desig ?? ($log->toUser->acc_username ?? 'User') }}
                                                @endif
                                            </span>
                                        </div>

                                        @if($log->notes)
                                            <div class="mt-2 p-2 rounded text-sm text-secondary font-italic" style="background: var(--rd-surface2); border: 1px solid var(--rd-border);">
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
    </div>
</div>

{{-- MODAL FOR RETURN COMMENTS --}}
<div class="modal fade" id="returnModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg rounded-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-undo mr-2"></i> Return to Division</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('sord.action') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <input type="hidden" name="doc_id" value="{{ $document->doc_id }}">
                    <input type="hidden" name="action" value="return">
                    
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-dark mb-2">Resubmission Comments / Reason for Return <span class="text-danger">*</span></label>
                        <textarea name="remarks" class="form-control shadow-sm" rows="4" placeholder="Enter instructions for the division so they can correct and resubmit..." required autofocus></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm"><i class="fas fa-paper-plane mr-2"></i> Confirm Return</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- JAVASCRIPT FOR EDIT TOGGLE --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editBtn = document.getElementById('editMprBtn');
    const cancelBtn = document.getElementById('cancelEditBtn');
    const viewMode = document.getElementById('mprViewMode');
    const editForm = document.getElementById('mprEditForm');
    const textarea = document.getElementById('mprTextarea');

    if(editBtn) {
        editBtn.addEventListener('click', function() {
            viewMode.style.display = 'none';
            editForm.style.display = 'block';
            textarea.focus();
            
            // Move cursor to end
            const val = textarea.value;
            textarea.value = '';
            textarea.value = val;
        });
    }

    if(cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            editForm.style.display = 'none';
            viewMode.style.display = 'block';
        });
    }
});
</script>

<style>
    .shadow-inner { box-shadow: inset 0 2px 4px rgba(0,0,0,0.05); }
    .timeline { margin: 0 0 15px; }
    .timeline>div>.timeline-item { box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075); margin-bottom: 20px; }
    .timeline>div>i { width: 35px; height: 35px; font-size: 15px; line-height: 35px; top: 0; left: 18px; }
</style>
@endsection
