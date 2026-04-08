<style>
.trail-item { position: relative; padding-left: 25px; margin-bottom: 20px; border-left: 1px dashed rgba(255,255,255,0.1); }
.trail-dot { position: absolute; left: -6px; top: 0; width: 11px; height: 11px; border-radius: 50%; background: #007bff; border: 2px solid #080b0f; }
.trail-dot.bg-success { background: #28a745; }
.trail-dot.bg-danger { background: #dc3545; }
.trail-dot.bg-warning { background: #f39c12; }
.comment-box { background: rgba(255,255,255,0.03); border-radius: 6px; padding: 10px; margin-top: 8px; border-left: 3px solid rgba(255,255,255,0.1); display: none; }
.trail-role { font-family: 'Rajdhani', sans-serif; font-size: 11px; font-weight: 700; color: #8a96a3; display: block; }
.trail-action { font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 600; color: #fff; }
</style>

<div class="p-2">
    @forelse($purchase->decisions as $index => $decision)
        @php
            $act = $decision->pdec_action;
            $color = 'primary';
            $icon = 'paper-plane';
            if($act == 'approve') { $color = 'success'; $icon = 'check-double'; }
            if($act == 'return') { $color = 'danger'; $icon = 'undo'; }
            if($act == 'reject') { $color = 'danger'; $icon = 'ban'; $act = 'Not Approved'; }
        @endphp
        <div class="trail-item">
            <div class="trail-dot bg-{{$color}} shadow-sm"></div>
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="trail-role">{{ strtoupper($decision->pdec_role) }} • {{ $decision->account->acc_name }}</span>
                    <span class="trail-action">
                        {{ $act == 'Not Approved' ? 'Not Approved' : ucfirst($act).'ed' }} to 
                        <span class="text-{{$color}}">{{ $decision->pdec_to_status }}</span>
                    </span>
                </div>
                <div class="text-right">
                    <div class="text-xs text-muted">{{ \Carbon\Carbon::parse($decision->created_at)->diffForHumans() }}</div>
                    <button class="btn btn-link btn-xs p-0 mt-1" onclick="$(this).closest('.trail-item').find('.comment-box').slideToggle(200)">
                        <i class="fas fa-comment-dots text-{{$color}}" style="font-size: 14px;"></i>
                    </button>
                </div>
            </div>
            
            <div class="comment-box shadow-sm">
                <div class="text-xs text-white-50 mb-1 font-weight-bold">OFFICIAL REMARKS:</div>
                <div class="small {{ $decision->pdec_remarks ? 'text-muted' : 'text-danger italic' }}" style="line-height: 1.4;">
                    {{ $decision->pdec_remarks ?: 'No Comments' }}
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-4">
            <i class="fas fa-history text-muted mb-2" style="font-size: 24px; opacity: 0.2;"></i>
            <div class="text-muted small">No movement history recorded.</div>
        </div>
    @endforelse
</div>
