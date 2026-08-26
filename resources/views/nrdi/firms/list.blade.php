@extends('welcome')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap');

.firms-list-page {
    font-family: 'Inter', sans-serif;
    background: #f8fafc !important;
    min-height: 100vh;
    color: #1e293b;
    padding-top: 20px;
    padding-bottom: 50px;
}

.rajdhani {
    font-family: 'Rajdhani', sans-serif;
    letter-spacing: 0.5px;
}

.card-cyber {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
}

.kpi-box {
    border-left: 4px solid #0284c7;
    padding: 14px 18px;
    border-radius: 10px;
    background: #ffffff;
    border-top: 1px solid #f1f5f9;
    border-right: 1px solid #f1f5f9;
    border-bottom: 1px solid #f1f5f9;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}

.form-control-cyber {
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    color: #1e293b;
    border-radius: 8px;
    font-size: 13px;
}
.form-control-cyber:focus {
    background: #ffffff;
    border-color: #0284c7;
    color: #0f172a;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
}

.table-cyber th {
    background: #f8fafc !important;
    border-bottom: 2px solid #cbd5e1 !important;
    border-top: 1px solid #e2e8f0 !important;
    color: #334155 !important;
    font-family: 'Rajdhani', sans-serif;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    font-size: 12px;
    font-weight: 700;
    padding: 12px 14px !important;
    white-space: nowrap;
}
.table-cyber td {
    border-bottom: 1px solid #e2e8f0 !important;
    padding: 12px 14px !important;
    vertical-align: middle;
    font-size: 13px;
}
.table-cyber tr:hover {
    background: #f1f5f9 !important;
}

.pill-filter {
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 20px;
    padding: 5px 14px;
    font-family: 'Rajdhani', sans-serif;
    font-size: 12.5px;
    font-weight: 700;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none !important;
}
.pill-filter:hover, .pill-filter.active {
    background: #0284c7;
    border-color: #0284c7;
    color: #ffffff;
}

.adv-search-panel {
    background: #ffffff;
    border: 2px solid #38bdf8;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(2, 132, 199, 0.12);
    display: none;
    animation: fadeInDown 0.3s ease;
}

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-12px); }
    to { opacity: 1; transform: translateY(0); }
}

.tab-nav-firm .nav-link {
    border: 1.5px solid #cbd5e1;
    margin-right: 6px;
    border-radius: 8px 8px 0 0;
    font-weight: 700;
    font-family: 'Rajdhani', sans-serif;
    font-size: 14px;
    color: #64748b;
    background: #f8fafc;
}
.tab-nav-firm .nav-link.active {
    background: #ffffff !important;
    color: #0284c7 !important;
    border-color: #0284c7 #0284c7 #ffffff !important;
    border-top: 3px solid #0284c7 !important;
}
</style>

@php
    $userArea = strtolower(trim((string) (Auth::user()->acc_untarea ?? '')));
    $canAddFirm = in_array($userArea, ['fin', 'proc', 'prc']) 
        || session('impersonated_by_god') 
        || strtolower(Auth::user()->acc_username ?? '') === 'superadminrdw';
@endphp

