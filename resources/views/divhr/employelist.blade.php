@extends('welcome')

@section('content')

<div class="content-wrapper pt-2">
    <div class="content-header pb-1">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h1 id="page-heading" class="m-0 font-weight-bold text-primary" style="font-size: 1.5rem;">
                        <i class="fas fa-users mr-1"></i> Division Employees
                    </h1>
                    <form method="get" action="{{ route('divhr.employelist') }}" class="ml-auto">
                        @php $st = request('status','Current'); @endphp
                        <div class="btn-group shadow-sm">
                            <a href="{{ route('divhr.employelist',['status'=>'Current','term'=>request('term')]) }}" class="btn btn-sm btn-outline-primary {{ $st=='Current'?'active':'' }}">Current</a>
                            <a href="{{ route('divhr.employelist',['status'=>'Previous','term'=>request('term')]) }}" class="btn btn-sm btn-outline-success {{ $st=='Previous'?'active':'' }}">Previous</a>
                        </div>
                    </form>
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
                    <div class="table-responsive" style="max-height: 75vh; overflow-y: auto;">
                        <table class="table table-hover table-striped mb-0 text-nowrap" id="employeesTable">
                            @php $st = request('status','Current'); @endphp
                            <thead class="bg-light text-muted sticky-top shadow-sm" style="z-index: 1;">
                                <tr>
                                    <th class="text-center p-2 col-eye"><i class="fas fa-eye"></i></th>
                                    <th class="p-2" style="width: 28%;">Name</th>
                                    <th style="width: 14%;" class="p-2">Employee ID</th>
                                    <th style="width: 14%;" class="p-2">Joined</th>
                                    @if($st === 'Previous')
                                      <th style="width: 14%;" class="p-2">Release Date</th>
                                    @endif
                                    <th style="width: 20%;" class="p-2">Head/Contract</th>
                                    <th style="width: 10%;" class="text-right p-2">Status</th>
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
                                    <td class="align-middle p-0 text-center border-right col-eye">
                                        <a href="{{ route('divhr.employeedetail', $emp->emp_id) }}" class="vertical-btn d-block text-white bg-primary" title="View Details" style="height:28px;">
                                            <i class="fas fa-chevron-right" style="font-size:.7rem"></i>
                                        </a>
                                    </td>
                                    <td class="align-middle p-2">
                                        <span class="font-weight-bold text-primary" style="font-size: 1rem;">{{ $emp->emp_name }}</span>
                                    </td>
                                    <td class="align-middle p-2">
                                        <span class="font-weight-bold">{{ $emp->emp_id }}</span>
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
.table td{vertical-align:middle;font-size:.95rem;padding:.6rem}
.btn-xs{padding:.1rem .4rem;font-size:.7rem;line-height:1.2;border-radius:4px}
.text-xs{font-size:.7rem}
.sticky-top{position:sticky;top:0;background-color:#f8f9fa;border-bottom:2px solid #dee2e6}
.vertical-btn{display:flex;align-items:center;justify-content:center;width:100%;height:28px;transition:background-color .2s;border-radius:0 4px 4px 0}
.vertical-btn:hover{background-color:#0056b3!important}
.col-eye{width:3%;min-width:36px}
</style>

@endsection
