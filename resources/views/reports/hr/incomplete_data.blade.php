@extends('welcome')

@section('content')
<div class="content-wrapper">
    <div class="container-fluid pt-4 px-4 pb-5">

        {{-- ─── Header Row ─────────────────────────────────────── --}}
        <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom border-secondary">
            <div>
                <a href="{{ route('reports.center') }}" class="btn btn-sm btn-outline-secondary mr-3">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
                <span class="h5 font-weight-bold rajdhani text-uppercase mb-0" style="color: var(--rd-text1); letter-spacing: 2px;">
                    <i class="fas fa-exclamation-triangle mr-2 text-warning"></i> HR — Incomplete Employee Data
                </span>
            </div>
            <div class="d-flex align-items-center">
                {{-- Summary Badges --}}
                @php
                    $total     = $employees->count();
                    // Define columns for counting
                    $colFields = [
                        'emp_qualif','emp_discip','emp_father','emp_gender','emp_dob','emp_pob',
                        'emp_ntnlty','emp_marital','emp_email','emp_mobile','emp_taddress','emp_paddress',
                        'emp_idmark','emp_height','emp_caste','emp_religion','emp_police','emp_political',
                        'emp_nokcnic','emp_nokname','emp_nokrelation','emp_emername','emp_emerrelation',
                        'emp_emermobile','emp_cnum','emp_secclear',
                    ];
                    $incompleteCount = $employees->filter(function($e) use ($colFields) {
                        foreach ($colFields as $f) {
                            if (empty($e->$f)) return true;
                        }
                        if (!$e->has_qualifs)  return true;
                        if (!$e->has_vehicles) return true;
                        if (!$e->has_banks)    return true;
                        return false;
                    })->count();
                    $completeCount = $total - $incompleteCount;
                @endphp
                <span class="badge badge-pill badge-success mr-2 px-3 py-2" style="font-size:0.85rem;">
                    <i class="fas fa-check-circle mr-1"></i> {{ $completeCount }} Complete
                </span>
                <span class="badge badge-pill badge-danger mr-2 px-3 py-2" style="font-size:0.85rem;">
                    <i class="fas fa-times-circle mr-1"></i> {{ $incompleteCount }} Incomplete
                </span>
                <span class="badge badge-pill badge-secondary px-3 py-2" style="font-size:0.85rem;">
                    <i class="fas fa-users mr-1"></i> {{ $total }} Total
                </span>
            </div>
        </div>

        {{-- ─── Table ───────────────────────────────────────────── --}}
        <div class="card border-0 shadow-sm" style="border-radius:12px; background: var(--rd-surface); overflow:hidden;">
            <div class="table-responsive" style="max-height: 85vh; overflow-y: auto; overflow-x: auto;">
                <table class="table table-sm table-bordered mb-0 incomplete-table" id="incompleteTable">
                    <thead class="sticky-top">
                        <tr class="header-main">
                            <th rowspan="2" class="align-middle text-center freeze-col" style="min-width:40px;">#</th>
                            <th rowspan="2" class="align-middle freeze-col2" style="min-width:200px;">Employee name</th>
                            <th rowspan="2" class="align-middle text-center" style="min-width:100px;">Department</th>
                            {{-- Group: Basic Info --}}
                            <th colspan="6" class="text-center group-basic">Basic Info</th>
                            {{-- Group: Address --}}
                            <th colspan="2" class="text-center group-address">Address</th>
                            {{-- Group: Physical --}}
                            <th colspan="5" class="text-center group-physical">Personal Details</th>
                            {{-- Group: Next of Kin --}}
                            <th colspan="3" class="text-center group-nok">Next of Kin</th>
                            {{-- Group: Emergency --}}
                            <th colspan="3" class="text-center group-emer">Emergency</th>
                            {{-- Group: Security --}}
                            <th colspan="2" class="text-center group-sec">Security</th>
                            {{-- Group: Records --}}
                            <th colspan="3" class="text-center group-records">Linked Records</th>
                        </tr>
                        <tr class="header-sub">
                            {{-- Basic Info --}}
                            <th class="col-hdr group-basic">Qualification</th>
                            <th class="col-hdr group-basic">Father name</th>
                            <th class="col-hdr group-basic">Gender</th>
                            <th class="col-hdr group-basic">Date of birth</th>
                            <th class="col-hdr group-basic">Mobile</th>
                            <th class="col-hdr group-basic">Email</th>
                            {{-- Address --}}
                            <th class="col-hdr group-address">Temp address</th>
                            <th class="col-hdr group-address">Perm address</th>
                            {{-- Physical --}}
                            <th class="col-hdr group-physical">Place of birth</th>
                            <th class="col-hdr group-physical">Nationality</th>
                            <th class="col-hdr group-physical">Marital status</th>
                            <th class="col-hdr group-physical">Caste</th>
                            <th class="col-hdr group-physical">Religion</th>
                            {{-- Next of Kin --}}
                            <th class="col-hdr group-nok">NOK Name</th>
                            <th class="col-hdr group-nok">NOK CNIC</th>
                            <th class="col-hdr group-nok">NOK relation</th>
                            {{-- Emergency --}}
                            <th class="col-hdr group-emer">Emer contact</th>
                            <th class="col-hdr group-emer">Emer mobile</th>
                            <th class="col-hdr group-emer">Emer relation</th>
                            {{-- Security --}}
                            <th class="col-hdr group-sec">Gate pass no.</th>
                            <th class="col-hdr group-sec">Gate pass exp.</th>
                            {{-- Linked Records --}}
                            <th class="col-hdr group-records">Qualifications</th>
                            <th class="col-hdr group-records">Bank accounts</th>
                            <th class="col-hdr group-records">Vehicles</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($employees as $idx => $emp)
                        <tr class="data-row">
                            <td class="text-center text-muted small freeze-col">{{ $idx + 1 }}</td>
                            <td class="freeze-col2">
                                <span class="font-weight-bold" style="color: var(--rd-text1);">{{ $emp->emp_name }}</span>
                            </td>
                            <td class="small" style="color: var(--rd-text2);">{{ $emp->department ?? '—' }}</td>

                            {{-- Basic Info --}}
                            @foreach(['emp_qualif','emp_father','emp_gender','emp_dob','emp_mobile','emp_email'] as $f)
                                <td class="text-center cell-status p-0 align-middle">@include('reports.hr._cell', ['val' => $emp->$f])</td>
                            @endforeach

                            {{-- Address --}}
                            @foreach(['emp_taddress','emp_paddress'] as $f)
                                <td class="text-center cell-status p-0 align-middle">@include('reports.hr._cell', ['val' => $emp->$f])</td>
                            @endforeach

                            {{-- Physical --}}
                            @foreach(['emp_pob','emp_ntnlty','emp_marital','emp_caste','emp_religion'] as $f)
                                <td class="text-center cell-status p-0 align-middle">@include('reports.hr._cell', ['val' => $emp->$f])</td>
                            @endforeach

                            {{-- Next of Kin --}}
                            @foreach(['emp_nokname','emp_nokcnic','emp_nokrelation'] as $f)
                                <td class="text-center cell-status p-0 align-middle">@include('reports.hr._cell', ['val' => $emp->$f])</td>
                            @endforeach

                            {{-- Emergency --}}
                            @foreach(['emp_emername','emp_emermobile','emp_emerrelation'] as $f)
                                <td class="text-center cell-status p-0 align-middle">@include('reports.hr._cell', ['val' => $emp->$f])</td>
                            @endforeach

                            {{-- Security --}}
                            @foreach(['emp_cnum','emp_secclear'] as $f)
                                <td class="text-center cell-status p-0 align-middle">@include('reports.hr._cell', ['val' => $emp->$f])</td>
                            @endforeach

                            {{-- Linked Records --}}
                            <td class="text-center cell-status p-0 align-middle">@include('reports.hr._bool_cell', ['val' => $emp->has_qualifs])</td>
                            <td class="text-center cell-status p-0 align-middle">@include('reports.hr._bool_cell', ['val' => $emp->has_banks])</td>
                            <td class="text-center cell-status p-0 align-middle">@include('reports.hr._bool_cell', ['val' => $emp->has_vehicles])</td>
                        </tr>
                    @empty
                        <tr><td colspan="27" class="text-center py-5 text-muted">No active employees found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<style>
