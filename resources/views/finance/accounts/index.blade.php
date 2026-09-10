@extends('welcome')

@section('content')
<div class="content-wrapper pt-3 pb-5">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                {{-- Left: Page Title --}}
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark font-weight-bold" style="letter-spacing: -0.5px;">
                        <i class="fas fa-wallet text-cyan mr-2"></i> Accounts
                    </h1>
                    <p class="text-muted text-sm mb-0">Project Financial Accounts</p>
                </div>

                {{-- Right: Filters & Create New Account Button --}}
                <div class="col-sm-6 text-sm-right mt-3 mt-sm-0">
                    <div class="d-inline-flex align-items-center flex-wrap" style="gap: 8px;">
                        {{-- Open / Closed Status Filter Pills --}}
                        <div class="btn-group shadow-sm mr-2" role="group">
                            <a href="{{ route('finance.accounts.index', array_merge(request()->except('page'), ['status' => 'open'])) }}"
                               class="btn btn-sm {{ ($status ?? 'open') === 'open' ? 'btn-primary font-weight-bold' : 'btn-outline-primary bg-white' }} px-3">
                                <i class="fas fa-folder-open mr-1"></i> Open ({{ $openCount }})
                            </a>
                            <a href="{{ route('finance.accounts.index', array_merge(request()->except('page'), ['status' => 'closed'])) }}"
                               class="btn btn-sm {{ ($status ?? 'open') === 'closed' ? 'btn-primary font-weight-bold' : 'btn-outline-primary bg-white' }} px-3">
                                <i class="fas fa-lock mr-1"></i> Closed ({{ $closedCount }})
                            </a>
                        </div>

                        {{-- Prominent Create New Account Button in Top Corner --}}
                        <a href="{{ route('finance.accounts.create') }}" class="btn btn-sm btn-success font-weight-bold rounded-pill px-3 shadow-sm">
                            <i class="fas fa-plus-circle mr-1"></i> Create New Account
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            {{-- Flash Success / Error Messages --}}
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-3" role="alert" style="border-left: 5px solid #28a745 !important;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle fa-lg mr-2 text-success"></i>
                    <div class="font-weight-semibold">{{ session('success') }}</div>
                </div>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif

            @if(isset($errors) && $errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-3" role="alert" style="border-left: 5px solid #dc3545 !important;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-lg mr-2 text-danger"></i>
                    <ul class="mb-0 pl-2 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif

            {{-- Filter & Search Toolbar --}}
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body p-3">
                    <form action="{{ route('finance.accounts.index') }}" method="GET" class="row align-items-center" style="gap: 8px 0;">
                        <input type="hidden" name="status" value="{{ $status ?? 'open' }}">

                        {{-- Search Text --}}
                        <div class="col-md-5 col-sm-6">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0"><i class="fas fa-search text-muted"></i></span>
                                </div>
                                <input type="text" name="search" class="form-control border-left-0" 
                                       placeholder="Search account code or division..." 
                                       value="{{ $search ?? '' }}">
                            </div>
                        </div>

                        {{-- Division Filter --}}
                        <div class="col-md-4 col-sm-6">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light"><i class="fas fa-building text-muted"></i></span>
                                </div>
                                <select name="division" class="form-control" onchange="this.form.submit()">
                                    <option value="">-- All Divisions --</option>
                                    @foreach($divisions as $d)
                                        <option value="{{ $d->unt_id }}" {{ ($division ?? '') == $d->unt_id ? 'selected' : '' }}>
                                            {{ $d->unt_name }} ({{ $d->unt_namesh }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Buttons --}}
                        <div class="col-md-3 col-sm-12 text-right">
                            <button type="submit" class="btn btn-sm btn-primary font-weight-bold px-3">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                            @if(!empty($search) || !empty($division))
                                <a href="{{ route('finance.accounts.index', ['status' => $status ?? 'open']) }}" class="btn btn-sm btn-outline-secondary ml-1">
                                    <i class="fas fa-times"></i> Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- Accounts Data Table --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h3 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-list-ul text-primary mr-2"></i>
                        @if(($status ?? 'open') === 'closed')
                            Closed Accounts
                        @else
                            Open Accounts
                        @endif
                        <span class="badge badge-light border ml-2 text-muted">{{ $accounts->total() }} accounts</span>
                    </h3>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-sm text-nowrap mb-0 align-middle" style="font-size: 0.88rem;">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="width: 130px;">Account Code</th>
                                    <th style="width: 120px;">Division</th>
                                    <th style="width: 140px;">GST Type</th>
                                    <th style="width: 140px;">Opening Date</th>
                                    <th style="width: 250px;">Closing Date</th>
                                    <th style="width: 100px;" class="text-center">Status</th>
                                    <th style="width: 120px;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accounts as $acc)
                                <tr>
                                    {{-- 1. Account Code --}}
                                    <td>
                                        <span class="badge badge-primary px-2.5 py-1.5 font-weight-bold text-sm" style="letter-spacing: 0.5px;">
                                            {{ $acc->hed_code }}
                                        </span>
                                    </td>

                                    {{-- 2. Division --}}
                                    <td>
                                        <span class="badge badge-info px-2 py-1 font-weight-semibold">
                                            {{ $acc->unt_namesh ?: ($acc->unt_name ?: 'Unit #' . $acc->hed_unt_id) }}
                                        </span>
                                    </td>

                                    {{-- 3. GST Type (Shown before Opening Date per requirement) --}}
                                    <td>
                                        @if($acc->hed_transtype == 1)
                                            <span class="badge badge-light border px-2 py-1" title="GST in MTSS Share">
                                                <i class="fas fa-tag text-secondary mr-1"></i> Without GST
                                            </span>
                                        @elseif($acc->hed_transtype == 2)
                                            <span class="badge badge-light border px-2 py-1" title="GST in R&D Share">
                                                <i class="fas fa-tag text-info mr-1"></i> With GST
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    {{-- 4. Opening Date --}}
                                    <td>
                                        @if($acc->hed_opendt)
                                            <span class="text-dark font-weight-semibold">
                                                <i class="far fa-calendar-alt text-muted mr-1"></i> {{ \Carbon\Carbon::parse($acc->hed_opendt)->format('d M Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    {{-- 5. Closing Date (Box below to enter date and save to move to closing) --}}
                                    <td>
                                        @if($acc->hed_closedt)
                                            {{-- Already Closed: Display Date & Reopen Button --}}
                                            <div class="d-flex align-items-center justify-content-between" style="max-width: 230px;">
                                                <span class="text-danger font-weight-bold">
                                                    <i class="fas fa-calendar-times mr-1"></i> {{ \Carbon\Carbon::parse($acc->hed_closedt)->format('d M Y') }}
                                                </span>
                                                <form action="{{ route('finance.accounts.reopen', $acc->hed_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Reopen account {{ $acc->hed_code }}?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-xs btn-outline-success px-2 py-0.5" title="Reopen this account">
                                                        <i class="fas fa-undo mr-1"></i> Reopen
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            {{-- Open: Input box to enter closing date and save to close --}}
                                            <form action="{{ route('finance.accounts.close', $acc->hed_id) }}" method="POST" class="d-flex align-items-center" style="max-width: 240px;" onsubmit="return confirm('Close account {{ $acc->hed_code }} with the selected closing date?');">
                                                @csrf
                                                <div class="input-group input-group-sm">
                                                    <input type="date" name="hed_closedt" class="form-control form-control-sm px-2 text-xs font-weight-semibold" 
                                                           value="{{ date('Y-m-d') }}" required style="height: 28px; border-radius: 4px 0 0 4px;">
                                                    <div class="input-group-append">
                                                        <button type="submit" class="btn btn-xs btn-danger font-weight-bold px-2" style="height: 28px; line-height: 1.2;" title="Save Closing Date & Close Account">
                                                            <i class="fas fa-save mr-1"></i> Save
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        @endif
                                    </td>

                                    {{-- 6. Status --}}
                                    <td class="text-center">
                                        @if($acc->hed_closedt)
                                            <span class="badge badge-danger px-2.5 py-1">
                                                <i class="fas fa-lock mr-1"></i> Closed
                                            </span>
                                        @else
                                            <span class="badge badge-success px-2.5 py-1">
                                                <i class="fas fa-check-circle mr-1"></i> Open
                                            </span>
                                        @endif
                                    </td>

                                    {{-- 7. Action --}}
                                    <td class="text-center">
                                        @if(!empty($acc->prj_id))
                                            <a href="{{ route('projects.financial_view', $acc->prj_id) }}" 
                                               class="btn btn-xs btn-outline-primary font-weight-bold px-2 py-1 rounded shadow-none" 
                                               title="View Financial Standing">
                                                <i class="fas fa-chart-line mr-1"></i> Financials
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="fas fa-folder-open fa-2x mb-2 text-secondary d-block"></i>
                                        No accounts found matching the selected filter.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($accounts->hasPages())
                <div class="card-footer bg-white py-2 border-top d-flex justify-content-between align-items-center">
                    <div class="text-muted text-xs">
                        Showing {{ $accounts->firstItem() }} to {{ $accounts->lastItem() }} of {{ $accounts->total() }} accounts
                    </div>
                    <div>
                        {{ $accounts->links('pagination::bootstrap-4') }}
                    </div>
                </div>
                @endif
            </div>

        </div>
    </section>
</div>
@endsection