<div class="content-wrapper firms-list-page px-4">
    
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge px-3 py-1 rajdhani font-weight-bold" style="background: rgba(2, 132, 199, 0.12); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.3); font-size: 11px;">
                    <i class="fas fa-building mr-1"></i> CENTRAL VENDOR DIRECTORY
                </span>
                <span class="badge px-2.5 py-1 rajdhani font-weight-bold" style="background: rgba(34, 197, 94, 0.12); color: #15803d; border: 1px solid rgba(34, 197, 94, 0.3); font-size: 11px;">
                    <i class="fas fa-list-ul mr-1"></i> ALL REGISTERED FIRMS
                </span>
            </div>
            <h2 class="font-weight-bold text-dark rajdhani m-0" style="font-size: 26px;">
                <i class="fas fa-store mr-2 text-info"></i>Suppliers & Firms Roster
            </h2>
            <p class="text-muted m-0 small">Registered vendor profiles, verified NTN/GST credentials, branch offices, and past procurement award history.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            {{-- Advanced Search Dropdown Toggle Button --}}
            <button type="button" id="btnToggleAdvSearch" class="btn btn-sm btn-info rajdhani font-weight-bold px-3 py-2 shadow-sm" style="border-radius: 8px; font-size: 13.5px;">
                <i class="fas fa-search-plus mr-1"></i> ADVANCED SEARCH <i class="fas fa-chevron-down ml-1" id="advSearchIcon"></i>
            </button>

            {{-- + Add Firm Button (Visible ONLY to Finance & Procurement Directorate / God Mode) --}}
            @if($canAddFirm)
                <button type="button" class="btn btn-sm btn-success rajdhani font-weight-bold px-3.5 py-2 shadow-sm" data-toggle="modal" data-target="#modalAddFirm" style="border-radius: 8px; font-size: 13.5px; box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25);">
                    <i class="fas fa-plus-circle mr-1"></i> + ADD NEW FIRM
                </button>
            @endif

            <a href="{{ route('nrdi.procurement.reports.index') }}?type=pcs_by_firms" class="btn btn-sm btn-outline-secondary rajdhani font-weight-bold px-3 py-2" style="border-radius: 8px; font-size: 13px;">
                <i class="fas fa-file-excel mr-1"></i> Export Excel
            </a>
        </div>
    </div>

    {{-- Top Floating Toast Notification --}}
    <div id="firmToastAlert" class="alert alert-success border-0 shadow-lg d-none align-items-center mb-4 px-4 py-3" style="background: #ecfdf5; border-left: 4px solid #10b981 !important; border-radius: 8px; position: sticky; top: 15px; z-index: 1050; animation: fadeInDown 0.3s ease;">
        <i class="fas fa-check-circle text-success mr-3" style="font-size: 22px;"></i>
        <div>
            <h6 class="mb-0 text-success font-weight-bold rajdhani" style="font-size: 15px;">FIRM REGISTERED SUCCESSFULLY</h6>
            <p class="mb-0 text-muted small" id="firmToastMessage">New supplier has been added to central roster and is immediately available for quotations.</p>
        </div>
    </div>

    {{-- 1. COLLAPSIBLE ADVANCED SEARCH PANEL (MATCHING USER SCREENSHOT EXACTLY) --}}
    <div id="advancedSearchCollapse" class="adv-search-panel p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <h5 class="rajdhani font-weight-bold text-info m-0" style="font-size: 18px; letter-spacing: 0.8px;">
                <i class="fas fa-search-dollar mr-1.5"></i> ADVANCED SEARCH
            </h5>
            <button type="button" class="btn btn-sm btn-light text-muted font-weight-bold" onclick="toggleAdvSearch(false)" style="border-radius: 6px;">
                <i class="fas fa-times"></i> Close
            </button>
        </div>

        <form id="advSearchForm" onsubmit="executeAdvancedSearch(event)">
            <div class="row">
                {{-- 1. General Data --}}
                <div class="col-md-4 mb-3">
                    <label class="small font-weight-bold text-muted mb-1 rajdhani" style="font-size: 12px;">General data</label>
                    <select id="adv_gen_field" class="form-control form-control-sm font-weight-bold text-dark mb-1.5 rajdhani">
                        <option value="Name" selected>Name</option>
                        <option value="NTN">NTN</option>
                        <option value="GST">GST</option>
                        <option value="Type">Type</option>
                        <option value="Entity">Entity</option>
                    </select>
                    <input type="text" id="adv_gen_val" class="form-control form-control-sm form-control-cyber" placeholder="Value...">
                </div>

                {{-- 2. Office --}}
                <div class="col-md-4 mb-3">
                    <label class="small font-weight-bold text-muted mb-1 rajdhani" style="font-size: 12px;">Office</label>
                    <select id="adv_off_field" class="form-control form-control-sm font-weight-bold text-dark mb-1.5 rajdhani">
                        <option value="Name">Name</option>
                        <option value="City" selected>City</option>
                        <option value="Address">Address</option>
                        <option value="Type">Type</option>
                    </select>
                    <input type="text" id="adv_off_val" class="form-control form-control-sm form-control-cyber" placeholder="Value...">
                </div>

                {{-- 3. Contacts --}}
                <div class="col-md-4 mb-3">
                    <label class="small font-weight-bold text-muted mb-1 rajdhani" style="font-size: 12px;">Contacts</label>
                    <div style="margin-bottom: 31px;"></div>
                    <input type="text" id="adv_contact_val" class="form-control form-control-sm form-control-cyber" placeholder="Phone, email, web...">
                </div>

                {{-- 4. Speciality --}}
                <div class="col-md-4 mb-3">
                    <label class="small font-weight-bold text-muted mb-1 rajdhani" style="font-size: 12px;">Speciality</label>
                    <input type="text" id="adv_spec_val" class="form-control form-control-sm form-control-cyber" placeholder="Speciality keyword...">
                </div>

                {{-- 5. Person --}}
                <div class="col-md-4 mb-3">
                    <label class="small font-weight-bold text-muted mb-1 rajdhani" style="font-size: 12px;">Person</label>
                    <select id="adv_per_field" class="form-control form-control-sm font-weight-bold text-dark mb-1.5 rajdhani">
                        <option value="Name" selected>Name</option>
                        <option value="Designation">Designation</option>
                        <option value="Department">Department</option>
                        <option value="Title">Title</option>
                    </select>
                    <input type="text" id="adv_per_val" class="form-control form-control-sm form-control-cyber" placeholder="Value...">
                </div>

                {{-- 6. Project --}}
                <div class="col-md-4 mb-3">
                    <label class="small font-weight-bold text-muted mb-1 rajdhani" style="font-size: 12px;">Project</label>
                    <select id="adv_prj_field" class="form-control form-control-sm font-weight-bold text-dark mb-1.5 rajdhani">
                        <option value="Name" selected>Name</option>
                        <option value="Scope">Scope</option>
                        <option value="Tech">Technology</option>
                        <option value="Status">Status</option>
                    </select>
                    <input type="text" id="adv_prj_val" class="form-control form-control-sm form-control-cyber" placeholder="Value...">
                </div>

                {{-- 7. Facility --}}
                <div class="col-md-4 mb-3">
                    <label class="small font-weight-bold text-muted mb-1 rajdhani" style="font-size: 12px;">Facility</label>
                    <input type="text" id="adv_facil_val" class="form-control form-control-sm form-control-cyber" placeholder="Facility / Equipment...">
                </div>

                {{-- Actions Row --}}
                <div class="col-md-8 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2 pt-3">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="adv_any_part" checked>
                        <label class="custom-control-label font-weight-bold text-dark rajdhani" for="adv_any_part" style="font-size: 13px; cursor: pointer;">
                            Any part of text
                        </label>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" id="btnAdvFind" class="btn btn-success font-weight-bold px-4 py-2 rajdhani" style="border-radius: 8px; font-size: 14px; min-width: 100px;">
                            <i class="fas fa-search mr-1" id="advFindIcon"></i> <span id="advFindText">Find</span>
                        </button>
                        <button type="button" class="btn btn-link text-muted font-weight-bold rajdhani p-0" onclick="resetAdvancedSearch()" style="font-size: 13.5px; text-decoration: underline;">
                            Reset Data
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- KPI Summary Row --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-sm-6 mb-3">
            <div class="card-cyber kpi-box" style="border-left-color: #0284c7;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="rajdhani text-muted font-weight-bold text-uppercase small">Total Registered</span>
                    <i class="fas fa-building text-info"></i>
                </div>
                <div class="rajdhani font-weight-bold text-dark" id="kpiTotalFirms" style="font-size: 26px;">{{ $totalFirms }}</div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 mb-3">
            <div class="card-cyber kpi-box" style="border-left-color: #22c55e;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="rajdhani text-muted font-weight-bold text-uppercase small">Active Vendors</span>
                    <i class="fas fa-check-circle text-success"></i>
                </div>
                <div class="rajdhani font-weight-bold text-success" id="kpiActiveFirms" style="font-size: 26px;">{{ $activeFirms }}</div>
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

    {{-- Quick Search & Filter Bar on top of List --}}
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
            <h4 class="rajdhani font-weight-bold text-dark m-0" style="font-size: 18px;">
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
                            <span class="badge font-weight-bold" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">{{ $f->frm_entity }}</span>
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
                                <span class="badge badge-danger px-2.5 py-1 rajdhani font-weight-bold" style="font-size: 11.5px;">BLACKLISTED</span>
                            @else
                                <span class="badge badge-success px-2.5 py-1 rajdhani font-weight-bold" style="font-size: 11.5px;">ACTIVE</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button class="btn btn-xs btn-outline-info rajdhani font-weight-bold px-2.5 py-1" onclick="viewFirmDossier({{ $f->frm_id }})" style="border-radius: 6px; font-size: 12px;">
                                <i class="fas fa-eye mr-1"></i> Details
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

