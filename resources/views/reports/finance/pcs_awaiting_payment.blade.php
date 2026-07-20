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
                        <i class="fas fa-hourglass-half mr-2 text-warning"></i> Purchase Cases Awaiting Payments
                    </h5>
                    <small class="text-muted">Pending payment breakdown by project</small>
                </div>
            </div>
            <div class="d-flex align-items-center" style="gap:8px;">
                <button class="btn btn-sm btn-outline-info" onclick="window.print()">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius:12px; background:var(--rd-surface); overflow:hidden;">
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0" id="pcAwaitingTable">
                    <thead style="background-color: var(--rd-surface-lighter); color: var(--rd-text1);">
                        <tr>
                            <th style="min-width:60px;">ID</th>
                            <th style="min-width:100px;">Date</th>
                            <th class="text-center" style="min-width:50px;">Min</th>
                            <th style="min-width:200px;">Title</th>
                            <th style="min-width:160px;">Firm</th>
                            <th class="text-right" style="min-width:110px;">Cost</th>
                            <th class="text-right" style="min-width:100px;">Paid</th>
                            <th class="text-right" style="min-width:100px;">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($grouped as $head => $cases)
                            {{-- Project Group Header --}}
                            <tr class="group-header-row">
                                <td colspan="8">
                                    <span class="font-weight-bold">{{ $head ?: 'Unknown' }}</span>
                                    <span class="text-muted ml-2" style="font-size:0.8rem;">(Without Tax)</span>
                                </td>
                            </tr>

                            @foreach($cases as $case)
                            <tr class="data-row">
                                <td>{{ $case->pc_id }}</td>
                                <td>{{ $case->pc_date ? \Carbon\Carbon::parse($case->pc_date)->format('d-M-Y') : '-' }}</td>
                                <td class="text-center">{{ $case->minute }}</td>
                                <td>{{ $case->title }}</td>
                                <td>{{ $case->firm }}</td>
                                <td class="text-right">{{ number_format($case->cost) }}</td>
                                <td class="text-right">{{ number_format($case->paid) }}</td>
                                <td class="text-right @if($case->balance < 0) text-danger @endif">
                                    {{ number_format($case->balance) }}
                                </td>
                            </tr>
                            @endforeach

                            {{-- Subtotal per group --}}
                            <tr class="subtotal-row">
                                <td colspan="5" class="text-right text-muted" style="font-size:0.8rem;"></td>
                                <td class="text-right font-weight-bold">{{ number_format($cases->sum('cost')) }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($cases->sum('paid')) }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($cases->sum('balance')) }}</td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No awaiting payment cases found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2 text-muted d-flex justify-content-between" style="font-size: 0.8rem; background-color: var(--rd-surface-lighter);">
                <span>1 of 1</span>
                <span>Printed on {{ now()->format('d M y H:i') }}</span>
            </div>
        </div>

    </div>
</div>
<style>
    #pcAwaitingTable th, #pcAwaitingTable td { vertical-align: middle; font-size: 0.83rem; }
    .group-header-row td {
        background-color: rgba(255,255,255,0.06) !important;
        border-top: 2px solid var(--rd-border) !important;
        padding: 8px 12px;
        font-size: 0.88rem;
    }
    .subtotal-row td {
        background-color: rgba(255,255,255,0.04) !important;
        border-top: 1px dashed var(--rd-border) !important;
    }
    .data-row:hover td { background-color: rgba(0,0,0,0.02); }
</style>
@endsection
