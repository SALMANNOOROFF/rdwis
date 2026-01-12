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
        .history-item.latest .history-desc {
            font-size: 1rem; color: #222; -webkit-line-clamp: 10;
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

            <a href="{{ route('projects.show', $project->prj_id) }}" class="btn btn-sm btn-secondary mb-3 shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i> Back to Project Details
            </a>

            <div class="mpr-wrapper">
                
                {{-- LEFT SIDE --}}
                <div class="mpr-left">
                    <div class="card card-success card-outline shadow">
                        <div class="card-header">
                            <h3 class="card-title text-bold"><i class="fas fa-edit mr-2"></i> Prepare Monthly Report</h3>
                        </div>
                        
                        <form action="{{ route('mpr.store', $project->prj_id) }}" method="POST">
                            @csrf
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Report Date <span class="text-danger">*</span></label>
                                    <input type="date" name="pgh_dtg" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="form-group">
                                    <label>Work Description <span class="text-danger">*</span></label>
                                    <textarea name="pgh_progress" class="form-control" rows="10" placeholder="Enter detailed progress update here..." required></textarea>
                                </div>
                                <div class="alert alert-light border mt-3">
                                   <small class="text-muted">
                                        <i class="fas fa-info-circle mr-1"></i> Author: 
                                        <strong>{{ Auth::user()->role->rol_desigshort ?? Auth::user()->acc_username }}</strong>
                                    </small>
                                </div>
                            </div>
                            <div class="card-footer text-right bg-white border-top">
                                <button type="submit" class="btn btn-success px-4 shadow-sm">
                                    <i class="fas fa-paper-plane mr-1"></i> Submit Report
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- RIGHT SIDE --}}
                <div class="mpr-right">
                    
                    <div class="milestone-context-box">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="font-weight-bold m-0 text-dark"><i class="fas fa-crosshairs mr-1 text-warning"></i> Current Target</h6>
                            @if($currentMilestone)
                                <span class="badge badge-warning text-white">{{ $currentMilestone->msn_status }}</span>
                            @else
                                <span class="badge badge-secondary">No Active Target</span>
                            @endif
                        </div>
                        @if($currentMilestone)
                            <p class="mb-2 text-dark font-weight-bold" style="font-size: 1.1rem; line-height: 1.2;">{{ Str::limit($currentMilestone->msn_desc, 60) }}</p>
                            <div class="d-flex justify-content-between text-muted small">
                                <span><i class="far fa-calendar-alt mr-1"></i> Target:</span>
                                <span class="font-weight-bold text-danger">{{ \Carbon\Carbon::parse($currentMilestone->msn_targetdt)->format('d M, Y') }}</span>
                            </div>
                        @else
                            <p class="text-muted small">No pending milestones found.</p>
                        @endif
                    </div>

                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                            <h6 class="font-weight-bold text-secondary m-0">Report History</h6>
                            <span class="badge badge-light border">{{ $mprHistory->count() }} Records</span>
                        </div>

                        <div class="history-scroll-box">
                            @forelse($mprHistory as $history)
                                <div class="history-item {{ $loop->first ? 'latest' : '' }}">
                                    
                                    {{-- NEW ROBUST COPY BUTTON --}}
                                    <button type="button" class="copy-btn shadow-sm" onclick="copyToClipboard(this, 'desc-{{ $history->pgh_id }}')" title="Copy Text">
                                        <i class="fas fa-copy"></i>
                                    </button>

                                    @if($loop->first)
                                        <div class="latest-badge"><i class="fas fa-star mr-1"></i> LATEST UPDATE</div>
                                    @endif

                                    <div class="d-flex justify-content-between mb-1 align-items-center">
                                        <span class="history-date text-primary">
                                            <i class="far fa-clock mr-1"></i> {{ \Carbon\Carbon::parse($history->pgh_dtg)->format('d M, Y') }}
                                        </span>
                                    </div>
                                    
                                    <div class="history-title">Progress Report</div>
                                    
                                    <div class="history-desc" id="desc-{{ $history->pgh_id }}">{{ $history->pgh_progress }}</div>
                                    
                                    <div class="mt-2 text-right border-top pt-2">
                                        <small class="text-muted font-italic">Author: <strong>{{ $history->pgh_author }}</strong></small>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted border rounded bg-white">
                                    <i class="fas fa-folder-open mb-2"></i><br>No reports submitted yet.
                                </div>
                            @endforelse
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
        // 1. Get the text
        var textToCopy = document.getElementById(elementId).innerText;
        
        // 2. Logic to handle HTTP vs HTTPS (IP Address Issue Fix)
        if (navigator.clipboard && window.isSecureContext) {
            // Secure method (HTTPS/Localhost)
            navigator.clipboard.writeText(textToCopy).then(() => {
                showCopiedFeedback(btn);
            }).catch(err => {
                fallbackCopyText(textToCopy, btn);
            });
        } else {
            // Fallback method (For HTTP / IP Addresses)
            fallbackCopyText(textToCopy, btn);
        }
    }

    function fallbackCopyText(text, btn) {
        // Create a temporary text area
        var textArea = document.createElement("textarea");
        textArea.value = text;
        
        // Ensure it's not visible but part of DOM
        textArea.style.position = "fixed";
        textArea.style.left = "-9999px";
        document.body.appendChild(textArea);
        
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            showCopiedFeedback(btn);
        } catch (err) {
            console.error('Fallback: Oops, unable to copy', err);
            alert('Copy failed manually select text.');
        }
        
        document.body.removeChild(textArea);
    }

    function showCopiedFeedback(btn) {
        var icon = btn.querySelector('i');
        
        // Remove Copy Icon
        icon.classList.remove('fa-copy');
        // Add Check Icon
        icon.classList.add('fa-check');
        icon.style.color = '#28a745';
        
        // Revert after 2 seconds
        setTimeout(function() {
            icon.classList.remove('fa-check');
            icon.classList.add('fa-copy');
            icon.style.color = '';
        }, 2000);
    }
</script>
@endsection