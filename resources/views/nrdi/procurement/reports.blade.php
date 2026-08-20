@extends('welcome')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap');

.report-hub {
    font-family: 'Inter', sans-serif;
    background: #080b0f !important;
    min-height: 100vh;
    color: #cbd5e0;
    padding-top: 20px;
    padding-bottom: 50px;
}

.rajdhani {
    font-family: 'Rajdhani', sans-serif;
    letter-spacing: 0.5px;
}

.card-cyber {
    background: rgba(18, 26, 34, 0.85);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 14px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.report-pill {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 8px;
    padding: 9px 16px;
    color: #94a3b8;
    font-family: 'Rajdhani', sans-serif;
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    text-decoration: none !important;
}

.report-pill:hover {
    background: rgba(0, 191, 255, 0.1);
    border-color: rgba(0, 191, 255, 0.3);
    color: #fff;
}

.report-pill.active {
    background: rgba(0, 191, 255, 0.18);
    border-color: #00BFFF;
    color: #00BFFF;
    box-shadow: 0 0 15px rgba(0, 191, 255, 0.2);
}

.form-control-cyber {
    background: rgba(10, 15, 22, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #fff;
    border-radius: 8px;
    font-size: 13px;
}
.form-control-cyber:focus {
    background: rgba(10, 15, 22, 1);
    border-color: #00BFFF;
    color: #fff;
    box-shadow: 0 0 10px rgba(0, 191, 255, 0.2);
}

.col-checkbox-label {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 6px;
    padding: 4px 10px;
    font-size: 11px;
    color: #94a3b8;
    cursor: pointer;
    user-select: none;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin: 3px;
}

.col-checkbox-label:hover {
    background: rgba(255, 255, 255, 0.08);
    color: #fff;
}

.col-checkbox-label input[type="checkbox"]:checked + span {
    color: #38bdf8;
    font-weight: bold;
}

.col-checkbox-label:has(input[type="checkbox"]:checked) {
    background: rgba(0, 191, 255, 0.15);
    border-color: rgba(0, 191, 255, 0.4);
}

.table-cyber th {
    background: rgba(18, 26, 34, 0.95) !important;
    border-bottom: 2px solid rgba(255, 255, 255, 0.08) !important;
    color: #67e8f9 !important;
    font-family: 'Rajdhani', sans-serif;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 11px;
    font-weight: bold;
    padding: 10px 12px !important;
    white-space: nowrap;
}
.table-cyber td {
    border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
    padding: 10px 12px !important;
    vertical-align: middle;
    font-size: 12px;
    white-space: nowrap;
}
.table-cyber tr:hover {
    background: rgba(255, 255, 255, 0.02) !important;
}

.btn-export {
    background: #10b981;
    border: none;
    color: #fff;
    font-family: 'Rajdhani', sans-serif;
    font-weight: 700;
    font-size: 13px;
    padding: 8px 18px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}
.btn-export:hover {
    background: #059669;
    box-shadow: 0 0 15px rgba(16, 185, 129, 0.4);
    color: #fff;
}
</style>

<div class="content-wrapper report-hub px-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge px-3 py-1 rajdhani" style="background: rgba(0, 191, 255, 0.15); color: #00BFFF; border: 1px solid rgba(0, 191, 255, 0.3); font-size: 10px;">
                    <i class="fas fa-chart-line mr-1"></i> DIRECTORATE OF PROCUREMENT
                </span>
                <span class="badge px-2 py-1 rajdhani" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); font-size: 10px;">
                    <i class="fas fa-file-excel mr-1"></i> EXCEL GENERATION ENGINE
                </span>
            </div>
            <h2 class="font-weight-bold text-white rajdhani m-0" style="font-size: 2rem;">
                <i class="fas fa-boxes mr-2 text-info"></i>Procurement & Inventory Reports Hub
            </h2>
            <p class="text-muted m-0 small">Create custom inventory/asset reports across all divisions with selectable columns and dynamic procurement audit reports.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn-export" id="btnExportExcel">
                <i class="fas fa-file-excel"></i> Export to Excel (.csv)
            </button>
        </div>
    </div>

    {{-- Report Categories Tabs --}}
    <div class="card-cyber p-3 mb-4">
        <div class="d-flex flex-wrap gap-2" id="reportPillsContainer">
            <a href="javascript:void(0)" class="report-pill active" data-type="inventory_assets_custom">
                <i class="fas fa-boxes text-info"></i> 1. Custom Inventory & Assets
            </a>
            <a href="javascript:void(0)" class="report-pill" data-type="pcs_by_firms">
                <i class="fas fa-building text-warning"></i> 2. Purchase Cases by Firms
            </a>
            <a href="javascript:void(0)" class="report-pill" data-type="pcs_by_firms_projects">
                <i class="fas fa-project-diagram text-cyan"></i> 3. PCs by Firms & Projects
            </a>
            <a href="javascript:void(0)" class="report-pill" data-type="pcs_by_single_firm">
                <i class="fas fa-store text-success"></i> 4. PCs by Single Firm
            </a>
            <a href="javascript:void(0)" class="report-pill" data-type="cases_without_items">
                <i class="fas fa-exclamation-triangle text-danger"></i> 5. Cases Without Items
            </a>
            <a href="javascript:void(0)" class="report-pill" data-type="allocations_status">
                <i class="fas fa-coins text-warning"></i> 6. Allocation Status
            </a>
            <a href="javascript:void(0)" class="report-pill" data-type="accounts_status">
                <i class="fas fa-wallet text-info"></i> 7. Account Status
            </a>
            <a href="javascript:void(0)" class="report-pill" data-type="proj_shares_status">
                <i class="fas fa-share-alt text-primary"></i> 8. Project Share Status
            </a>
            <a href="javascript:void(0)" class="report-pill" data-type="subheads_status">
                <i class="fas fa-list-ol text-light"></i> 9. Subheads Status
            </a>
            <a href="javascript:void(0)" class="report-pill" data-type="csrf_status">
                <i class="fas fa-hand-holding-usd text-success"></i> 10. CSRF Status
            </a>
        </div>
    </div>

    {{-- Filter Panel --}}
    <div class="card-cyber p-4 mb-4">
        <div class="row align-items-end">
            {{-- Category Filter (Asset vs Inventory vs All) --}}
            <div class="col-md-2 col-sm-6 mb-3" id="filterCategoryBox">
                <label class="rajdhani text-muted font-weight-bold small mb-1">Category</label>
                <select id="filterCategory" class="form-control form-control-cyber">
                    <option value="All">All Categories</option>
                    <option value="Assets">Assets Only</option>
                    <option value="Inventory">Inventory Only</option>
                </select>
            </div>

            {{-- Status Filter --}}
            <div class="col-md-2 col-sm-6 mb-3" id="filterStatusBox">
                <label class="rajdhani text-muted font-weight-bold small mb-1">Status</label>
                <select id="filterStatus" class="form-control form-control-cyber">
                    <option value="All">All Statuses</option>
                    <option value="On Charge">On Charge (Store Custody)</option>
                    <option value="Charged Off">Charged Off (Dispensed)</option>
                    <option value="Untagged">Untagged</option>
                    <option value="Tagged">Tagged</option>
                    <option value="Held">Held</option>
                    <option value="Issued to User">Issued to User</option>
                    <option value="Installed">Installed</option>
                    <option value="Consumed">Consumed</option>
                    <option value="Written Off">Written Off</option>
                    <option value="Approved">Approved (PCs)</option>
                    <option value="Draft">Draft (PCs)</option>
                    <option value="Under Scrutiny">Under Scrutiny (PCs)</option>
                </select>
            </div>

            {{-- Division / Department Filter (All Divisions) --}}
            <div class="col-md-3 col-sm-6 mb-3">
                <label class="rajdhani text-muted font-weight-bold small mb-1">Division / Unit</label>
                <select id="filterUnit" class="form-control form-control-cyber">
                    <option value="All">All Divisions & Units</option>
                    @foreach($units as $u)
                        <option value="{{ $u->unt_id }}">{{ $u->unt_namesh ?: $u->unt_name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Project Filter --}}
            <div class="col-md-3 col-sm-6 mb-3" id="filterProjectBox">
                <label class="rajdhani text-muted font-weight-bold small mb-1">Project</label>
                <select id="filterProject" class="form-control form-control-cyber">
                    <option value="All">All Projects</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->prj_id }}">{{ $p->prj_code }} - {{ \Illuminate\Support\Str::limit($p->prj_title, 25) }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Firm / Supplier Filter --}}
            <div class="col-md-2 col-sm-6 mb-3" id="filterFirmBox" style="display: none;">
                <label class="rajdhani text-muted font-weight-bold small mb-1">Supplier / Firm</label>
                <select id="filterFirm" class="form-control form-control-cyber">
                    <option value="All">All Suppliers</option>
                    @foreach($firms as $f)
                        <option value="{{ $f->frm_id }}">{{ $f->frm_name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Action Buttons --}}
            <div class="col-md-2 col-sm-6 mb-3">
                <button type="button" class="btn btn-info btn-block rajdhani font-weight-bold" id="btnApplyFilter">
                    <i class="fas fa-sync-alt mr-1"></i> GENERATE REPORT
                </button>
            </div>
        </div>

        {{-- Search Input --}}
        <div class="row mt-1">
            <div class="col-md-12">
                <input type="text" id="filterSearch" class="form-control form-control-cyber" placeholder="Keyword search in description, custodian person, location, remarks, or case title...">
            </div>
        </div>

        {{-- Custom Column Picker (For Custom Inventory & Assets Report) --}}
        <div class="mt-4 pt-3 border-top border-secondary" id="columnSelectorSection">
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                <div class="rajdhani font-weight-bold text-white" style="font-size: 14px;">
                    <i class="fas fa-columns text-cyan mr-2"></i>CUSTOM COLUMN SELECTOR:
                </div>
                <div>
                    <button type="button" class="btn btn-xs btn-outline-info rounded px-2 py-1 mr-1" id="btnSelectAllCols">Select All</button>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded px-2 py-1" id="btnDeselectAllCols">Deselect All</button>
                </div>
            </div>

            <div class="d-flex flex-wrap" id="checkboxesGrid">
                @foreach($customAssetColumns as $colKey => $colLabel)
                    <label class="col-checkbox-label">
                        <input type="checkbox" name="custom_cols" value="{{ $colKey }}" checked>
                        <span>{{ $colLabel }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Report Data Table Preview --}}
    <div class="card-cyber p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="rajdhani font-weight-bold text-white m-0" id="previewTitle">
                    Custom Inventory & Assets Report Preview
                </h4>
                <div class="small text-muted" id="previewSummary">Loading live records...</div>
            </div>
            <span class="badge badge-info rajdhani px-3 py-2" id="recordCountBadge" style="font-size: 13px;">0 Records</span>
        </div>

        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
            <table class="table table-cyber mb-0" id="reportPreviewTable">
                <thead id="reportTableHead">
                    {{-- Dynamically populated --}}
                </thead>
                <tbody id="reportTableBody">
                    <tr>
                        <td colspan="15" class="text-center py-5 text-muted">
                            <i class="fas fa-spinner fa-spin fa-2x mb-2 text-info"></i>
                            <div>Fetching report data...</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentType = 'inventory_assets_custom';
    let cachedData = [];

    const columnNamesMap = {
        'item_id': 'Item ID',
        'description': 'Description',
        'dept': 'Department / Division',
        'dept_details': 'Dept Details',
        'qty': 'Quantity',
        'denomination': 'Denomination',
        'charge_qty': 'Charge Quantity',
        'charge_denomination': 'Charge Denomination',
        'charge_date': 'Charge Date',
        'price': 'Price w/o Tax (Rs)',
        'asset_inventory': 'Asset / Inventory',
        'subtype': 'Subtype',
        'parent_item': 'Parent Item',
        'location': 'Location',
        'custodian_group': 'Custodian Group',
        'custodian_person': 'Custodian Person',
        'shared': 'Shared',
        'is_parent': 'Is Parent',
        'is_assembly': 'Is Assembly',
        'disposal_date': 'Disposal Date',
        'status': 'Status',
        'remarks': 'Remarks',
        'purchase_case_id': 'Purchase Case ID',
        'project': 'Project',
        'pcs_id': 'Case Ref #',
        'pcs_title': 'Case Title',
        'pcs_date': 'Case Date',
        'pcs_price': 'Quoted / Awarded (Rs)',
        'pcs_intprice': 'Initiated Cost (Rs)',
        'pcs_midprice': 'Mid Cost (Rs)',
        'pcs_status': 'Case Status',
        'frm_name': 'Supplier / Firm',
        'frm_type': 'Firm Type',
        'division': 'Division',
        'prj_code': 'Project Code',
        'prj_title': 'Project Title',
        'firm_name': 'Supplier Firm',
        'hed_code': 'Head Code',
        'hed_name': 'Head Name',
        'project_code': 'Project Code',
        'allocation': 'Allocation (Rs)',
        'mtss_share': 'MTSS Share (Rs)',
        'rdw_share': 'RDW Share (Rs)',
        'csrf_share': 'CSRF Share (Rs)',
        'equipment_share': 'Equipment Share (Rs)',
        'hr_share': 'HR Share (Rs)',
        'misc_share': 'Misc Share (Rs)',
        'received': 'Received (Rs)',
        'commitments': 'Commitments (Rs)',
        'expenditure': 'Expenditure (Rs)',
        'balance': 'Balance (Rs)',
        'available': 'Available (Rs)',
        'cf_share': 'CSRF Share (Rs)',
        'cf_received': 'CSRF Received (Rs)',
        'cf_expenditure': 'CSRF Spent (Rs)',
        'cf_balance': 'CSRF Balance (Rs)',
        'cf_commitments': 'CSRF Commitments (Rs)',
        'cf_in_process': 'CSRF In Process (Rs)',
        'cf_available': 'CSRF Available (Rs)',
        'sha_cf': 'CSRF Share (Rs)',
        'sha_pcc': 'PCC Share (Rs)',
        'sha_prj': 'Project Share (Rs)',
        'sha_prj_sal': 'Salary Share (Rs)',
        'sha_prj_pur': 'Purchase Share (Rs)',
        'sbh_id': 'Subhead ID',
        'sbh_name': 'Subhead Name',
        'sbh_alloc': 'Subhead Allocation (Rs)'
    };

    // Tab Switching
    document.querySelectorAll('.report-pill').forEach(pill => {
        pill.addEventListener('click', function() {
            document.querySelectorAll('.report-pill').forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            currentType = this.getAttribute('data-type');
            
            // Adjust UI visibility based on report type
            if (currentType === 'inventory_assets_custom') {
                document.getElementById('columnSelectorSection').style.display = 'block';
                document.getElementById('filterCategoryBox').style.display = 'block';
                document.getElementById('filterStatusBox').style.display = 'block';
                document.getElementById('filterFirmBox').style.display = 'none';
                document.getElementById('previewTitle').innerText = 'Custom Inventory & Assets Report Preview';
            } else {
                document.getElementById('columnSelectorSection').style.display = 'none';
                if (currentType === 'pcs_by_single_firm') {
                    document.getElementById('filterFirmBox').style.display = 'block';
                } else {
                    document.getElementById('filterFirmBox').style.display = 'none';
                }
                document.getElementById('previewTitle').innerText = this.innerText.trim() + ' Preview';
            }

            fetchReportData();
        });
    });

    // Select/Deselect All Columns
    document.getElementById('btnSelectAllCols').addEventListener('click', () => {
        document.querySelectorAll('input[name="custom_cols"]').forEach(cb => cb.checked = true);
        renderTable(cachedData);
    });
    document.getElementById('btnDeselectAllCols').addEventListener('click', () => {
        document.querySelectorAll('input[name="custom_cols"]').forEach(cb => cb.checked = false);
        renderTable(cachedData);
    });

    // Checkbox change re-render
    document.querySelectorAll('input[name="custom_cols"]').forEach(cb => {
        cb.addEventListener('change', () => renderTable(cachedData));
    });

    // Apply Filter Button & Keypress
    document.getElementById('btnApplyFilter').addEventListener('click', fetchReportData);
    document.getElementById('filterSearch').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') fetchReportData();
    });

    // Fetch Report Data from Server
    function fetchReportData() {
        const tbody = document.getElementById('reportTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="15" class="text-center py-5 text-muted">
                    <i class="fas fa-spinner fa-spin fa-2x mb-2 text-info"></i>
                    <div>Fetching records...</div>
                </td>
            </tr>
        `;

        const params = new URLSearchParams({
            type: currentType,
            category: document.getElementById('filterCategory').value,
            status: document.getElementById('filterStatus').value,
            unit_id: document.getElementById('filterUnit').value,
            project_id: document.getElementById('filterProject').value,
            firm_id: document.getElementById('filterFirm').value,
            search: document.getElementById('filterSearch').value,
            limit: 500
        });

        fetch('{{ route("nrdi.procurement.reports.data") }}?' + params.toString())
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    cachedData = res.data || [];
                    document.getElementById('recordCountBadge').innerText = (res.count || 0) + ' Records';
                    document.getElementById('previewSummary').innerText = `Displaying ${res.count || 0} records matching criteria`;
                    renderTable(cachedData);
                } else {
                    tbody.innerHTML = `<tr><td colspan="15" class="text-danger text-center py-4">Error loading data: ${res.message || 'Unknown error'}</td></tr>`;
                }
            })
            .catch(err => {
                tbody.innerHTML = `<tr><td colspan="15" class="text-danger text-center py-4">Network error loading report data.</td></tr>`;
            });
    }

    // Render Table Based on Data & Columns
    function renderTable(data) {
        const thead = document.getElementById('reportTableHead');
        const tbody = document.getElementById('reportTableBody');

        if (!data || data.length === 0) {
            thead.innerHTML = `<tr><th>Information</th></tr>`;
            tbody.innerHTML = `<tr><td class="text-center py-4 text-muted">No records found matching the active filters.</td></tr>`;
            return;
        }

        let columns = [];
        if (currentType === 'inventory_assets_custom') {
            document.querySelectorAll('input[name="custom_cols"]:checked').forEach(cb => {
                columns.push(cb.value);
            });
            if (columns.length === 0) {
                columns = Object.keys(data[0]);
            }
        } else {
            columns = Object.keys(data[0]);
        }

        // Build Header
        let thHtml = '<tr>';
        columns.forEach(col => {
            let label = columnNamesMap[col] || col.replace(/_/g, ' ').toUpperCase();
            thHtml += `<th>${label}</th>`;
        });
        thHtml += '</tr>';
        thead.innerHTML = thHtml;

        // Build Rows
        let trHtml = '';
        data.forEach(row => {
            trHtml += '<tr>';
            columns.forEach(col => {
                let val = row[col] !== undefined && row[col] !== null ? row[col] : 'N/A';
                
                // Format price / currency fields
                if (['price', 'pcs_price', 'pcs_intprice', 'pcs_midprice', 'allocation', 'mtss_share', 'received', 'commitments', 'expenditure', 'balance', 'available', 'cf_share', 'cf_expenditure', 'cf_received', 'cf_balance', 'sha_cf', 'sha_pcc', 'sha_prj', 'sha_prj_sal', 'sha_prj_pur', 'sbh_alloc', 'prj_aprvcost'].includes(col) && typeof val === 'number') {
                    val = '<span class="rajdhani font-weight-bold text-success">PKR ' + Number(val).toLocaleString() + '</span>';
                }

                // Format status badges
                if (col === 'status' || col === 'pcs_status') {
                    let badgeClass = 'badge-secondary';
                    if (['Approved', 'On Charge', 'Tagged'].includes(val)) badgeClass = 'badge-success';
                    if (['Draft', 'Untagged', 'Held'].includes(val)) badgeClass = 'badge-warning';
                    if (['Under Scrutiny', 'In-Pipeline'].includes(val)) badgeClass = 'badge-info';
                    if (['Charged Off', 'Issued to User', 'Installed', 'Consumed'].includes(val)) badgeClass = 'badge-primary';
                    val = `<span class="badge ${badgeClass} px-2 py-1">${val}</span>`;
                }

                trHtml += `<td>${val}</td>`;
            });
            trHtml += '</tr>';
        });
        tbody.innerHTML = trHtml;
    }

    // Export to Excel Button
    document.getElementById('btnExportExcel').addEventListener('click', function() {
        let selectedCols = [];
        if (currentType === 'inventory_assets_custom') {
            document.querySelectorAll('input[name="custom_cols"]:checked').forEach(cb => {
                selectedCols.push(cb.value);
            });
        }

        const params = new URLSearchParams({
            type: currentType,
            category: document.getElementById('filterCategory').value,
            status: document.getElementById('filterStatus').value,
            unit_id: document.getElementById('filterUnit').value,
            project_id: document.getElementById('filterProject').value,
            firm_id: document.getElementById('filterFirm').value,
            search: document.getElementById('filterSearch').value,
            columns: selectedCols.join(',')
        });

        window.location.href = '{{ route("nrdi.procurement.reports.export") }}?' + params.toString();
    });

    // Initial Load
    fetchReportData();
});
</script>
@endsection
