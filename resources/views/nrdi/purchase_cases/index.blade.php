@extends('welcome')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600&display=swap');

.scrutiny-hub { font-family: 'Inter', sans-serif; background: #080b0f; min-height: 100vh; color: #cbd5e0; }
.rajdhani { font-family: 'Rajdhani', sans-serif; letter-spacing: 0.5px; }

/* Dashboard Header & Navigation */
.hub-header { background: #0f161e; padding: 20px 30px; border-bottom: 1px solid rgba(255,255,255,0.05); }
.div-pill { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 8px 16px; color: #8a96a3; font-weight: 600; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 8px; }
.div-pill.active { background: #111a24; border-color: #3b82f6; color: #fff; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15); }
.div-pill:hover:not(.active) { background: rgba(255,255,255,0.06); }
.div-badge { background: #ef4444; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 10px; font-weight: bold; }

/* Tabs Logic */
.hub-tabs { border-bottom: 1px solid rgba(255,255,255,0.05); margin-bottom: 20px; }
.hub-tab-link { padding: 12px 24px; color: #64748b; font-weight: 600; font-size: 13px; text-decoration: none !important; border-bottom: 2px solid transparent; transition: all 0.2s; display: flex; align-items: center; gap: 8px; background: transparent; border-top: none; border-left: none; border-right: none; }
.hub-tab-link:hover { color: #fff; }
.hub-tab-link.active { color: #3b82f6; border-bottom-color: #3b82f6; }

/* Table Styling */
.hub-table { width: 100%; border-collapse: separate; border-spacing: 0 4px; }
.hub-table th { font-family: 'Rajdhani', sans-serif; font-size: 10px; text-transform: uppercase; letter-spacing: 1.2px; color: #4b5563; padding: 12px 16px; font-weight: 700; border: none !important; }
.hub-row { background: #0d1218; transition: transform 0.2s, background 0.2s; }
.hub-row:hover { background: #111a24; transform: scale(1.002); }
.hub-row td { padding: 14px 16px; border-top: 1px solid rgba(255,255,255,0.02) !important; border-bottom: 1px solid rgba(255,255,255,0.02) !important; vertical-align: middle; }
.hub-row td:first-child { border-left: 1px solid rgba(255,255,255,0.02) !important; border-radius: 8px 0 0 8px; }
.hub-row td:last-child { border-right: 1px solid rgba(255,255,255,0.02) !important; border-radius: 0 8px 8px 0; }

/* Type & Status Badges */
.type-badge { width: 26px; height: 26px; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: bold; color: #fff; text-transform: uppercase; }
.type-ps { background: rgba(59, 130, 246, 0.2); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.3); }
.type-pt { background: rgba(59, 130, 246, 0.2); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.3); }
.status-pill { background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; border: 1px solid rgba(245, 158, 11, 0.2); display: inline-flex; align-items: center; gap: 4px; }
.status-pill i { font-size: 10px; }

.text-amount { font-family: 'Rajdhani', sans-serif; font-size: 16px; font-weight: 600; color: #fff; }
.text-ref { font-size: 10px; color: #4b5563; font-weight: 500; }
.view-btn { background: #0d1218; border: 1px solid rgba(255,255,255,0.1); color: #94a3b8; padding: 6px 20px; border-radius: 6px; font-size: 11px; font-weight: 600; transition: all 0.2s; }
.view-btn:hover { background: #1e293b; color: #fff; border-color: rgba(255,255,255,0.3); }

/* Animation */
.fade-up { animation: fadeUp 0.4s ease-out forwards; opacity: 0; transform: translateY(10px); }
@keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
</style>

<div class="content-wrapper scrutiny-hub">
    {{-- Header Area with Division Filter --}}
    <div class="hub-header">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="div-pill active" data-div="all">
                <i class="fas fa-th-large"></i> All Divisions
            </div>
            @foreach($unitNameMap as $id => $name)
                @php $pendingInDiv = $purchases->where('pcs_unt_id', $id); @endphp
                @if($pendingInDiv->count() > 0)
                <div class="div-pill" data-div="{{ $id }}">
                    <i class="fas fa-building"></i> {{ $name }}
                    <span class="div-badge">{{ $pendingInDiv->count() }}</span>
                </div>
                @endif
            @endforeach
        </div>
    </div>

    <div class="p-4 pt-1">
        {{-- Main Hub Tabs --}}
        <ul class="nav nav-tabs hub-tabs" role="tablist">
            <li class="nav-item">
                <a class="hub-tab-link active" id="pending-tab" data-toggle="tab" href="#pending" role="tab">
                    <i class="fas fa-clock"></i> Pending Action ({{ $caseCount }})
                </a>
            </li>
            <li class="nav-item">
                <a class="hub-tab-link" id="action-tab" data-toggle="tab" href="#actionTaken" role="tab">
                    <i class="fas fa-history"></i> Action Taken ({{ $processedCount }})
                </a>
            </li>
        </ul>

        <div class="tab-content">
            {{-- Pending Cases Content --}}
            <div class="tab-pane fade show active" id="pending" role="tabpanel">
                <div class="table-responsive">
                    <table class="hub-table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">Type</th>
                                <th>Title / Description</th>
                                <th style="width: 150px;">Date</th>
                                <th style="width: 180px; text-align: right;">Est. Amount</th>
                                <th style="width: 160px; text-align: center;">Status</th>
                                <th style="width: 100px; text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchases as $idx => $p)
                            @php 
                                $isOld = \Carbon\Carbon::parse($p->pcs_date)->diffInDays() > 2;
                                $statusIcon = match(strtolower($p->pcs_status)) {
                                    'under scrutiny' => 'fa-binoculars',
                                    'with md' => 'fa-user-tie',
                                    'with dg' => 'fa-user-shield',
                                    'with dfinance' => 'fa-file-invoice-dollar',
                                    default => 'fa-hourglass-half'
                                };
                            @endphp
                            <tr class="hub-row fade-up div-row-{{ $p->pcs_unt_id }}" style="animation-delay: {{ $idx * 0.05 }}s">
                                <td>
                                    <div class="type-badge type-ps shadow-sm">{{ strtoupper(substr($p->pcs_type ?? 'PS', 0, 2)) }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($isOld)
                                            <span class="mr-2 pulse-red" title="Pending for > 48 hours"></span>
                                        @endif
                                        <div class="text-white font-weight-bold" style="font-size: 14px; letter-spacing: 0.3px;">{{ $p->pcs_title }}</div>
                                    </div>
                                    <div class="text-ref">Ref: {{ $p->pcs_type }}-{{ $p->pcs_id }}</div>
                                </td>
                                <td class="text-muted small font-weight-bold rajdhani" style="font-size: 12px;">
                                    {{ \Carbon\Carbon::parse($p->pcs_date)->format('d M, Y') }}
                                </td>
                                <td class="text-right">
                                    <div class="text-amount rajdhani">Rs. {{ number_format($p->pcs_price) }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="status-pill rajdhani">
                                        <i class="fas {{ $statusIcon }}"></i> {{ strtoupper($p->pcs_status) }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route($detailsRouteName, $p->pcs_id) }}" class="view-btn rajdhani">VIEW</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted rajdhani" style="opacity: 0.3;">
                                        <i class="fas fa-check-double fa-3x mb-3 d-block"></i>
                                        NO PENDING CASES IN YOUR QUEUE
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Action Taken Content --}}
            <div class="tab-pane fade" id="actionTaken" role="tabpanel">
                <div class="table-responsive">
                    <table class="hub-table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">Type</th>
                                <th>Title / Description</th>
                                <th style="width: 150px;">Date</th>
                                <th style="width: 180px; text-align: right;">Action Result</th>
                                <th style="width: 160px; text-align: center;">Trail Log</th>
                                <th style="width: 100px; text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($processed as $p)
                            <tr class="hub-row fade-up div-row-{{ $p->pcs_unt_id }}">
                                <td>
                                    <div class="type-badge type-pt" style="opacity: 0.6;">{{ strtoupper(substr($p->pcs_type ?? 'PT', 0, 2)) }}</div>
                                </td>
                                <td>
                                    <div class="text-muted font-weight-bold" style="font-size: 14px;">{{ $p->pcs_title }}</div>
                                    <div class="text-ref">Ref: {{ $p->pcs_type }}-{{ $p->pcs_id }}</div>
                                </td>
                                <td class="text-muted small font-weight-bold rajdhani" style="font-size: 11px;">
                                    {{ \Carbon\Carbon::parse($p->pcs_date)->format('d M, Y') }}
                                </td>
                                <td class="text-right">
                                    <div class="text-amount text-muted rajdhani" style="font-size: 14px;">Rs. {{ number_format($p->pcs_price) }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="status-pill" style="opacity: 0.5; border-color: rgba(255,255,255,0.05);">
                                        <i class="fas fa-history"></i> {{ $p->pcs_status }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route($detailsRouteName, $p->pcs_id) }}" class="view-btn rajdhani">LOG</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted rajdhani small italic opacity-50">Nothing here yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.pulse-red {
    display: inline-block;
    width: 6px;
    height: 6px;
    background: #ef4444;
    border-radius: 50%;
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
    animation: pulse-red 1.5s infinite;
}
@keyframes pulse-red {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pills = document.querySelectorAll('.div-pill');
    const rows = document.querySelectorAll('.hub-row');

    pills.forEach(pill => {
        pill.addEventListener('click', function() {
            // Update UI
            pills.forEach(p => p.classList.remove('active'));
            this.classList.add('active');

            const selectedDiv = this.getAttribute('data-div');

            // Filter Rows
            rows.forEach(row => {
                if (selectedDiv === 'all') {
                    row.style.display = '';
                } else {
                    if (row.classList.contains('div-row-' + selectedDiv)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
        });
    });
});
</script>
@endsection
