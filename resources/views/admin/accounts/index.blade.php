@extends('welcome')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap" style="gap: 15px;">
            <h1 class="m-0 font-weight-bold text-dark" style="font-family: 'Rajdhani', sans-serif;">
                <i class="fas fa-users mr-2 text-primary"></i>User Accounts
            </h1>
            <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                <div class="btn-group shadow-sm">
                    <a href="{{ route('admin.accounts.index', ['status' => 'open']) }}"
                       class="btn btn-sm {{ ($status ?? 'open') === 'open' ? 'btn-primary' : 'btn-outline-primary' }} px-3">
                        Open ({{ $accountsOpenCount ?? 0 }})
                    </a>
                    <a href="{{ route('admin.accounts.index', ['status' => 'closed']) }}"
                       class="btn btn-sm {{ ($status ?? 'open') === 'closed' ? 'btn-primary' : 'btn-outline-primary' }} px-3">
                        Closed ({{ $accountsClosedCount ?? 0 }})
                    </a>
                </div>
                <a href="{{ route('admin.accounts.create') }}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                    <i class="fas fa-user-plus mr-1"></i> New
                </a>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-header border-bottom-0">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-list mr-2 text-muted"></i>
                        {{ ($status ?? 'open') === 'closed' ? 'Closed Accounts' : 'Open Accounts' }}
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="rd-table-responsive" style="max-height: calc(100vh - 220px); overflow-y: auto;">
                        <table class="table table-hover table-striped table-sm text-nowrap mb-0" style="font-size:0.82rem;">
                        <thead class="thead-dark">
                        <tr>
                            <th>Username</th>
                            <th>Name</th>
                            <th>Rank / Desig</th>
                            <th>Unit</th>
                            <th>Area</th>
                            <th>Auth</th>
                            <th>Status</th>
                            <th style="width: 165px;">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($accounts as $acc)
                            <tr>
                                <td><strong>{{ $acc->acc_username }}</strong></td>
                                <td style="white-space:nowrap; max-width: 220px; overflow:hidden; text-overflow:ellipsis;" title="{{ $acc->acc_name }}">{{ $acc->acc_name }}</td>
                                <td>
                                    <span style="white-space:nowrap; max-width: 260px; overflow:hidden; text-overflow:ellipsis; display:inline-block;"
                                          title="{{ trim(($acc->acc_rank ?? '') . ' — ' . ($acc->acc_desigshort ?? '') . ' — ' . ($acc->acc_desig ?? '')) }}">
                                        {{ $acc->acc_rank }} — <span class="text-muted">{{ $acc->acc_desigshort }}</span> — {{ $acc->acc_desig }}
                                    </span>
                                </td>
                                <td>
                                    <span style="white-space:nowrap; max-width: 260px; overflow:hidden; text-overflow:ellipsis; display:inline-block;"
                                          title="{{ trim(($acc->acc_untname ?? '') . ' — ' . ($acc->acc_unttype ?? '')) }}">
                                        {{ $acc->acc_untname }} <span class="text-muted">({{ $acc->acc_unttype }})</span>
                                    </span>
                                </td>
                                <td><span class="badge badge-info text-uppercase">{{ $acc->acc_untarea }}</span></td>
                                <td>
                                    @if(in_array(strtolower(trim((string) $acc->acc_auth)), ['approver', 'editor'], true))
                                        <span class="badge badge-success">Approver</span>
                                    @else
                                        <span class="badge badge-secondary">Viewer</span>
                                    @endif
                                </td>
                                <td>
                                    @if($acc->acc_status === 'Active')
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Closed</span>
                                    @endif
                                </td>
                                <td style="white-space:nowrap;">
                                    @if($acc->acc_status === 'Active')
                                        <div class="d-flex" style="gap: 6px; flex-wrap:nowrap;">
                                            <form action="{{ route('admin.accounts.close', $acc->acc_id) }}" method="POST"
                                                  onsubmit="return confirm('Close this account?');">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-outline-danger" style="padding:3px 10px; border-radius:10px; font-size:11px;">
                                                    <i class="fas fa-user-slash"></i> Close
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.accounts.reset_password', $acc->acc_id) }}" method="POST"
                                                  onsubmit="return confirm('Reset password to default?');">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-outline-warning" style="padding:3px 10px; border-radius:10px; font-size:11px;">
                                                    <i class="fas fa-key"></i> Reset
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="d-flex" style="gap: 6px; flex-wrap:nowrap;">
                                            <form action="{{ route('admin.accounts.reopen', $acc->acc_id) }}" method="POST"
                                                  onsubmit="return confirm('Re-open this account?');">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-outline-success" style="padding:3px 10px; border-radius:10px; font-size:11px;">
                                                    <i class="fas fa-user-check"></i> Re-open
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.accounts.reset_password', $acc->acc_id) }}" method="POST"
                                                  onsubmit="return confirm('Reset password to default?');">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-outline-warning" style="padding:3px 10px; border-radius:10px; font-size:11px;">
                                                    <i class="fas fa-key"></i> Reset
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $accounts->links() }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
