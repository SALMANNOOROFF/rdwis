@extends('welcome')

@section('content')
<div class="content-wrapper pt-3 pb-5">

    <style>
        .drilldown-container {
            max-width: 1300px;
            margin: 0 auto;
        }
        .drill-header-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            overflow: hidden;
            color: #fff;
            margin-bottom: 20px;
        }
        .drill-header-card.pcc, .drill-header-card.acc, .drill-header-card.prj {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
        }
        .drill-header-card.csrf {
            background: linear-gradient(135deg, #198754, #146c43);
        }
        .drill-header-card.subhead {
            background: linear-gradient(135deg, #0dcaf0, #0aa2c0);
        }
        .drill-header-card.loans {
            background: linear-gradient(135deg, #fd7e14, #d65a00);
        }

        /* Metric Stat Cards */
        .metric-mini-card {
            background: #fff;
            border-radius: 10px;
            border: 1px solid var(--rd-border, #e2e8f0);
            padding: 14px 18px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Table Styling */
        .drill-table th {
            background: var(--rd-surface2, #f8f9fa);
            border-bottom: 2px solid var(--rd-border, #dee2e6);
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 9px 12px;
            color: #495057;
            white-space: nowrap;
        }
        .drill-table td {
            font-size: 0.84rem;
            vertical-align: middle;
            padding: 8px 12px;
        }
        .drill-table tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.03) !important;
        }
        .font-mono {
            font-family: 'Consolas', 'Courier New', monospace;
        }

        /* Search input */
        .search-drill-box {
            border-radius: 20px;
            border: 1px solid #ced4da;
            padding: 6px 14px 6px 36px;
            font-size: 0.85rem;
            width: 260px;
            transition: all 0.2s;
        }
        .search-drill-box:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
        }
    </style>

    <div class="container-fluid drilldown-container">

        {{-- Top Back Navigation Bar --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('projects.financial_view', $head->prj_id) }}"
               class="btn btn-secondary btn-sm shadow-sm font-weight-bold" style="border-radius: 20px; padding: 6px 16px;">
                <i class="fas fa-arrow-left mr-1"></i> Back to {{ $head->prj_title }} Financial View
            </a>

            <div>
                <a href="{{ route('view-projects') }}" class="btn btn-outline-secondary btn-sm rounded-pill font-weight-bold">
                    <i class="fas fa-folder-open mr-1"></i> All Projects
                </a>
            </div>
        </div>

        {{-- Header Card --}}
        <div class="drill-header-card {{ $scope }} p-4 shadow">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <div class="d-flex align-items-center mb-1" style="gap: 8px;">
                        <span class="badge badge-light text-dark font-weight-bold px-3 py-1" style="border-radius: 12px; font-size: 0.8rem;">
                            {{ $scopeLabel }}
                        </span>
                        <span class="badge badge-warning text-dark font-weight-bold px-3 py-1" style="border-radius: 12px; font-size: 0.8rem;">
                            {{ $figureLabel }}
                        </span>
                    </div>
                    <h3 class="font-weight-bold m-0 mt-2">
                        <i class="fas fa-list-alt mr-2"></i> {{ $figureLabel }} Breakdown
                    </h3>
                    <div class="text-white-50 mt-1 font-weight-bold" style="font-size: 0.9rem;">
                        {{ $head->hed_code }} — {{ $head->prj_title }}
                    </div>
                </div>

                {{-- Total Metric Box in Header --}}
                <div class="text-right mt-3 mt-md-0 bg-white p-3 rounded text-dark shadow-sm border" style="min-width: 220px;">
                    <div class="text-muted small font-weight-bold text-uppercase">Total {{ $figureLabel }}</div>
                    <div class="h3 font-weight-bold text-primary font-mono m-0">
                        Rs. {{ number_format(abs($totalSum), 2) }}
                    </div>
                    <small class="text-muted">{{ $totalItems }} itemized record(s)</small>
                </div>
            </div>
        </div>

        {{-- Mini Stat Metrics Row --}}
        <div class="row mb-3">
            <div class="col-md-3 col-sm-6 mb-2">
                <div class="metric-mini-card">
                    <div>
                        <div class="text-muted small font-weight-bold">PROJECT CODE</div>
                        <div class="font-weight-bold text-dark h5 mb-0">{{ $head->hed_code }}</div>
                    </div>
                    <i class="fas fa-tag fa-2x text-primary opacity-50"></i>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
                <div class="metric-mini-card">
                    <div>
                        <div class="text-muted small font-weight-bold">TOTAL TRANSACTIONS</div>
                        <div class="font-weight-bold text-dark h5 mb-0">{{ $totalItems }}</div>
                    </div>
                    <i class="fas fa-receipt fa-2x text-info opacity-50"></i>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
                <div class="metric-mini-card">
                    <div>
                        <div class="text-muted small font-weight-bold">NET TOTAL AMOUNT</div>
                        <div class="font-weight-bold text-success h5 mb-0 font-mono">Rs. {{ number_format(abs($totalSum), 0) }}</div>
                    </div>
                    <i class="fas fa-money-bill-wave fa-2x text-success opacity-50"></i>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
                <div class="metric-mini-card">
                    <div>
                        <div class="text-muted small font-weight-bold">GST APPLICABILITY</div>
                        <div class="font-weight-bold text-secondary h6 mb-0">{{ ($head->hed_transtype ?? 1) == 1 ? 'Without GST' : 'With GST' }}</div>
                    </div>
                    <i class="fas fa-percentage fa-2x text-secondary opacity-50"></i>
                </div>
            </div>
        </div>

        {{-- Main Table Card --}}
        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center">
                <h5 class="card-title font-weight-bold m-0 text-dark">
                    <i class="fas fa-table text-primary mr-1"></i> Itemized Transaction Breakdown
                </h5>

                {{-- Client-Side Instant Search Box --}}
                <div class="position-relative mt-2 mt-sm-0">
                    <i class="fas fa-search position-absolute text-muted" style="left: 12px; top: 10px; font-size: 0.85rem;"></i>
                    <input type="text" id="drillSearchInput" class="form-control form-control-sm search-drill-box"
                           placeholder="Search records...">
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover drill-table mb-0" id="drillTable">
                        <thead>
                            <tr>
                                <th width="4%">#</th>
                                <th width="11%">Date / Period</th>
                                <th width="10%">Doc Ref</th>
                                <th>Description / Title</th>
                                <th width="14%">Subhead</th>
                                <th width="14%">Vendor / Payee</th>
                                @if($figure === 'commitments')
                                    <th width="12%" class="text-right">Committed (Rs)</th>
                                    <th width="11%" class="text-right">Paid (Rs)</th>
                                    <th width="12%" class="text-right">Balance (Rs)</th>
                                @else
                                    <th width="12%" class="text-right">Amount (Rs)</th>
                                    @if($figure === 'expenditure')
                                        <th width="8%" class="text-right">Tax (Rs)</th>
                                    @endif
                                    <th width="12%" class="text-right">Total (Rs)</th>
                                @endif
                                <th width="8%" class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $idx => $it)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td class="font-weight-bold text-dark">{{ $it->date }}</td>
                                    <td>
                                        <span class="badge badge-light border text-dark font-mono px-2 py-1">
                                            {{ $it->ref_no }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="font-weight-bold text-primary">{{ $it->title }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info px-2 py-1" style="font-size: 0.75rem;">
                                            {{ $it->subhead }}
                                        </span>
                                    </td>
                                    <td class="text-muted small font-weight-bold">
                                        {{ $it->vendor }}
                                    </td>

                                    @if($figure === 'commitments')
                                        <td class="text-right font-mono">{{ number_format($it->committed ?? 0, 2) }}</td>
                                        <td class="text-right font-mono text-muted">{{ number_format($it->paid ?? 0, 2) }}</td>
                                        <td class="text-right font-mono font-weight-bold text-warning">{{ number_format($it->amount ?? 0, 2) }}</td>
                                    @else
                                        <td class="text-right font-mono font-weight-bold text-dark">{{ number_format($it->amount ?? 0, 2) }}</td>
                                        @if($figure === 'expenditure')
                                            <td class="text-right font-mono text-muted">{{ number_format($it->tax ?? 0, 2) }}</td>
                                        @endif
                                        <td class="text-right font-mono font-weight-bold text-primary">{{ number_format($it->total ?? ($it->amount ?? 0), 2) }}</td>
                                    @endif

                                    <td class="text-center">
                                        <span class="badge badge-success px-2 py-1">
                                            {{ $it->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-5">
                                        <i class="fas fa-folder-open fa-3x mb-3 text-secondary d-block" style="opacity: 0.3;"></i>
                                        <h6 class="font-weight-bold">No Records Found</h6>
                                        <p class="small text-muted mb-0">There are currently no transaction records for <strong>{{ $scopeLabel }} {{ $figureLabel }}</strong> on this project account.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        @if(count($items) > 0)
                            <tfoot class="bg-light font-weight-bold">
                                <tr>
                                    <td colspan="6" class="text-right uppercase">Total Computed Sum:</td>
                                    @if($figure === 'commitments')
                                        <td class="text-right font-mono">Rs. {{ number_format(abs(collect($items)->sum('committed')), 2) }}</td>
                                        <td class="text-right font-mono text-muted">Rs. {{ number_format(abs(collect($items)->sum('paid')), 2) }}</td>
                                        <td class="text-right font-mono font-weight-bold text-warning">Rs. {{ number_format(abs($totalSum), 2) }}</td>
                                    @else
                                        <td class="text-right font-mono font-weight-bold">Rs. {{ number_format(abs(collect($items)->sum('amount')), 2) }}</td>
                                        @if($figure === 'expenditure')
                                            <td class="text-right font-mono text-muted">Rs. {{ number_format(abs(collect($items)->sum('tax')), 2) }}</td>
                                        @endif
                                        <td class="text-right font-mono font-weight-bold text-primary">Rs. {{ number_format(abs($totalSum), 2) }}</td>
                                    @endif
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white py-2 d-flex justify-content-between align-items-center">
                <small class="text-muted"><i class="fas fa-info-circle mr-1"></i> Data live-synced from PostgreSQL accounting ledgers.</small>
                <button class="btn btn-sm btn-outline-secondary rounded-pill" onclick="window.print();">
                    <i class="fas fa-print mr-1"></i> Print Breakdown
                </button>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById('drillSearchInput');
    const tableBody = document.querySelector('#drillTable tbody');

    if (searchInput && tableBody) {
        searchInput.addEventListener('keyup', function() {
            const filter = searchInput.value.toLowerCase();
            const rows = tableBody.getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const text = rows[i].textContent.toLowerCase();
                if (text.indexOf(filter) > -1) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        });
    }
});
</script>
@endpush

@endsection
