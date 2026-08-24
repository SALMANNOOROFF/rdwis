@extends('welcome')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap');

.firms-list-page {
    font-family: 'Inter', sans-serif;
    background: var(--rd-bg) !important;
    min-height: 100vh;
    color: var(--rd-text1);
    padding-top: 20px;
    padding-bottom: 50px;
}

.rajdhani {
    font-family: 'Rajdhani', sans-serif;
    letter-spacing: 0.5px;
}

.card-cyber {
    background: var(--rd-surface);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.kpi-box {
    border-left: 4px solid #00BFFF;
    padding: 14px 18px;
    border-radius: 8px;
    background: var(--rd-surface);
}

.form-control-cyber {
    background: var(--rd-surface);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: #fff;
    border-radius: 8px;
    font-size: 13px;
}
.form-control-cyber:focus {
    background: rgba(15, 23, 32, 1);
    border-color: #00BFFF;
    color: #fff;
    box-shadow: 0 0 10px rgba(0, 191, 255, 0.25);
}

.table-cyber th {
    background: var(--rd-surface) !important;
    border-bottom: 2px solid rgba(255, 255, 255, 0.08) !important;
    color: #67e8f9 !important;
    font-family: 'Rajdhani', sans-serif;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 11px;
    font-weight: bold;
    padding: 12px 14px !important;
    white-space: nowrap;
}
.table-cyber td {
    border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
    padding: 12px 14px !important;
    vertical-align: middle;
    font-size: 13px;
}
.table-cyber tr:hover {
    background: var(--rd-neutral-50) !important;
}

.pill-filter {
    background: var(--rd-neutral-50);
    border: 1px solid var(--rd-border);
    border-radius: 20px;
    padding: 5px 14px;
    font-family: 'Rajdhani', sans-serif;
    font-size: 12px;
    font-weight: 700;
    color: var(--rd-text3);
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none !important;
}
.pill-filter:hover, .pill-filter.active {
    background: rgba(0, 191, 255, 0.18);
    border-color: #00BFFF;
    color: #00BFFF;
}
</style>

<div class="content-wrapper firms-list-page px-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge px-3 py-1 rajdhani" style="background: rgba(0, 191, 255, 0.15); color: #00BFFF; border: 1px solid rgba(0, 191, 255, 0.3); font-size: 10px;">
                    <i class="fas fa-building mr-1"></i> CENTRAL VENDOR DIRECTORY
                </span>
                <span class="badge px-2 py-1 rajdhani" style="background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.2); font-size: 10px;">
                    <i class="fas fa-list-ul mr-1"></i> ALL REGISTERED FIRMS
                </span>
            </div>
            <h2 class="font-weight-bold text-dark rajdhani m-0" style="font-size: 2rem;">
                <i class="fas fa-store mr-2 text-cyan"></i>Registered Suppliers & Firms Directory
            </h2>
            <p class="text-muted m-0 small">Complete list of registered firms, awarded procurement case history, and vendor performance intelligence.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('nrdi.firms.index') }}" class="btn btn-sm btn-info rajdhani font-weight-bold px-3 py-2">
                <i class="fas fa-search mr-1"></i> Advanced Search Engine
            </a>
            <a href="{{ route('nrdi.procurement.reports.index') }}?type=pcs_by_firms" class="btn btn-sm btn-outline-secondary rajdhani font-weight-bold px-3 py-2">
                <i class="fas fa-file-excel mr-1"></i> Export Firms Report
            </a>
        </div>
    </div>

    {{-- KPI Summary Row --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-sm-6 mb-3">
            <div class="card-cyber kpi-box" style="border-left-color: #00BFFF;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="rajdhani text-muted font-weight-bold text-uppercase small">Total Registered</span>
                    <i class="fas fa-building text-info"></i>
                </div>
                <div class="rajdhani font-weight-bold text-dark" style="font-size: 26px;">{{ $totalFirms }}</div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 mb-3">
            <div class="card-cyber kpi-box" style="border-left-color: #22c55e;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="rajdhani text-muted font-weight-bold text-uppercase small">Active Vendors</span>
                    <i class="fas fa-check-circle text-success"></i>
                </div>
                <div class="rajdhani font-weight-bold text-success" style="font-size: 26px;">{{ $activeFirms }}</div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 mb-3">
            <div class="card-cyber kpi-box" style="border-left-color: #a855f7;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="rajdhani text-muted font-weight-bold text-uppercase small">Awarded Cases</span>
                    <i class="fas fa-shopping-bag text-primary"></i>
                </div>
                <div class="rajdhani font-weight-bold text-dark" style="font-size: 26px;">{{ $awardedFirmsCount }} <span class="small text-muted" style="font-size: 13px;">firms</span></div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 mb-3">
            <div class="card-cyber kpi-box" style="border-left-color: #ef4444;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="rajdhani text-muted font-weight-bold text-uppercase small">Blacklisted</span>
                    <i class="fas fa-ban text-danger"></i>
                </div>
                <div class="rajdhani font-weight-bold text-danger" style="font-size: 26px;">{{ $blacklistedFirms }}</div>
            </div>
        </div>
    </div>

    {{-- Live Quick Search & Filter Bar on top of List --}}
    <div class="card-cyber p-3 mb-4">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-2 mb-lg-0">
                <div style="position: relative;">
                    <i class="fas fa-search text-muted" style="position: absolute; left: 14px; top: 11px;"></i>
                    <input type="text" id="quickSearchInput" class="form-control form-control-cyber" style="padding-left: 38px;" placeholder="Search firm name, NTN, GST, city, entity, type, or notes...">
                </div>
            </div>

            <div class="col-lg-6 d-flex justify-content-lg-end align-items-center flex-wrap gap-2">
                <a href="javascript:void(0)" class="pill-filter active" data-filter="all">All ({{ $totalFirms }})</a>
                <a href="javascript:void(0)" class="pill-filter" data-filter="active">Active Only ({{ $activeFirms }})</a>
                <a href="javascript:void(0)" class="pill-filter" data-filter="awarded">With Awards ({{ $awardedFirmsCount }})</a>
                <a href="javascript:void(0)" class="pill-filter" data-filter="blacklisted">Blacklisted ({{ $blacklistedFirms }})</a>
            </div>
        </div>
    </div>

    {{-- Complete List Table --}}
    <div class="card-cyber p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="rajdhani font-weight-bold text-dark m-0">
                <i class="fas fa-table text-info mr-2"></i>Firms Master Roster
            </h4>
            <span class="badge badge-info rajdhani px-3 py-2" id="visibleFirmsCount" style="font-size: 13px;">{{ $totalFirms }} Firms</span>
        </div>

        <div class="table-responsive" style="max-height: 750px; overflow-y: auto;">
            <table class="table table-cyber mb-0" id="allFirmsTable">
                <thead>
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th>Supplier / Firm Details</th>
                        <th>Entity & Category</th>
                        <th>NTN & GST Registration</th>
                        <th>Primary Location</th>
                        <th class="text-center">Awarded Cases</th>
                        <th class="text-right">Total Financial Volume</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody id="allFirmsTableBody">
                    @foreach($enrichedFirms as $f)
                    @if($f->frm_id > 0 && !str_contains($f->frm_name, '< Select'))
                    <tr class="firm-row" 
                        data-name="{{ strtolower($f->frm_name) }}"
                        data-ntn="{{ strtolower($f->frm_ntn) }}"
                        data-gst="{{ strtolower($f->frm_gst) }}"
                        data-city="{{ strtolower($f->main_city) }}"
                        data-entity="{{ strtolower($f->frm_entity) }}"
                        data-type="{{ strtolower($f->frm_type) }}"
                        data-black="{{ $f->frm_black ? '1' : '0' }}"
                        data-awarded="{{ $f->approved_cases_count > 0 ? '1' : '0' }}">
                        <td class="rajdhani text-info font-weight-bold">#{{ $f->frm_id }}</td>
                        <td>
                            <div class="font-weight-bold text-dark" style="font-size: 14px;">{{ $f->frm_name }}</div>
                            @if(!empty($f->frm_notes))
                                <div class="small text-muted">{{ \Illuminate\Support\Str::limit($f->frm_notes, 40) }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-dark text-cyan font-weight-bold">{{ $f->frm_entity }}</span>
                            <div class="small text-muted mt-1">{{ $f->frm_type }}</div>
                        </td>
                        <td>
                            <div class="small text-muted">NTN: <strong class="text-dark">{{ $f->frm_ntn }}</strong></div>
                            <div class="small text-muted">GST: <strong class="text-dark">{{ $f->frm_gst }}</strong></div>
                        </td>
                        <td>
                            <div class="text-dark"><i class="fas fa-map-marker-alt text-danger mr-1"></i>{{ $f->main_city }}</div>
                            <div class="small text-muted">{{ $f->offices_count }} Office(s) | {{ $f->persons_count }} Contact(s)</div>
                        </td>
                        <td class="text-center">
                            @if($f->approved_cases_count > 0)
                                <span class="badge badge-success px-2 py-1 rajdhani font-weight-bold" style="font-size: 12px;">
                                    <i class="fas fa-check mr-1"></i>{{ $f->approved_cases_count }} Awarded
                                </span>
                            @else
                                <span class="badge badge-secondary px-2 py-1 rajdhani">0 Cases</span>
                            @endif
                        </td>
                        <td class="text-right rajdhani font-weight-bold {{ $f->total_awarded > 0 ? 'text-success' : 'text-muted' }}" style="font-size: 14px;">
                            PKR {{ number_format($f->total_awarded) }}
                        </td>
                        <td class="text-center">
                            @if($f->frm_black)
                                <span class="badge badge-danger px-2 py-1">Blacklisted</span>
                            @else
                                <span class="badge badge-success px-2 py-1">Active</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-info btn-open-dossier" data-id="{{ $f->frm_id }}">
                                <i class="fas fa-folder-open mr-1"></i> Dossier
                            </button>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Firm Full Profile / Dossier Modal --}}
<div class="modal fade" id="firmDossierModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content bg-white border border-secondary text-dark">
            <div class="modal-header border-secondary">
                <h5 class="modal-title rajdhani font-weight-bold" id="dossierModalTitle">Firm Profile & Dossier</h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" id="dossierModalBody">
                <div class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x text-info"></i>
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary rajdhani font-weight-bold" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('quickSearchInput');
    const filterPills = document.querySelectorAll('.pill-filter');
    const rows = document.querySelectorAll('.firm-row');
    const countBadge = document.getElementById('visibleFirmsCount');

    let activeFilter = 'all';

    searchInput.addEventListener('input', applyFilters);

    filterPills.forEach(pill => {
        pill.addEventListener('click', function() {
            filterPills.forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            activeFilter = this.getAttribute('data-filter');
            applyFilters();
        });
    });

    function applyFilters() {
        const query = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        rows.forEach(row => {
            const name = row.getAttribute('data-name');
            const ntn = row.getAttribute('data-ntn');
            const gst = row.getAttribute('data-gst');
            const city = row.getAttribute('data-city');
            const entity = row.getAttribute('data-entity');
            const type = row.getAttribute('data-type');
            const isBlack = row.getAttribute('data-black') === '1';
            const isAwarded = row.getAttribute('data-awarded') === '1';

            const matchesQuery = !query || 
                name.includes(query) || 
                ntn.includes(query) || 
                gst.includes(query) || 
                city.includes(query) || 
                entity.includes(query) || 
                type.includes(query);

            let matchesPill = true;
            if (activeFilter === 'active') matchesPill = !isBlack;
            if (activeFilter === 'blacklisted') matchesPill = isBlack;
            if (activeFilter === 'awarded') matchesPill = isAwarded;

            if (matchesQuery && matchesPill) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        countBadge.innerText = visibleCount + ' Firms';
    }

    // Attach Dossier Modal
    document.querySelectorAll('.btn-open-dossier').forEach(btn => {
        btn.addEventListener('click', function() {
            const firmId = this.getAttribute('data-id');
            openFirmDossier(firmId);
        });
    });

    function openFirmDossier(firmId) {
        $('#firmDossierModal').modal('show');
        const modalBody = document.getElementById('dossierModalBody');
        modalBody.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-spinner fa-spin fa-2x text-info mb-2"></i>
                <div>Loading complete firm dossier...</div>
            </div>
        `;

        fetch(`/nrdi/firms/${firmId}`)
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    const f = res.firm;
                    document.getElementById('dossierModalTitle').innerText = `${f.frm_name} - Dossier Profile`;

                    let officesHtml = (res.offices || []).map(o => `
                        <div class="p-2 mb-2 rounded bg-white border border-secondary">
                            <strong>${o.off_name || 'Office'}</strong> (${o.off_city || 'City'})
                            <div class="small text-muted">${o.off_address || 'No address specified'}</div>
                        </div>
                    `).join('') || '<div class="text-muted small">No offices recorded.</div>';

                    let personsHtml = (res.persons || []).map(p => `
                        <div class="p-2 mb-2 rounded bg-white border border-secondary">
                            <strong>${p.per_title || ''} ${p.per_name}</strong> - <span class="text-info">${p.per_desig || 'Personnel'}</span>
                            <div class="small text-muted">Dept: ${p.per_dept || 'N/A'} | Expertise: ${p.per_exprt || 'N/A'}</div>
                        </div>
                    `).join('') || '<div class="text-muted small">No personnel recorded.</div>';

                    let contactsHtml = (res.contacts || []).map(c => `
                        <div class="badge badge-secondary p-2 mr-2 mb-2">
                            <strong>${c.inf_type}:</strong> ${c.inf_value}
                        </div>
                    `).join('') || '<div class="text-muted small">No contact channels recorded.</div>';

                    let specsHtml = (res.specialities || []).map(s => `
                        <span class="badge badge-info p-2 mr-2 mb-2">${s.spc_spec}</span>
                    `).join('') || '<div class="text-muted small">No specialities listed.</div>';

                    let facilsHtml = (res.facilities || []).map(fc => `
                        <span class="badge badge-success p-2 mr-2 mb-2">${fc.fcl_facil}</span>
                    `).join('') || '<div class="text-muted small">No facilities listed.</div>';

                    let casesHtml = (res.cases || []).map(c => `
                        <tr>
                            <td class="rajdhani text-info font-weight-bold">#${c.pcs_id}</td>
                            <td>${c.pcs_title}</td>
                            <td>${c.unt_namesh || 'HQ'}</td>
                            <td class="rajdhani font-weight-bold text-success">PKR ${Number(c.pcs_price).toLocaleString()}</td>
                            <td><span class="badge badge-info">${c.pcs_status}</span></td>
                        </tr>
                    `).join('') || '<tr><td colspan="5" class="text-muted text-center py-3">No purchase cases found.</td></tr>';

                    modalBody.innerHTML = `
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h3 class="rajdhani text-dark font-weight-bold m-0">${f.frm_name}</h3>
                                <div class="text-muted small">Entity: ${f.frm_entity || 'N/A'} | Type: ${f.frm_type || 'N/A'}</div>
                                <div class="mt-2 text-muted small">
                                    NTN: <strong class="text-dark">${f.frm_ntn || 'N/A'}</strong> | GST: <strong class="text-dark">${f.frm_gst || 'N/A'}</strong>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-right">
                                ${f.frm_black ? '<span class="badge badge-danger p-2 font-weight-bold">BLACKLISTED</span>' : '<span class="badge badge-success p-2 font-weight-bold">VERIFIED ACTIVE</span>'}
                                <div class="mt-2 small text-muted">${f.frm_notes || ''}</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h5 class="rajdhani text-cyan font-weight-bold"><i class="fas fa-building mr-1"></i> Offices & Branches</h5>
                                ${officesHtml}
                            </div>
                            <div class="col-md-6 mb-3">
                                <h5 class="rajdhani text-cyan font-weight-bold"><i class="fas fa-users mr-1"></i> Key Personnel</h5>
                                ${personsHtml}
                            </div>
                        </div>

                        <div class="mb-3">
                            <h5 class="rajdhani text-cyan font-weight-bold"><i class="fas fa-address-book mr-1"></i> Contact Channels</h5>
                            <div>${contactsHtml}</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-2">
                                <h5 class="rajdhani text-cyan font-weight-bold"><i class="fas fa-star mr-1"></i> Specializations</h5>
                                <div>${specsHtml}</div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <h5 class="rajdhani text-cyan font-weight-bold"><i class="fas fa-cogs mr-1"></i> Facilities & Equipment</h5>
                                <div>${facilsHtml}</div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top border-secondary">
                            <h5 class="rajdhani text-cyan font-weight-bold mb-3"><i class="fas fa-shopping-cart mr-1"></i> Procurement Cases History</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-cyber">
                                    <thead>
                                        <tr>
                                            <th>Case #</th>
                                            <th>Title</th>
                                            <th>Division</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${casesHtml}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                } else {
                    modalBody.innerHTML = `<div class="text-danger text-center py-4">Error loading dossier: ${res.message}</div>`;
                }
            })
            .catch(err => {
                modalBody.innerHTML = `<div class="text-danger text-center py-4">Network error loading dossier.</div>`;
            });
    }
});
</script>
@endsection
