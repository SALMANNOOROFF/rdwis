@extends('welcome')

@section('content')

<div class="content-wrapper pt-2">
    <div class="content-header pb-1">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12 d-flex justify-content-between align-items-center flex-wrap" style="gap: 15px;">
                    <h1 id="page-heading" class="m-0 font-weight-bold text-primary" style="font-size: 1.5rem; font-family: 'Rajdhani', sans-serif;">
                        <i class="fas fa-users mr-1"></i> Division Employees
                        @if($mode === 's')
                            <span class="badge badge-info ml-2" style="font-size: 0.7rem; vertical-align: middle;">Dept Only</span>
                        @else
                            <span class="badge badge-danger ml-2" style="font-size: 0.7rem; vertical-align: middle;">All Data</span>
                        @endif
                    </h1>
                    <div class="ml-sm-auto d-flex align-items-center flex-wrap" style="gap: 10px;">
                        <div class="btn-group btn-group-sm shadow-sm" role="group">
                            <a href="{{ route('divhr.employelist', ['mode' => 'm', 'status'=>request('status','Current')]) }}" 
                               class="btn {{ $mode === 'm' ? 'btn-danger font-weight-bold' : 'btn-outline-danger' }}" style="{{ $mode === 'm' ? '' : 'background: var(--rd-surface2);' }}">
                                <i class="fas fa-globe mr-1"></i> Global
                            </a>
                            <a href="{{ route('divhr.employelist', ['mode' => 's', 'status'=>request('status','Current')]) }}" 
                               class="btn {{ $mode === 's' ? 'btn-info font-weight-bold' : 'btn-outline-info' }}"
                               style="{{ $mode === 's' ? 'background-color: #17a2b8; border-color: #17a2b8; color: white;' : 'background: var(--rd-surface2); border-color: #17a2b8;' }}">
                                <i class="fas fa-sitemap mr-1"></i> My Dept
                            </a>
                        </div>
                        <form method="get" action="{{ route('divhr.employelist') }}" class="m-0">
                            <input type="hidden" name="mode" value="{{ $mode }}">
                            @php $st = request('status','Current'); @endphp
                            <div class="btn-group btn-group-sm shadow-sm">
                                <a href="{{ route('divhr.employelist',['status'=>'Current','mode'=>$mode,'term'=>request('term')]) }}" class="btn btn-primary {{ $st=='Current'?'active':'secondary' }}" style="{{ $st=='Current' ? '' : 'background: var(--rd-surface2); color: var(--rd-text2); border-color: var(--rd-border);' }}">Current</a>
                                <a href="{{ route('divhr.employelist',['status'=>'Previous','mode'=>$mode,'term'=>request('term')]) }}" class="btn btn-success {{ $st=='Previous'?'active':'secondary' }}" style="{{ $st=='Previous' ? '' : 'background: var(--rd-surface2); color: var(--rd-text2); border-color: var(--rd-border);' }}">Previous</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card card-outline card-primary shadow-sm mb-2">
                <div class="card-body py-2">
                    <form method="get" action="{{ route('divhr.employelist') }}">
                        <div class="row align-items-end">
                            <div class="col-md-4 mb-1">
                                <label class="small text-muted mb-0">Search</label>
                                <input type="text" name="term" value="{{ request('term') }}" class="form-control form-control-sm" placeholder="Name or ID..." onkeyup="applyEmpFilters()">
                            </div>
                            <div class="col-md-8 mb-1 text-right">
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
                            <thead class="bg-dark text-muted sticky-top shadow-sm" style="z-index: 1; background-color: var(--rd-surface2) !important; border-bottom: 2px solid var(--rd-border);">
                                <tr>
                                    <th class="text-center p-2 col-eye" style="width: 20px; color: var(--rd-text2);"><i class="fas fa-eye"></i></th>
                                    <th class="p-2" style="width: 30%; color: var(--rd-text2);">Name</th>
                                    <th style="width: 15%; color: var(--rd-text2);" class="p-2">Employee ID</th>
                                    <th style="width: 14%; color: var(--rd-text2);" class="p-2">Joined</th>
                                    @if($st === 'Previous')
                                      <th style="width: 14%; color: var(--rd-text2);" class="p-2">Release Date</th>
                                    @endif
                                    <th style="width: 20%; color: var(--rd-text2);" class="p-2">Head/Contract</th>
                                    <th style="width: 10%; color: var(--rd-text2);" class="text-right p-2">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees ?? [] as $emp)
                                @php
                                    $raw = strtolower($emp->emp_status ?? '');
                                    $status = in_array($raw,['active','current']) ? 'Current' : 'Previous';
                                    $join = $emp->emp_joindt ? \Carbon\Carbon::parse($emp->emp_joindt) : null;
                                    $last = $emp->emp_lastdt ? \Carbon\Carbon::parse($emp->emp_lastdt) : null;
                                    $headCode = $emp->hed_code ?? null;
                                    $headDisplay = $headCode ?: ($emp->emp_title ?: ($emp->emp_hed_id ? '#'.$emp->emp_hed_id : '—'));
                                @endphp
                                <tr class="employee-row"
                                    data-name="{{ strtolower($emp->emp_name) }}"
                                    data-id="{{ strtolower($emp->emp_id) }}"
                                    data-status="{{ strtolower($status) }}">
                                    <td class="align-middle text-center p-2 col-eye" style="width: 50px;">
                                        <a href="{{ route('divhr.employeedetail', $emp->emp_id) }}" class="btn btn-xs btn-primary shadow-sm" style="width: 30px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px; background-color: var(--rd-accent) !important; border-color: var(--rd-accent) !important;">
                                            <i class="fas fa-chevron-right text-xs"></i>
                                        </a>
                                    </td>
                                    <td class="align-middle p-2 text-truncate">
                                        <span class="font-weight-bold" style="font-size: 1rem; color: #fff;">{{ $emp->emp_name }}</span>
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
                                    <td class="align-middle p-2">
                                        <div>{{ $headDisplay }}</div>
                                    </td>
                                    <td class="align-middle text-right p-2">
                                        @php $cls = $status==='Current' ? 'text-success font-weight-bold' : 'text-danger font-weight-bold'; @endphp
                                        <span class="{{ $cls }}" style="font-size: .95rem">{{ $status }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ request('status','Current')==='Previous' ? 7 : 6 }}" class="text-center py-5 text-muted">
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
