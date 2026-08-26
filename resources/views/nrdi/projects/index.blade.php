@extends('welcome')

@section('content')
<div class="content-wrapper pt-2">
    <div class="content-header pb-1">
        <h1>salman noor</h1>
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12 d-flex flex-wrap justify-content-between align-items-center" style="gap: 10px;">
                    <h1 class="m-0 font-weight-bold text-primary" style="font-size: 1.5rem; font-family:'Rajdhani',sans-serif; letter-spacing:1px;">
                        <i class="fas fa-folder-open mr-1"></i> PROJECTS
                    </h1>
                    <div class="btn-group shadow-sm">
                        <a href="{{ route('nrdi.projects.index', ['status' => 'open', 'division' => $divisionId, 'term' => $term]) }}"
                           class="btn btn-sm {{ ($status ?? 'open') === 'open' ? 'btn-primary' : 'btn-outline-primary' }}" style="padding:4px 16px;">
                            Open
                        </a>
                        <a href="{{ route('nrdi.projects.index', ['status' => 'closed', 'division' => $divisionId, 'term' => $term]) }}"
                           class="btn btn-sm {{ ($status ?? 'open') === 'closed' ? 'btn-primary' : 'btn-outline-primary' }}" style="padding:4px 16px;">
                            Closed
                        </a>
                        <a href="{{ route('nrdi.projects.index', ['status' => 'all', 'division' => $divisionId, 'term' => $term]) }}"
                           class="btn btn-sm {{ ($status ?? 'open') === 'all' ? 'btn-primary' : 'btn-outline-primary' }}" style="padding:4px 16px;">
                            All
                        </a>
                    </div>
                </div>
            </div>

            {{-- FILTER CARD --}}
            <div class="card card-outline card-primary shadow-sm mb-2">
                <div class="card-body py-2">
                    <form method="GET" action="{{ route('nrdi.projects.index') }}" class="row align-items-end">
                        <input type="hidden" name="status" value="{{ $status ?? 'open' }}">
                        <div class="col-md-4 col-sm-6 mb-2">
                            <label class="small text-muted mb-0">Division</label>
                            <select class="form-control form-control-sm" name="division" onchange="this.form.submit()">
                                <option value="">All Divisions</option>
                                @foreach($divisions as $d)
                                    <option value="{{ $d->unt_id }}" {{ (string) $divisionId === (string) $d->unt_id ? 'selected' : '' }}>
                                        {{ $d->unt_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5 col-sm-6 mb-2">
                            <label class="small text-muted mb-0">Search</label>
                            <input class="form-control form-control-sm" name="term" value="{{ $term }}" placeholder="Code or title...">
                        </div>
                        <div class="col-md-2 col-6 mb-2">
                            <button class="btn btn-sm btn-primary btn-block" type="submit">
                                <i class="fas fa-search mr-1"></i> Search
                            </button>
                        </div>
                        <div class="col-md-1 col-6 mb-2">
                            <a class="btn btn-sm btn-outline-secondary btn-block" href="{{ route('nrdi.projects.index', ['status' => $status]) }}">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="rd-table-responsive" style="max-height: 75vh; overflow-y: auto;">
                        <table class="table table-hover table-striped mb-0 text-nowrap" id="projectsTable">
                            <thead class="bg-light text-muted sticky-top shadow-sm" style="z-index: 1;">
                                <tr>
                                    <th style="width: 50px;" class="text-center p-2"><i class="fas fa-eye"></i></th>
                                    <th style="min-width: 280px;" class="p-2">Project Details</th>
                                    <th style="min-width: 200px;" class="p-2">Division</th>
                                    <th style="min-width: 140px;" class="text-right p-2">Budget</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($projects as $p)
                                @php
                                    $isClosed = in_array(strtolower(trim($p->prj_status ?? '')), ['closed', 'completed', 'cancelled']);
                                @endphp
                                <tr class="project-row">
                                    {{-- 1. LEFT ACTION BUTTON (Tall & Slim) --}}
                                    <td class="align-middle p-0 text-center border-right">
                                        <a href="{{ route('nrdi.projects.show', $p->prj_id) }}" class="vertical-btn d-block text-white bg-primary shadow-hover h-100" title="View Details">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </td>

                                    {{-- 2. PROJECT DETAILS (Code + Status on one line, Title below) --}}
                                    <td class="align-middle p-2">
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="badge mr-2" style="font-size: 0.8rem; background-color: var(--rd-primary-600) !important; border: 1px solid var(--rd-primary-700) !important; color: #ffffff !important; font-weight: 700; letter-spacing: 0.5px; padding: 3px 8px; border-radius: 4px;">{{ $p->prj_code }}</span>
                                            <span class="badge {{ $isClosed ? 'badge-secondary' : 'badge-success' }} text-uppercase" style="font-size: 0.65rem;">
                                                {{ $p->prj_status }}
                                            </span>
                                        </div>
                                        <div class="font-weight-bold text-dark text-truncate" style="max-width: 380px; font-size: 0.9rem;" title="{{ $p->prj_title }}">
                                            {{ $p->prj_title }}
                                        </div>
                                    </td>

                                    {{-- 3. DIVISION --}}
                                    <td class="align-middle p-2">
                                        <span class="badge badge-light border text-dark font-weight-normal px-2 py-1" style="font-size: 0.82rem;" title="{{ $p->unit?->unt_name }}">
                                            <i class="fas fa-building text-secondary mr-1"></i> {{ $p->unit?->unt_name ?? 'N/A' }}
                                        </span>
                                    </td>

                                    {{-- 4. BUDGET --}}
                                    <td class="align-middle text-right p-2 font-weight-bold" style="font-size: 0.9rem; color: var(--rd-text1);">
                                        Rs. {{ number_format((float) ($p->prj_propcost ?? 0)) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="fas fa-folder-open fa-3x mb-3 text-muted" style="opacity: 0.3;"></i>
                                        <p class="font-weight-bold mb-0">No projects found.</p>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($projects->hasPages())
                <div class="card-footer py-2">
                    {{ $projects->links() }}
                </div>
                @endif
            </div>
        </div>
    </section>
</div>

<style>
    /* Compact Table Styling */
    .table td { vertical-align: middle; font-size: 0.85rem; padding: 0.5rem; }
    .btn-xs { padding: 0.1rem 0.4rem; font-size: 0.7rem; line-height: 1.2; border-radius: 4px; }
    .text-xs { font-size: 0.7rem; }
    
    /* Sticky Header */
    .sticky-top { position: sticky; top: 0; background-color: var(--rd-surface2); border-bottom: 2px solid var(--rd-border); }

    /* Vertical Action Button */
    .vertical-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 60px; /* Fixed height for the button bar */
        transition: background-color 0.2s;
        border-radius: 0 4px 4px 0; /* Rounded right corners */
    }
    .vertical-btn:hover {
        background-color: var(--rd-accent-dark) !important;
    }
    .shadow-hover:hover {
        box-shadow: inset 0 0 10px rgba(0,0,0,0.1);
    }
</style>
@endsection
