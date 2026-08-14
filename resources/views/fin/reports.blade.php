@extends('welcome')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap');

    .finance-hub {
        font-family: 'Inter', sans-serif;
        background: #080b0f !important;
        min-height: 100vh;
        color: #cbd5e0;
        padding-top: 15px;
    }

    .rajdhani {
        font-family: 'Rajdhani', sans-serif;
        letter-spacing: 0.5px;
    }

    /* Premium Cyber/Glass Panels */
    .card-cyber {
        background: rgba(18, 26, 34, 0.85);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 14px;
        box-shadow: 0 0 24px rgba(0, 0, 0, 0.2);
    }

    .kpi-title {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: rgba(229, 229, 229, 0.6);
        font-weight: 600;
    }

    /* Form Controls */
    .form-control-cyber {
        background: rgba(8, 11, 15, 0.8) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        color: #fff !important;
        border-radius: 8px !important;
        height: 38px !important;
        font-size: 13px !important;
        transition: all 0.2s ease !important;
    }
    .form-control-cyber:focus {
        border-color: rgba(0, 191, 255, 0.4) !important;
        box-shadow: 0 0 8px rgba(0, 191, 255, 0.15) !important;
    }

    /* Table Design */
    .table-cyber {
        background: transparent;
        color: #cbd5e0;
    }
    .table-cyber th {
        background: rgba(18, 26, 34, 0.95) !important;
        border-bottom: 2px solid rgba(255, 255, 255, 0.08) !important;
        color: #67e8f9 !important;
        font-family: 'Rajdhani', sans-serif;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 12px;
        font-weight: bold;
        padding: 12px 16px !important;
    }
    .table-cyber td {
        border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
        padding: 12px 16px !important;
        vertical-align: middle;
        font-size: 13px;
    }
    .table-cyber tr:hover {
        background: rgba(255, 255, 255, 0.02) !important;
    }

    /* Columns Selection Grid */
    .columns-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 10px 15px;
        background: rgba(8, 11, 15, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 10px;
        padding: 18px;
    }
    .checkbox-option {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        font-size: 13px;
        color: #cbd5e0;
        margin: 0;
        transition: color 0.15s ease;
    }
    .checkbox-option:hover {
        color: #fff;
    }
    .checkbox-option input[type="checkbox"] {
        accent-color: #00BFFF;
        cursor: pointer;
        width: 16px;
        height: 16px;
    }

    /* Buttons */
    .btn-cyber-primary {
        background: rgba(0, 191, 255, 0.1);
        border: 1px solid rgba(0, 191, 255, 0.4);
        color: #00BFFF;
        font-weight: 600;
        font-size: 13px;
        border-radius: 20px;
        height: 38px;
        padding: 0 20px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-cyber-primary:hover {
        background: rgba(0, 191, 255, 0.25);
        box-shadow: 0 0 15px rgba(0, 191, 255, 0.25);
        color: #fff;
    }

    .btn-cyber-secondary {
        background: rgba(34, 197, 94, 0.1);
        border: 1px solid rgba(34, 197, 94, 0.4);
        color: #22c55e;
        font-weight: 600;
        font-size: 13px;
        border-radius: 20px;
        height: 38px;
        padding: 0 20px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-cyber-secondary:hover {
        background: rgba(34, 197, 94, 0.25);
        box-shadow: 0 0 15px rgba(34, 197, 94, 0.25);
        color: #fff;
    }

    /* Loader */
    .dashboard-loader {
        font-size: 12px;
        color: #00BFFF;
        font-weight: 500;
        letter-spacing: 0.5px;
    }

    /* Custom Select2 Cyber Dark Theme Overrides */
    .select2-container--bootstrap4 .select2-selection {
        background-color: rgba(8, 11, 15, 0.8) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        color: #fff !important;
        border-radius: 8px !important;
        min-height: 38px !important;
        transition: all 0.2s ease !important;
    }
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
        background-color: rgba(0, 191, 255, 0.15) !important;
        border: 1px solid rgba(0, 191, 255, 0.4) !important;
        color: #00BFFF !important;
        border-radius: 4px !important;
        font-size: 12px !important;
        margin-top: 5px !important;
    }
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove {
        color: #ff3e3e !important;
        margin-right: 5px !important;
    }
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #ff0000 !important;
    }
    .select2-container--bootstrap4 .select2-search__field {
        color: #fff !important;
        background: transparent !important;
    }
    .select2-dropdown {
        background-color: #121a22 !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: #cbd5e0 !important;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5) !important;
        z-index: 9999 !important;
    }
    .select2-results__option {
        padding: 8px 12px !important;
        font-size: 13px !important;
    }
    .select2-results__option--highlighted[aria-selected] {
        background-color: rgba(0, 191, 255, 0.2) !important;
        color: #fff !important;
    }
    .select2-container--bootstrap4 .select2-results__option[aria-selected=true] {
        background-color: rgba(0, 191, 255, 0.1) !important;
        color: #00BFFF !important;
    }
</style>

