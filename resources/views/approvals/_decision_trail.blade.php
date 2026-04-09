<style>
.trail-scroll-container { max-height: 350px; overflow-y: auto; padding-right: 5px; border: 1px solid rgba(255,255,255,0.05); border-radius: 8px; background: rgba(0,0,0,0.1); }
.trail-scroll-container::-webkit-scrollbar { width: 4px; }
.trail-scroll-container::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
.trail-item { position: relative; padding-left: 20px; margin-bottom: 15px; border-left: 1px dashed rgba(255,255,255,0.15); margin-left: 10px; }
.trail-dot { position: absolute; left: -6px; top: 2px; width: 11px; height: 11px; border-radius: 50%; background: #007bff; border: 2px solid #080b0f; z-index: 2; }
.trail-dot.bg-success { background: #28a745; }
.trail-dot.bg-danger { background: #dc3545; }
.trail-dot.bg-warning { background: #f39c12; }
.comment-box { background: rgba(255,255,255,0.04); border-radius: 6px; padding: 10px; margin-top: 6px; border-left: 3px solid rgba(255,255,255,0.1); display: block; }
.trail-role { font-family: 'Rajdhani', sans-serif; font-size: 10px; font-weight: 700; color: #8a96a3; display: block; letter-spacing: 0.5px; }
.trail-action { font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 600; color: #fff; line-height: 1.2; }
</style>

<div class="dg-panel-r mt-3">
    <div class="dg-panel-r-hdr py-2 px-3">
        <i class="fas fa-history text-accent" style="font-size: 12px;"></i>
        <span class="dg-panel-r-title" style="font-size: 11px;">Decision History (Latest First)</span>
    </div>
    <div class="p-2">
        <div class="trail-scroll-container p-2">
            @forelse($purchase->decisions->sortByDesc('created_at') as $index => $decision)
                @php
                    $act = $decision->pdec_action;
                    $color = 'primary';
                    if($act == 'approve') { $color = 'success'; }
                    if($act == 'return') { $color = 'warning'; }
                    if($act == 'reject' || $act == 'not_approved') { $color = 'danger'; $act = 'Not Approved'; }
                @endphp
                <div class="trail-item">
                    <div class="trail-dot bg-{{$color}} shadow-sm"></div>
                    <div class="d-flex justify-content-between align-items-start">
                        <div style="flex: 1;">
                            <span class="trail-role">{{ strtoupper($decision->pdec_role) }} • {{ $decision->account->acc_name }}</span>
                            <div class="d-flex align-items-center">
                                <span class="trail-action">
                                    {{ $act == 'Not Approved' ? 'Not Approved' : ucfirst($act).'ed' }} to 
                                    <span class="text-{{$color}}">{{ $decision->pdec_to_status }}</span>
                                </span>
                                @if($decision->pdec_remarks)
                                    <button class="btn btn-link btn-xs p-0 ml-2 text-muted" onclick="$(this).closest('.trail-item').find('.comment-box').slideToggle(150)" title="View Remarks">
                                        <i class="fas fa-comment-alt"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="text-right" style="min-width: 70px;">
                            <div style="font-size: 9px; color: rgba(255,255,255,0.4);">{{ \Carbon\Carbon::parse($decision->created_at)->format('d M, H:i') }}</div>
                        </div>
                    </div>
                    
                    @if($decision->pdec_remarks)
                    <div class="comment-box shadow-sm" style="display: none;">
                        <div class="small text-muted" style="line-height: 1.3; font-size: 11px;">
                            <i class="fas fa-quote-left mr-1 opacity-50" style="font-size: 9px;"></i>
                            {{ $decision->pdec_remarks }}
                        </div>
                    </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-4">
                    <i class="fas fa-history text-muted mb-2" style="font-size: 20px; opacity: 0.2;"></i>
                    <div class="text-muted small">No movement recorded.</div>
                </div>
            @endforelse
        </div>
    </div>
</div>
