@extends('welcome')

@section('content')
<div class="content-wrapper">
    <div class="container-fluid pt-4 px-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
            <div>
                <h3 class="text-uppercase font-weight-bold rajdhani mb-0" style="letter-spacing: 2px; color: inherit;">
                    <i class="fas fa-chart-bar mr-2 text-primary"></i> Reports Center
                </h3>
                <small class="text-muted">Access division reports, generate letters, and manage documentation</small>
            </div>
        </div>

        <!-- Split Layout -->
        <div class="row">
            <!-- Left Panel: Menu -->
            <div class="col-xl-2 col-lg-3 mb-4">
                <div id="reportsAccordion" class="accordion-custom">

                    <!-- 1. HUMAN RESOURCES -->
                    <div class="card accordion-card border-0 shadow-sm mb-3">
                        <div class="card-header accordion-trigger collapsed" data-toggle="collapse" data-target="#hrCollapse" aria-expanded="false" aria-controls="hrCollapse">
                            <div class="d-flex align-items-center justify-content-between w-100">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-info-gradient mr-3">
                                        <i class="fas fa-users text-white"></i>
                                    </div>
                                    <span class="accordion-title" style="font-size: 1rem;">Human Resources (HR)</span>
                                </div>
                                <i class="fas fa-chevron-down toggle-icon"></i>
                            </div>
                        </div>

                        <div id="hrCollapse" class="collapse" data-parent="#reportsAccordion">
                            <div class="card-body bg-theme-dark p-3">
                                <a href="{{ route('reports.hr.incomplete_data') }}" target="reportFrame" class="stacked-menu-item" onclick="showIframe()">Incomplete Data</a>
                                <a href="{{ route('reports.hr.grades') }}" target="reportFrame" class="stacked-menu-item" onclick="showIframe()">Grades</a>
                                <a href="{{ route('reports.hr.qualifications') }}" target="reportFrame" class="stacked-menu-item" onclick="showIframe()">Qualifications</a>
                                <a href="{{ route('reports.hr.current_employees') }}" target="reportFrame" class="stacked-menu-item" onclick="showIframe()">Current Employees</a>
                                <a href="{{ route('reports.hr.ex_servicemen') }}" target="reportFrame" class="stacked-menu-item" onclick="showIframe()">Ex Servicemen</a>
                                <a href="{{ route('reports.hr.salaries_paid') }}" target="reportFrame" class="stacked-menu-item" onclick="showIframe()">Salaries Paid</a>
                                <a href="{{ route('reports.hr.custom') }}" target="reportFrame" class="stacked-menu-item" onclick="showIframe()">Custom</a>
                            </div>
                        </div>
                    </div>

                    <!-- 2. FINANCE -->
                    <div class="card accordion-card border-0 shadow-sm mb-3">
                        <div class="card-header accordion-trigger collapsed" data-toggle="collapse" data-target="#financeCollapse" aria-expanded="false" aria-controls="financeCollapse">
                            <div class="d-flex align-items-center justify-content-between w-100">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-success-gradient mr-3">
                                        <i class="fas fa-coins text-white"></i>
                                    </div>
                                    <span class="accordion-title" style="font-size: 1rem;">Finance</span>
                                </div>
                                <i class="fas fa-chevron-down toggle-icon"></i>
                            </div>
                        </div>

                        <div id="financeCollapse" class="collapse" data-parent="#reportsAccordion">
                            <div class="card-body bg-theme-dark p-3">
                                <a href="{{ route('reports.finance.allocation_status') }}" target="reportFrame" class="stacked-menu-item" onclick="showIframe()">Allocations Status</a>
                                <a href="{{ route('reports.finance.account_status') }}" target="reportFrame" class="stacked-menu-item" onclick="showIframe()">Accounts Status</a>
                                <a href="{{ route('reports.finance.proj_shares_status') }}" target="reportFrame" class="stacked-menu-item" onclick="showIframe()">Proj Shares Status</a>
                                <a href="{{ route('reports.finance.subheads_status') }}" target="reportFrame" class="stacked-menu-item" onclick="showIframe()">Subheads Status</a>
                                <a href="{{ route('reports.finance.csrf_status') }}" target="reportFrame" class="stacked-menu-item" onclick="showIframe()">CSRF Status</a>
                                <a href="{{ route('reports.finance.pcs_awaiting_payment') }}" target="reportFrame" class="stacked-menu-item" onclick="showIframe()">PCs Awaiting Payment</a>
                            </div>
                        </div>
                    </div>

                    <!-- 3. INVENTORY AND ASSETS -->
                    <div class="card accordion-card border-0 shadow-sm mb-3">
                        <div class="card-header accordion-trigger collapsed" data-toggle="collapse" data-target="#inventoryCollapse" aria-expanded="false" aria-controls="inventoryCollapse">
                            <div class="d-flex align-items-center justify-content-between w-100">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-purple-gradient mr-3">
                                        <i class="fas fa-boxes text-white"></i>
                                    </div>
                                    <span class="accordion-title" style="font-size: 1rem;">Inventory and Assets</span>
                                </div>
                                <i class="fas fa-chevron-down toggle-icon"></i>
                            </div>
                        </div>

                        <div id="inventoryCollapse" class="collapse" data-parent="#reportsAccordion">
                            <div class="card-body bg-theme-dark p-3">
                                <a href="{{ route('reports.inventory.shared_assets') }}" target="reportFrame" class="stacked-menu-item" onclick="showIframe()">Shared Assets</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Frame -->
            <div class="col-xl-10 col-lg-9 mb-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius:12px; background:var(--rd-surface); min-height: 85vh; overflow:hidden; position:relative;">
                    <!-- Placeholder State -->
                    <div id="framePlaceholder" class="d-flex flex-column justify-content-center align-items-center w-100 h-100" style="position:absolute; top:0; left:0; pointer-events:none;">
                        <div class="text-center">
                            <i class="fas fa-chart-pie text-muted mb-3" style="font-size:4rem; opacity:0.3;"></i>
                            <h5 class="text-muted" style="font-weight:600; letter-spacing:1px;">Select a report from the menu</h5>
                            <p class="text-muted small">The report will load here</p>
                        </div>
                    </div>
                    
                    <!-- Iframe -->
                    <iframe name="reportFrame" id="reportFrame" style="width:100%; height:100%; min-height: 85vh; border:none; display:none; position:relative; z-index:2; background:transparent;" src="" onload="if(this.contentWindow.document.body) this.style.height = (this.contentWindow.document.body.scrollHeight + 50) + 'px';"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Accordion & Card Custom Styling matching Dark Theme */
