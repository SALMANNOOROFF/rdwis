@extends('welcome')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap');

.firms-hub {
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

/* Glassmorphism Cyber Card */
.card-cyber {
    background: var(--rd-surface);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

/* Classic Form Fieldset Box Styling matching UI Reference */
.search-fieldset {
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    padding: 14px 18px !important;
    margin-bottom: 12px !important;
    border-radius: 8px;
    background: var(--rd-surface);
}

.search-legend {
    width: auto !important;
    padding: 0 10px !important;
    font-family: 'Rajdhani', sans-serif;
    font-size: 13px !important;
    font-weight: 700 !important;
    color: #00BFFF !important;
    text-transform: uppercase;
    letter-spacing: 1px;
    border-bottom: none !important;
    margin-bottom: 0 !important;
}

.form-control-cyber {
    background: rgba(15, 23, 32, 0.95);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: #fff;
    border-radius: 6px;
    font-size: 12px;
    height: 32px;
    padding: 4px 8px;
}

.form-control-cyber:focus {
    background: rgba(18, 28, 40, 1);
    border-color: #00BFFF;
    color: #fff;
    box-shadow: 0 0 8px rgba(0, 191, 255, 0.25);
}

.field-label {
    font-family: 'Rajdhani', sans-serif;
    font-size: 12px;
    font-weight: 600;
    color: var(--rd-text3);
    margin-bottom: 3px;
}

/* Buttons matching Reference Layout */
.btn-find {
    background: var(--rd-primary-600);
    border: 1px solid #38bdf8;
    color: #fff;
    font-family: 'Rajdhani', sans-serif;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.5px;
    border-radius: 6px;
    height: 32px;
    padding: 0 20px;
    transition: all 0.2s ease;
}

.btn-find:hover {
    background: var(--rd-primary-700);
    box-shadow: 0 0 12px rgba(2, 132, 199, 0.4);
    color: #fff;
}

.btn-view-firms {
    background: rgba(0, 191, 255, 0.15);
    border: 1px solid rgba(0, 191, 255, 0.4);
    color: #00BFFF;
    font-family: 'Rajdhani', sans-serif;
    font-weight: 700;
    font-size: 13px;
    border-radius: 6px;
    height: 34px;
    width: 100%;
    transition: all 0.2s ease;
}

.btn-view-firms:hover {
    background: var(--rd-primary-500);
    color: #fff;
    box-shadow: 0 0 12px rgba(0, 191, 255, 0.3);
}

.btn-reset {
    color: var(--rd-text3);
    font-family: 'Rajdhani', sans-serif;
    font-size: 13px;
    font-weight: 600;
    background: none;
    border: none;
    padding: 0;
    text-decoration: underline;
    cursor: pointer;
}

.btn-reset:hover {
    color: #ef4444;
}

/* Data Table */
.table-cyber th {
    background: var(--rd-surface) !important;
    border-bottom: 2px solid rgba(255, 255, 255, 0.08) !important;
    color: #67e8f9 !important;
    font-family: 'Rajdhani', sans-serif;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 11px;
    font-weight: bold;
    padding: 10px 12px !important;
}

.table-cyber td {
    border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
    padding: 10px 12px !important;
    vertical-align: middle;
    font-size: 13px;
}

.table-cyber tr:hover {
    background: var(--rd-neutral-50) !important;
}
</style>

<div class="content-wrapper firms-hub px-4">
    {{-- Top Header --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge px-3 py-1 rajdhani" style="background: rgba(0, 191, 255, 0.15); color: #00BFFF; border: 1px solid rgba(0, 191, 255, 0.3); font-size: 10px;">
                    <i class="fas fa-building mr-1"></i> CENTRAL SUPPLIER DIRECTORY
                </span>
                <span class="badge px-2 py-1 rajdhani" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); font-size: 10px;">
                    <i class="fas fa-check-circle mr-1"></i> ADVANCED SEARCH & VERIFICATION
                </span>
            </div>
            <h2 class="font-weight-bold text-dark rajdhani m-0" style="font-size: 2rem;">
                <i class="fas fa-search-location mr-2 text-cyan"></i>Suppliers & Registered Firms Directory
            </h2>
            <p class="text-muted m-0 small">Query vendors, offices, key personnel, technical facilities, specializations, and award history.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('nrdi.firms.list') }}" class="btn btn-sm btn-success rajdhani font-weight-bold px-3 py-2">
                <i class="fas fa-list-ul mr-1"></i> List of All Firms
            </a>
            <a href="{{ route('nrdi.procurement.reports.index') }}?type=pcs_by_firms" class="btn btn-sm btn-outline-info rajdhani font-weight-bold px-3 py-2">
                <i class="fas fa-chart-bar mr-1"></i> Firm Cases Report
            </a>
        </div>
    </div>

    {{-- EXACT SEARCH INTERFACE MATCHING REFERENCE IMAGE --}}
    <div class="card-cyber p-4 mb-4">
        <div class="row">
            {{-- Left Box: Category / Scope Selector & View Firms Button --}}
            <div class="col-lg-3 col-md-4 mb-3">
                <div class="h-100 p-3 rounded" style="background: var(--rd-surface); border: 1px solid rgba(255, 255, 255, 0.12);">
                    <div class="form-group mb-3">
                        <label class="field-label text-cyan font-weight-bold">Firms Scope</label>
                        <select id="leftStatusFilter" class="form-control form-control-cyber">
                            <option value="All" selected>All</option>
                            <option value="Active">Active Vendors</option>
                            <option value="Blacklisted">Blacklisted</option>
                            <optgroup label="Entity Types">
                                @foreach($entities as $e)
                                    <option value="{{ $e }}">{{ $e }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Supplier Types">
                                @foreach($types as $t)
                                    <option value="{{ $t }}">{{ $t }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>

                    <button type="button" class="btn-view-firms mt-2" id="btnViewFirms">
                        <i class="fas fa-list mr-1"></i> View Firms
                    </button>

                    <div class="mt-4 pt-3 border-top border-secondary small text-muted">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Matching Records:</span>
                            <strong class="text-info" id="liveRecordCount">0</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Box: Search & Advanced Search Fieldsets --}}
            <div class="col-lg-9 col-md-8 mb-3">
                {{-- Fieldset 1: Top Search Bar --}}
                <fieldset class="search-fieldset">
                    <legend class="search-legend">Search</legend>
                    <div class="d-flex align-items-center gap-2">
                        <input type="text" id="mainSearchInput" class="form-control form-control-cyber flex-grow-1" placeholder="Enter search term...">
                        <button type="button" class="btn-find ml-2" id="btnMainFind">
                            Find
                        </button>
                    </div>
                </fieldset>

                {{-- Fieldset 2: Advanced Search Fields --}}
                <fieldset class="search-fieldset mt-3">
                    <legend class="search-legend">Advanced Search</legend>

                    <div class="row">
                        {{-- 1. General Data --}}
                        <div class="col-md-4 col-sm-6 mb-3">
                            <div class="field-label">General data</div>
                            <select id="advGenField" class="form-control form-control-cyber mb-1">
                                <option value="Name" selected>Name</option>
                                <option value="NTN">NTN</option>
                                <option value="GST">GST</option>
                                <option value="Entity">Entity</option>
                                <option value="Type">Type</option>
                            </select>
                            <input type="text" id="advGenVal" class="form-control form-control-cyber" placeholder="Value...">
                        </div>

                        {{-- 2. Office --}}
                        <div class="col-md-4 col-sm-6 mb-3">
                            <div class="field-label">Office</div>
                            <select id="advOffField" class="form-control form-control-cyber mb-1">
                                <option value="Name" selected>Name</option>
                                <option value="City">City</option>
                                <option value="Address">Address</option>
                                <option value="Type">Type</option>
                            </select>
                            <input type="text" id="advOffVal" class="form-control form-control-cyber" placeholder="Value...">
                        </div>

                        {{-- 3. Contacts --}}
                        <div class="col-md-4 col-sm-6 mb-3">
                            <div class="field-label">Contacts</div>
                            <input type="text" id="advContactVal" class="form-control form-control-cyber mt-4" placeholder="Phone, email, web...">
                        </div>

                        {{-- 4. Speciality --}}
                        <div class="col-md-4 col-sm-6 mb-3">
                            <div class="field-label">Speciality</div>
                            <input type="text" id="advSpecVal" class="form-control form-control-cyber" placeholder="Speciality keyword...">
                        </div>

                        {{-- 5. Person --}}
                        <div class="col-md-4 col-sm-6 mb-3">
                            <div class="field-label">Person</div>
                            <select id="advPerField" class="form-control form-control-cyber mb-1">
                                <option value="Name" selected>Name</option>
                                <option value="Designation">Designation</option>
                                <option value="Department">Department</option>
                                <option value="Expertise">Expertise</option>
                                <option value="Title">Title</option>
                            </select>
                            <input type="text" id="advPerVal" class="form-control form-control-cyber" placeholder="Value...">
                        </div>

                        {{-- 6. Project --}}
                        <div class="col-md-4 col-sm-6 mb-3">
                            <div class="field-label">Project</div>
                            <select id="advPrjField" class="form-control form-control-cyber mb-1">
                                <option value="Name" selected>Name</option>
                                <option value="Scope">Scope</option>
                                <option value="Tech">Tech</option>
                                <option value="Status">Status</option>
                            </select>
                            <input type="text" id="advPrjVal" class="form-control form-control-cyber" placeholder="Value...">
                        </div>

                        {{-- 7. Facility --}}
                        <div class="col-md-4 col-sm-6 mb-3">
                            <div class="field-label">Facility</div>
                            <input type="text" id="advFacilVal" class="form-control form-control-cyber" placeholder="Facility / Equipment...">
                        </div>

                        {{-- Action Controls & Checkbox --}}
                        <div class="col-md-8 col-sm-6 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2 pt-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="chkAnyPart" checked>
                                <label class="form-check-label small text-dark rajdhani font-weight-bold" for="chkAnyPart" style="cursor: pointer;">
                                    Any part of text
                                </label>
                            </div>

                            <div class="d-flex align-items-center gap-3">
                                <button type="button" class="btn-find" id="btnAdvFind">
                                    Find
                                </button>
                                <button type="button" class="btn-reset ml-3" id="btnResetData">
                                    Reset Data
                                </button>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>

    {{-- Results Table (Hidden initially until user performs search) --}}
    <div class="card-cyber p-4" id="searchResultsSection" style="display: none;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="rajdhani font-weight-bold text-dark m-0">
                <i class="fas fa-list text-info mr-2"></i>Search Results
            </h4>
            <span class="badge badge-secondary rajdhani px-3 py-2" id="resultsCountBadge" style="font-size: 13px;">0 Firms Found</span>
        </div>

        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
            <table class="table table-cyber mb-0" id="firmsTable">
                <thead>
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th>Firm / Supplier Name</th>
                        <th>Entity & Type</th>
                        <th>NTN / GST</th>
                        <th class="text-center">Offices</th>
                        <th class="text-center">Personnel</th>
                        <th class="text-center">Awarded Cases</th>
                        <th class="text-right">Total Awarded</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody id="firmsTableBody">
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="fas fa-spinner fa-spin fa-2x mb-2 text-info"></i>
                            <div>Loading firms database...</div>
                        </td>
                    </tr>
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
    // Search Trigger Elements
    const btnViewFirms = document.getElementById('btnViewFirms');
    const btnMainFind = document.getElementById('btnMainFind');
    const btnAdvFind = document.getElementById('btnAdvFind');
    const btnResetData = document.getElementById('btnResetData');

    btnViewFirms.addEventListener('click', performSearch);
    btnMainFind.addEventListener('click', performSearch);
    btnAdvFind.addEventListener('click', performSearch);

    document.getElementById('mainSearchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') performSearch();
    });
    document.getElementById('advGenVal').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') performSearch();
    });

    btnResetData.addEventListener('click', function() {
        document.getElementById('leftStatusFilter').value = 'All';
        document.getElementById('mainSearchInput').value = '';
        document.getElementById('advGenVal').value = '';
        document.getElementById('advOffVal').value = '';
        document.getElementById('advContactVal').value = '';
        document.getElementById('advSpecVal').value = '';
        document.getElementById('advPerVal').value = '';
        document.getElementById('advFacilVal').value = '';
        document.getElementById('advPrjVal').value = '';
        document.getElementById('chkAnyPart').checked = true;
        document.getElementById('searchResultsSection').style.display = 'none';
        document.getElementById('liveRecordCount').innerText = '0';
    });

    function performSearch() {
        document.getElementById('searchResultsSection').style.display = 'block';
        const tbody = document.getElementById('firmsTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="10" class="text-center py-5 text-muted">
                    <i class="fas fa-spinner fa-spin fa-2x mb-2 text-info"></i>
                    <div>Searching firms...</div>
                </td>
            </tr>
        `;

        const params = new URLSearchParams({
            status_filter: document.getElementById('leftStatusFilter').value,
            main_search: document.getElementById('mainSearchInput').value,
            gen_field: document.getElementById('advGenField').value,
            gen_val: document.getElementById('advGenVal').value,
            off_field: document.getElementById('advOffField').value,
            off_val: document.getElementById('advOffVal').value,
            contact_val: document.getElementById('advContactVal').value,
            spec_val: document.getElementById('advSpecVal').value,
            per_field: document.getElementById('advPerField').value,
            per_val: document.getElementById('advPerVal').value,
            facil_val: document.getElementById('advFacilVal').value,
            prj_field: document.getElementById('advPrjField').value,
            prj_val: document.getElementById('advPrjVal').value,
            any_part: document.getElementById('chkAnyPart').checked ? 1 : 0
        });

        fetch('{{ route("nrdi.firms.data") }}?' + params.toString())
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    renderFirmsTable(res.data || []);
                    document.getElementById('liveRecordCount').innerText = res.count || 0;
                    document.getElementById('resultsCountBadge').innerText = (res.count || 0) + ' Firms Found';
                } else {
                    tbody.innerHTML = `<tr><td colspan="10" class="text-danger text-center py-4">Error searching firms: ${res.message || 'Unknown error'}</td></tr>`;
                }
            })
            .catch(err => {
                tbody.innerHTML = `<tr><td colspan="10" class="text-danger text-center py-4">Network error during search.</td></tr>`;
            });
    }

    function renderFirmsTable(firms) {
        const tbody = document.getElementById('firmsTableBody');
        if (!firms || firms.length === 0) {
            tbody.innerHTML = `<tr><td colspan="10" class="text-center py-5 text-muted">No firms matching the search criteria.</td></tr>`;
            return;
        }

        let html = '';
        firms.forEach(f => {
            let statusBadge = f.frm_black 
                ? '<span class="badge badge-danger px-2 py-1">Blacklisted</span>' 
                : '<span class="badge badge-success px-2 py-1">Active</span>';

            html += `
                <tr>
                    <td class="rajdhani text-info font-weight-bold">#${f.frm_id}</td>
                    <td>
                        <div class="font-weight-bold text-dark mb-0" style="font-size: 14px;">${f.frm_name}</div>
                        <div class="small text-muted">${f.frm_notes ? f.frm_notes.substring(0, 40) + '...' : ''}</div>
                    </td>
                    <td>
                        <div class="text-dark">${f.frm_entity}</div>
                        <div class="small text-muted">${f.frm_type}</div>
                    </td>
                    <td>
                        <div class="small text-muted">NTN: <strong class="text-dark">${f.frm_ntn}</strong></div>
                        <div class="small text-muted">GST: <strong class="text-dark">${f.frm_gst}</strong></div>
                    </td>
                    <td class="text-center"><span class="badge badge-dark">${f.offices_count}</span></td>
                    <td class="text-center"><span class="badge badge-dark">${f.persons_count}</span></td>
                    <td class="text-center"><span class="badge badge-info">${f.cases_count}</span></td>
                    <td class="text-right rajdhani font-weight-bold text-success">
                        PKR ${Number(f.total_awarded).toLocaleString()}
                    </td>
                    <td class="text-center">${statusBadge}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-info btn-view-dossier" data-id="${f.frm_id}">
                            <i class="fas fa-folder-open mr-1"></i> Dossier
                        </button>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;

        // Attach Dossier Modal Event
        document.querySelectorAll('.btn-view-dossier').forEach(btn => {
            btn.addEventListener('click', function() {
                const firmId = this.getAttribute('data-id');
                openFirmDossier(firmId);
            });
        });
    }

    function openFirmDossier(firmId) {
        $('#firmDossierModal').modal('show');
        const modalBody = document.getElementById('dossierModalBody');
        modalBody.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-spinner fa-spin fa-2x text-info mb-2"></i>
                <div>Loading complete firm profile...</div>
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
                            <div class="small text-muted">${o.off_address || 'No address'}</div>
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

    // Results will only show when user clicks Find or View Firms
});
</script>
@endsection
