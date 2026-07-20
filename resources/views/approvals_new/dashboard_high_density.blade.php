@extends('welcome')

@section('content')
<div class="content-wrapper bg-dark">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-white font-weight-bold">
                        <i class="fas fa-shield-alt mr-2 text-warning"></i> {{ $area == 'rdw' ? 'Managing Director' : 'Deputy Director General' }} Portal
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Stats Row -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-navy shadow-lg border-gold">
                        <div class="inner">
                            <h3>{{ $purchases->count() }}</h3>
                            <p>Pending Approvals</p>
                        </div>
                        <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-navy shadow-lg border-gold">
                        <div class="inner">
                            <h3>{{ number_format($purchases->sum('pcs_price') / 1000000, 2) }}M</h3>
                            <p>Total Value (Million)</p>
                        </div>
                        <div class="icon"><i class="fas fa-coins"></i></div>
                    </div>
                </div>
            </div>

            <!-- Main Table Card -->
            <div class="card bg-navy border-gold shadow-lg">
                <div class="card-header border-0">
                    <h3 class="card-title font-weight-bold">Recent Purchase Cases</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-valign-middle text-white mb-0">
                            <thead class="bg-dark text-warning">
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Division</th>
                                    <th>Amount</th>
                                    <th>Budget Head</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchases as $p)
                                <tr>
                                    <td>#{{ $p->pcs_id }}</td>
                                    <td class="font-weight-bold">{{ $p->pcs_title }}</td>
                                    <td>{{ $unitNameMap[$p->pcs_unt_id] ?? 'Unknown' }}</td>
                                    <td><span class="text-success">{{ number_format($p->pcs_price, 0) }} PKR</span></td>
                                    <td><span class="badge badge-secondary">{{ $p->project->prj_name ?? 'N/A' }}</span></td>
                                    <td>
                                        <a href="{{ route('approvals.show', $p->pcs_id) }}" class="btn btn-sm btn-warning text-dark font-weight-bold">
                                            <i class="fas fa-file-signature"></i> Review
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.border-gold { border-top: 3px solid #f39c12 !important; }
.bg-navy { background-color: #001f3f !important; }
</style>
@endsection