.accordion-card {
    background-color: var(--rd-surface) !important;
    border-radius: 12px !important;
    overflow: hidden;
    transition: all 0.3s ease;
    border: 1px solid var(--rd-border) !important;
}

.accordion-trigger {
    padding: 1.2rem 1.5rem;
    background-color: var(--rd-surface) !important;
    border-bottom: 0px solid transparent;
    cursor: pointer;
}

.accordion-trigger:not(.collapsed) {
    background-color: var(--rd-surface2) !important;
    border-bottom: 1px solid var(--rd-border) !important;
}

.accordion-title {
    font-size: 1.15rem;
    font-weight: 700;
    font-family: 'Rajdhani', sans-serif;
    letter-spacing: 0.5px;
    color: inherit;
}

.toggle-icon {
    font-size: 1rem;
    color: var(--rd-text3);
    transition: transform 0.3s ease;
}

.accordion-trigger:not(.collapsed) .toggle-icon {
    transform: rotate(180deg);
    color: var(--rd-accent);
}

.bg-theme-dark {
    background-color: var(--rd-bg) !important;
}

.bg-surface-dark {
    background-color: var(--rd-surface) !important;
}

/* Stack Container matching sketch */
.menu-stack-container {
    max-width: 480px;
    margin: 0 auto;
    padding: 24px;
    border: 1px solid var(--rd-border);
    border-radius: 12px;
    background-color: var(--rd-surface);
}

/* Stacked Item Buttons matching sketch */
.stacked-menu-item {
    display: block;
    width: 100%;
    margin-bottom: 12px;
    padding: 12px 18px;
    background-color: var(--rd-surface2);
    border: 1px solid var(--rd-border2);
    border-radius: 6px;
    color: inherit;
    text-align: center;
    font-size: 14px;
    font-weight: 500;
    letter-spacing: 0.5px;
    transition: all 0.2s ease;
    text-decoration: none !important;
    cursor: pointer;
}

