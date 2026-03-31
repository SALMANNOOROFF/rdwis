@extends('welcome')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1 class="m-0 font-weight-bold text-dark">System Admin — User Accounts</h1>
            <div class="d-flex align-items-center" style="gap: 8px;">
                <div class="btn-group">
                    <a href="{{ route('admin.accounts.index', ['status' => 'open']) }}"
                       class="btn btn-sm {{ ($status ?? 'open') === 'open' ? 'btn-primary' : 'btn-outline-primary' }}">
                        Open ({{ $accountsOpenCount ?? 0 }})
                    </a>
                    <a href="{{ route('admin.accounts.index', ['status' => 'closed']) }}"
                       class="btn btn-sm {{ ($status ?? 'open') === 'closed' ? 'btn-primary' : 'btn-outline-primary' }}">
                        Closed ({{ $accountsClosedCount ?? 0 }})
                    </a>
                </div>
                <a href="{{ route('admin.accounts.create') }}" class="btn btn-primary">
                    <i class="fas fa-user-plus mr-1"></i> New Account
                </a>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        {{ ($status ?? 'open') === 'closed' ? 'Closed Accounts' : 'Open Accounts' }} (cen.accounts)
                    </h3>
                </div>
                <div class="card-body table-responsive p-0" style="max-height: 600px;">
                    <table class="table table-hover table-striped table-sm">
                        <thead class="thead-dark">
                        <tr>
                            <th>Username</th>
                            <th>Name</th>
                            <th>Rank / Desig</th>
                            <th>Unit</th>
                            <th>Area</th>
                            <th>Auth</th>
                            <th>Status</th>
                            <th>Ranges</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($accounts as $acc)
                            <tr>
                                <td><strong>{{ $acc->acc_username }}</strong></td>
                                <td>{{ $acc->acc_name }}</td>
                                <td>
                                    <div>{{ $acc->acc_rank }}</div>
                                    <small class="text-muted">{{ $acc->acc_desigshort }} — {{ $acc->acc_desig }}</small>
                                </td>
                                <td>
                                    <div>{{ $acc->acc_untname }}</div>
                                    <small class="text-muted">{{ $acc->acc_unt_id }} ({{ $acc->acc_unttype }})</small>
                                </td>
                                <td><span class="badge badge-info text-uppercase">{{ $acc->acc_untarea }}</span></td>
                                <td>
                                    @if($acc->acc_auth === 'approver')
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
                                <td>
                                    <small>
                                        U: {{ $acc->acc_lowers }}–{{ $acc->acc_uppers }}<br>
                                        M: {{ $acc->acc_lowerm }}–{{ $acc->acc_upperm }}
                                    </small>
                                </td>
                                <td>
                                    @if($acc->acc_status === 'Active')
                                        <div class="d-flex flex-column" style="gap: 6px;">
                                            <form action="{{ route('admin.accounts.close', $acc->acc_id) }}" method="POST"
                                                  onsubmit="return confirm('Close this account?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-user-slash"></i> Close
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.accounts.reset_password', $acc->acc_id) }}" method="POST"
                                                  onsubmit="return confirm('Reset password to default?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-key"></i> Reset Pass
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="d-flex flex-column" style="gap: 6px;">
                                            <form action="{{ route('admin.accounts.reopen', $acc->acc_id) }}" method="POST"
                                                  onsubmit="return confirm('Re-open this account?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-user-check"></i> Re-open
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.accounts.reset_password', $acc->acc_id) }}" method="POST"
                                                  onsubmit="return confirm('Reset password to default?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-key"></i> Reset Pass
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
                <div class="card-footer">
                    {{ $accounts->links() }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
