@extends('welcome')

@section('content')

<div class="content-wrapper pt-2">
    <div class="content-header pb-1">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12 d-flex justify-content-between align-items-center flex-wrap" style="gap: 15px;">
                    <div class="d-flex align-items-center flex-wrap" style="gap: 15px;">
                        <h1 id="page-heading" class="m-0 font-weight-bold text-primary" style="font-size: 1.5rem; font-family: 'Rajdhani', sans-serif;">
                            <i class="fas fa-user-tie mr-1"></i> Hired Employees
                            @if(in_array(strtolower(trim((string) (Auth::user()->acc_untarea ?? ''))), ['fin', 'hr', 'nrdi', 'rdw', 'hqs', 'proc', 'prc']))
                                @if($mode === 's')
                                    <span class="badge badge-info ml-2" style="font-size: 0.7rem; vertical-align: middle;">Dept Only</span>
                                @else
                                    <span class="badge badge-danger ml-2" style="font-size: 0.7rem; vertical-align: middle;">All Divisions</span>
                                @endif
                            @endif
                        </h1>
                        <a href="{{ route('nrdi.contract_cases_new.index') }}" class="btn btn-sm btn-primary shadow-sm" style="background-color: var(--rd-accent) !important; border-color: var(--rd-accent) !important; font-weight: 600; border-radius: 6px;">
                            <i class="fas fa-file-signature mr-1"></i> INITIATE CONTRACT CASE
                        </a>
                    </div>
                    <div class="ml-sm-auto d-flex align-items-center flex-wrap" style="gap: 10px;">
                        @if(in_array(strtolower(trim((string) (Auth::user()->acc_untarea ?? ''))), ['fin', 'hr', 'nrdi', 'rdw', 'hqs', 'proc', 'prc']))
                        <div class="btn-group btn-group-sm shadow-sm" role="group">
                            <a href="{{ route('divhr.employelist', ['mode' => 'm', 'status'=>request('status','Current')]) }}" 
                               class="btn {{ $mode === 'm' ? 'btn-danger font-weight-bold' : 'btn-outline-danger' }}" style="{{ $mode === 'm' ? '' : 'background: var(--rd-surface2);' }}">
                                <i class="fas fa-globe mr-1"></i> All Divisions
                            </a>
                            <a href="{{ route('divhr.employelist', ['mode' => 's', 'status'=>request('status','Current')]) }}" 
                               class="btn {{ $mode === 's' ? 'btn-info font-weight-bold' : 'btn-outline-info' }}"
                                style="{{ $mode === 's' ? 'background-color: var(--rd-primary-500); border-color: var(--rd-primary-500); color: white;' : 'background: var(--rd-surface2); border-color: var(--rd-primary-500);' }}">
                                <i class="fas fa-sitemap mr-1"></i> My Dept
                            </a>
                        </div>
                        @endif
                        <form method="get" action="{{ route('divhr.employelist') }}" class="m-0">
                            <input type="hidden" name="mode" value="{{ $mode }}">
                            @if(request('unit_id')) <input type="hidden" name="unit_id" value="{{ request('unit_id') }}"> @endif
                            @php $st = request('status','Current'); @endphp
                            <div class="btn-group btn-group-sm shadow-sm">
                                <a href="{{ route('divhr.employelist',['status'=>'Current','mode'=>$mode,'term'=>request('term'),'unit_id'=>request('unit_id')]) }}" class="btn btn-primary {{ $st=='Current'?'active':'secondary' }}" style="{{ $st=='Current' ? '' : 'background: var(--rd-surface2); color: var(--rd-text2); border-color: var(--rd-border);' }}">Current</a>
                                <a href="{{ route('divhr.employelist',['status'=>'Previous','mode'=>$mode,'term'=>request('term'),'unit_id'=>request('unit_id')]) }}" class="btn btn-success {{ $st=='Previous'?'active':'secondary' }}" style="{{ $st=='Previous' ? '' : 'background: var(--rd-surface2); color: var(--rd-text2); border-color: var(--rd-border);' }}">Previous</a>
                                <a href="{{ route('divhr.employelist',['status'=>'All','mode'=>$mode,'term'=>request('term'),'unit_id'=>request('unit_id')]) }}" class="btn btn-warning {{ $st=='All'?'active':'secondary' }}" style="{{ $st=='All' ? '' : 'background: var(--rd-surface2); color: var(--rd-text2); border-color: var(--rd-border);' }}">All</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card card-outline card-primary shadow-sm mb-2">
                <div class="card-body py-2">
                    <form method="get" action="{{ route('divhr.employelist') }}">
                        <input type="hidden" name="mode" value="{{ $mode }}">
                        <input type="hidden" name="status" value="{{ request('status','Current') }}">
                        <div class="row align-items-end">
                            <div class="{{ !empty($isGlobalHrViewer) ? 'col-md-4' : 'col-md-5' }} mb-1">
                                <label class="small text-muted mb-0 font-weight-bold">Search Employees</label>
                                <input type="text" name="term" value="{{ request('term') }}" class="form-control form-control-sm" placeholder="Name or ID..." onkeyup="applyEmpFilters()">
                            </div>
                            @if(!empty($isGlobalHrViewer) && !empty($divisions))
                            <div class="col-md-4 mb-1">
                                <label class="small text-muted mb-0 font-weight-bold"><i class="fas fa-sitemap mr-1 text-primary"></i> Filter Division / Unit</label>
                                <select name="unit_id" class="form-control form-control-sm" onchange="this.form.submit()">
                                    <option value="all" {{ request('unit_id', 'all') == 'all' ? 'selected' : '' }}>— All Divisions & Units —</option>
                                    @foreach($divisions as $div)
                                        <option value="{{ $div->unt_id }}" {{ request('unit_id') == $div->unt_id ? 'selected' : '' }}>
                                            {{ $div->unt_name }} @if(!empty($div->unt_namesh)) ({{ $div->unt_namesh }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            <div class="{{ !empty($isGlobalHrViewer) ? 'col-md-4' : 'col-md-7' }} mb-1 text-right">
                                <span class="text-muted" id="emp-count" style="font-size:.95rem"></span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="rd-table-responsive" style="max-height: 75vh; overflow-y: auto;">
                        <table class="table table-hover table-striped mb-0 text-nowrap" id="employeesTable">
                            @php $st = request('status','Current'); @endphp
                            <thead class="bg-white text-muted sticky-top shadow-sm" style="z-index: 1; background-color: var(--rd-surface2) !important; border-bottom: 2px solid var(--rd-border);">
                                <tr>
                                    <th class="text-center p-2 col-eye" style="width: 65px; color: var(--rd-text2); font-weight: 700;"><i class="fas fa-eye mr-1"></i> View</th>
                                    <th class="p-2" style="width: 30%; color: var(--rd-text2);">Name</th>
                                    <th style="width: 15%; color: var(--rd-text2);" class="p-2">Employee ID</th>
                                    <th style="width: 14%; color: var(--rd-text2);" class="p-2">Joined</th>
                                    @if($st === 'Previous')
                                      <th style="width: 14%; color: var(--rd-text2);" class="p-2">Release Date</th>
                                    @endif
                                    <th style="width: 18%; color: var(--rd-text2);" class="p-2">Head/Contract</th>
                                    <th style="width: 8%; color: var(--rd-text2);" class="text-center p-2">Status</th>
                                    <th style="width: 14%; color: var(--rd-text2);" class="text-right p-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees ?? [] as $emp)
                                @php
                                    $raw = strtolower($emp->emp_status ?? '');
                                    $status = in_array($raw,['active','current']) ? 'Current' : 'Previous';
                                    $join = $emp->emp_joindt ? \Carbon\Carbon::parse($emp->emp_joindt) : null;
                                    $last = $emp->emp_lastdt ? \Carbon\Carbon::parse($emp->emp_lastdt) : null;
                                    $latestCtr = $latestContracts[$emp->emp_id] ?? null;
                                    $headCode = $latestCtr?->current_head_code ?: ($latestCtr?->hed_code ?: ($latestCtr?->prj_code ?: ($emp->hed_code ?: ($emp->prj_code ?? null))));
                                    $prjTitle = $latestCtr?->current_prj_title ?: ($latestCtr?->prj_title ?: ($emp->prj_title ?: ($latestCtr?->hed_name ?: ($emp->hed_name ?? null))));
                                    $distinctCount = (int)($latestCtr?->distinct_count ?? 0);
                                    $plansList = $latestCtr?->plans_list ?? [];
                                    $ctrEnd = $latestCtr?->ctr_enddt ? \Carbon\Carbon::parse($latestCtr->ctr_enddt) : null;
                                    $isExpiringSoon = false;
                                    $daysRemaining = null;
                                    if ($status === 'Current' && $ctrEnd) {
                                        $now = \Carbon\Carbon::today();
                                        $daysRemaining = (int) $now->diffInDays($ctrEnd, false);
                                        if ($daysRemaining <= 45) {
                                            $isExpiringSoon = true;
                                        }
                                    }
                                @endphp
                                <tr class="employee-row"
                                    data-name="{{ strtolower($emp->emp_name) }}"
                                    data-id="{{ strtolower($emp->emp_id) }}"
                                    data-status="{{ strtolower($status) }}">
                                    <td class="align-middle text-center p-2 col-eye" style="width: 65px;">
                                        <a href="{{ route('divhr.employeedetail', $emp->emp_id) }}" class="btn btn-xs btn-primary font-weight-bold px-2 py-1 shadow-sm" style="border-radius: 4px; font-size: 0.76rem; background-color: var(--rd-accent) !important; border-color: var(--rd-accent) !important; white-space: nowrap;" title="View Profile">
                                            <i class="fas fa-eye mr-1"></i> View
                                        </a>
                                    </td>
                                    <td class="align-middle p-2 text-truncate">
                                        <div class="d-flex align-items-center">
                                            <a href="{{ route('divhr.employeedetail', $emp->emp_id) }}" class="mr-2 flex-shrink-0">
                                                <img src="{{ \App\Facades\FileStorage::url($emp->emp_photodest) ?: asset('dist/img/avatar.png') }}"
                                                     onerror="this.onerror=null; this.src='{{ asset('dist/img/avatar.png') }}';"
                                                     alt="{{ $emp->emp_name }}"
                                                     class="rounded-circle border shadow-sm"
                                                     style="width: 32px; height: 32px; object-fit: cover;">
                                            </a>
                                            <div>
                                                <div class="d-flex align-items-center flex-wrap">
                                                    <a href="{{ route('divhr.employeedetail', $emp->emp_id) }}" class="font-weight-bold text-decoration-none" style="font-size: 0.95rem; color: var(--rd-text1);">
                                                        {{ $emp->emp_name }}
                                                    </a>
                                                    @if(!empty($isGlobalHrViewer) && (!empty($emp->unt_namesh) || !empty($emp->unt_name)))
                                                        <span class="badge badge-light border text-secondary ml-1 font-weight-bold" style="font-size: 10px;">
                                                             {{ $emp->unt_namesh ?: $emp->unt_name }}
                                                        </span>
                                                    @endif
                                                    @if($isExpiringSoon)
                                                        @if($daysRemaining < 0)
                                                            <span class="badge badge-blinking-red ml-2" title="Contract expired on {{ $ctrEnd->format('d-M-Y') }}">
                                                                <i class="fas fa-exclamation-triangle mr-1"></i> Contract Expired
                                                            </span>
                                                        @elseif($daysRemaining == 0)
                                                            <span class="badge badge-blinking-red ml-2" title="Contract expires today!">
                                                                <i class="fas fa-exclamation-triangle mr-1"></i> Expires Today
                                                            </span>
                                                        @else
                                                            <span class="badge badge-blinking-red ml-2" title="Contract expires in {{ $daysRemaining }} days on {{ $ctrEnd->format('d-M-Y') }}">
                                                                <i class="fas fa-exclamation-triangle mr-1"></i> Expiring ({{ $daysRemaining }}d)
                                                            </span>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle p-2">
                                        <span class="font-weight-bold" style="color: var(--rd-text2);">{{ $emp->emp_id }}</span>
                                    </td>
                                    <td class="align-middle p-2">
                                        {{ $join ? $join->format('d-M-Y') : 'N/A' }}
                                    </td>
                                    @if($st === 'Previous')
                                      <td class="align-middle p-2">
                                          {{ ($status === 'Previous' && $last) ? $last->format('d-M-Y') : '—' }}
                                      </td>
                                    @endif
                                    <td class="align-middle p-2" style="max-width: 320px;">
                                        @if(!empty($headCode) || !empty($prjTitle))
                                            <div class="d-flex align-items-center flex-wrap">
                                                @if(!empty($headCode))
                                                    <span class="badge px-2 py-1 font-weight-bold mr-1.5 shadow-xs" style="background-color: #0284c7; color: #ffffff; font-size: 11px; border-radius: 5px; letter-spacing: 0.3px;">{{ $headCode }}</span>
                                                @endif
                                                <span class="font-weight-bold text-truncate" style="color: var(--rd-text1); font-size: 0.88rem; max-width: 170px;" title="{{ $prjTitle ?: $headCode }}">
                                                    {{ $prjTitle ?: $headCode }}
                                                </span>
                                                @if($distinctCount > 1 && !empty($plansList))
                                                    <div class="dropdown d-inline-block ml-1">
                                                        <button class="btn btn-xs dropdown-toggle shadow-none" 
                                                                type="button" 
                                                                id="planDrop{{ preg_replace('/[^a-zA-Z0-9]/', '', $emp->emp_id) }}" 
                                                                data-toggle="dropdown" 
                                                                aria-haspopup="true" 
                                                                aria-expanded="false"
                                                                style="background: rgba(2, 132, 199, 0.12); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.35); font-size: 9.5px; font-weight: 700; border-radius: 4px; padding: 2px 6px;">
                                                            <i class="fas fa-calendar-alt mr-1"></i>{{ $distinctCount }} Projects
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-right shadow-lg p-0 border" 
                                                             style="min-width: 320px; max-height: 320px; overflow-y: auto; z-index: 1050; border-radius: 8px;" 
                                                             aria-labelledby="planDrop{{ preg_replace('/[^a-zA-Z0-9]/', '', $emp->emp_id) }}">
                                                            <div class="bg-light px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                                                                <span class="font-weight-bold text-dark" style="font-size: 11.5px;"><i class="fas fa-layer-group text-primary mr-1"></i> Project Allocations</span>
                                                                <span class="badge badge-info font-weight-bold" style="font-size: 9.5px;">{{ count($plansList) }} {{ count($plansList) === 1 ? 'Period' : 'Periods' }}</span>
                                                            </div>
                                                            <div class="p-1.5">
                                                                @foreach($plansList as $pl)
                                                                    @php
                                                                        $periodStr = ($pl['start_label'] === $pl['end_label'])
                                                                            ? $pl['start_label'] . ' (1 Mo)'
                                                                            : 'From ' . $pl['start_label'] . ' To ' . $pl['end_label'] . ' (' . $pl['months_count'] . ' Mos)';
                                                                    @endphp
                                                                    <div class="p-2 rounded mb-1 {{ $pl['is_current'] ? 'bg-primary-subtle' : '' }}" 
                                                                         style="{{ $pl['is_current'] ? 'background-color: rgba(14, 165, 233, 0.1); border-left: 3px solid #0284c7;' : 'border-bottom: 1px solid #f1f5f9;' }}">
                                                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                                                            <div class="d-flex align-items-center text-truncate mr-2">
                                                                                <span class="badge {{ $pl['is_current'] ? 'badge-primary' : 'badge-secondary' }} px-1.5 py-0.5 font-weight-bold mr-1.5" 
                                                                                      style="font-size: 9.5px; {{ $pl['is_current'] ? 'background-color: #0284c7 !important;' : '' }}">
                                                                                    {{ $pl['code'] }}
                                                                                </span>
                                                                                <span class="font-weight-600 text-dark text-truncate" style="font-size: 11px; max-width: 150px;" title="{{ $pl['title'] }}">
                                                                                    {{ $pl['title'] }}
                                                                                </span>
                                                                            </div>
                                                                            @if($pl['is_current'])
                                                                                <span class="badge badge-success flex-shrink-0" style="font-size: 7.5px; padding: 1px 4px; font-weight: 800;">CURRENT</span>
                                                                            @endif
                                                                        </div>
                                                                        <div class="text-muted small font-weight-500 pl-1" style="font-size: 10px;">
                                                                            <i class="far fa-calendar-alt text-secondary mr-1"></i>{{ $periodStr }}
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <div class="font-weight-bold" style="color: var(--rd-text3);">—</div>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center p-2">
                                        @php $cls = $status==='Current' ? 'text-success font-weight-bold' : 'text-danger font-weight-bold'; @endphp
                                        <span class="{{ $cls }}" style="font-size: .9rem">{{ $status }}</span>
                                    </td>
                                    <td class="align-middle text-right p-2">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('divhr.employeedetail', $emp->emp_id) }}" class="btn btn-light border btn-xs" title="View Profile">
                                                <i class="fas fa-eye text-secondary"></i>
                                            </a>
                                            @if($canEdit ?? false)
                                                <a href="{{ route('divhr.employee.edit', $emp->emp_id) }}" class="btn btn-light border btn-xs" title="Edit Profile">
                                                    <i class="fas fa-edit text-primary"></i>
                                                </a>
                                                @if($status === 'Current')
                                                    <a href="{{ route('division.contract-cases.create', ['type' => 'Cr', 'emp_id' => $emp->emp_id]) }}" class="btn btn-light border btn-xs" title="Renew Contract (Cr)">
                                                        <i class="fas fa-sync-alt text-warning"></i>
                                                    </a>
                                                    <a href="{{ route('division.contract-cases.create', ['type' => 'Ce', 'emp_id' => $emp->emp_id]) }}" class="btn btn-light border btn-xs" title="Extend Contract (Ce)">
                                                        <i class="fas fa-calendar-plus text-success"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('division.contract-cases.create', ['type' => 'Rh', 'emp_id' => $emp->emp_id]) }}" class="btn btn-light border btn-xs" title="Rehire Employee (Rh)">
                                                        <i class="fas fa-user-plus text-info"></i>
                                                    </a>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ request('status','Current')==='Previous' ? 8 : 7 }}" class="text-center py-5 text-muted">
                                        <i class="fas fa-user-times fa-3x mb-3 d-block text-gray-300"></i>
                                        No employees found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function applyEmpFilters(){
    const term = (document.querySelector('input[name="term"]')?.value || '').toLowerCase();
    const rows = document.querySelectorAll('.employee-row');
    let count = 0;
    rows.forEach(r=>{
        const name = r.dataset.name || '';
        const id = r.dataset.id || '';
        let show = true;
        if(term && !(name.includes(term) || id.includes(term))) show = false;
        r.style.display = show ? 'table-row' : 'none';
        if(show) count++;
    });
    const head = document.getElementById('page-heading');
    const cnt = document.getElementById('emp-count');
    if(head) head.innerHTML = '<i class="fas fa-users mr-1"></i> Division Employees ('+count+')';
    if(cnt) cnt.innerText = count+' shown';
}
document.addEventListener('DOMContentLoaded', applyEmpFilters);
</script>

<style>
.table td{vertical-align:middle;font-size:.95rem;padding:.6rem; border-top-color: var(--rd-border) !important; color: var(--rd-text1);}
.btn-xs{padding:.1rem .4rem;font-size:.7rem;line-height:1.2;border-radius:4px}
.text-xs{font-size:.7rem}
.sticky-top{position:sticky;top:0;background-color:var(--rd-surface2);border-bottom:2px solid var(--rd-border)}
.vertical-btn{display:flex;align-items:center;justify-content:center;width:100%;height:100%;transition:all .2s;border-radius:0 4px 4px 0;}
.vertical-btn:hover{background-color:var(--rd-accent-hover)!important;}
.shadow-hover:hover { box-shadow: inset 0 0 10px rgba(0,0,0,0.2) !important; }
.col-eye{width:50px; min-width:50px; max-width:50px;}
</style>

@endsection