.stacked-menu-item:last-child {
    margin-bottom: 0;
}

.stacked-menu-item:hover {
    background-color: var(--rd-surface3);
    border-color: var(--rd-accent);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

.disabled-btn {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Header icon colors */
.icon-box {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}
.bg-info-gradient {
    background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);
}
.bg-success-gradient {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
}
.bg-purple-gradient {
    background: linear-gradient(135deg, #6f42c1 0%, #563d7c 100%);
}

.btn-xs {
    padding: .15rem .5rem;
    font-size: .75rem;
    line-height: 1.5;
    border-radius: .2rem;
}
</style>

<script>
    let cIdx = 1;
    let iIdx = 1;

    function addCompRow() {
        let html = `
        <div class="firm-block border border-secondary rounded p-3 mb-3 bg-surface-dark shadow-sm">
            <div class="row">
                <div class="col-md-3 mb-2"><label class="small font-weight-bold">Firm Name</label><input type="text" name="firms[${cIdx}][name]" class="form-control form-control-sm" required></div>
                <div class="col-md-2 mb-2"><label class="small font-weight-bold">Q-No/Date</label><input type="text" name="firms[${cIdx}][q_no]" class="form-control form-control-sm"></div>
                <div class="col-md-2 mb-2"><label class="small font-weight-bold">Rate (Total)</label><input type="text" name="firms[${cIdx}][rate]" class="form-control form-control-sm"></div>
                <div class="col-md-3 mb-2"><label class="small font-weight-bold">Address</label><input type="text" name="firms[${cIdx}][address]" class="form-control form-control-sm"></div>
                <div class="col-md-2 mb-2"><label class="small font-weight-bold">NTN/STRN</label><input type="text" name="firms[${cIdx}][ntn]" class="form-control form-control-sm"></div>
                <div class="col-md-4"><label class="small font-weight-bold">Contact/Email</label><input type="text" name="firms[${cIdx}][contact]" class="form-control form-control-sm"></div>
                <div class="col-md-3"><label class="small font-weight-bold">Auth. Dealer</label><input type="text" name="firms[${cIdx}][dealer]" class="form-control form-control-sm"></div>
                <div class="col-md-4"><label class="small font-weight-bold">Remarks</label><input type="text" name="firms[${cIdx}][remarks]" class="form-control form-control-sm" value="Accepted"></div>
                <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-danger btn-sm w-100" onclick="this.parentElement.parentElement.parentElement.remove()">X</button></div>
            </div>
        </div>`;
        document.getElementById('comp-firms-area').insertAdjacentHTML('beforeend', html);
        cIdx++;
    }

    function addITRow() {
        let html = `
        <div class="it-firm-block border border-secondary rounded p-3 mb-3 bg-surface-dark shadow-sm">
            <div class="row">
                <div class="col-md-4"><label class="small font-weight-bold">Firm Name</label><input type="text" name="firms[${iIdx}][name]" class="form-control form-control-sm" required></div>
                <div class="col-md-6"><label class="small font-weight-bold">Address</label><input type="text" name="firms[${iIdx}][address]" class="form-control form-control-sm"></div>
                <div class="col-md-2 d-flex align-items-end"><input type="text" name="firms[${iIdx}][tel]" class="form-control form-control-sm mr-2" placeholder="Tel"><button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.parentElement.parentElement.remove()">X</button></div>
            </div>
        </div>`;
        document.getElementById('it-firms-area').insertAdjacentHTML('beforeend', html);
        iIdx++;
    }

    function showIframe() {
        document.getElementById('framePlaceholder').style.display = 'none';
        document.getElementById('reportFrame').style.display = 'block';
        
        // Remove active class from all stacked items and add to clicked
        setTimeout(() => {
            document.querySelectorAll('.stacked-menu-item').forEach(el => el.classList.remove('active-menu-item'));
            if(event && event.target && event.target.classList.contains('stacked-menu-item')) {
                event.target.classList.add('active-menu-item');
            }
        }, 10);
    }
</script>

<style>
    .active-menu-item {
        background-color: var(--rd-surface3) !important;
        border-color: var(--rd-accent) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }
</style>
@endsection