{{-- ========================================================================= --}}
{{-- 2. + ADD FIRM TABBED MODAL (FOR FINANCE & PROCUREMENT DIRECTORATE)        --}}
{{-- ========================================================================= --}}
@if($canAddFirm)
<div class="modal fade" id="modalAddFirm" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 14px; overflow: hidden; background: #ffffff;">
            
            {{-- Modal Header (Clean Light Professional Theme) --}}
            <div class="modal-header py-3.5 px-4 bg-white" style="border-bottom: 2px solid #e2e8f0;">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: #ecfdf5; color: #16a34a; font-size: 19px; border: 1.5px solid #bbf7d0;">
                        <i class="fas fa-store"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold text-dark rajdhani m-0" style="font-size: 20px; letter-spacing: 0.5px;">
                            REGISTER NEW SUPPLIER / FIRM
                        </h5>
                        <span class="text-muted small" style="font-size: 12px;">Directorate of Procurement & Finance Central Registration Master</span>
                    </div>
                </div>
                <button type="button" class="close text-secondary" data-dismiss="modal" aria-label="Close" style="opacity: 0.7; font-size: 24px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- Modal Body Form --}}
            <form id="addFirmForm" onsubmit="submitNewFirm(event)">
                @csrf
                <div class="modal-body p-4" style="background: #f8fafc;">
                    
                    {{-- 4 Main Tabs matching Windows Legacy App --}}
                    <ul class="nav nav-tabs tab-nav-firm mb-4" id="firmModalTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-gen" data-toggle="tab" href="#firm-tab-general" role="tab">
                                <i class="fas fa-info-circle mr-1 text-primary"></i> General
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-off" data-toggle="tab" href="#firm-tab-offices" role="tab">
                                <i class="fas fa-building mr-1 text-info"></i> Offices
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-per" data-toggle="tab" href="#firm-tab-persons" role="tab">
                                <i class="fas fa-users mr-1 text-success"></i> Persons
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-prj" data-toggle="tab" href="#firm-tab-projects" role="tab">
                                <i class="fas fa-project-diagram mr-1 text-warning"></i> Projects
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" id="firmModalTabContent">
                        
                        {{-- TAB 1: GENERAL (Image 4) --}}
                        <div class="tab-pane fade show active" id="firm-tab-general" role="tabpanel">
                            <div class="row">
                                {{-- Left Column: General Fields --}}
                                <div class="col-lg-5 mb-3">
                                    <div class="card p-3 border-0 shadow-sm" style="border-radius: 10px; background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                                        <div class="form-group mb-2.5">
                                            <label class="small font-weight-bold text-dark rajdhani mb-1">Firm / Company Name <span class="text-danger">*</span></label>
                                            <input type="text" name="frm_name" id="new_frm_name" required class="form-control form-control-sm font-weight-bold" placeholder="e.g. ABC Technologies Pvt Ltd">
                                        </div>

                                        <div class="form-group mb-2.5">
                                            <label class="small font-weight-bold text-muted rajdhani mb-1">Type</label>
                                            <select name="frm_type" id="new_frm_type" class="form-control form-control-sm font-weight-bold">
                                                <option value="Private company" selected>Private company</option>
                                                <option value="Public limited">Public limited</option>
                                                <option value="Sole proprietorship">Sole proprietorship</option>
                                                <option value="Partnership">Partnership</option>
                                                <option value="Government">Government / Semi-Govt</option>
                                                <option value="Foreign firm">Foreign firm</option>
                                                <option value="Autonomous body">Autonomous body</option>
                                            </select>
                                        </div>

                                        <div class="row">
                                            <div class="col-6 form-group mb-2.5">
                                                <label class="small font-weight-bold text-muted rajdhani mb-1">NTN #</label>
                                                <input type="text" name="frm_ntn" id="new_frm_ntn" class="form-control form-control-sm" placeholder="NTN Number">
                                            </div>
                                            <div class="col-6 form-group mb-2.5">
                                                <label class="small font-weight-bold text-muted rajdhani mb-1">GST #</label>
                                                <input type="text" name="frm_gst" id="new_frm_gst" class="form-control form-control-sm" placeholder="GST Number">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-6 form-group mb-2.5">
                                                <label class="small font-weight-bold text-muted rajdhani mb-1">Group</label>
                                                <input type="text" name="frm_group" id="new_frm_group" class="form-control form-control-sm" placeholder="Corporate Group">
                                            </div>
                                            <div class="col-6 form-group mb-2.5">
                                                <label class="small font-weight-bold text-muted rajdhani mb-1">Employees</label>
                                                <input type="number" name="frm_emp" id="new_frm_emp" class="form-control form-control-sm" placeholder="Count">
                                            </div>
                                        </div>

                                        <div class="form-group mb-2.5">
                                            <label class="small font-weight-bold text-muted rajdhani mb-1">Remarks</label>
                                            <textarea name="frm_notes" id="new_frm_notes" rows="2" class="form-control form-control-sm" placeholder="General vendor remarks or profile highlights..."></textarea>
                                        </div>

                                        <div class="row">
                                            <div class="col-6 form-group mb-0">
                                                <label class="small font-weight-bold text-muted rajdhani mb-1">Points</label>
                                                <select name="frm_points" id="new_frm_points" class="form-control form-control-sm font-weight-bold">
                                                    <option value="5" selected>5 - Standard</option>
                                                    <option value="1">1 - Poor</option>
                                                    <option value="2">2 - Fair</option>
                                                    <option value="3">3 - Good</option>
                                                    <option value="4">4 - Very Good</option>
                                                    <option value="5">5 - Excellent</option>
                                                </select>
                                            </div>
                                            <div class="col-6 form-group mb-0">
                                                <label class="small font-weight-bold text-muted rajdhani mb-1">Black listed</label>
                                                <select name="frm_black" id="new_frm_black" class="form-control form-control-sm font-weight-bold">
                                                    <option value="false" selected>False</option>
                                                    <option value="true">True (Blacklisted)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Middle Columns: Specialities & Facilities (Matching User Screenshots) --}}
                                <div class="col-lg-3 mb-3">
                                    {{-- Specialities with Pre-loaded Dropdown --}}
                                    <div class="card p-3 border-0 shadow-sm mb-3" style="border-radius: 10px; background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="small font-weight-bold text-dark rajdhani mb-0">Specialities</label>
                                            <span class="badge badge-light text-muted" style="font-size: 10px;">Select / Type</span>
                                        </div>
                                        <div class="input-group input-group-sm mb-2">
                                            <input list="specsDatalist" id="input_new_spec" class="form-control form-control-sm font-weight-bold" placeholder="Select or type speciality...">
                                            <datalist id="specsDatalist">
                                                @if(isset($specialities))
                                                    @foreach($specialities as $sp)
                                                        <option value="{{ $sp }}">{{ $sp }}</option>
                                                    @endforeach
                                                @endif
                                            </datalist>
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary btn-sm px-2.5 font-weight-bold" onclick="addSpecItem()"><i class="fas fa-plus"></i></button>
                                            </div>
                                        </div>
                                        <div id="specsContainer" class="p-2 border rounded" style="min-height: 90px; max-height: 120px; overflow-y: auto; background: #f8fafc;"></div>
                                    </div>

                                    {{-- Facilities with Pre-loaded Dropdown --}}
                                    <div class="card p-3 border-0 shadow-sm" style="border-radius: 10px; background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="small font-weight-bold text-dark rajdhani mb-0">Facilities</label>
                                            <span class="badge badge-light text-muted" style="font-size: 10px;">Select / Type</span>
                                        </div>
                                        <div class="input-group input-group-sm mb-2">
                                            <input list="facilsDatalist" id="input_new_facil" class="form-control form-control-sm font-weight-bold" placeholder="Select or type facility...">
                                            <datalist id="facilsDatalist">
                                                @if(isset($facilities))
                                                    @foreach($facilities as $fc)
                                                        <option value="{{ $fc }}">{{ $fc }}</option>
                                                    @endforeach
                                                @endif
                                            </datalist>
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-info btn-sm px-2.5 font-weight-bold" onclick="addFacilItem()"><i class="fas fa-plus"></i></button>
                                            </div>
                                        </div>
                                        <div id="facilsContainer" class="p-2 border rounded" style="min-height: 90px; max-height: 120px; overflow-y: auto; background: #f8fafc;"></div>
                                    </div>
                                </div>

                                {{-- Right Column: Contacts (Matching User Screenshot: Mobile, Landline, Fax, Email, Website) --}}
                                <div class="col-lg-4 mb-3">
                                    <div class="card p-3 border-0 shadow-sm" style="border-radius: 10px; background: #ffffff; border: 1.5px solid #e2e8f0 !important; height: 100%;">
                                        <label class="small font-weight-bold text-dark rajdhani mb-1">Contacts</label>
                                        <div class="d-flex gap-1.5 mb-2">
                                            <select id="input_contact_type" class="form-control form-control-sm font-weight-bold" style="width: 38%;">
                                                <option value="Mobile" selected>Mobile</option>
                                                <option value="Landline">Landline</option>
                                                <option value="Fax">Fax</option>
                                                <option value="Email">Email</option>
                                                <option value="Website">Website</option>
                                            </select>
                                            <input type="text" id="input_contact_val" class="form-control form-control-sm" placeholder="Value...">
                                            <button type="button" class="btn btn-success btn-sm font-weight-bold px-2.5" onclick="addContactItem()"><i class="fas fa-plus"></i></button>
                                        </div>

                                        <div class="table-responsive border rounded" style="max-height: 250px; overflow-y: auto; background: #ffffff;">
                                            <table class="table table-sm table-bordered mb-0" id="tableGeneralContacts">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th class="py-1 px-2 text-muted small" style="width: 38%;">Type</th>
                                                        <th class="py-1 px-2 text-muted small">Value</th>
                                                        <th class="py-1 px-2 text-center" style="width: 30px;">✕</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="contactsTbody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- TAB 2: OFFICES (Image 5) --}}
                        <div class="tab-pane fade" id="firm-tab-offices" role="tabpanel">
                            <div class="card p-3 border-0 shadow-sm mb-3" style="border-radius: 10px; background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                                <h6 class="font-weight-bold text-dark rajdhani mb-2"><i class="fas fa-plus-circle text-info mr-1"></i> Add Branch / Head Office</h6>
                                <div class="row">
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="small font-weight-bold text-muted rajdhani mb-1">Type</label>
                                        <select id="new_off_type" class="form-control form-control-sm font-weight-bold">
                                            <option value="Head Office" selected>Head Office</option>
                                            <option value="Branch Office">Branch Office</option>
                                            <option value="Regional Office">Regional Office</option>
                                            <option value="Warehouse">Warehouse / Depot</option>
                                            <option value="Site Office">Site Office</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="small font-weight-bold text-muted rajdhani mb-1">City</label>
                                        <input type="text" id="new_off_city" class="form-control form-control-sm" placeholder="e.g. Islamabad, Lahore, Karachi">
                                    </div>
                                    <div class="col-md-4 form-group mb-2">
                                        <label class="small font-weight-bold text-muted rajdhani mb-1">Address</label>
                                        <input type="text" id="new_off_address" class="form-control form-control-sm" placeholder="Street / Plaza / Plot address...">
                                    </div>
                                    <div class="col-md-2 form-group mb-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-info btn-sm btn-block font-weight-bold rajdhani py-1.5" onclick="addOfficeToList()">
                                            <i class="fas fa-plus mr-1"></i> Add Office
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Offices List Table --}}
                            <div class="card p-3 border-0 shadow-sm" style="border-radius: 10px; background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                                <h6 class="font-weight-bold text-dark rajdhani mb-2">Registered Offices List</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0" id="tableOfficesList">
                                        <thead class="bg-light">
                                            <tr>
                                                <th style="width: 20%;">Type</th>
                                                <th style="width: 25%;">City</th>
                                                <th>Address</th>
                                                <th class="text-center" style="width: 50px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="officesTbody">
                                            <tr id="noOfficesRow"><td colspan="4" class="text-center text-muted small py-3 font-italic">No branch offices added yet.</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- TAB 3: PERSONS (Image 3) --}}
                        <div class="tab-pane fade" id="firm-tab-persons" role="tabpanel">
                            <div class="card p-3 border-0 shadow-sm mb-3" style="border-radius: 10px; background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                                <h6 class="font-weight-bold text-dark rajdhani mb-2"><i class="fas fa-user-plus text-success mr-1"></i> Add Contact Person / Officer</h6>
                                <div class="row">
                                    <div class="col-md-2 form-group mb-2">
                                        <label class="small font-weight-bold text-muted rajdhani mb-1">Title</label>
                                        <select id="new_per_title" class="form-control form-control-sm font-weight-bold">
                                            <option value="Mr." selected>Mr.</option>
                                            <option value="Ms.">Ms.</option>
                                            <option value="Dr.">Dr.</option>
                                            <option value="Engr.">Engr.</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="small font-weight-bold text-muted rajdhani mb-1">Name <span class="text-danger">*</span></label>
                                        <input type="text" id="new_per_name" class="form-control form-control-sm font-weight-bold" placeholder="Person Name">
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="small font-weight-bold text-muted rajdhani mb-1">Desig</label>
                                        <input type="text" id="new_per_desig" class="form-control form-control-sm" placeholder="e.g. Sales Manager, Director">
                                    </div>
                                    <div class="col-md-2 form-group mb-2">
                                        <label class="small font-weight-bold text-muted rajdhani mb-1">Phone / Mobile</label>
                                        <input type="text" id="new_per_contact" class="form-control form-control-sm" placeholder="Contact number">
                                    </div>
                                    <div class="col-md-2 form-group mb-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-success btn-sm btn-block font-weight-bold rajdhani py-1.5" onclick="addPersonToList()">
                                            <i class="fas fa-plus mr-1"></i> Add Person
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Persons List Table --}}
                            <div class="card p-3 border-0 shadow-sm" style="border-radius: 10px; background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                                <h6 class="font-weight-bold text-dark rajdhani mb-2">Key Personnel Directory</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0" id="tablePersonsList">
                                        <thead class="bg-light">
                                            <tr>
                                                <th style="width: 15%;">Title</th>
                                                <th style="width: 30%;">Name</th>
                                                <th style="width: 25%;">Designation</th>
                                                <th>Contact</th>
                                                <th class="text-center" style="width: 50px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="personsTbody">
                                            <tr id="noPersonsRow"><td colspan="5" class="text-center text-muted small py-3 font-italic">No contact persons added yet.</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- TAB 4: PROJECTS (Image 2) --}}
                        <div class="tab-pane fade" id="firm-tab-projects" role="tabpanel">
                            <div class="card p-3 border-0 shadow-sm mb-3" style="border-radius: 10px; background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                                <h6 class="font-weight-bold text-dark rajdhani mb-2"><i class="fas fa-project-diagram text-warning mr-1"></i> Add Past / Track Record Project</h6>
                                <div class="row">
                                    <div class="col-md-4 form-group mb-2">
                                        <label class="small font-weight-bold text-muted rajdhani mb-1">Name <span class="text-danger">*</span></label>
                                        <input type="text" id="new_prj_name" class="form-control form-control-sm font-weight-bold" placeholder="Project Title">
                                    </div>
                                    <div class="col-md-4 form-group mb-2">
                                        <label class="small font-weight-bold text-muted rajdhani mb-1">Scope</label>
                                        <input type="text" id="new_prj_scope" class="form-control form-control-sm" placeholder="Project Scope / Deliverables">
                                    </div>
                                    <div class="col-md-4 form-group mb-2">
                                        <label class="small font-weight-bold text-muted rajdhani mb-1">Technology</label>
                                        <input type="text" id="new_prj_tech" class="form-control form-control-sm" placeholder="Tech / Hardware stack">
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="small font-weight-bold text-muted rajdhani mb-1">Cost (PKR)</label>
                                        <input type="number" id="new_prj_cost" class="form-control form-control-sm" placeholder="Total Cost">
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="small font-weight-bold text-muted rajdhani mb-1">Status</label>
                                        <select id="new_prj_status" class="form-control form-control-sm font-weight-bold">
                                            <option value="Completed" selected>Completed</option>
                                            <option value="In Progress">In Progress</option>
                                            <option value="Awarded">Awarded</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 form-group mb-2">
                                        <label class="small font-weight-bold text-muted rajdhani mb-1">Award date</label>
                                        <input type="date" id="new_prj_awarddt" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-2 form-group mb-2">
                                        <label class="small font-weight-bold text-muted rajdhani mb-1">Completion date</label>
                                        <input type="date" id="new_prj_compdt" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-2 form-group mb-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-warning btn-sm btn-block font-weight-bold rajdhani py-1.5" onclick="addProjectToList()">
                                            <i class="fas fa-plus mr-1"></i> Add Project
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Projects List Table --}}
                            <div class="card p-3 border-0 shadow-sm" style="border-radius: 10px; background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                                <h6 class="font-weight-bold text-dark rajdhani mb-2">Track Record Projects List</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0" id="tableProjectsList">
                                        <thead class="bg-light">
                                            <tr>
                                                <th style="width: 25%;">Project Name</th>
                                                <th style="width: 20%;">Scope</th>
                                                <th style="width: 15%;">Technology</th>
                                                <th style="width: 15%;">Cost</th>
                                                <th style="width: 15%;">Status</th>
                                                <th class="text-center" style="width: 50px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="projectsTbody">
                                            <tr id="noProjectsRow"><td colspan="6" class="text-center text-muted small py-3 font-italic">No past projects added yet.</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer py-3 px-4 bg-white border-top d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-secondary font-weight-bold rajdhani px-3.5 py-2" data-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" id="btnSubmitFirm" class="btn btn-success font-weight-bold rajdhani px-4 py-2" style="font-size: 15px; box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);">
                        <i class="fas fa-save mr-1.5" id="btnSubmitFirmIcon"></i> <span id="btnSubmitFirmText">SAVE & REGISTER FIRM</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endif

