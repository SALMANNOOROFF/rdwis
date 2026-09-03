@extends('welcome')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap');

    .finance-hub {
        font-family: 'Inter', sans-serif;
        background: var(--rd-bg) !important;
        color: var(--rd-text1);
        padding-top: 20px;
        padding-bottom: 40px;
        min-height: 85vh;
    }

    .rajdhani {
        font-family: 'Rajdhani', sans-serif;
        letter-spacing: 0.5px;
    }

    .card-cyber {
        background: var(--rd-surface);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.07);
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }

    .nav-tabs-cyber {
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        gap: 8px;
    }
    .nav-tabs-cyber .nav-link {
        color: var(--rd-text2);
        background: transparent;
        border: 1px solid transparent;
        border-radius: 8px 8px 0 0;
        font-family: 'Rajdhani', sans-serif;
        font-weight: 700;
        font-size: 15px;
        padding: 10px 20px;
        transition: all 0.2s;
    }
    .nav-tabs-cyber .nav-link:hover {
        color: var(--rd-text1);
        border-color: rgba(255, 255, 255, 0.1);
    }
    .nav-tabs-cyber .nav-link.active {
        color: #00BFFF !important;
        background: rgba(0, 191, 255, 0.08) !important;
        border-color: rgba(0, 191, 255, 0.3) rgba(0, 191, 255, 0.3) transparent !important;
    }

    .table-cyber {
        background: transparent;
        color: var(--rd-text1);
    }
    .table-cyber th {
        background: var(--rd-surface) !important;
        border-bottom: 2px solid rgba(255, 255, 255, 0.08) !important;
        color: #67e8f9 !important;
        font-family: 'Rajdhani', sans-serif;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        font-size: 12px;
        font-weight: 700;
        padding: 12px 14px !important;
        white-space: nowrap;
    }
    .table-cyber td {
        border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
        padding: 12px 14px !important;
        vertical-align: middle;
        font-size: 13px;
    }
    .table-cyber tr:hover {
        background: var(--rd-neutral-50) !important;
    }

    .btn-cyber {
        background: rgba(0, 191, 255, 0.1);
        border: 1px solid rgba(0, 191, 255, 0.3);
        color: #00BFFF;
        font-weight: 600;
        border-radius: 6px;
        transition: all 0.2s;
        font-size: 12px;
        padding: 5px 12px;
    }
    .btn-cyber:hover {
        background: rgba(0, 191, 255, 0.25);
        color: #fff;
        box-shadow: 0 0 10px rgba(0, 191, 255, 0.2);
    }

    .form-control-cyber, .form-select-cyber {
        background: var(--rd-surface);
        border: 1px solid rgba(255, 255, 255, 0.12);
        color: #fff;
        border-radius: 8px;
        font-size: 13px;
    }
    .form-control-cyber:focus, .form-select-cyber:focus {
        background: var(--rd-surface);
        border-color: #00BFFF;
        color: #fff;
        box-shadow: 0 0 8px rgba(0, 191, 255, 0.25);
    }
</style>

