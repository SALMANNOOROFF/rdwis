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
    </style>

    <div class="container-fluid">
        
        <div class="row mb-3">
            <div class="col-md-6">
                <h5>Review MPR: <span class="text-primary">{{ $document->project->prj_title }}</span></h5>
            </div>
            <div class="col-md-6 text-right">
                @if($document->status == 'Returned')
                    <h5 class="text-danger font-weight-bold" style="font-size: 1rem;">
                        Status: Returned 
                        @if($document->latestVersion && $document->latestVersion->remarks)
                             <span class="text-dark ml-2 bg-warning px-2 rounded" style="font-size: 0.9em;">Reason: "{{ $document->latestVersion->remarks }}"</span>
                        @endif
                    </h5>
                @else
                    <h5 class="text-info font-weight-bold" style="font-size: 1rem;">Status: {{ $document->status }}</h5>
                @endif
            </div>
        </div>

        <div class="mpr-wrapper">
            
            {{-- LEFT SIDE: FORM --}}
            <div class="mpr-left">
                <form action="{{ route('sord.action') }}" method="POST">
                    @csrf
                    <input type="hidden" name="doc_id" value="{{ $document->doc_id }}">

                    <div class="card card-outline card-primary shadow-lg">
                        <div class="card-header d-flex justify-content-between align-items-center py-2">
                            <h6 class="card-title text-bold m-0"><i class="fas fa-edit mr-2"></i> Monthly Progress Report</h6>
                            
                            @if($isEditable)
                            <button type="button" id="edit-btn" class="btn btn-tool btn-sm" onclick="toggleEditMode()" title="Edit Report">
                                <i class="fas fa-pencil-alt text-primary"></i> <span class="text-primary font-weight-bold ml-1" style="font-size: 0.9rem;">Edit</span>
                            </button>
                            @endif
                        </div>
                        
                        <div class="card-body">
                            {{-- Field 1: MAIN REPORT --}}
                            <div class="form-group">
                                <label class="small text-muted text-uppercase font-weight-bold px-1">Main Report Content</label>
                                <textarea name="mpr_content" id="mpr_content" class="form-control bg-light" rows="15" readonly
                                          placeholder="Report content..."
                                          style="font-size: 0.95rem; line-height: 1.6; border: 1px solid #dee2e6;">{{ $latestVersion->content_data['mpr_content'] ?? '' }}</textarea>
                            </div>

                            <hr class="my-3">

                            {{-- Field 2: REVIEWER REMARKS --}}
                            <div class="form-group">
                                <label class="small text-muted text-uppercase font-weight-bold px-1">Reviewer Remarks</label>
                                <textarea name="remarks" id="remarks" class="form-control bg-light" rows="3" readonly
                                          placeholder="Add your remarks here...">{{ $sordVersion && $sordVersion->version_no > ($divisionVersion->version_no ?? 0) ? $sordVersion->remarks : '' }}</textarea>
                            </div>
                        </div>
                        
                        {{-- FOOTER BUTTONS --}}
                        <div class="card-footer bg-white border-top d-none" id="action-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <button type="submit" name="action" value="save" class="btn btn-sm btn-default shadow-sm border">
                                    <i class="fas fa-save mr-1"></i> Save Draft
                                </button>
                                
                                <div>
                                    <button type="button" class="btn btn-sm btn-secondary mr-2" onclick="toggleEditMode()">Cancel</button>
                                    <button type="submit" name="action" value="return" class="btn btn-sm btn-danger shadow-sm mr-2" onclick="return confirm('Return to Division?');">
                                        <i class="fas fa-undo mr-1"></i> Return
                                    </button>
                                    <button type="submit" name="action" value="forward" class="btn btn-sm btn-success shadow-sm" onclick="return confirm('Finalize and Lock?');">
                                        <i class="fas fa-check-circle mr-1"></i> Finalize
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- RIGHT SIDE: HISTORY --}}
            <div class="mpr-right">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="font-weight-bold m-0 text-dark small text-uppercase"><i class="fas fa-history mr-1"></i> Workflow History</h6>
                </div>

                <div class="history-scroll-box">
                    @if($document && $document->history->count() > 0)
                        @foreach($document->history as $log)
                            {{-- Dynamic Color based on Action --}}
                            @php
                                $borderClass = 'border-left-primary';
                                $bgClass = 'bg-white';
                                $icon = 'fa-paper-plane';

                                if($log->action_type == 'Returned') {
                                    $borderClass = 'border-left-danger';
                                    $bgClass = 'bg-light-danger';
                                    $icon = 'fa-undo';
                                } elseif($log->action_type == 'Forwarded MD' || $log->action_type == 'Approved' || $log->action_type == 'Finalized') {
                                    $borderClass = 'border-left-success';
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
                                        <span class="text-xs text-muted font-weight-bold text-uppercase" style="font-size: 0.7rem;">
                                            {{ $log->action_type }}
                                        </span>
                                        <span class="text-xs text-muted" style="font-size: 0.7rem;">
                                            {{ \Carbon\Carbon::parse($log->created_at)->format('d M, h:i A') }}
                                        </span>
                                    </div>
                                    
                                    <div class="mt-1">
                                        @if($log->fromUser)
                                            <div class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                                <i class="fas {{ $icon }} mr-1 text-secondary"></i>
                                                {{ $log->fromUser->role->rol_desig ?? 'Unknown Role' }}
                                            </div>
                                            <div class="text-xs text-muted ml-3 pl-1">{{ $log->fromUser->acc_username }}</div>
                                        @else
                                            <span class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                                <i class="fas {{ $icon }} mr-1 text-secondary"></i> System
                                            </span>
                                        @endif
                                    </div>

                                    @if($log->notes)
                                        <div class="mt-2 p-2 bg-white border rounded text-sm text-secondary font-italic" style="font-size: 0.8rem;">
                                            "{{ $log->notes }}"
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4 small">No history found.</div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    let isEditing = false;

    function toggleEditMode() {
        const mprField = document.getElementById('mpr_content');
        const remField = document.getElementById('remarks');
        const footer = document.getElementById('action-footer');
        const btnIcon = document.querySelector('#edit-btn i');
        const btnText = document.querySelector('#edit-btn span');

        isEditing = !isEditing;

        if(isEditing) {
            // Enable Editing
            mprField.removeAttribute('readonly');
            mprField.classList.remove('bg-light');
            mprField.classList.add('bg-white');
            mprField.focus();

            remField.removeAttribute('readonly');
            remField.classList.remove('bg-light');
            remField.classList.add('bg-white');

            footer.classList.remove('d-none');
            
            // UI Update
            if(btnIcon) btnIcon.className = 'fas fa-times text-danger';
            if(btnText) { btnText.innerText = 'Close'; btnText.className = 'text-danger font-weight-bold ml-1'; }
            
        } else {
            // Disable Editing
            mprField.setAttribute('readonly', 'true');
            mprField.classList.add('bg-light');
            mprField.classList.remove('bg-white');

            remField.setAttribute('readonly', 'true');
            remField.classList.add('bg-light');
            remField.classList.remove('bg-white');

            footer.classList.add('d-none');

            // UI Update
            if(btnIcon) btnIcon.className = 'fas fa-pencil-alt text-primary';
            if(btnText) { btnText.innerText = 'Edit'; btnText.className = 'text-primary font-weight-bold ml-1'; }
        }
    }
</script>
@endsection