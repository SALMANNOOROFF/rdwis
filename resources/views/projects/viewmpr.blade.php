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

            {{-- BACK BUTTON --}}
            <a href="{{ route('projects.show', $project->prj_id) }}" class="btn btn-sm btn-secondary mb-3 shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i> Back to Project Details
            </a>

            {{-- LOCK ALERT (If file is with SORD) --}}
            @if(!$isEditable && $document)
                <div class="alert alert-warning shadow-sm border-0">
                    <h5><i class="icon fas fa-lock"></i> File Locked!</h5>
                    This MPR is currently with <strong>{{ $document->currentOwner->acc_name ?? 'SORD' }}</strong> for review. You cannot edit it until it is returned.
                </div>
            @endif

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
                                    $latestData = $document && $document->latestVersion ? $document->latestVersion->content_data : [];
                                    $remarks = $document && $document->latestVersion ? $document->latestVersion->remarks : '';
                                @endphp

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Physical Progress (%) <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="number" name="physical_progress" class="form-control" 
                                                       value="{{ $latestData['physical_progress'] ?? '' }}" 
                                                       min="0" max="100" step="0.1" required
                                                       {{ !$isEditable ? 'disabled' : '' }}>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Financial Progress (Rs)</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">Rs.</span>
                                                </div>
                                                <input type="number" name="financial_progress" class="form-control" 
                                                       value="{{ $latestData['financial_progress'] ?? '' }}"
                                                       {{ !$isEditable ? 'disabled' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Work Description / Issues / Remarks <span class="text-danger">*</span></label>
                                    <textarea name="remarks" class="form-control" rows="8" 
                                              placeholder="Enter detailed progress update, issues, or remarks here..." required
                                              {{ !$isEditable ? 'disabled' : '' }}>{{ $remarks }}</textarea>
                                </div>

                                <div class="alert alert-light border mt-3">
                                   <small class="text-muted">
                                        <i class="fas fa-info-circle mr-1"></i> Author: 
                                        <strong>{{ Auth::user()->acc_name ?? Auth::user()->acc_username }}</strong>
                                   </small>
                                </div>
                            </div>
                            
                            {{-- FOOTER BUTTONS --}}
                            @if($isEditable)
                            <div class="card-footer bg-white border-top d-flex justify-content-between">
                                <button type="submit" name="action" value="save" class="btn btn-secondary shadow-sm">
                                    <i class="fas fa-save mr-1"></i> Save Draft
                                </button>
                                <button type="submit" name="action" value="forward" class="btn btn-success px-4 shadow-sm" onclick="return confirm('Are you sure you want to forward this to SORD? You will not be able to edit it until returned.');">
                                    <i class="fas fa-paper-plane mr-1"></i> Forward to SORD
                                </button>
                            </div>
                            @endif
                        </form>
                    </div>
                </div>

                {{-- RIGHT SIDE: HISTORY --}}
                <div class="mpr-right">
                    
                    {{-- Milestone Box (Kept from your old code) --}}
                    {{-- Assuming $currentMilestone is passed from Controller --}}
                    @if(isset($currentMilestone))
                    <div class="milestone-context-box mb-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="font-weight-bold m-0 text-dark"><i class="fas fa-crosshairs mr-1 text-warning"></i> Current Target</h6>
                            <span class="badge badge-warning text-white">{{ $currentMilestone->msn_status }}</span>
                        </div>
                        <p class="mb-2 text-dark font-weight-bold" style="font-size: 1.1rem; line-height: 1.2;">{{ Str::limit($currentMilestone->msn_desc, 60) }}</p>
                        <div class="d-flex justify-content-between text-muted small">
                            <span><i class="far fa-calendar-alt mr-1"></i> Target:</span>
                            <span class="font-weight-bold text-danger">{{ \Carbon\Carbon::parse($currentMilestone->msn_targetdt)->format('d M, Y') }}</span>
                        </div>
                    </div>
                    @endif

                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                            <h6 class="font-weight-bold text-secondary m-0">Version History</h6>
                            @if($document)
                                <span class="badge badge-light border">{{ $document->versions->count() }} Versions</span>
                            @endif
                        </div>

                        <div class="history-scroll-box">
                            @if($document && $document->versions->count() > 0)
                                @foreach($document->versions as $ver)
                                    <div class="history-item {{ $loop->first ? 'latest' : '' }}">
                                        
                                        {{-- COPY BUTTON --}}
                                        <button type="button" class="copy-btn shadow-sm" onclick="copyToClipboard(this, 'desc-{{ $ver->ver_id }}')" title="Copy Text">
                                            <i class="fas fa-copy"></i>
                                        </button>

                                        @if($loop->first)
                                            <div class="latest-badge"><i class="fas fa-star mr-1"></i> LATEST v{{ $ver->version_no }}</div>
                                        @endif

                                        <div class="d-flex justify-content-between mb-1 align-items-center">
                                            <span class="history-date text-primary">
                                                <i class="far fa-clock mr-1"></i> {{ \Carbon\Carbon::parse($ver->action_date)->format('d M, Y h:i A') }}
                                            </span>
                                            @if(!$loop->first)
                                                <span class="badge badge-secondary">v{{ $ver->version_no }}</span>
                                            @endif
                                        </div>
                                        
                                        {{-- Content Display --}}
                                        <div class="history-desc mt-2" id="desc-{{ $ver->ver_id }}">
                                            <strong>Physical:</strong> {{ $ver->content_data['physical_progress'] ?? '0' }}% | 
                                            <strong>Financial:</strong> Rs. {{ number_format((float)($ver->content_data['financial_progress'] ?? 0)) }}
                                            <hr class="my-1">
                                            {{ $ver->remarks }}
                                        </div>
                                        
                                        <div class="mt-2 text-right border-top pt-2">
                                            <small class="text-muted font-italic">Action by: <strong>{{ $ver->actor->acc_name ?? 'User' }}</strong></small>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-4 text-muted border rounded bg-white">
                                    <i class="fas fa-file-alt mb-2 fa-2x text-light"></i><br>No MPR history found.
                                </div>
                            @endif
                        </div>
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
            navigator.clipboard.writeText(textToCopy).then(() => {
                showCopiedFeedback(btn);
            }).catch(err => {
                fallbackCopyText(textToCopy, btn);
            });
        } else {
            fallbackCopyText(textToCopy, btn);
        }
    }

    function fallbackCopyText(text, btn) {
        var textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        textArea.style.left = "-9999px";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            showCopiedFeedback(btn);
        } catch (err) {
            alert('Copy failed manually select text.');
        }
        document.body.removeChild(textArea);
    }

    function showCopiedFeedback(btn) {
        var icon = btn.querySelector('i');
        icon.classList.remove('fa-copy');
        icon.classList.add('fa-check');
        icon.style.color = '#28a745';
        setTimeout(function() {
            icon.classList.remove('fa-check');
            icon.classList.add('fa-copy');
            icon.style.color = '';
        }, 2000);
    }
</script>
@endsection