@extends('welcome')

@section('content')
<div class="content-wrapper">
    <div class="container-fluid pt-4 px-4 pb-5">

        <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom border-secondary">
            <div class="d-flex align-items-center">
                <a href="{{ route('reports.center') }}" class="btn btn-sm btn-outline-secondary mr-3">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
                <div>
                    <h5 class="text-uppercase font-weight-bold rajdhani mb-0" style="letter-spacing:2px; color:var(--rd-text1);">
                        <i class="fas fa-layer-group mr-2" style="color:#a56eff;"></i> Subheads Status - Comm
                    </h5>
                    <small class="text-muted">Subhead-wise breakdown by project</small>
                </div>
            </div>
            <div class="d-flex align-items-center" style="gap:8px;">
                <button class="btn btn-sm btn-outline-warning" onclick="toggleDetails()">
                    <i class="fas fa-eye mr-1"></i> Show/Hide Details
                </button>
                <button class="btn btn-sm btn-outline-info" onclick="window.print()">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius:12px; background:var(--rd-surface); overflow:hidden;">
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0" id="subheadsTable">
                    <thead style="background-color: var(--rd-surface-lighter); color: var(--rd-text1);">
                        <tr>
                            <th style="min-width:130px;">Project</th>
                            <th class="text-right" style="min-width:120px;">Allocation</th>
                            <th class="text-right" style="min-width:120px;">Expenditure</th>
                            <th class="text-right" style="min-width:110px;">Commitments</th>
                            <th class="text-right" style="min-width:100px;">In Process</th>
                            <th class="text-right" style="min-width:120px;">Remaining</th>
                        </tr>
                        <tr class="font-weight-bold" style="background-color: rgba(255,255,255,0.07);">
                            <td>Total</td>
                            <td class="text-right">{{ number_format($total['allocation']) }}</td>
                            <td class="text-right">{{ number_format($total['expenditure']) }}</td>
                            <td class="text-right">{{ number_format($total['commitments']) }}</td>
                            <td class="text-right">{{ number_format($total['in_process']) }}</td>
                            <td class="text-right">{{ number_format($total['remaining']) }}</td>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grouped as $head => $subheads)
                            @php
                                $headAlloc  = $subheads->sum('allocation');
                                $headExp    = $subheads->sum('expenditure');
                                $headComm   = $subheads->sum('commitments');
                                $headInProc = $subheads->sum('in_process');
                                $headRemain = $subheads->sum('remaining');
                            @endphp
                            {{-- Project summary row --}}
                            <tr class="project-row">
                                <td class="font-weight-bold">{{ $head }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($headAlloc) }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($headExp) }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($headComm) }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($headInProc) }}</td>
                                <td class="text-right font-weight-bold @if($headRemain < 0) text-danger @endif">
                                    {{ number_format($headRemain) }}
                                </td>
                            </tr>
                            {{-- Subhead detail rows --}}
                            @foreach($subheads as $sbh)
                            <tr class="detail-row subhead-row">
                                <td class="pl-4 text-muted">{{ $sbh->subhead }}</td>
                                <td class="text-right">{{ number_format($sbh->allocation) }}</td>
                                <td class="text-right">{{ number_format($sbh->expenditure) }}</td>
                                <td class="text-right">{{ number_format($sbh->commitments) }}</td>
                                <td class="text-right">{{ number_format($sbh->in_process) }}</td>
                                <td class="text-right @if($sbh->remaining < 0) text-danger @endif">
                                    {{ number_format($sbh->remaining) }}
                                </td>
                            </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2 text-muted d-flex justify-content-between" style="font-size: 0.8rem; background-color: var(--rd-surface-lighter);">
                <span>* With GST &nbsp;&nbsp; 1 of 1</span>
                <span>Printed on {{ now()->format('d M y H:i') }}</span>
            </div>
        </div>

    </div>
</div>
<style>
    #subheadsTable th, #subheadsTable td { vertical-align: middle; font-size: 0.83rem; }
    .project-row td { background-color: rgba(255,255,255,0.04); border-top: 2px solid var(--rd-border) !important; }
    .subhead-row td { font-size: 0.8rem; color: var(--rd-text2); }
</style>
<script>
    let detailsVisible = true;
    function toggleDetails() {
        detailsVisible = !detailsVisible;
        document.querySelectorAll('.detail-row').forEach(r => r.style.display = detailsVisible ? '' : 'none');
    }
</script>
@endsection