<div class="content-wrapper finance-hub px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <h2 class="font-weight-bold text-white rajdhani m-0">
            <i class="fas fa-chart-line mr-2 text-info"></i>Finance Reports Center
        </h2>
        
        <div id="loader" class="dashboard-loader" style="display: none;">
            <i class="fas fa-spinner fa-spin mr-1"></i> QUERYING DATABASE...
        </div>
    </div>

    <!-- Filters Dashboard Card -->
    <div class="card-cyber px-4 py-3 mb-4">
        <div class="row">
            <!-- 1. Report Selector -->
            <div class="col-md-4 mb-3">
                <label class="kpi-title mb-2 d-block"><i class="fas fa-file-alt mr-1 text-cyan"></i> Report Type</label>
                <select id="report-type" class="form-control form-control-cyber">
                    <option value="">-- Select Report Type --</option>
                    <option value="inventory_assets">Inventory & Assets Custody Report</option>
                    <option value="allocations_status">Allocations Status</option>
                    <option value="accounts_status">Accounts Status</option>
                    <option value="proj_shares_status">Proj Shares Status</option>
                    <option value="subheads_status">Subheads Status</option>
                    <option value="csrf_status">CSRF Status</option>
                    <option value="monthly_return">Monthly Return</option>
                    <option value="pcs_awaiting_payment">PCs Awaiting Payment</option>
                    <option value="current_employees">Current Employees</option>
                    <option value="pcs_by_firms">PCs by Firms</option>
                    <option value="pcs_by_firms_projects">PCs by Firms & Projects</option>
                    <option value="pcs_by_single_firm">PCs by Single Firm</option>
                    <option value="attachment_summary">Attachment Summary</option>
                    <option value="reversals_shifting_history">Head History & Shifting</option>
                    <option value="fund_shifting">Fund Shifting Report</option>
                </select>
            </div>

            <!-- 2. Division Filter -->
            <div class="col-md-4 mb-3" id="div-filter-container">
                <label class="kpi-title mb-2 d-block"><i class="fas fa-sitemap mr-1 text-cyan"></i> Division / Unit</label>
                <select id="division-dropdown" class="form-control form-control-cyber">
                    <option value="All">All Divisions</option>
                    @foreach($units as $unit)
                    <option value="{{ $unit->unt_id }}">{{ $unit->unt_name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 3. Project Filter -->
            <div class="col-md-4 mb-3" id="project-filter-container">
                <label class="kpi-title mb-2 d-block"><i class="fas fa-project-diagram mr-1 text-cyan"></i> Project</label>
                <select id="project-dropdown" class="form-control form-control-cyber">
                    <option value="All">All Projects</option>
                    @foreach($projects as $p)
                    <option value="{{ $p->prj_id }}" data-division="{{ $p->prj_unt_id }}">{{ $p->prj_code }} — {{ $p->prj_title }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row">
            <!-- 4. Category Filter -->
            <div class="col-md-6 mb-3" id="category-filter-container">
                <label class="kpi-title mb-2 d-block"><i class="fas fa-tags mr-1 text-cyan"></i> Category</label>
                <select id="category-dropdown" class="form-control form-control-cyber">
                    <option value="All">All Items (Assets & Inventory)</option>
                    <option value="Assets">Assets (Capital Items)</option>
                    <option value="Inventory">Inventory (Consumable/Stationery)</option>
                </select>
            </div>

            <!-- 5. Status Filter -->
            <div class="col-md-6 mb-3" id="status-filter-container">
                <label class="kpi-title mb-2 d-block"><i class="fas fa-info-circle mr-1 text-cyan"></i> Statuses</label>
                <select id="status-dropdown" class="form-control form-control-cyber" multiple="multiple" style="width: 100%;">
                    <option value="Untagged">Untagged</option>
                    <option value="Tagged">Tagged</option>
                    <option value="Held">Held (Store Custody)</option>
                    <option value="Issued to User">Issued to User</option>
                    <option value="Installed">Installed</option>
                    <option value="Consumed">Consumed</option>
                    <option value="Written Off">Written Off</option>
                </select>
            </div>
        </div>

        <div class="row">
            <!-- 5b. Firm Filter (Hidden by default) -->
            <div class="col-md-4 mb-3 d-none" id="firm-filter-container">
                <label class="kpi-title mb-2 d-block"><i class="fas fa-building mr-1 text-cyan"></i> Select Firm</label>
                <select id="firm-dropdown" class="form-control form-control-cyber">
                    <option value="All">All Firms</option>
                    @foreach($firms as $f)
                    <option value="{{ $f->frm_id }}">{{ $f->frm_name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 5c. Start Date Filter (Hidden by default) -->
            <div class="col-md-4 mb-3 d-none" id="start-date-container">
                <label class="kpi-title mb-2 d-block"><i class="far fa-calendar-alt mr-1 text-cyan"></i> Start Date</label>
                <input type="date" id="start-date" class="form-control form-control-cyber">
            </div>

            <!-- 5d. End Date Filter (Hidden by default) -->
            <div class="col-md-4 mb-3 d-none" id="end-date-container">
                <label class="kpi-title mb-2 d-block"><i class="far fa-calendar-alt mr-1 text-cyan"></i> End Date</label>
                <input type="date" id="end-date" class="form-control form-control-cyber">
            </div>
        </div>

        <div class="row">
            <!-- 6. Custom Columns Selector Grid -->
            <div class="col-12 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap" style="gap: 10px;">
                    <label class="kpi-title m-0"><i class="fas fa-table mr-1 text-cyan"></i> Select Columns to Include</label>
                    <div style="gap: 15px;" class="d-flex">
                        <a href="javascript:void(0)" class="text-info font-weight-bold text-xs" id="col-select-all" style="text-decoration: none;"><i class="fas fa-check-square mr-1"></i> Select All</a>
                        <a href="javascript:void(0)" class="text-warning font-weight-bold text-xs" id="col-deselect-all" style="text-decoration: none;"><i class="fas fa-minus-square mr-1"></i> Clear All</a>
                    </div>
                </div>
                <div class="columns-grid">
                    @foreach($columns as $key => $label)
                    <label class="checkbox-option">
                        <input type="checkbox" value="{{ $key }}" class="col-checkbox" checked>
                        <span>{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Action Panel -->
        <div class="d-flex justify-content-end gap-3 mt-2 flex-wrap" style="gap: 12px;">
            <button id="btn-reset" class="btn btn-sm btn-outline-secondary px-4" style="border-radius: 20px; height: 38px; font-weight: 600;">
                <i class="fas fa-undo mr-1"></i> Reset
            </button>
            <button id="btn-generate" class="btn-cyber-primary">
                <i class="fas fa-cogs"></i> Generate Report
            </button>
            <button id="btn-export" class="btn-cyber-secondary">
                <i class="fas fa-file-excel"></i> Export Excel
            </button>
        </div>
    </div>

    <!-- Output View Card -->
    <div class="card-cyber p-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <span class="font-weight-bold text-white text-sm">
                <i class="fas fa-list mr-2 text-cyan"></i> Generated Report Output
            </span>
            <span class="text-muted text-xs" id="records-counter">0 records loaded</span>
        </div>

        <div class="rd-table-responsive" style="max-height: 600px; overflow-y: auto; border: 1px solid rgba(255,255,255,0.05); border-radius: 8px;">
            <div id="table-placeholder" class="text-center py-5 text-muted">
                <i class="fas fa-table mb-3 text-cyan" style="font-size: 3rem;"></i><br>
                Configure the filters above and click "Generate Report" to view results.
            </div>
            
            <table class="table table-hover table-cyber text-nowrap m-0 d-none" id="output-table">
                <thead class="sticky-top">
                    <tr id="table-headers">
                        <!-- Dynamic headers -->
                    </tr>
                </thead>
                <tbody id="table-body">
                    <!-- Dynamic rows -->
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const isDivisionUser = {{ (Auth::user()->isDivision()) ? 'true' : 'false' }};
    const userUnitId = "{{ Auth::user()->acc_unt_id }}";

    // Dynamic columns for each report type
    const reportColumns = {
        inventory_assets: {
            id: 'Item ID',
            desc: 'Description',
            category: 'Category',
            subtype: 'Subtype',
            qty: 'Quantity',
            price: 'Unit Price',
            total_value: 'Total Value',
            status: 'Status',
            person: 'Custodian',
            location: 'Location',
            division: 'Division',
            head_code: 'Project Head',
            purchase_case: 'Purchase Case',
            charge_date: 'Charge Date',
            disposal_date: 'Disposal Date',
            remarks: 'Remarks'
        },
        allocations_status: {
            head_code: 'Head Code',
            head_name: 'Head Name',
            project_code: 'Project Code',
            allocation: 'Allocation',
            mtss_share: 'MTSS Share',
            rdw_share: 'RDW Share',
            csrf_share: 'CSRF Share',
            equipment_share: 'Equipment',
            hr_share: 'Utilized: HR',
            misc_share: 'Misc'
        },
        accounts_status: {
            hed_code: 'Head Code',
            hed_name: 'Head Name',
            project_code: 'Project Code',
            allocation: 'Allocation',
            mtss_share: 'MTSS Share',
            received: 'Received',
            commitments: 'Commitments',
            expenditure: 'Expenditure',
            cf_share: 'CSRF Share',
            cf_expenditure: 'CSRF Spent',
            balance: 'Balance',
            available: 'Available'
        },
        proj_shares_status: {
            prj_code: 'Project Code',
            prj_title: 'Project Title',
            prj_aprvcost: 'Approved Budget',
            sha_cf: 'CF Share',
            sha_pcc: 'PCC Share',
            sha_prj: 'Project Share',
            sha_prj_sal: 'Salary Share',
            sha_prj_pur: 'Purchase Share'
        },
        subheads_status: {
            sbh_id: 'Subhead ID',
            sbh_name: 'Subhead Name',
            sbh_alloc: 'Allocation',
            head_code: 'Head Code',
            head_name: 'Head Name'
        },
        csrf_status: {
            prj_code: 'Project Code',
            prj_title: 'Project Title',
            hed_code: 'Head Code',
            cf_share: 'CF Share',
            cf_received: 'CF Received',
            cf_expenditure: 'CF Spent',
            cf_balance: 'CF Balance',
            cf_commitments: 'CF Commitments',
            cf_in_process: 'CF In Process',
            cf_available: 'CF Available'
        },
        monthly_return: {
            trn_id: 'Transaction ID',
            trn_date: 'Transaction Date',
            trn_amount1: 'Amount 1',
            trn_amount2: 'Amount 2',
            trn_tax1: 'Tax 1',
            trn_balance: 'Balance',
            trn_transtype: 'Transaction Type',
            head_code: 'Head Code',
            head_name: 'Head Name'
        },
        pcs_awaiting_payment: {
            pcs_id: 'PC ID',
            pcs_title: 'PC Title',
            pcs_date: 'PC Date',
            pcs_status: 'PC Status',
            pcs_price: 'PC Price',
            pcs_intprice: 'Int Price',
            pcs_midprice: 'Mid Price',
            head_code: 'Head Code',
            frm_name: 'Firm Name'
        },
        current_employees: {
            emp_id: 'Employee ID',
            emp_cnic: 'CNIC',
            emp_name: 'Employee Name',
            emp_title: 'Title',
            emp_rank: 'Rank',
            emp_status: 'Status',
            emp_joindt: 'Join Date',
            emp_lastdt: 'Last Date',
            emp_remarks: 'Remarks',
            emp_locked: 'Locked',
            emp_cleared: 'Cleared',
            emp_photodest: 'Photo Path',
            division: 'Division',
            head_code: 'Project Head'
        },
        pcs_by_firms: {
            pcs_id: 'PC ID',
            pcs_title: 'PC Title',
            pcs_date: 'PC Date',
            pcs_price: 'PC Price',
            frm_name: 'Firm Name',
            frm_type: 'Firm Type',
            pcs_status: 'PC Status'
        },
        pcs_by_firms_projects: {
            pcs_id: 'PC ID',
            pcs_title: 'PC Title',
            pcs_date: 'PC Date',
            pcs_price: 'PC Price',
            frm_name: 'Firm Name',
            prj_code: 'Project Code',
            prj_title: 'Project Title',
            pcs_status: 'PC Status'
        },
        pcs_by_single_firm: {
            pcs_id: 'PC ID',
            pcs_title: 'PC Title',
            pcs_date: 'PC Date',
            pcs_price: 'PC Price',
            frm_name: 'Firm Name',
            pcs_status: 'PC Status'
        },
        attachment_summary: {
            pat_id: 'Attachment ID',
            source_type: 'Attachment Source',
            obj_type: 'Object Type',
            obj_id: 'Object ID',
            file_type: 'File Type',
            file_path: 'File Path',
            purchase_case: 'Purchase Case'
        },
        reversals_shifting_history: {
            head_code: 'Head Code',
            head_name: 'Head Name',
            project_code: 'Project Code',
            division: 'Division',
            hed_opendt: 'Opened Date',
            reversals_count: 'Reversals Filed',
            shifts_count: 'Fund Shifts',
            last_shift_amount: 'Last Shift Amount',
            last_shift_date: 'Last Shift Date',
            balance_before: 'Balance Before Shift',
            balance_after: 'Balance After Shift'
        },
        fund_shifting: {
            date: 'Transfer Date',
            title: 'Subhead / Description',
            amount: 'Shifted Amount',
            type_label: 'Transfer Type',
            from_code: 'From Head Code',
            from_name: 'From Head Name',
            from_division: 'From Division',
            from_old_val: 'From Old Balance',
            from_new_val: 'From New Balance',
            to_code: 'To Head Code',
            to_name: 'To Head Name',
            to_division: 'To Division',
            to_old_val: 'To Old Balance',
            to_new_val: 'To New Balance',
            status: 'Status'
        }
    };

    // Global column styling mapping for table generation
    const columnMeta = {
        id: { label: 'Item ID', class: 'text-left font-weight-bold text-white' },
        desc: { label: 'Description', class: 'text-wrap font-weight-bold text-white', style: 'min-width: 250px;' },
        category: { label: 'Category', class: '' },
        subtype: { label: 'Subtype', class: 'text-muted' },
        qty: { label: 'Qty', class: 'text-center' },
        price: { label: 'Unit Price', class: 'text-right' },
        total_value: { label: 'Total Value', class: 'text-right font-weight-bold text-info' },
        status: { label: 'Status', class: 'text-center' },
        person: { label: 'Custodian', class: 'text-white' },
        location: { label: 'Location', class: '' },
        division: { label: 'Division', class: '' },
        head_code: { label: 'Project Head', class: 'text-cyan font-weight-bold' },
        purchase_case: { label: 'Purchase Case', class: 'text-muted text-wrap', style: 'max-width: 200px;' },
        charge_date: { label: 'Charge Date', class: 'text-muted' },
        disposal_date: { label: 'Disposal Date', class: 'text-muted' },
        remarks: { label: 'Remarks', class: 'text-muted text-wrap', style: 'max-width: 200px;' },
        
        // Dynamic additions across reports
        sha_cf: { label: 'CF Share', class: 'text-right' },
        sha_pcc: { label: 'PCC Share', class: 'text-right' },
        sha_prj: { label: 'Project Share', class: 'text-right' },
        sha_prj_sal: { label: 'Salary Share', class: 'text-right' },
        sha_prj_pur: { label: 'Purchase Share', class: 'text-right' },
        
        // Project Head Status (Mapped to Accounts Status)
        hed_code: { label: 'Head Code', class: 'text-cyan font-weight-bold' },
        hed_name: { label: 'Head Name', class: 'text-wrap' },
        received: { label: 'Received', class: 'text-right text-success' },
        expenditure: { label: 'Expenditure', class: 'text-right text-danger' },
        cf_share: { label: 'CSRF Share', class: 'text-right' },
        cf_expenditure: { label: 'CSRF Spent', class: 'text-right text-danger' },
        balance: { label: 'Balance', class: 'text-right font-weight-bold' },
        available: { label: 'Available', class: 'text-right font-weight-bold text-info' },
        
        // Shares Mapping
        mtss_share: { label: 'MTSS Share', class: 'text-right' },
        rdw_share: { label: 'RDW Share', class: 'text-right' },
        csrf_share: { label: 'CSRF Share', class: 'text-right' },
        equipment_share: { label: 'Equipment', class: 'text-right' },
        hr_share: { label: 'Utilized: HR', class: 'text-right' },
        misc_share: { label: 'Misc', class: 'text-right' },

        acc_id: { label: 'Account ID', class: 'text-left font-weight-bold text-white' },
        acc_name: { label: 'Full Name', class: 'font-weight-bold text-white' },
        acc_username: { label: 'Username', class: '' },
        acc_title: { label: 'Title', class: '' },
        acc_desig: { label: 'Designation', class: '' },
        acc_desigshort: { label: 'Short Designation', class: 'text-muted' },
        acc_status: { label: 'Status', class: 'text-center' },
        acc_untname: { label: 'Unit Name', class: '' },
        acc_untarea: { label: 'Unit Area', class: '' },
        acc_level: { label: 'Level', class: 'text-center' },
        acc_startdt: { label: 'Start Date', class: 'text-muted' },
        acc_enddt: { label: 'End Date', class: 'text-muted' },
        sbh_id: { label: 'Subhead ID', class: 'text-left font-weight-bold text-white' },
        sbh_name: { label: 'Subhead Name', class: 'font-weight-bold text-white' },
        sbh_alloc: { label: 'Allocation', class: 'text-right font-weight-bold text-info' },
        cf_received: { label: 'CF Received', class: 'text-right text-success' },
        cf_balance: { label: 'CF Balance', class: 'text-right font-weight-bold' },
        cf_commitments: { label: 'CF Commitments', class: 'text-right' },
        cf_in_process: { label: 'CF In Process', class: 'text-right' },
        cf_available: { label: 'CF Available', class: 'text-right font-weight-bold text-info' },
        trn_id: { label: 'Transaction ID', class: 'text-left font-weight-bold text-white' },
        trn_date: { label: 'Transaction Date', class: 'text-muted' },
        trn_amount1: { label: 'Amount 1', class: 'text-right' },
        trn_amount2: { label: 'Amount 2', class: 'text-right' },
        trn_tax1: { label: 'Tax 1', class: 'text-right text-muted' },
        trn_balance: { label: 'Balance', class: 'text-right font-weight-bold text-cyan' },
        trn_transtype: { label: 'Type', class: 'text-center' },
        pcs_id: { label: 'PC ID', class: 'text-left font-weight-bold text-white' },
        pcs_title: { label: 'PC Title', class: 'text-wrap font-weight-bold text-white', style: 'min-width: 200px;' },
        pcs_date: { label: 'PC Date', class: 'text-muted' },
        pcs_status: { label: 'PC Status', class: 'text-center' },
        pcs_price: { label: 'PC Price', class: 'text-right font-weight-bold text-info' },
        pcs_intprice: { label: 'Int Price', class: 'text-right text-muted' },
        pcs_midprice: { label: 'Mid Price', class: 'text-right text-muted' },
        
        // Employee columns
        emp_id: { label: 'Employee ID', class: 'text-left font-weight-bold text-white' },
        emp_cnic: { label: 'CNIC', class: '' },
        emp_name: { label: 'Employee Name', class: 'font-weight-bold text-white' },
        emp_title: { label: 'Title', class: '' },
        emp_rank: { label: 'Rank', class: '' },
        emp_status: { label: 'Status', class: 'text-center' },
        emp_joindt: { label: 'Join Date', class: 'text-muted' },
        emp_lastdt: { label: 'Last Date', class: 'text-muted' },
        emp_remarks: { label: 'Remarks', class: 'text-muted text-wrap', style: 'max-width: 200px;' },
        emp_locked: { label: 'Locked', class: 'text-center' },
        emp_cleared: { label: 'Cleared', class: 'text-center' },
        emp_photodest: { label: 'Photo Path', class: 'text-muted' },

        frm_name: { label: 'Firm Name', class: 'font-weight-bold text-white' },
        frm_type: { label: 'Firm Type', class: 'text-muted' },
        pat_id: { label: 'Attachment ID', class: 'text-left font-weight-bold text-white' },
        pat_objtype: { label: 'Object Type', class: 'text-muted' },
        pat_objid: { label: 'Object ID', class: 'text-muted' },
        pat_type: { label: 'Attachment Type', class: '' },
        pat_path: { label: 'File Path', class: 'text-info text-wrap', style: 'max-width: 250px;' },
        source_type: { label: 'Source', class: 'font-weight-bold text-cyan' },
        file_type: { label: 'File Type', class: '' },
        file_path: { label: 'File Path', class: 'text-info text-wrap', style: 'max-width: 250px;' },
        
        // Head History & Shifting
        hed_opendt: { label: 'Opened Date', class: 'text-muted' },
        reversals_count: { label: 'Reversals Filed', class: 'text-center font-weight-bold text-warning' },
        shifts_count: { label: 'Fund Shifts', class: 'text-center font-weight-bold text-info' },
        last_shift_amount: { label: 'Last Shift Amount', class: 'text-right' },
        last_shift_date: { label: 'Last Shift Date', class: 'text-muted' },
        balance_before: { label: 'Before Balance', class: 'text-right text-muted' },
        balance_after: { label: 'After Balance', class: 'text-right font-weight-bold text-success' }
    };

    // Asset status options
    const assetStatuses = [
        { val: 'Untagged', label: 'Untagged' },
        { val: 'Tagged', label: 'Tagged' },
        { val: 'Held', label: 'Held (Store Custody)' },
        { val: 'Issued to User', label: 'Issued to User' },
        { val: 'Installed', label: 'Installed' },
        { val: 'Consumed', label: 'Consumed' },
        { val: 'Written Off', label: 'Written Off' }
    ];

    // Purchase Case status options
    const pcStatuses = [
        { val: 'Approved', label: 'Approved' },
        { val: 'Fulfilled', label: 'Fulfilled' },
        { val: 'Under Approval', label: 'Under Approval' },
        { val: 'Under Scrutiny', label: 'Under Scrutiny' },
        { val: 'Rejected', label: 'Rejected' },
        { val: 'Under Revision', label: 'Under Revision' },
        { val: 'Partially Fulfilled', label: 'Partially Fulfilled' },
        { val: 'Returned', label: 'Returned' },
        { val: 'Draft', label: 'Draft' },
        { val: 'Cancelled', label: 'Cancelled' }
    ];

    // Populate Status filter options based on report type
    function updateStatusOptions(reportType) {
        const select = $('#status-dropdown');
        select.empty();
        
        let statuses = [];
        if (reportType === 'inventory_assets') {
            statuses = assetStatuses;
        } else if (['pcs_by_firms', 'pcs_by_firms_projects', 'pcs_by_single_firm'].includes(reportType)) {
            statuses = pcStatuses;
        }
        
        statuses.forEach(s => {
            select.append(`<option value="${s.val}">${s.label}</option>`);
        });
        
        select.val(null).trigger('change');
    }

    // Populate Columns grid checkboxes based on report type
    function updateColumnsCheckboxList(reportType) {
        const cols = reportColumns[reportType];
        const container = $('.columns-grid');
        container.empty();
        
        for (const key in cols) {
            container.append(`
                <label class="checkbox-option">
                     <input type="checkbox" value="${key}" class="col-checkbox" checked>
                     <span>${cols[key]}</span>
                </label>
            `);
        }
    }

    // Dynamic Visibility rules for filter fields
    function showHideFilters(reportType) {
        // Hide all initially
        $('#project-filter-container').addClass('d-none');
        $('#category-filter-container').addClass('d-none');
        $('#status-filter-container').addClass('d-none');
        $('#firm-filter-container').addClass('d-none');
        $('#start-date-container').addClass('d-none');
        $('#end-date-container').addClass('d-none');
        
        if (!reportType) return;

        // Apply rules
        if (reportType === 'inventory_assets') {
            $('#project-filter-container').removeClass('d-none');
            $('#category-filter-container').removeClass('d-none');
            $('#status-filter-container').removeClass('d-none');
        } else if (reportType === 'project_financial') {
            $('#project-filter-container').removeClass('d-none');
        } else if (reportType === 'fund_shifting') {
            $('#project-filter-container').removeClass('d-none');
            $('#start-date-container').removeClass('d-none');
            $('#end-date-container').removeClass('d-none');
        } else if (reportType === 'allocations_status') {
            $('#project-filter-container').removeClass('d-none');
        } else if (reportType === 'accounts_status') {
            // Mapped to Project Head Status (Has project filter option)
            $('#project-filter-container').removeClass('d-none');
        } else if (reportType === 'proj_shares_status') {
            $('#project-filter-container').removeClass('d-none');
        } else if (reportType === 'subheads_status') {
            $('#project-filter-container').removeClass('d-none');
        } else if (reportType === 'csrf_status') {
            $('#project-filter-container').removeClass('d-none');
        } else if (reportType === 'monthly_return') {
            $('#project-filter-container').removeClass('d-none');
            $('#start-date-container').removeClass('d-none');
            $('#end-date-container').removeClass('d-none');
        } else if (reportType === 'pcs_awaiting_payment') {
            $('#project-filter-container').removeClass('d-none');
        } else if (reportType === 'current_employees') {
            $('#project-filter-container').removeClass('d-none');
        } else if (reportType === 'pcs_by_firms') {
            $('#status-filter-container').removeClass('d-none');
        } else if (reportType === 'pcs_by_firms_projects') {
            $('#project-filter-container').removeClass('d-none');
            $('#status-filter-container').removeClass('d-none');
        } else if (reportType === 'pcs_by_single_firm') {
            $('#firm-filter-container').removeClass('d-none');
            $('#status-filter-container').removeClass('d-none');
        } else if (reportType === 'attachment_summary') {
            // No filters
        } else if (reportType === 'reversals_shifting_history') {
            $('#project-filter-container').removeClass('d-none');
        }
    }

    $(document).ready(function() {
        if (isDivisionUser && userUnitId && userUnitId !== '0') {
            $('#division-dropdown').val(userUnitId);
        }
    });

    // "Select All" columns click
    $('#col-select-all').click(function() {
        $('.col-checkbox').prop('checked', true);
    });

    // "Clear All" columns click
    $('#col-deselect-all').click(function() {
        $('.col-checkbox').prop('checked', false);
    });

    // Money formatter
    function formatMoney(val) {
        const n = Number(val || 0);
        return 'Rs ' + n.toLocaleString(undefined, { maximumFractionDigits: 0 });
    }

    // Collect checked columns
    function getSelectedColumns() {
        const cols = [];
        $('.col-checkbox:checked').each(function() {
            cols.push($(this).val());
        });
        return cols;
    }

    // Compile dynamic AJAX query string
    function compileQueryString() {
        const divisionId = $('#division-dropdown').val();
        const projectId = $('#project-dropdown').val();
        const category = $('#category-dropdown').val();
        const status = $('#status-dropdown').val(); // returns array
        const statusStr = (status && status.length > 0) ? status.join(',') : 'All';
        const startDate = $('#start-date').val();
        const endDate = $('#end-date').val();
        const firmId = $('#firm-dropdown').val();

        const qs = new URLSearchParams();
        qs.set('type', reportType);
        qs.set('unit_id', divisionId);
        qs.set('project_id', projectId);
        qs.set('category', category);
        qs.set('status', statusStr);
        if (startDate) qs.set('start_date', startDate);
        if (endDate) qs.set('end_date', endDate);
        if (firmId) qs.set('firm_id', firmId);

        return qs;
    }

    // AJAX Report Generation
    $('#btn-generate').click(function() {
        const selectedCols = getSelectedColumns();
        if (selectedCols.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Columns Selected',
                text: 'Please select at least one column to generate the report.',
                background: '#121a22',
                color: '#fff',
                confirmButtonColor: '#00BFFF'
            });
            return;
        }

        $('#loader').show();
        $('#table-placeholder').addClass('d-none');
        $('#output-table').addClass('d-none');

        const qs = compileQueryString();

        $.ajax({
            url: '/fin/reports/data?' + qs.toString(),
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success && res.data) {
                    renderTable(res.data, selectedCols);
                } else {
                    showEmptyPlaceholder('No records found for the selected filters.');
                }
            },
            error: function(err) {
                showEmptyPlaceholder('Failed to fetch data from the server. Please try again.');
                console.error("Report generation fail", err);
            },
            complete: function() {
                $('#loader').hide();
            }
        });
    });

    function showEmptyPlaceholder(msg) {
        $('#output-table').addClass('d-none');
        $('#table-placeholder').removeClass('d-none').html(`
            <i class="fas fa-exclamation-triangle mb-3 text-warning" style="font-size: 3rem;"></i><br>
            ${msg}
        `);
        $('#records-counter').text('0 records loaded');
    }

    function renderTable(data, columns) {
        const thead = $('#table-headers');
        const tbody = $('#table-body');
        
        thead.empty();
        tbody.empty();

        if (data.length === 0) {
            showEmptyPlaceholder('No records found for the selected filters.');
            return;
        }

        // Build Table Headers
        columns.forEach(col => {
            const meta = columnMeta[col] || { label: col, class: '' };
            thead.append(`<th class="${meta.class || ''}" style="${meta.style || ''}">${meta.label}</th>`);
        });

        // Build Table Rows
        data.forEach(row => {
            let rowHtml = '<tr>';
            columns.forEach(col => {
                const meta = columnMeta[col] || { class: '' };
                let val = row[col];

                // Formatting adjustments
                if (['price', 'total_value', 'approved_budget', 'allocation', 'spent_prj', 'spent_cf', 'total_spent', 'remaining', 'commitments', 'in_process', 'sha_cf', 'sha_pcc', 'sha_prj', 'sha_prj_sal', 'sha_prj_pur', 'sbh_alloc', 'cf_share', 'cf_received', 'cf_expenditure', 'cf_balance', 'cf_commitments', 'cf_in_process', 'cf_available', 'trn_amount1', 'trn_amount2', 'trn_tax1', 'trn_balance', 'amount', 'from_old_val', 'from_new_val', 'to_old_val', 'to_new_val', 'pcs_price', 'pcs_intprice', 'pcs_midprice', 'received', 'expenditure', 'cf_expenditure', 'balance', 'available', 'csrf_share', 'equipment_share', 'rdw_share', 'hr_share', 'misc_share'].includes(col)) {
                    val = formatMoney(val);
                } else if (['status', 'pcs_status', 'acc_status', 'emp_status'].includes(col)) {
                    val = `<span class="badge badge-secondary px-2.5 py-1" style="background: rgba(0, 191, 255, 0.12); border: 1px solid rgba(0, 191, 255, 0.35); color: #00BFFF; border-radius: 20px;">${val}</span>`;
                } else if (col === 'emp_locked' || col === 'emp_cleared') {
                    val = val ? '<span class="text-success font-weight-bold"><i class="fas fa-check-circle"></i> Yes</span>' : '<span class="text-muted"><i class="fas fa-times-circle"></i> No</span>';
                }

                rowHtml += `<td class="${meta.class || ''}" style="${meta.style || ''}">${val !== null && val !== undefined ? val : 'N/A'}</td>`;
            });
            rowHtml += '</tr>';
            tbody.append(rowHtml);
        });

        $('#records-counter').text(`${data.length} records loaded`);
        $('#output-table').removeClass('d-none');
    }

    // Export report trigger
    $('#btn-export').click(function() {
        const selectedCols = getSelectedColumns();
        if (selectedCols.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Columns Selected',
                text: 'Please select at least one column to export the report.',
                background: '#121a22',
                color: '#fff',
                confirmButtonColor: '#00BFFF'
            });
            return;
        }

        const qs = compileQueryString();
        
        // Append columns array
        selectedCols.forEach(c => {
            qs.append('columns[]', c);
        });

        // Trigger file download
        window.location.href = '/fin/reports/export?' + qs.toString();
    });

    // Reset filters
    $('#btn-reset').click(function() {
        $('#project-dropdown').val('All');
        $('#category-dropdown').val('All');
        $('#status-dropdown').val(null).trigger('change');
        $('#start-date').val('');
        $('#end-date').val('');
        $('#firm-dropdown').val('All');

        if (!isDivisionUser) {
            $('#division-dropdown').val('All');
        }
        
        $('.col-checkbox').prop('checked', true);

        // Clear output table
        $('#output-table').addClass('d-none');
        $('#table-placeholder').removeClass('d-none').html(`
            <i class="fas fa-table mb-3 text-cyan" style="font-size: 3rem;"></i><br>
            Please select a report type to begin.
        `);
        $('#records-counter').text('0 records loaded');
    });

    let allProjects = [];

    $(document).ready(function() {
        if (isDivisionUser) {
            $('#div-filter-container').addClass('d-none');
        }

        // Initialize Select2 multiselect on status-dropdown
        if (typeof $.fn.select2 !== 'undefined') {
            $('#status-dropdown').select2({
                placeholder: "Select Statuses (Leave empty for All)",
                allowClear: true,
                width: '100%',
                theme: 'bootstrap4'
            });
        }

        // Change layout/filters when Report Type changes
        $('#report-type').change(function() {
            const reportType = $(this).val();
            if (!reportType) {
                $('.columns-grid').empty();
                showHideFilters('');
                $('#btn-generate, #btn-export').prop('disabled', true).addClass('opacity-50');
                $('#output-table').addClass('d-none');
                $('#table-placeholder').removeClass('d-none').html(`
                    <i class="fas fa-table mb-3 text-cyan" style="font-size: 3rem;"></i><br>
                    Please select a report type to begin.
                `);
                $('#records-counter').text('0 records loaded');
                return;
            }

            $('#btn-generate, #btn-export').prop('disabled', false).removeClass('opacity-50');
            updateColumnsCheckboxList(reportType);
            updateStatusOptions(reportType);
            showHideFilters(reportType);
            
            // Auto click Select All columns to refresh checkboxes state
            $('#col-select-all').trigger('click');
        });

        // Trigger initial setup (Empty by default)
        $('#report-type').trigger('change');

        // Cache all project options in memory
        $('#project-dropdown option').each(function() {
            const val = $(this).val();
            const text = $(this).text();
            const division = $(this).data('division');
            if (val !== 'All') {
                allProjects.push({ val: val, text: text, division: division });
            }
        });

        // Trigger filter on division change
        $('#division-dropdown').change(function() {
            const selectedDiv = $(this).val();
            const projectSelect = $('#project-dropdown');
            
            projectSelect.empty();
            projectSelect.append('<option value="All">All Projects</option>');

            allProjects.forEach(function(proj) {
                if (selectedDiv === 'All' || String(proj.division) === String(selectedDiv)) {
                    projectSelect.append(`<option value="${proj.val}" data-division="${proj.division}">${proj.text}</option>`);
                }
            });
            
            projectSelect.val('All');
        });
    });
</script>
@endsection
