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
                        <i class="fas fa-file-invoice-dollar mr-2 text-info"></i> Account Status - Comm
                        <span class="text-muted" style="font-size: 0.8rem;">(Project + CSRF)</span>
                    </h5>
                    <small class="text-muted">Financial status breakdown by head</small>
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
                <table class="table table-sm table-bordered table-hover mb-0" id="accountStatusTable">
                    <thead style="background-color: var(--rd-surface-lighter); color: var(--rd-text1);">
                        <tr>
                            <th style="min-width:120px;">Head</th>
                            <th class="text-right" style="min-width:110px;">Allocation</th>
                            <th class="text-right" style="min-width:110px;">MTSS Share</th>
                            <th class="text-right" style="min-width:110px;">RDW Share</th>
                            <th class="text-right" style="min-width:110px;">Received</th>
                            <th class="text-right" style="min-width:110px;">Expenditure</th>
                            <th class="text-right" style="min-width:100px;">In Process</th>
                            <th class="text-right" style="min-width:110px;">Commitments</th>
                            <th class="text-right" style="min-width:110px;">Available</th>
                            <th class="text-right" style="min-width:110px;">Remaining</th>
                        </tr>
                        {{-- Totals row --}}
                        <tr class="font-weight-bold" style="background-color: rgba(255,255,255,0.07);">
                            <td>Total</td>
                            <td class="text-right">{{ number_format($total['allocation']) }}</td>
                            <td class="text-right">{{ number_format($total['mtss_share']) }}</td>
                            <td class="text-right">{{ number_format($total['rdw_share']) }}</td>
                            <td class="text-right">{{ number_format($total['received']) }}</td>
                            <td class="text-right">{{ number_format($total['expenditure']) }}</td>
                            <td class="text-right">{{ number_format($total['in_process']) }}</td>
                            <td class="text-right">{{ number_format($total['commitments']) }}</td>
                            <td class="text-right">{{ number_format($total['available']) }}</td>
                            <td class="text-right">{{ number_format($total['remaining']) }}</td>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                        <tr>
                            <td>{{ $row->head }}</td>
                            <td class="text-right">{{ $row->allocation ? number_format($row->allocation) : '' }}</td>
                            <td class="text-right">{{ $row->mtss_share ? number_format($row->mtss_share) : '' }}</td>
                            <td class="text-right">{{ $row->rdw_share ? number_format($row->rdw_share) : '' }}</td>
                            <td class="text-right">{{ $row->received ? number_format($row->received) : '0' }}</td>
                            <td class="text-right">{{ $row->expenditure ? number_format($row->expenditure) : '0' }}</td>
                            <td class="text-right">{{ $row->in_process ? number_format($row->in_process) : '0' }}</td>
                            <td class="text-right">{{ $row->commitments ? number_format($row->commitments) : '0' }}</td>
                            <td class="text-right @if($row->available < 0) text-danger @endif">
                                {{ number_format($row->available) }}
                            </td>
                            <td class="text-right @if($row->remaining < 0) text-danger @endif">
                                {{ number_format($row->remaining) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2 text-muted d-flex justify-content-between" style="font-size: 0.8rem; background-color: var(--rd-surface-lighter);">
                <span>* With GST</span>
                <span>Printed on {{ now()->format('d M y H:i') }}</span>
            </div>
        </div>

    </div>
</div>
<style>
    #accountStatusTable th, #accountStatusTable td { vertical-align: middle; font-size: 0.84rem; }
    #accountStatusTable tbody tr:hover { background-color: rgba(0,0,0,0.02); }
</style>
@endsection
