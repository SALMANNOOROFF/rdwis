@extends('welcome')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap');

    .fin-unlock-hub {
        font-family: 'Inter', sans-serif;
        background: var(--rd-bg, #f8fafc) !important;
        min-height: 90vh;
        color: var(--rd-text1, #0f172a);
        padding-top: 18px;
        padding-bottom: 60px;
    }

    .rajdhani {
        font-family: 'Rajdhani', sans-serif;
        letter-spacing: 0.5px;
    }

    .card-clean {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
        transition: all 0.2s ease;
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
        letter-spacing: 0.5px;
        font-size: 13px;
        font-weight: 700;
        padding: 12px 16px !important;
        white-space: nowrap;
    }
    .table-clean td {
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 12px 16px !important;
        vertical-align: middle;
    }

    .badge-status {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }
</style>

<div class="content-wrapper fin-unlock-hub">
    <div class="container-fluid px-4">
        <!-- Top Navigation / Header -->
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-4" style="gap: 15px;">
            <div>
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <a href="{{ route('divhr.employelist', ['mode' => 'm']) }}" class="btn btn-sm btn-outline-secondary rounded-circle shadow-sm" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;" title="Back to Hired Employees">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h2 class="m-0 rajdhani font-weight-bold" style="color: #d97706; font-size: 1.7rem;">
                        <i class="fas fa-unlock-alt mr-2"></i> FINANCE ATTENDANCE UNLOCK HUB
                    </h2>
                </div>
                <div class="text-muted small mt-1">
                    <span class="badge badge-warning text-dark font-weight-bold mr-2"><i class="fas fa-shield-alt mr-1"></i> FINANCE AUDIT EXCLUSIVE</span>
                    Selective attendance cell unlocking for salary reconciliation with mandatory audit remarks.
                </div>
            </div>
            <div>
                <a href="{{ route('divhr.employelist', ['mode' => 'm']) }}" class="btn btn-sm btn-outline-primary shadow-sm font-weight-bold">
                    <i class="fas fa-users mr-1"></i> Back to Hired Employees
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Filter & Search Bar -->
        <div class="card-clean p-3 mb-4">
            <form method="GET" action="{{ route('fin.attendance.unlock_list') }}">
                <input type="hidden" name="status" value="{{ $status }}">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-2">
                        <label class="small text-muted font-weight-bold mb-1">Search Employee (Name, ID, Rank)</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" name="term" value="{{ $term }}" class="form-control" placeholder="Type name, emp ID or rank...">
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small text-muted font-weight-bold mb-1">Filter Division / Unit</label>
                        <select name="unit_id" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="all" {{ $unitId == 'all' ? 'selected' : '' }}>— All Divisions & Units —</option>
                            @foreach($divisions as $div)
                                <option value="{{ $div->unt_id }}" {{ $unitId == $div->unt_id ? 'selected' : '' }}>
                                    {{ $div->unt_name }} @if(!empty($div->unt_namesh)) ({{ $div->unt_namesh }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small text-muted font-weight-bold mb-1">Employee Status</label>
                        <div class="btn-group btn-group-sm w-100 shadow-sm" role="group">
                            <a href="{{ route('fin.attendance.unlock_list', ['status' => 'Current', 'term' => $term, 'unit_id' => $unitId]) }}" 
                               class="btn {{ $status === 'Current' ? 'btn-primary font-weight-bold' : 'btn-outline-secondary' }}">
                                Current
                            </a>
                            <a href="{{ route('fin.attendance.unlock_list', ['status' => 'Previous', 'term' => $term, 'unit_id' => $unitId]) }}" 
                               class="btn {{ $status === 'Previous' ? 'btn-success font-weight-bold' : 'btn-outline-secondary' }}">
                                Previous
                            </a>
                            <a href="{{ route('fin.attendance.unlock_list', ['status' => 'All', 'term' => $term, 'unit_id' => $unitId]) }}" 
                               class="btn {{ $status === 'All' ? 'btn-warning font-weight-bold' : 'btn-outline-secondary' }}">
                                All
                            </a>
                        </div>
                    </div>
                    <div class="col-md-2 mb-2 text-right">
                        <button type="submit" class="btn btn-sm btn-primary font-weight-bold shadow-sm mr-1">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                        <a href="{{ route('fin.attendance.unlock_list') }}" class="btn btn-sm btn-light border shadow-sm">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Employees Table -->
        <div class="card-clean shadow-sm overflow-hidden mb-4">
            <div class="table-responsive">
                <table class="table table-clean table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 12%;">Employee ID</th>
                            <th style="width: 25%;">Employee Name</th>
                            <th style="width: 20%;">Rank / Designation</th>
                            <th style="width: 20%;">Division / Unit</th>
                            <th style="width: 10%;" class="text-center">Status</th>
                            <th style="width: 13%;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $emp)
                            <tr>
                                <td class="font-weight-bold font-monospace" style="color: #2563eb;">
                                    {{ $emp->emp_id }}
                                </td>
                                <td>
                                    <div class="font-weight-bold text-dark">{{ $emp->emp_name }}</div>
                                    @if(!empty($emp->emp_joindt))
                                        <div class="small text-muted">Joined: {{ \Carbon\Carbon::parse($emp->emp_joindt)->format('d-M-Y') }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-secondary">{{ $emp->emp_rank ?? '—' }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-light border text-dark">{{ $emp->unt_name ?? '—' }}</span>
                                </td>
                                <td class="text-center">
                                    @if(in_array(strtolower((string) $emp->emp_status), ['active', 'current']))
                                        <span class="badge-status bg-success text-white">Active</span>
                                    @else
                                        <span class="badge-status bg-secondary text-white">{{ $emp->emp_status }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('fin.attendance.unlock_employee', ['empId' => $emp->emp_id]) }}" class="btn btn-sm btn-warning font-weight-bold shadow-sm" style="border-radius: 6px;">
                                        <i class="fas fa-calendar-check mr-1"></i> Unlock Days
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-user-slash fa-3x mb-3 text-secondary opacity-50"></i>
                                    <p class="font-weight-bold mb-0">No employees found matching criteria.</p>
                                    <small>Try clearing your search term or unit filter.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($employees->hasPages())
                <div class="p-3 border-top d-flex justify-content-between align-items-center">
                    <span class="small text-muted">
                        Showing {{ $employees->firstItem() ?? 0 }} to {{ $employees->lastItem() ?? 0 }} of {{ $employees->total() }} employees
                    </span>
                    {{ $employees->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
