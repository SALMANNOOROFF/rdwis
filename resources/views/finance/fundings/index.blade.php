@extends('welcome')

@section('content')
<div class="content-wrapper pt-3">
    <style>
        .fd-card { border-radius: 10px; border: 1px solid #e5e7eb; }
        .fd-card > .card-header { background: #fff; border-bottom: 1px solid #eef0f3; }
        .fd-label { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 3px; }
        .fd-sum th, .fd-sum td { padding: 7px 12px; font-size: 12.5px; }
        .fd-sum thead th { background: #e2e8f0; color: #475569; font-size: 11px; text-transform: uppercase; letter-spacing: .4px; }
        .fd-sum .lbl { font-weight: 700; color: #334155; }
        .fd-list table { font-size: 12.5px; }
        .fd-list thead th { background: #f1f5f9; font-size: 11px; text-transform: uppercase; letter-spacing: .3px; color: #475569; }
        .money { text-align: right; font-variant-numeric: tabular-nums; }
    </style>

    <div class="container-fluid">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap: 10px;">
            <div>
                <h1 class="m-0 font-weight-bold text-primary" style="font-size: 1.3rem; font-family: 'Rajdhani', sans-serif; letter-spacing: 1px;">
                    <i class="fas fa-money-check-alt mr-1"></i> FUNDINGS &mdash; {{ $project->prj_code ?? $head->hed_code }}
                </h1>
                <small class="text-muted">{{ $project->prj_title ?? $head->hed_name }} &middot; Head {{ $head->hed_code }} (ID {{ $head->hed_id }})</small>
            </div>
            <div class="d-flex" style="gap: 8px;">
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 font-weight-bold" data-toggle="modal" data-target="#msnCostModal">
                    <i class="fas fa-tasks mr-1"></i> Milestone Costs
                    @if($costsLocked)
                        <span class="badge badge-success ml-1">Set</span>
                    @else
                        <span class="badge badge-warning text-dark ml-1">Pending</span>
                    @endif
                </button>
                @if($project)
                    <a href="{{ route('projects.financial_view', $project->prj_id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fas fa-arrow-left mr-1"></i> Back to financials
                    </a>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success py-2 px-3 small"><i class="fas fa-check-circle mr-1"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger py-2 px-3 small"><i class="fas fa-exclamation-circle mr-1"></i> {!! session('error') !!}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger py-2 px-3 small">
                <ul class="mb-0 pl-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- SUMMARY (allocation / received / yet to be received) --}}
        <div class="card fd-card shadow-sm mb-3">
            <div class="table-responsive">
                <table class="table fd-sum mb-0">
                    <thead>
                        <tr>
                            <th style="width: 18%;"></th>
                            <th class="money">Total</th>
                            <th class="money" style="color: #dc2626;">MTSS Share</th>
                            <th class="money" style="color: #0284c7;">RDW Share</th>
                            <th class="money" style="color: #d97706;">CSRF Share</th>
                            <th class="money" style="color: #16a34a;">Project Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="lbl">Allocation</td>
                            <td class="money">{{ number_format($alloc) }}</td>
                            <td class="money">{{ number_format($mtssShare) }}</td>
                            <td class="money">{{ number_format($accShare) }}</td>
                            <td class="money">{{ number_format($cfShare) }}</td>
                            <td class="money">{{ number_format($pccShare) }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">Received</td>
                            <td class="money">{{ number_format($inwardSum) }}</td>
                            <td class="money">{{ number_format($mtssSum) }}</td>
                            <td class="money">{{ number_format($accSum) }}</td>
                            <td class="money">{{ number_format($cfSum) }}</td>
                            <td class="money">{{ number_format($pccSum) }}</td>
                        </tr>
                        <tr style="background: #fff7ed;">
                            <td class="lbl">Yet to be received</td>
                            <td class="money font-weight-bold">{{ number_format($ovrYtbr) }}</td>
                            <td class="money">&mdash;</td>
                            <td class="money font-weight-bold">{{ number_format($accYtbr) }}</td>
                            <td class="money font-weight-bold">{{ number_format($cfYtbr) }}</td>
                            <td class="money font-weight-bold">{{ number_format($pccYtbr) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ADD FUNDING --}}
        <div class="card fd-card shadow-sm mb-3">
            <div class="card-header py-2">
                <h3 class="card-title font-weight-bold m-0" style="font-size: 14px;">
                    <i class="fas fa-plus-circle text-success mr-1"></i> Add Funding
                </h3>
            </div>
            <form method="POST" action="{{ route('finance.fundings.store', $head->hed_id) }}" onsubmit="return beforeSettle();">
                @csrf
                <div class="card-body py-3">
                    <div class="row">
                        <div class="col-md-2 form-group mb-2">
                            <div class="fd-label">Date <span class="text-danger">*</span></div>
                            <input type="date" name="trn_date" id="trn_date" class="form-control form-control-sm"
                                   max="{{ date('Y-m-d') }}" value="{{ old('trn_date') }}" required>
                        </div>
                        <div class="col-md-2 form-group mb-2">
                            <div class="fd-label">Received (Total) <span class="text-danger">*</span></div>
                            <input type="number" step="1" min="0" name="ovr_amount" id="ovr_amount" class="form-control form-control-sm"
                                   value="{{ old('ovr_amount') }}" oninput="recalc()" required>
                        </div>
                        <div class="col-md-2 form-group mb-2">
                            <div class="fd-label">MTSS Share</div>
                            <input type="text" id="mtss_amount_view" class="form-control form-control-sm bg-light" readonly value="0">
                        </div>
                        <div class="col-md-2 form-group mb-2">
                            <div class="fd-label">RDW Share <span class="text-danger">*</span></div>
                            <input type="number" step="1" min="0" name="acc_amount" id="acc_amount" class="form-control form-control-sm"
                                   value="{{ old('acc_amount') }}" oninput="recalcFromAcc()" required>
                        </div>
                        <div class="col-md-2 form-group mb-2">
                            <div class="fd-label">CSRF Share</div>
                            <input type="number" step="1" min="0" name="cf_amount" id="cf_amount" class="form-control form-control-sm"
                                   value="{{ old('cf_amount', 0) }}" oninput="recalcPcc()">
                        </div>
                        <div class="col-md-2 form-group mb-2">
                            <div class="fd-label">Project Share</div>
                            <input type="number" step="1" min="0" name="pcc_amount" id="pcc_amount" class="form-control form-control-sm"
                                   value="{{ old('pcc_amount', 0) }}">
                        </div>
                    </div>
                    <div class="row align-items-end">
                        <div class="col-md-5 form-group mb-2">
                            <div class="fd-label">Milestone (against which the amount is received)</div>
                            <select name="milestone" id="milestone" class="form-control form-control-sm" onchange="showMsnCost()">
                                <option value="">&mdash; No milestone &mdash;</option>
                                @foreach($milestones as $m)
                                    <option value="{{ $m->msn_idd }}" data-cost="{{ (float) $m->mct_cost }}">
                                        MS-{{ $m->msn_id }} &middot; {{ \Illuminate\Support\Str::limit($m->msn_desc, 60) }}
                                        @if((float) $m->mct_cost > 0) (Rs. {{ number_format((float) $m->mct_cost) }}) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group mb-2">
                            <div class="fd-label">Milestone Cost</div>
                            <input type="text" id="msncost" class="form-control form-control-sm bg-light" readonly value="">
                        </div>
                        <div class="col-md-4 form-group mb-2 text-right">
                            <button type="submit" class="btn btn-sm btn-success px-4 font-weight-bold">
                                <i class="fas fa-check mr-1"></i> Settle / Add Funding
                            </button>
                        </div>
                    </div>
                    <small id="noMsnWarning" class="text-warning font-weight-bold" style="display: none;">
                        <i class="fas fa-exclamation-triangle mr-1"></i> No milestone selected &mdash; the payment will be added without a milestone.
                    </small>
                </div>
            </form>
        </div>

        {{-- INSTALLMENTS --}}
        <div class="card fd-card shadow-sm">
            <div class="card-header py-2">
                <h3 class="card-title font-weight-bold m-0" style="font-size: 14px;">
                    <i class="fas fa-list text-primary mr-1"></i> Fundings Received
                    <span class="badge badge-light border ml-1">{{ $installments->count() }}</span>
                </h3>
            </div>
            <div class="card-body p-0 fd-list">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 110px;">Date</th>
                            <th class="money">Inward</th>
                            <th class="money">MTSS</th>
                            <th class="money">RDW</th>
                            <th class="money">CSRF</th>
                            <th class="money">Project</th>
                            <th>Milestone</th>
                            <th class="money" style="width: 120px;">Milestone Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($installments as $i)
                            <tr>
                                <td>{{ $i->trn_date ? \Carbon\Carbon::parse($i->trn_date)->format('d M, Y') : '—' }}</td>
                                <td class="money">{{ number_format((float) $i->inward) }}</td>
                                <td class="money text-danger">{{ number_format((float) $i->mtss_share) }}</td>
                                <td class="money">{{ number_format((float) $i->inward - (float) $i->mtss_share) }}</td>
                                <td class="money">{{ number_format((float) $i->shi_cf) }}</td>
                                <td class="money">{{ number_format((float) $i->shi_pcc) }}</td>
                                <td class="text-muted">
                                    @if($i->mct_msn_id)
                                        MS-{{ $i->mct_msn_id }} &middot; {{ \Illuminate\Support\Str::limit($i->msn_desc, 45) }}
                                    @else
                                        &mdash;
                                    @endif
                                </td>
                                <td class="money">{{ $i->mct_cost ? number_format((float) $i->mct_cost) : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No funding received yet.</td></tr>
                        @endforelse
                    </tbody>
                    @if($installments->count())
                        <tfoot>
                            <tr style="background: #f8fafc; font-weight: 700;">
                                <td>Total</td>
                                <td class="money">{{ number_format($inwardSum) }}</td>
                                <td class="money text-danger">{{ number_format($mtssSum) }}</td>
                                <td class="money">{{ number_format($accSum) }}</td>
                                <td class="money">{{ number_format($cfSum) }}</td>
                                <td class="money">{{ number_format($pccSum) }}</td>
                                <td></td>
                                <td class="money">{{ number_format($costTotal) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- ================= MILESTONE COSTS MODAL (legacy fin_msncosts_plus) ================= --}}
    <div class="modal fade" id="msnCostModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 10px;">
                <div class="modal-header py-2">
                    <h5 class="modal-title font-weight-bold" style="font-size: 15px;">
                        <i class="fas fa-tasks text-primary mr-2"></i> Milestone Costs &mdash; {{ $project->prj_code ?? $head->hed_code }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form method="POST" action="{{ route('finance.fundings.milestone_costs', $head->hed_id) }}">
                    @csrf
                    <div class="modal-body px-4 py-3">
                        @if($costsLocked)
                            <div class="alert alert-info py-2 px-3 small">
                                <i class="fas fa-lock mr-1"></i>
                                Costs are already set. To change the break-up they must be reversed first.
                            </div>
                        @else
                            <div class="alert alert-warning py-2 px-3 small">
                                <i class="fas fa-info-circle mr-1"></i>
                                The sum of milestone costs must be exactly equal to the project allocation
                                (<strong>Rs. {{ number_format($alloc) }}</strong>), otherwise nothing is saved.
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-2" style="font-size: 12.5px;">
                                <thead style="background: #f1f5f9;">
                                    <tr>
                                        <th style="width: 60px;">MS #</th>
                                        <th>Description</th>
                                        <th style="width: 120px;">Status</th>
                                        <th style="width: 150px;">Cost (Rs.)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($milestones as $m)
                                        <tr>
                                            <td class="font-weight-bold">MS-{{ $m->msn_id }}</td>
                                            <td>{{ $m->msn_desc }}</td>
                                            <td><span class="badge badge-light border">{{ $m->msn_status ?: '—' }}</span></td>
                                            <td>
                                                <input type="number" step="1" min="0" class="form-control form-control-sm msn-cost"
                                                       name="costs[{{ $m->msn_idd }}]" value="{{ (int) ($m->mct_cost ?? 0) }}"
                                                       oninput="costTotal()" {{ $costsLocked ? 'readonly' : '' }}>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-3">This project has no milestones yet.</td></tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr style="background: #f8fafc;">
                                        <td colspan="3" class="text-right font-weight-bold">Total</td>
                                        <td class="font-weight-bold" id="costTotalCell">{{ number_format($costTotal) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-right text-muted">Allocation</td>
                                        <td class="text-muted">{{ number_format($alloc) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div id="costMismatch" class="small text-danger font-weight-bold" style="display: none;">
                            Total does not match the allocation.
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-sm btn-secondary px-3" data-dismiss="modal">Close</button>
                        @if(! $costsLocked && $milestones->count())
                            <button type="submit" class="btn btn-sm btn-primary px-4 font-weight-bold">
                                <i class="fas fa-save mr-1"></i> Save Costs
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const ALLOC     = {{ (float) $alloc }};
const ACC_SHARE = {{ (float) $accShare }};
const CF_SHARE  = {{ (float) $cfShare }};
const PCC_SHARE = {{ (float) $pccShare }};
const CF_YTBR   = {{ (float) $cfYtbr }};
const PCC_YTBR  = {{ (float) $pccYtbr }};

function num(id) { const v = parseFloat(document.getElementById(id).value); return isNaN(v) ? 0 : v; }

// Legacy CalculateCfAmount()
function calcCf(ovr, acc) {
    if (CF_YTBR === 0) return 0;
    if (PCC_YTBR === 0) return acc;
    if (CF_SHARE / CF_YTBR === PCC_SHARE / PCC_YTBR && CF_YTBR > 0.3 * ovr && (acc - Math.round(0.3 * ovr)) <= PCC_YTBR) {
        return Math.round(0.3 * ovr);
    }
    return CF_YTBR;
}

function recalc() {
    const ovr = num('ovr_amount');
    const acc = ALLOC > 0 ? Math.round(ovr * ACC_SHARE / ALLOC) : 0;
    document.getElementById('acc_amount').value = acc;
    recalcFromAcc();
}

function recalcFromAcc() {
    const ovr = num('ovr_amount');
    const acc = num('acc_amount');
    document.getElementById('mtss_amount_view').value = (ovr - acc).toLocaleString();
    document.getElementById('cf_amount').value = calcCf(ovr, acc);
    recalcPcc();
    document.getElementById('noMsnWarning').style.display =
        document.getElementById('milestone').value ? 'none' : 'block';
}

function recalcPcc() {
    const acc = num('acc_amount');
    const cf = num('cf_amount');
    document.getElementById('pcc_amount').value = Math.max(0, acc - cf);
}

function showMsnCost() {
    const sel = document.getElementById('milestone');
    const opt = sel.options[sel.selectedIndex];
    const cost = opt ? parseFloat(opt.getAttribute('data-cost') || 0) : 0;
    document.getElementById('msncost').value = sel.value && cost ? cost.toLocaleString() : '';
    document.getElementById('noMsnWarning').style.display = sel.value ? 'none' : 'block';
}

function beforeSettle() {
    if (!document.getElementById('milestone').value) {
        return confirm('No milestone has been entered. The payment will be added without a milestone. Continue?');
    }
    return true;
}

function costTotal() {
    let total = 0;
    document.querySelectorAll('.msn-cost').forEach(function (i) {
        const v = parseFloat(i.value); if (!isNaN(v)) total += v;
    });
    document.getElementById('costTotalCell').innerText = total.toLocaleString();
    document.getElementById('costMismatch').style.display = (Math.abs(total - ALLOC) < 1) ? 'none' : 'block';
}
</script>
@endsection