/* ── Table Base ─────────────────────────────────────── */
.incomplete-table {
    font-size: 0.85rem;
    border-collapse: separate;
    border-spacing: 0;
    color: var(--rd-text2);
    background: var(--rd-surface);
}
.incomplete-table th,
.incomplete-table td {
    border: 1px solid var(--rd-border) !important;
    vertical-align: middle !important;
    white-space: nowrap;
}

/* ── Sticky Headers ──────────────────────────────────── */
.incomplete-table thead.sticky-top th {
    position: sticky;
    top: 0;
    z-index: 5;
    background: var(--rd-surface2) !important;
    color: var(--rd-text1) !important;
    font-size: 0.75rem;
    font-weight: 700;
}
.incomplete-table thead tr.header-sub th {
    top: 36px;
}
.col-hdr {
    writing-mode: vertical-rl;
    text-orientation: mixed;
    padding: 8px 4px !important;
    text-align: left;
    height: 140px;
    vertical-align: bottom !important;
    font-weight: normal !important;
    color: var(--rd-text2) !important;
    width: 28px;
    max-width: 28px;
}

/* ── Group Header Colors ─────────────────────────────── */
.group-basic    { background: rgba(23,162,184,0.1) !important; }
.group-address  { background: rgba(40,167,69,0.1)  !important; }
.group-physical { background: rgba(111,78,193,0.1) !important; }
.group-nok      { background: rgba(253,126,20,0.1) !important; }
.group-emer     { background: rgba(220,53,69,0.1)  !important; }
.group-sec      { background: rgba(255,193,7,0.1)  !important; }
.group-records  { background: rgba(108,117,125,0.1)!important; }

/* ── Row Background ─────────────────────────── */
.data-row:nth-child(even) { background: rgba(255,255,255,0.02) !important; }
.data-row:hover td { background: rgba(255,255,255,0.06) !important; }

/* ── Missing Square ────────────────────────────────── */
.missing-square {
    display: inline-block;
    width: 14px;
    height: 14px;
    background-color: #dc3545;
}

/* ── Print ───────────────────────────────────────────── */
@media print {
    .content-wrapper { margin-left: 0 !important; }
    .table-responsive { max-height: none !important; overflow: visible !important; }
    .sticky-top th { position: static !important; }
}
</style>
@endsection