<div class="content-wrapper finance-hub px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-white rajdhani font-weight-bold mb-1">Purchase Case Commitments</h2>
            <p class="text-muted small mb-0">Record disbursements, monitor remaining liabilities, and manage payments against sanctioned purchase cases.</p>
        </div>
        <div>
            <span class="badge badge-secondary p-2 rajdhani" style="font-size: 13px; background: rgba(255,255,255,0.06);">
                <i class="fas fa-shield-alt text-info mr-1"></i> HORIZON SCOPED
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success bg-success-subtle text-success border-success-subtle mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger bg-danger-subtle text-danger border-danger-subtle mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Card with Tabs and Filter Controls -->
    <div class="card card-cyber p-4">
        <!-- Tabs & Filters Row -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <!-- Open / Closed Tabs -->
            <ul class="nav nav-tabs-cyber border-0">
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'Open' ? 'active' : '' }}" 
                       href="{{ route('fin.payments.index', ['tab' => 'Open', 'unit_id' => $unitFilter, 'search' => $search]) }}">
                        <i class="fas fa-hourglass-half mr-1 text-warning"></i> OPEN (AWAITED)
                        <span class="badge badge-warning ml-2">{{ number_format($openCount) }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'Closed' ? 'active' : '' }}" 
                       href="{{ route('fin.payments.index', ['tab' => 'Closed', 'unit_id' => $unitFilter, 'search' => $search]) }}">
                        <i class="fas fa-check-circle mr-1 text-success"></i> CLOSED (PAID / CANCELLED)
                        <span class="badge badge-success ml-2">{{ number_format($closedCount) }}</span>
                    </a>
                </li>
            </ul>

            <!-- Filters: Division & Search -->
            <form action="{{ route('fin.payments.index') }}" method="GET" class="d-flex gap-2 align-items-center">
                <input type="hidden" name="tab" value="{{ $tab }}">
                
                <!-- Division / Unit Filter -->
                <div style="min-width: 200px;">
                    <select name="unit_id" class="form-control form-control-cyber" onchange="this.form.submit()">
                        <option value="All" {{ $unitFilter === 'All' ? 'selected' : '' }}>All Units (In Horizon)</option>
                        @foreach($units as $u)
                            <option value="{{ $u->unt_id }}" {{ (string)$unitFilter === (string)$u->unt_id ? 'selected' : '' }}>
                                {{ $u->unt_namesh }} ({{ $u->unt_name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Text Search -->
                <div style="min-width: 240px;">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control form-control-cyber" placeholder="Search title, case ID, firm..." value="{{ $search }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-cyber">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>

                @if($unitFilter !== 'All' || !empty($search))
                    <a href="{{ route('fin.payments.index', ['tab' => $tab]) }}" class="btn btn-sm btn-outline-secondary" title="Clear filters">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </form>
        </div>

        <!-- Commitments Table -->
        <div class="table-responsive">
            <table class="table table-cyber mb-0">
                <thead>
                    <tr>
                        <th>Cmt ID</th>
                        <th>Case ID</th>
                        <th>Date</th>
                        <th>Title / Subject</th>
                        <th>Min #</th>
                        <th>Initiator Head</th>
                        <th>For Head</th>
                        <th class="text-right">Price</th>
                        <th class="text-right">Tax</th>
                        <th class="text-right">Total Sanction</th>
                        <th>Firm</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commitments as $c)
                        @php
                            // Legacy canonical mapping (Queries/fin_docs_ipc1.sql):
                            // pcs_midprice = amount1, pcs_midtax = tax1, pcs_price = amount2.
                            // pcs_intprice/pcs_inttax are the initiation estimate, not the sanction.
                            $price = (float)($c->pcs_midprice ?? 0);
                            $tax = (float)($c->pcs_midtax ?? 0);
                            $total = (float)($c->pcs_price ?: ($price + $tax));
                        @endphp
                        <tr>
                            <td class="rajdhani text-info font-weight-bold">#{{ $c->cmt_id }}</td>
                            <td class="rajdhani font-weight-bold">
                                <a href="{{ route('nrdi.purchase_cases_new.show', $c->pcs_id) }}" class="text-white text-decoration-none" title="View Case">
                                    #{{ $c->pcs_id }}
                                </a>
                            </td>
                            <td>{{ $c->cmt_date ? date('d-M-Y', strtotime($c->cmt_date)) : 'N/A' }}</td>
                            <td style="max-width: 250px;">
                                <div class="font-weight-bold text-white text-truncate mb-0" title="{{ $c->pcs_title }}">
                                    {{ $c->pcs_title ?? 'N/A' }}
                                </div>
                                <span class="text-muted small">Type: {{ strtoupper($c->pcs_type ?? $c->cmt_type ?? 'N/A') }} | Unit: {{ $c->int_unt_namesh ?? 'N/A' }}</span>
                            </td>
                            <td class="rajdhani font-weight-bold text-cyan">{{ $c->pcs_minute ?? '-' }}</td>
                            <td>
                                <div class="font-weight-bold text-info small">{{ $c->eff_hed_code ?? 'N/A' }}</div>
                                <span class="text-muted small">{{ $c->eff_unt_namesh ?? '' }}</span>
                            </td>
                            <td>
                                @if($c->for_hed_code)
                                    <div class="font-weight-bold text-white small">{{ $c->for_hed_code }}</div>
                                    <span class="text-muted small">{{ $c->for_unt_namesh ?? '' }}</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-right rajdhani">{{ number_format($price, 2) }}</td>
                            <td class="text-right rajdhani text-muted">{{ number_format($tax, 2) }}</td>
                            <td class="text-right rajdhani font-weight-bold text-warning">PKR {{ number_format($total, 2) }}</td>
                            <td>
                                <span class="text-truncate d-inline-block small" style="max-width: 140px;" title="{{ $c->frm_name ?? 'N/A' }}">
                                    {{ $c->frm_name ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                @if($c->cmt_status === 'Awaited')
                                    <span class="badge badge-warning px-2 py-1 rajdhani">AWAITED</span>
                                @elseif($c->cmt_status === 'Paid')
                                    <span class="badge badge-success px-2 py-1 rajdhani">PAID</span>
                                @elseif($c->cmt_status === 'Cancelled')
                                    <span class="badge badge-danger px-2 py-1 rajdhani">CANCELLED</span>
                                @else
                                    <span class="badge badge-secondary px-2 py-1 rajdhani">{{ $c->cmt_status }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('fin.payments.show', $c->cmt_id) }}" class="btn btn-cyber">
                                    {{ $c->cmt_status === 'Awaited' ? 'Settle Payment' : 'View Details' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 text-secondary d-block"></i>
                                No commitments found for tab <strong>{{ $tab }}</strong>
                                @if($unitFilter !== 'All') under selected unit @endif
                                @if(!empty($search)) matching "{{ $search }}" @endif.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 d-flex justify-content-center">
            {{ $commitments->appends(['tab' => $tab, 'unit_id' => $unitFilter, 'search' => $search])->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection
