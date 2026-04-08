@extends('welcome')

@section('content')
<div class="content-wrapper">
    <div class="content-header pb-2">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap: 15px;">
                <div>
                    <h1 class="m-0 font-weight-bold" style="color:var(--rd-text1); letter-spacing: -0.5px; font-family: 'Rajdhani', sans-serif;">
                        <i class="fas fa-chalkboard-teacher mr-2 text-info"></i> Training Queue
                    </h1>
                </div>
                <div>
                    <a href="{{ route('training.create') }}" class="btn btn-info btn-sm px-3 shadow-sm font-weight-bold" style="border-radius:6px;">
                        <i class="fas fa-plus-circle mr-2"></i> New Case
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0" style="background:var(--rd-surface); border-radius:12px;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 text-white">
                            <thead>
                                <tr class="extra-small text-uppercase tracking-wider text-muted" style="background: rgba(0,0,0,0.2);">
                                    <th class="py-3 pl-4 border-0">ID</th>
                                    <th class="py-3 border-0">Type</th>
                                    <th class="py-3 border-0">Topic / Purpose</th>
                                    <th class="py-3 border-0">Status</th>
                                    <th class="py-3 pr-4 border-0 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="py-3 pl-4 border-top border-secondary align-middle">TRN-001</td>
                                    <td class="py-3 border-top border-secondary align-middle">
                                        <span class="badge badge-info px-2">In-House</span>
                                    </td>
                                    <td class="py-3 border-top border-secondary align-middle">Advanced Laravel Ecosystem</td>
                                    <td class="py-3 border-top border-secondary align-middle">
                                        <span class="badge badge-success px-2">Approved</span>
                                    </td>
                                    <td class="py-3 pr-4 border-top border-secondary align-middle text-right">
                                        <a href="#" class="btn btn-outline-light btn-xs px-2">Details</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<style>
    :root {
        --rd-surface: #1e1e2d;
    }
    .extra-small { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.5px; }
</style>
@endsection
