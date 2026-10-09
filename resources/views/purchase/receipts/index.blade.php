@extends('welcome')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap');

    .receipt-hub {
        font-family: 'Inter', sans-serif;
        background: var(--rd-bg, #F7F5F0) !important;
        color: var(--rd-neutral-900, #1e293b);
        padding-top: 20px;
        padding-bottom: 50px;
        min-height: 85vh;
    }

    .rajdhani {
        font-family: 'Rajdhani', sans-serif;
        letter-spacing: 0.5px;
    }

    .card-clean {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.03);
    }

    .nav-tabs-clean {
        border-bottom: 2px solid #e2e8f0;
    }
    .nav-tabs-clean .nav-link {
        font-family: 'Rajdhani', sans-serif;
        font-weight: 700;
        font-size: 15px;
        letter-spacing: 0.5px;
        color: #64748b;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 10px 22px;
        transition: all 0.2s ease;
        text-transform: uppercase;
        background: transparent;
    }
    .nav-tabs-clean .nav-link:hover {
        color: #292824;
        border-bottom-color: #cbd5e1;
    }
    .nav-tabs-clean .nav-link.active {
        color: #5F7858;
        border-bottom-color: #5F7858;
        background: transparent;
    }

    .table-clean {
        background: #ffffff;
        color: #1e293b;
    }
    .table-clean th {
        background: #f8fafc !important;
        border-bottom: 2px solid #e2e8f0 !important;
        border-top: none !important;
        color: #475569 !important;
        font-family: 'Rajdhani', sans-serif;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        font-size: 12px;
        font-weight: 700;
        padding: 12px 14px !important;
        white-space: nowrap;
    }
    .table-clean td {
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 12px 14px !important;
        vertical-align: middle;
        font-size: 13.5px;
        color: #1e293b;
    }
    .table-clean tr:hover td {
        background: #fbfbfd;
    }

    .form-control-clean {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #0f172a;
        border-radius: 8px;
        font-size: 13.5px;
        padding: 6px 12px;
    }
    .form-control-clean:focus {
        border-color: #5F7858;
        box-shadow: 0 0 0 3px rgba(95, 120, 88, 0.15);
        outline: none;
    }

    .btn-view-receipt {
        background: #5F7858;
        color: #ffffff !important;
        border: 1px solid #5F7858;
        border-radius: 6px;
        font-family: 'Rajdhani', sans-serif;
        font-weight: 700;
        font-size: 13px;
        padding: 4px 12px;
        transition: all 0.2s ease;
        text-decoration: none !important;
    }
    .btn-view-receipt:hover {
        background: #4a5e44;
        border-color: #4a5e44;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(95, 120, 88, 0.25);
    }
</style>

<div class="content-wrapper receipt-hub px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-dark rajdhani font-weight-bold mb-1" style="font-size: 1.85rem;">
                <i class="fas fa-receipt mr-2 text-success"></i> Purchase Receipts
            </h2>
            <p class="text-muted small mb-0">
                @if($isDivision)
                    Manage and inspect goods receipts (Draft &amp; Closed) for your division.
                @elseif($isProc)
                    Procurement Goods Receipts &mdash; Inspection and Acceptance Management.
                @else
                    Purchase Receipts Registry across projects and divisions.
                @endif
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($isDivision && $pendingCasesCount > 0)
                <span class="badge badge-warning px-3 py-2 rajdhani" style="font-size: 13px;">
                    <i class="fas fa-clock mr-1"></i> {{ $pendingCasesCount }} Approved Cases Awaiting Receipt
                </span>
            @endif
            <a href="{{ route('inventory.assets.index') }}" class="btn btn-outline-secondary btn-sm rajdhani font-weight-bold px-3 py-1.5 ml-2">
                <i class="fas fa-boxes mr-1"></i> Inventory &amp; Assets
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success bg-success-subtle text-success border-success-subtle mb-4" style="border-radius: 8px;">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger bg-danger-subtle text-danger border-danger-subtle mb-4" style="border-radius: 8px;">
            <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
        </div>
    @endif

    <!-- Main Card -->
    <div class="card card-clean p-4 mb-4">
        <!-- Tabs & Filters Row -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <!-- Tabs -->
            <ul class="nav nav-tabs-clean mb-2 mb-md-0">
                <li class="nav-item">
                    <a href="{{ route('purchase.receipts.index', ['tab' => 'closed', 'unit_id' => $unitFilter, 'search' => $search]) }}" 
                       class="nav-link {{ $activeTab === 'closed' ? 'active' : '' }}">
                        <i class="fas fa-check-double mr-1 text-success"></i> Closed Receipts
                        <span class="badge badge-success ml-2">{{ number_format($closedCount) }}</span>
                    </a>
                </li>
                @if($isDivision)
                    <li class="nav-item">
                        <a href="{{ route('purchase.receipts.index', ['tab' => 'draft', 'unit_id' => $unitFilter, 'search' => $search]) }}" 
                           class="nav-link {{ $activeTab === 'draft' ? 'active' : '' }}">
                            <i class="fas fa-file-signature mr-1 text-warning"></i> Draft Receipts
                            <span class="badge badge-warning ml-2">{{ number_format($draftCount) }}</span>
                        </a>
                    </li>
                @endif
            </ul>

            <!-- Search & Filters Form -->
            <form method="GET" action="{{ route('purchase.receipts.index') }}" class="d-flex flex-wrap align-items-center gap-2">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                
                @if(!$isDivision)
                    <div class="mr-2">
                        <select name="unit_id" class="form-control form-control-clean form-control-sm" onchange="this.form.submit()" style="width: 170px;">
                            <option value="All" {{ $unitFilter === 'All' ? 'selected' : '' }}>All Divisions / Units</option>
                            @foreach($units as $u)
                                <option value="{{ $u->unt_id }}" {{ (string)$unitFilter === (string)$u->unt_id ? 'selected' : '' }}>
                                    {{ $u->unt_namesh }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" 
                           name="search" 
                           value="{{ $search }}" 
                           class="form-control form-control-clean" 
                           placeholder="Receipt #, Case #, Title, Firm...">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                @if(!empty($search) || $unitFilter !== 'All')
                    <a href="{{ route('purchase.receipts.index', ['tab' => $activeTab]) }}" class="btn btn-sm btn-link text-danger ml-1" title="Clear Filters">
                        <i class="fas fa-times-circle"></i> Clear
                    </a>
                @endif
            </form>
        </div>

        <!-- Receipts Table -->
        <div class="table-responsive">
            <table class="table table-clean mb-0">
                <thead>
                    <tr>
                        <th style="width: 90px;">Receipt #</th>
                        <th style="width: 110px;">Date</th>
                        <th>Purchase Case</th>
                        <th>Project / Head</th>
                        <th>Division</th>
                        <th>Vendor / Firm</th>
                        <th class="text-right">Total Value</th>
                        <th class="text-center" style="width: 100px;">Status</th>
                        <th class="text-center" style="width: 110px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receipts as $r)
                        <tr>
                            <td class="font-weight-bold rajdhani text-dark" style="font-size: 15px;">
                                #{{ $r->prt_id }}
                            </td>
                            <td class="text-muted small">
                                {{ $r->prt_date ? date('d-M-Y', strtotime($r->prt_date)) : 'N/A' }}
                            </td>
                            <td>
                                <div class="font-weight-bold text-dark mb-0">
                                    <span class="text-primary rajdhani">#{{ $r->pcs_id }}</span> &mdash; {{ $r->pcs_title }}
                                </div>
                                <span class="text-muted small">Minute: {{ $r->pcs_minute ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="badge badge-light border text-dark font-weight-bold">
                                    {{ $r->hed_code ?? 'N/A' }}
                                </span>
                                <div class="text-muted small text-truncate" style="max-width: 140px;">{{ $r->hed_name }}</div>
                            </td>
                            <td>
                                <strong class="text-dark">{{ $r->unt_namesh ?? $r->prt_unt_id }}</strong>
                            </td>
                            <td>
                                <span class="text-dark">{{ $r->frm_name ?? 'N/A' }}</span>
                            </td>
                            <td class="text-right font-weight-bold rajdhani text-dark" style="font-size: 14px;">
                                PKR {{ number_format((float)($r->prt_value ?? 0), 2) }}
                            </td>
                            <td class="text-center">
                                @if($r->prt_status === 'Finalized')
                                    <span class="badge badge-success px-2 py-1 rajdhani font-weight-bold">FINALIZED</span>
                                @elseif($r->prt_status === 'Draft')
                                    <span class="badge badge-warning px-2 py-1 rajdhani font-weight-bold">DRAFT</span>
                                @elseif($r->prt_status === 'Cancelled')
                                    <span class="badge badge-danger px-2 py-1 rajdhani font-weight-bold">CANCELLED</span>
                                @else
                                    <span class="badge badge-secondary px-2 py-1 rajdhani font-weight-bold">{{ $r->prt_status }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('purchase.receipts.show', $r->prt_id) }}" class="btn-view-receipt">
                                    <i class="fas fa-eye mr-1"></i> VIEW
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 text-secondary d-block"></i>
                                No {{ $activeTab === 'draft' ? 'draft' : 'closed' }} purchase receipts found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($receipts->hasPages())
            <div class="mt-4 d-flex justify-content-between align-items-center">
                <span class="text-muted small">
                    Showing {{ $receipts->firstItem() }} to {{ $receipts->lastItem() }} of {{ $receipts->total() }} receipts
                </span>
                <div>
                    {{ $receipts->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