{{-- ========================================================================= --}}
{{-- 3. FIRM DOSSIER DETAILS MODAL                                              --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="modalFirmDossier" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 14px; overflow: hidden; background: #ffffff;">
            <div class="modal-header py-3.5 px-4 bg-white" style="border-bottom: 2px solid #e2e8f0;">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: #e0f2fe; color: #0284c7; font-size: 18px; border: 1.5px solid #bae6fd;">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-info rajdhani px-2.5 py-1" id="dossierFirmId" style="font-size: 13px;">#--</span>
                        <h5 class="modal-title font-weight-bold text-dark rajdhani m-0" id="dossierFirmName" style="font-size: 20px;">Firm Dossier</h5>
                    </div>
                </div>
                <button type="button" class="close text-secondary" data-dismiss="modal" aria-label="Close" style="opacity: 0.7; font-size: 24px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" id="dossierModalBody">
                <div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-info"></i><p class="mt-2 text-muted small">Loading firm profile...</p></div>
            </div>
        </div>
    </div>
</div>

<script>
// 1. ADVANCED SEARCH TOGGLE & EXECUTION
let isAdvSearchOpen = false;
function toggleAdvSearch(forceState = null) {
    isAdvSearchOpen = (forceState !== null) ? forceState : !isAdvSearchOpen;
    const panel = document.getElementById('advancedSearchCollapse');
    const icon = document.getElementById('advSearchIcon');
    if (isAdvSearchOpen) {
        panel.style.display = 'block';
        icon.className = 'fas fa-chevron-up ml-1';
    } else {
        panel.style.display = 'none';
        icon.className = 'fas fa-chevron-down ml-1';
    }
}

document.getElementById('btnToggleAdvSearch').addEventListener('click', function() {
    toggleAdvSearch();
});

function executeAdvancedSearch(e) {
    if (e) e.preventDefault();
    const btn = document.getElementById('btnAdvFind');
    const icon = document.getElementById('advFindIcon');
    const txt = document.getElementById('advFindText');

    btn.disabled = true;
    icon.className = 'fas fa-spinner fa-spin mr-1';
    txt.innerText = 'Searching...';

    const params = new URLSearchParams({
        gen_field: document.getElementById('adv_gen_field').value,
        gen_val: document.getElementById('adv_gen_val').value,
        off_field: document.getElementById('adv_off_field').value,
        off_val: document.getElementById('adv_off_val').value,
        contact_val: document.getElementById('adv_contact_val').value,
        spec_val: document.getElementById('adv_spec_val').value,
        per_field: document.getElementById('adv_per_field').value,
        per_val: document.getElementById('adv_per_val').value,
        prj_field: document.getElementById('adv_prj_field').value,
        prj_val: document.getElementById('adv_prj_val').value,
        facil_val: document.getElementById('adv_facil_val').value,
        any_part: document.getElementById('adv_any_part').checked ? 1 : 0
    });

    fetch(`{{ route('nrdi.firms.data') }}?${params.toString()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        icon.className = 'fas fa-search mr-1';
        txt.innerText = 'Find';

        if (res.success) {
            renderFirmsTable(res.data);
            document.getElementById('visibleFirmsCount').innerText = `${res.count} Firms Found`;
        }
    })
    .catch(err => {
        btn.disabled = false;
        icon.className = 'fas fa-search mr-1';
        txt.innerText = 'Find';
        alert('Error performing advanced search.');
    });
}

function resetAdvancedSearch() {
    document.getElementById('advSearchForm').reset();
    window.location.reload();
}

// 2. QUICK SEARCH & PILL FILTERS (CLIENT-SIDE REAL-TIME)
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('quickSearchInput');
    const rows = document.querySelectorAll('.firm-row');
    const countBadge = document.getElementById('visibleFirmsCount');

    let currentFilter = 'all';

    function filterRows() {
        const query = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        rows.forEach(r => {
            const name = r.dataset.name || '';
            const ntn = r.dataset.ntn || '';
            const gst = r.dataset.gst || '';
            const city = r.dataset.city || '';
            const entity = r.dataset.entity || '';
            const type = r.dataset.type || '';
            const isBlack = r.dataset.black === '1';
            const isAwarded = r.dataset.awarded === '1';

            const matchesQuery = !query || name.includes(query) || ntn.includes(query) || gst.includes(query) || city.includes(query) || entity.includes(query) || type.includes(query);
            let matchesPill = true;

            if (currentFilter === 'active') matchesPill = !isBlack;
            else if (currentFilter === 'blacklisted') matchesPill = isBlack;
            else if (currentFilter === 'awarded') matchesPill = isAwarded;

            if (matchesQuery && matchesPill) {
                r.style.display = '';
                visibleCount++;
            } else {
                r.style.display = 'none';
            }
        });

        countBadge.innerText = `${visibleCount} Firms`;
    }

    if (searchInput) searchInput.addEventListener('input', filterRows);

    document.querySelectorAll('.pill-filter').forEach(pill => {
        pill.addEventListener('click', function() {
            document.querySelectorAll('.pill-filter').forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.filter;
            filterRows();
        });
    });
});

// 3. ADD FIRM DYNAMIC ITEMS (SPECS, FACILS, CONTACTS, OFFICES, PERSONS, PROJECTS)
const addedSpecs = [];
const addedFacils = [];
const addedContacts = [];
const addedOffices = [];
const addedPersons = [];
const addedProjects = [];

function addSpecItem() {
    const val = document.getElementById('input_new_spec').value.trim();
    if (!val) return;
    addedSpecs.push(val);
    document.getElementById('input_new_spec').value = '';
    renderBadges('specsContainer', addedSpecs, 'bg-primary');
}

function addFacilItem() {
    const val = document.getElementById('input_new_facil').value.trim();
    if (!val) return;
    addedFacils.push(val);
    document.getElementById('input_new_facil').value = '';
    renderBadges('facilsContainer', addedFacils, 'bg-info');
}

function renderBadges(containerId, list, bgClass) {
    const container = document.getElementById(containerId);
    container.innerHTML = list.map((item, idx) => `
        <span class="badge ${bgClass} text-white px-2 py-1 mr-1 mb-1 font-weight-bold d-inline-flex align-items-center" style="font-size: 11px;">
            ${item} <i class="fas fa-times ml-1.5 cursor-pointer" onclick="removeItem('${containerId}', ${idx})"></i>
        </span>
    `).join('');
}

function removeItem(containerId, idx) {
    if (containerId === 'specsContainer') { addedSpecs.splice(idx, 1); renderBadges(containerId, addedSpecs, 'bg-primary'); }
    else if (containerId === 'facilsContainer') { addedFacils.splice(idx, 1); renderBadges(containerId, addedFacils, 'bg-info'); }
}

function addContactItem() {
    const type = document.getElementById('input_contact_type').value;
    const val = document.getElementById('input_contact_val').value.trim();
    if (!val) return;
    addedContacts.push({ type, value: val });
    document.getElementById('input_contact_val').value = '';
    renderContactsTable();
}

function renderContactsTable() {
    const tbody = document.getElementById('contactsTbody');
    tbody.innerHTML = addedContacts.map((c, i) => `
        <tr>
            <td class="py-1 px-2 font-weight-bold text-dark small">${c.type}</td>
            <td class="py-1 px-2 small">${c.value}</td>
            <td class="py-1 px-2 text-center"><i class="fas fa-trash text-danger cursor-pointer" onclick="addedContacts.splice(${i}, 1); renderContactsTable();"></i></td>
        </tr>
    `).join('');
}

function addOfficeToList() {
    const type = document.getElementById('new_off_type').value;
    const city = document.getElementById('new_off_city').value.trim();
    const address = document.getElementById('new_off_address').value.trim();
    if (!city && !address) { alert('Please enter at least city or address.'); return; }
    
    addedOffices.push({ off_type: type, off_city: city, off_address: address });
    document.getElementById('new_off_city').value = '';
    document.getElementById('new_off_address').value = '';
    renderOfficesTable();
}

function renderOfficesTable() {
    const tbody = document.getElementById('officesTbody');
    if (addedOffices.length === 0) {
        tbody.innerHTML = '<tr id="noOfficesRow"><td colspan="4" class="text-center text-muted small py-3 font-italic">No branch offices added yet.</td></tr>';
        return;
    }
    tbody.innerHTML = addedOffices.map((o, i) => `
        <tr>
            <td class="font-weight-bold text-dark">${o.off_type}</td>
            <td><i class="fas fa-map-marker-alt text-danger mr-1"></i>${o.off_city || 'N/A'}</td>
            <td>${o.off_address || 'N/A'}</td>
            <td class="text-center"><i class="fas fa-trash text-danger cursor-pointer" onclick="addedOffices.splice(${i}, 1); renderOfficesTable();"></i></td>
        </tr>
    `).join('');
}

function addPersonToList() {
    const title = document.getElementById('new_per_title').value;
    const name = document.getElementById('new_per_name').value.trim();
    const desig = document.getElementById('new_per_desig').value.trim();
    const contact = document.getElementById('new_per_contact').value.trim();
    if (!name) { alert('Person Name is required.'); return; }

    addedPersons.push({ per_title: title, per_name: name, per_desig: desig, contacts: contact ? [{ type: 'Mobile', value: contact }] : [] });
    document.getElementById('new_per_name').value = '';
    document.getElementById('new_per_desig').value = '';
    document.getElementById('new_per_contact').value = '';
    renderPersonsTable();
}

function renderPersonsTable() {
    const tbody = document.getElementById('personsTbody');
    if (addedPersons.length === 0) {
        tbody.innerHTML = '<tr id="noPersonsRow"><td colspan="5" class="text-center text-muted small py-3 font-italic">No contact persons added yet.</td></tr>';
        return;
    }
    tbody.innerHTML = addedPersons.map((p, i) => `
        <tr>
            <td>${p.per_title}</td>
            <td class="font-weight-bold text-dark">${p.per_name}</td>
            <td>${p.per_desig || 'N/A'}</td>
            <td>${p.contacts && p.contacts[0] ? p.contacts[0].value : 'N/A'}</td>
            <td class="text-center"><i class="fas fa-trash text-danger cursor-pointer" onclick="addedPersons.splice(${i}, 1); renderPersonsTable();"></i></td>
        </tr>
    `).join('');
}

function addProjectToList() {
    const name = document.getElementById('new_prj_name').value.trim();
    const scope = document.getElementById('new_prj_scope').value.trim();
    const tech = document.getElementById('new_prj_tech').value.trim();
    const cost = document.getElementById('new_prj_cost').value.trim();
    const status = document.getElementById('new_prj_status').value;
    const awarddt = document.getElementById('new_prj_awarddt').value;
    const compdt = document.getElementById('new_prj_compdt').value;
    if (!name) { alert('Project Name is required.'); return; }

    addedProjects.push({ prj_name: name, prj_scope: scope, prj_tech: tech, prj_cost: cost, prj_status: status, prj_awarddt: awarddt, prj_compdt: compdt });
    document.getElementById('new_prj_name').value = '';
    document.getElementById('new_prj_scope').value = '';
    document.getElementById('new_prj_tech').value = '';
    document.getElementById('new_prj_cost').value = '';
    renderProjectsTable();
}

function renderProjectsTable() {
    const tbody = document.getElementById('projectsTbody');
    if (addedProjects.length === 0) {
        tbody.innerHTML = '<tr id="noProjectsRow"><td colspan="6" class="text-center text-muted small py-3 font-italic">No past projects added yet.</td></tr>';
        return;
    }
    tbody.innerHTML = addedProjects.map((pr, i) => `
        <tr>
            <td class="font-weight-bold text-dark">${pr.prj_name}</td>
            <td>${pr.prj_scope || 'N/A'}</td>
            <td>${pr.prj_tech || 'N/A'}</td>
            <td>${pr.prj_cost ? 'PKR ' + Number(pr.prj_cost).toLocaleString() : 'N/A'}</td>
            <td><span class="badge badge-success">${pr.prj_status}</span></td>
            <td class="text-center"><i class="fas fa-trash text-danger cursor-pointer" onclick="addedProjects.splice(${i}, 1); renderProjectsTable();"></i></td>
        </tr>
    `).join('');
}

// 4. SUBMIT NEW FIRM (AJAX)
function submitNewFirm(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitFirm');
    const icon = document.getElementById('btnSubmitFirmIcon');
    const txt = document.getElementById('btnSubmitFirmText');

    btn.disabled = true;
    icon.className = 'fas fa-spinner fa-spin mr-1.5';
    txt.innerText = 'SAVING...';

    const payload = {
        _token: '{{ csrf_token() }}',
        frm_name: document.getElementById('new_frm_name').value,
        frm_type: document.getElementById('new_frm_type').value,
        frm_ntn: document.getElementById('new_frm_ntn').value,
        frm_gst: document.getElementById('new_frm_gst').value,
        frm_group: document.getElementById('new_frm_group').value,
        frm_emp: document.getElementById('new_frm_emp').value,
        frm_notes: document.getElementById('new_frm_notes').value,
        frm_points: document.getElementById('new_frm_points').value,
        frm_black: document.getElementById('new_frm_black').value,
        specialities: addedSpecs,
        facilities: addedFacils,
        contacts: addedContacts,
        offices: addedOffices,
        persons: addedPersons,
        projects: addedProjects
    };

    fetch('{{ route("nrdi.firms.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        icon.className = 'fas fa-save mr-1.5';
        txt.innerText = 'SAVE & REGISTER FIRM';

        if (res.success) {
            $('#modalAddFirm').modal('hide');
            
            // Show toast
            const toast = document.getElementById('firmToastAlert');
            const toastMsg = document.getElementById('firmToastMessage');
            toastMsg.innerText = res.message || 'Firm registered successfully in central vendor roster!';
            toast.classList.remove('d-none');
            toast.classList.add('d-flex');
            window.scrollTo({ top: 0, behavior: 'smooth' });
            setTimeout(() => { toast.classList.remove('d-flex'); toast.classList.add('d-none'); }, 5000);

            // Prepend new row to table
            const f = res.firm;
            const newRowHtml = `
                <tr class="firm-row" style="background: #ecfdf5 !important;"
                    data-name="${f.frm_name.toLowerCase()}"
                    data-ntn="${f.frm_ntn.toLowerCase()}"
                    data-gst="${f.frm_gst.toLowerCase()}"
                    data-city="${f.main_city.toLowerCase()}"
                    data-entity="${f.frm_entity.toLowerCase()}"
                    data-type="${f.frm_type.toLowerCase()}"
                    data-black="${f.frm_black ? '1' : '0'}"
                    data-awarded="0">
                    <td class="rajdhani text-info font-weight-bold">#${f.frm_id}</td>
                    <td>
                        <div class="font-weight-bold text-dark" style="font-size: 14px;">${f.frm_name}</div>
                        ${f.frm_notes ? `<div class="small text-muted">${f.frm_notes}</div>` : ''}
                    </td>
                    <td>
                        <span class="badge font-weight-bold" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">${f.frm_entity}</span>
                        <div class="small text-muted mt-1">${f.frm_type}</div>
                    </td>
                    <td>
                        <div class="small text-muted">NTN: <strong class="text-dark">${f.frm_ntn}</strong></div>
                        <div class="small text-muted">GST: <strong class="text-dark">${f.frm_gst}</strong></div>
                    </td>
                    <td>
                        <div class="text-dark"><i class="fas fa-map-marker-alt text-danger mr-1"></i>${f.main_city}</div>
                        <div class="small text-muted">${f.offices_count} Office(s) | ${f.persons_count} Contact(s)</div>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-secondary px-2 py-1 rajdhani">0 Cases</span>
                    </td>
                    <td class="text-right rajdhani font-weight-bold text-muted" style="font-size: 14px;">
                        PKR 0
                    </td>
                    <td class="text-center">
                        <span class="badge badge-success px-2.5 py-1 rajdhani font-weight-bold" style="font-size: 11.5px;">ACTIVE</span>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-xs btn-outline-info rajdhani font-weight-bold px-2.5 py-1" onclick="viewFirmDossier(${f.frm_id})" style="border-radius: 6px; font-size: 12px;">
                            <i class="fas fa-eye mr-1"></i> Details
                        </button>
                    </td>
                </tr>
            `;
            document.getElementById('allFirmsTableBody').insertAdjacentHTML('afterbegin', newRowHtml);
        } else {
            alert(res.message || 'Could not register firm.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        icon.className = 'fas fa-save mr-1.5';
        txt.innerText = 'SAVE & REGISTER FIRM';
        alert('Server error occurred while saving firm.');
    });
}

// 5. VIEW FIRM DOSSIER DETAILS MODAL
function viewFirmDossier(firmId) {
    $('#modalFirmDossier').modal('show');
    const body = document.getElementById('dossierModalBody');
    body.innerHTML = '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-info"></i><p class="mt-2 text-muted small">Loading firm dossier...</p></div>';

    fetch(`/nrdi/firms/${firmId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            const f = res.firm;
            document.getElementById('dossierFirmId').innerText = `#${f.frm_id}`;
            document.getElementById('dossierFirmName').innerText = f.frm_name;

            let officesHtml = res.offices.length > 0 ? res.offices.map(o => `
                <div class="p-2 border rounded mb-2 bg-light">
                    <strong>${o.off_type || 'Office'}:</strong> ${o.off_city || ''} — <span class="text-muted">${o.off_address || ''}</span>
                </div>
            `).join('') : '<p class="text-muted small font-italic">No branch offices listed.</p>';

            let personsHtml = res.persons.length > 0 ? res.persons.map(p => `
                <div class="p-2 border rounded mb-2 bg-light">
                    <strong>${p.per_title || ''} ${p.per_name}:</strong> ${p.per_desig || 'Officer'} <span class="text-muted">${p.per_dept ? '(' + p.per_dept + ')' : ''}</span>
                </div>
            `).join('') : '<p class="text-muted small font-italic">No contact persons listed.</p>';

            let projectsHtml = res.projects.length > 0 ? res.projects.map(pr => `
                <div class="p-2 border rounded mb-2 bg-light">
                    <strong>${pr.prj_name}</strong>: ${pr.prj_scope || ''} <span class="badge badge-success float-right">${pr.prj_status || 'Completed'}</span>
                </div>
            `).join('') : '<p class="text-muted small font-italic">No past projects listed.</p>';

            body.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <div class="card p-3 border shadow-none mb-3 bg-white">
                            <h6 class="font-weight-bold rajdhani text-info">REGISTRATION DETAILS</h6>
                            <p class="mb-1 small"><strong>Entity:</strong> ${f.frm_entity || 'N/A'}</p>
                            <p class="mb-1 small"><strong>Type:</strong> ${f.frm_type || 'N/A'}</p>
                            <p class="mb-1 small"><strong>NTN:</strong> ${f.frm_ntn || 'N/A'}</p>
                            <p class="mb-1 small"><strong>GST:</strong> ${f.frm_gst || 'N/A'}</p>
                            <p class="mb-1 small"><strong>Status:</strong> ${f.frm_black ? '<span class="text-danger font-weight-bold">Blacklisted</span>' : '<span class="text-success font-weight-bold">Active</span>'}</p>
                            ${f.frm_notes ? `<p class="mb-0 small text-muted mt-2 border-top pt-2"><em>${f.frm_notes}</em></p>` : ''}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-3 border shadow-none mb-3 bg-white">
                            <h6 class="font-weight-bold rajdhani text-info">BRANCH OFFICES</h6>
                            ${officesHtml}
                        </div>
                        <div class="card p-3 border shadow-none mb-3 bg-white">
                            <h6 class="font-weight-bold rajdhani text-info">KEY PERSONNEL</h6>
                            ${personsHtml}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-3 border shadow-none mb-3 bg-white">
                            <h6 class="font-weight-bold rajdhani text-info">TRACK RECORD / PROJECTS</h6>
                            ${projectsHtml}
                        </div>
                    </div>
                </div>
            `;
        }
    });
}
</script>
@endsection
