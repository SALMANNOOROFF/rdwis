@extends('welcome')

@section('content')
<div class="content-wrapper bg-dark">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="text-white font-weight-bold">
                <i class="fas fa-microchip mr-2 text-info"></i> {{ strtoupper($area) }} Dashboard
            </h1>
            <p class="text-muted">Pending purchase cases for your verification.</p>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card bg-secondary border-0 shadow">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover text-white mb-0">
                            <thead class="bg-navy">
                                <tr>
                                    <th>ID</th>
                                    <th>Initiator (Unit)</th>
                                    <th>Title</th>
                                    <th>Amount</th>
                                    <th>Budget Head</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchases as $p)
                                <tr>
                                    <td>#{{ $p->pcs_id }}</td>
                                    <td>{{ $unitNameMap[$p->pcs_unt_id] ?? 'Unknown' }}</td>
                                    <td>{{ $p->pcs_title }}</td>
                                    <td class="text-warning font-weight-bold">{{ number_format($p->pcs_price, 2) }}</td>
                                    <td>{{ $p->project->prj_name ?? 'N/A' }}</td>
                                    <td><span class="badge badge-info">{{ $p->pcs_status }}</span></td>
                                    <td>
                                        <a href="{{ route('approvals.show', $p->pcs_id) }}" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i> View & Process
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No pending cases in your queue.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
