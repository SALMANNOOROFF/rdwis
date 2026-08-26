@extends('welcome')

@section('content')
<div class="content-wrapper pt-3 pb-5">

    <style>
        /* Modern Glassmorphism & Dashboard Styling */
        .fop-card {
            border: 1px solid var(--rd-border, #e2e8f0);
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
            background: var(--rd-surface, #ffffff);
            transition: transform 0.2s, box-shadow 0.2s;
            overflow: hidden;
        }
        .fop-card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }
        .fop-card-header {
            padding: 12px 18px;
            font-weight: 700;
            font-size: 0.9rem;
            border-bottom: 1px solid var(--rd-border, #e2e8f0);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Top Action Bar & Tabs */
        .nav-pills-custom .nav-link {
            border-radius: 20px;
            padding: 7px 18px;
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--rd-text2, #495057);
            border: 1px solid var(--rd-border, #dee2e6);
            background: var(--rd-surface, #fff);
            transition: all 0.25s;
            margin-right: 6px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .nav-pills-custom .nav-link:hover {
            background: var(--rd-surface2, #f8f9fa);
            border-color: var(--rd-primary-300, #90cdf4);
            color: var(--rd-primary-700, #2b6cb0);
        }
        .nav-pills-custom .nav-link.active {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7) !important;
            color: #ffffff !important;
            border-color: #0d6efd !important;
            box-shadow: 0 3px 8px rgba(13, 110, 253, 0.35);
        }

        /* Project Selector in Top Bar */
        .project-select-box {
            font-weight: 600;
            font-size: 0.88rem;
            border-radius: 20px;
            border: 1.5px solid var(--rd-primary-400, #3182ce);
            padding: 6px 14px;
            background-color: var(--rd-surface, #fff);
            color: var(--rd-text1, #2d3748);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
            min-width: 250px;
            cursor: pointer;
        }

        /* Account Summary Box matching Legacy Image 1 */
        .acc-summary-panel {
            background: #ffe3ca; /* soft warm peach tone matching legacy Access fin_headstatus */
            border: 1.5px solid #2b579a;
            border-radius: 6px;
            padding: 0;
            overflow: hidden;
        }
        .acc-summary-header {
            background: transparent;
            color: #c0392b;
            font-weight: 800;
            font-size: 1.05rem;
            text-align: center;
            padding: 8px 10px 4px 10px;
        }
        .acc-summary-amount {
            text-align: center;
            font-weight: 700;
            font-size: 0.95rem;
            color: #7f1d1d;
            margin-bottom: 6px;
        }
        .acc-summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .acc-summary-table tr {
            border-top: 1px solid rgba(0,0,0,0.05);
        }
        .acc-summary-table td {
            padding: 4px 8px;
            font-size: 0.82rem;
            vertical-align: middle;
        }
        .acc-summary-table td.label-col {
            text-align: right;
            color: #333;
            font-weight: 600;
            width: 48%;
        }
        .acc-summary-table td.val-col {
            text-align: right;
            font-family: 'Consolas', 'Courier New', monospace;
            font-weight: 700;
            color: #111;
            width: 40%;
        }
        .acc-summary-table td.btn-col {
            width: 12%;
            text-align: center;
            padding-right: 6px;
        }
        .acc-summary-table tr.highlight-balance {
            background: #f8be8f;
            border-top: 1.5px solid #2b579a;
            border-bottom: 1.5px solid #2b579a;
        }
        .acc-summary-table tr.highlight-avail {
            background: #f4a261;
            border-top: 1.5px solid #2b579a;
            border-bottom: 1.5px solid #2b579a;
        }

        /* Drill-down small buttons */
        .btn-drill {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.65rem;
            padding: 0;
            background: #fff;
            border: 1px solid #c0392b;
            color: #c0392b;
            transition: all 0.2s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .btn-drill:hover {
            background: #c0392b;
            color: #fff;
            transform: scale(1.1);
        }
        .btn-drill-blue {
            border-color: #0d6efd;
            color: #0d6efd;
        }
        .btn-drill-blue:hover {
            background: #0d6efd;
            color: #fff;
        }
        .btn-drill-green {
            border-color: #198754;
            color: #198754;
        }
        .btn-drill-green:hover {
            background: #198754;
            color: #fff;
        }

        /* Scope Panels (PCC Blue & CSRF Green) */
        .scope-panel-pcc {
            background: #e8f4fd;
            border: 1.5px solid #0d6efd;
            border-radius: 6px;
            overflow: hidden;
        }
        .scope-panel-csrf {
            background: #eafaf1;
            border: 1.5px solid #198754;
            border-radius: 6px;
            overflow: hidden;
        }

        /* Shares Info Box matching Image 1 Top Left */
        .shares-infobox {
            border: 1.5px solid #6c757d;
            border-radius: 4px;
            padding: 6px 12px;
            background: #fff;
            display: inline-block;
        }
        .shares-infobox table td {
            padding: 2px 8px;
            font-size: 0.85rem;
        }

        /* Fundings Matrix Table matching Image 2 */
        .fundings-matrix-table th {
            background: #e2e8f0;
            color: #2d3748;
            font-size: 0.82rem;
            font-weight: 700;
            text-align: center;
            padding: 8px;
        }
        .fundings-matrix-table td {
            text-align: center;
            font-size: 0.88rem;
            font-weight: 600;
            padding: 8px;
            font-family: 'Consolas', 'Courier New', monospace;
        }

        /* Compact Data Table */
        .fop-table td, .fop-table th {
            vertical-align: middle;
            font-size: 0.8rem;
            padding: 0.4rem 0.6rem !important;
            white-space: nowrap;
        }
        .fop-table thead th {
            background: var(--rd-surface2, #f4f6f9);
            border-bottom: 2px solid var(--rd-border, #dee2e6);
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
        }
        .fig-cell {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 4px;
        }
        .fig-val {
            font-weight: 600;
            font-size: 0.8rem;
            font-family: 'Consolas', 'Courier New', monospace;
        }
    </style>

    <div class="container-fluid px-3 px-md-4">

        {{-- ======================================================== --}}
        {{-- TOP BAR: Title, Project Selector Filter & Action Tabs --}}
        {{-- ======================================================== --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <div class="mb-2 mb-md-0">
                <h3 class="m-0 text-dark font-weight-bold d-flex align-items-center">
                    <i class="fas fa-chart-pie text-primary mr-2"></i> Finance of Project
                </h3>
                <small class="text-muted">Division Project Head Status & Financial Intelligence</small>
            </div>

            {{-- Top Right: Division + Project Filter Dropdowns --}}
            <div class="d-flex align-items-center flex-wrap" style="gap: 12px;">
                @if(!empty($isGlobalViewer) && !empty($divisions) && $divisions->count() > 1)
                <div class="d-flex align-items-center" style="gap: 6px;">
                    <label class="mb-0 text-muted font-weight-bold small text-nowrap"><i class="fas fa-sitemap text-info mr-1"></i> Division:</label>
                    <select class="form-control form-control-sm shadow-sm" id="divisionFilter" style="width: 170px; border-radius: 20px; font-weight: 600; font-size: 0.85rem; height: 36px; border: 1.5px solid var(--rd-border); background-color: var(--rd-surface, #fff);">
                        <option value="all">— All Divisions —</option>
                        @foreach($divisions as $div)
                            <option value="{{ $div->unt_id }}">{{ $div->unt_namesh ?: $div->unt_name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="d-flex align-items-center" style="gap: 6px;">
                    <label class="mb-0 text-muted font-weight-bold small text-nowrap"><i class="fas fa-filter text-primary mr-1"></i> Project:</label>
                    <select class="form-control form-control-sm project-select-box shadow-sm" id="projectFilter" onchange="window.location.href = this.value" style="width: auto; min-width: 260px; max-width: 380px; height: 36px;">
                        <option value="{{ route('division.finance-of-project.index', ['tab' => 'overview']) }}" {{ $activeTab === 'overview' ? 'selected' : '' }}>
                            📋 — All Projects (Overview Table) —
                        </option>
                        <optgroup label="{{ !empty($isGlobalViewer) ? 'All NRDI Projects (Central Master)' : 'Division Projects' }}" id="projectOptgroup">
                            @foreach($heads as $h)
                                <option value="{{ route('division.finance-of-project.index', ['head_id' => $h->hed_id, 'tab' => ($activeTab === 'overview' ? 'status' : $activeTab)]) }}"
                                        data-unt-id="{{ $h->hed_unt_id }}"
                                        {{ $selectedHeadId == $h->hed_id && $activeTab !== 'overview' ? 'selected' : '' }}>
                                    {{ $h->hed_code }} @if(!empty($isGlobalViewer) && !empty($h->unt_namesh)) [{{ $h->unt_namesh }}] @endif — {{ \Illuminate\Support\Str::limit($h->prj_title, 35) }}
                                </option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
            </div>
        </div>

        {{-- Action Buttons Tabs (Status, MTSS Status, Fundings, Loans, Overview) --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
            <div class="nav nav-pills nav-pills-custom">
                <a href="{{ route('division.finance-of-project.index', ['head_id' => $selectedHeadId, 'tab' => 'status']) }}"
                   class="nav-link {{ $activeTab === 'status' ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i> Status (Detailed)
                </a>
                <a href="{{ route('division.finance-of-project.index', ['head_id' => $selectedHeadId, 'tab' => 'mtss']) }}"
                   class="nav-link {{ $activeTab === 'mtss' ? 'active' : '' }}">
                    <i class="fas fa-university"></i> MTSS Status
                </a>
                <a href="{{ route('division.finance-of-project.index', ['head_id' => $selectedHeadId, 'tab' => 'fundings']) }}"
                   class="nav-link {{ $activeTab === 'fundings' ? 'active' : '' }}">
                    <i class="fas fa-money-check-alt"></i> Fundings & Milestones
                </a>
                <a href="{{ route('division.finance-of-project.index', ['head_id' => $selectedHeadId, 'tab' => 'loans']) }}"
                   class="nav-link {{ $activeTab === 'loans' ? 'active' : '' }}">
                    <i class="fas fa-hand-holding-usd"></i> Loans & Netting
                </a>
                <a href="{{ route('division.finance-of-project.index', ['tab' => 'overview']) }}"
                   class="nav-link {{ $activeTab === 'overview' ? 'active' : '' }}">
                    <i class="fas fa-table"></i> All Projects List
                </a>
            </div>

            @if($selectedHead && $activeTab !== 'overview')
                <div class="mt-2 mt-md-0">
                    <span class="badge badge-primary px-3 py-2" style="font-size: 0.85rem; border-radius: 15px;">
                        <i class="fas fa-tag mr-1"></i> Active: <strong>{{ $selectedHead->hed_code }}</strong> ({{ $selectedHead->prj_status }})
                    </span>
                </div>
            @endif
        </div>

        {{-- ======================================================== --}}
        {{-- TAB 1: DETAILED STATUS (Matching Legacy fin_headstatus & Image 1) --}}
        {{-- ======================================================== --}}
        @if($activeTab === 'status' && $selectedHead && $finStatus)

            {{-- Main Header Matching Legacy Form --}}
            <div class="d-flex flex-wrap justify-content-between align-items-start mb-3 bg-white p-3 rounded shadow-sm border">
                <div>
                    <h4 class="font-weight-bold text-dark m-0" style="letter-spacing: 0.5px;">
                        ACCOUNT SUMMARY - {{ $selectedHead->hed_code }}
                    </h4>
                    <div class="text-muted font-weight-bold mt-1" style="font-size: 0.9rem;">
                        DATED {{ now()->format('d M y') }}
                        <span class="ml-2 font-weight-normal text-secondary">
                            {{ ($selectedHead->hed_transtype ?? 1) == 1 ? '(Rupees without GST)' : '(Rupees with GST)' }}
                        </span>
                    </div>
                    <div class="small text-muted mt-1">
                        <strong>Project:</strong> {{ $selectedHead->prj_title }}
                    </div>
                </div>

                {{-- Top-Left Box: Allocation & MTSS Share (Matching Image 1) --}}
                <div class="shares-infobox mt-2 mt-md-0 shadow-sm">
                    <table class="table-sm m-0">
                        <tr>
                            <td class="text-muted font-weight-bold">Allocation</td>
                            <td class="text-right font-weight-bold text-dark font-monospace">{{ number_format($finStatus->allocation ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted font-weight-bold">MTSS Share</td>
                            <td class="text-right font-weight-bold text-danger font-monospace">{{ number_format($finStatus->mtss_share ?? 0, 2) }}</td>
                        </tr>
                        <tr style="border-top: 1px dashed #ccc;">
                            <td class="text-muted font-weight-bold">RDW Share</td>
                            <td class="text-right font-weight-bold text-primary font-monospace">{{ number_format($finStatus->rdw_share ?? 0, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Row with 3 Summary Panels (Account Column, PCC Column, CSRF Column) --}}
            <div class="row mb-4">

                {{-- Panel 1: Account Summary (Exact Match of Image 1 Center Card) --}}
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="acc-summary-panel shadow-sm h-100">
                        <div class="acc-summary-header">
                            Account
                        </div>
                        <div class="acc-summary-amount font-monospace">
                            {{ number_format($finStatus->rdw_share ?? 0, 2) }}
                        </div>
                        <table class="acc-summary-table">
                            <tr>
                                <td class="label-col">Received</td>
                                <td class="val-col">{{ number_format($finStatus->acc_received ?? 0, 2) }}</td>
                                <td class="btn-col">
                                    <a href="{{ route('division.finance-of-project.drilldown', [$selectedHeadId, 'acc', 'received']) }}"
                                       class="btn-drill" title="Received Breakdown"><i class="fas fa-search"></i></a>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col">Expenditure</td>
                                <td class="val-col">{{ number_format(abs($finStatus->acc_expenditure ?? 0), 2) }}</td>
                                <td class="btn-col">
                                    <a href="{{ route('division.finance-of-project.drilldown', [$selectedHeadId, 'acc', 'expenditure']) }}"
                                       class="btn-drill" title="Expenditure Breakdown"><i class="fas fa-search"></i></a>
                                </td>
                            </tr>
                            <tr class="highlight-balance">
                                <td class="label-col font-weight-bold">Balance</td>
                                <td class="val-col font-weight-bold {{ ($finStatus->balance ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($finStatus->balance ?? 0, 2) }}
                                </td>
                                <td class="btn-col"></td>
                            </tr>
                            <tr>
                                <td class="label-col">Commitments</td>
                                <td class="val-col">{{ number_format(abs($finStatus->acc_commitments ?? 0), 2) }}</td>
                                <td class="btn-col">
                                    <a href="{{ route('division.finance-of-project.drilldown', [$selectedHeadId, 'acc', 'commitments']) }}"
                                       class="btn-drill" title="Commitments Breakdown"><i class="fas fa-search"></i></a>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col">In Process</td>
                                <td class="val-col">{{ number_format($finStatus->acc_in_process ?? 0, 2) }}</td>
                                <td class="btn-col">
                                    <a href="{{ route('division.finance-of-project.drilldown', [$selectedHeadId, 'acc', 'in-process']) }}"
                                       class="btn-drill" title="In Process Breakdown"><i class="fas fa-search"></i></a>
                                </td>
                            </tr>
                            <tr class="highlight-avail">
                                <td class="label-col font-weight-bold">Available</td>
                                <td class="val-col font-weight-bold {{ ($finStatus->available ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($finStatus->available ?? 0, 2) }}
                                </td>
                                <td class="btn-col"></td>
                            </tr>
                            <tr>
                                <td class="label-col">Yet to be Received</td>
                                <td class="val-col">{{ number_format($finStatus->yet_to_be_received ?? 0, 2) }}</td>
                                <td class="btn-col"></td>
                            </tr>
                            <tr>
                                <td class="label-col font-weight-bold">Remaining</td>
                                <td class="val-col font-weight-bold text-dark">{{ number_format($finStatus->can_be_spent ?? 0, 2) }}</td>
                                <td class="btn-col"></td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- Panel 2: Project Scope (PCC) --}}
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="scope-panel-pcc shadow-sm h-100 p-0">
                        <div class="bg-primary text-white text-center py-2 font-weight-bold">
                            <i class="fas fa-building mr-1"></i> Project Scope (PCC)
                        </div>
                        <div class="text-center font-weight-bold text-primary py-1 font-monospace" style="font-size: 0.95rem;">
                            Share: Rs. {{ number_format($finStatus->pcc_share ?? 0, 2) }}
                        </div>
                        <table class="acc-summary-table bg-white">
                            <tr>
                                <td class="label-col">PCC Received</td>
                                <td class="val-col">{{ number_format($finStatus->pcc_received ?? 0, 2) }}</td>
                                <td class="btn-col">
                                    <a href="{{ route('division.finance-of-project.drilldown', [$selectedHeadId, 'pcc', 'received']) }}"
                                       class="btn-drill btn-drill-blue" title="PCC Received"><i class="fas fa-search"></i></a>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col">PCC Expenditure</td>
                                <td class="val-col">{{ number_format(abs($finStatus->pcc_expenditure ?? 0), 2) }}</td>
                                <td class="btn-col">
                                    <a href="{{ route('division.finance-of-project.drilldown', [$selectedHeadId, 'pcc', 'expenditure']) }}"
                                       class="btn-drill btn-drill-blue" title="PCC Expenditure"><i class="fas fa-search"></i></a>
                                </td>
                            </tr>
                            <tr style="background: rgba(13, 110, 253, 0.08);">
                                <td class="label-col font-weight-bold">PCC Balance</td>
                                <td class="val-col font-weight-bold {{ ($finStatus->pcc_balance ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($finStatus->pcc_balance ?? 0, 2) }}
                                </td>
                                <td class="btn-col"></td>
                            </tr>
                            <tr>
                                <td class="label-col">PCC Commitments</td>
                                <td class="val-col">{{ number_format(abs($finStatus->pcc_commitments ?? 0), 2) }}</td>
                                <td class="btn-col">
                                    <a href="{{ route('division.finance-of-project.drilldown', [$selectedHeadId, 'pcc', 'commitments']) }}"
                                       class="btn-drill btn-drill-blue" title="PCC Commitments"><i class="fas fa-search"></i></a>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col">PCC In Process</td>
                                <td class="val-col">{{ number_format($finStatus->pcc_in_process ?? 0, 2) }}</td>
                                <td class="btn-col">
                                    <a href="{{ route('division.finance-of-project.drilldown', [$selectedHeadId, 'pcc', 'in-process']) }}"
                                       class="btn-drill btn-drill-blue" title="PCC In Process"><i class="fas fa-search"></i></a>
                                </td>
                            </tr>
                            <tr style="background: rgba(13, 110, 253, 0.15);">
                                <td class="label-col font-weight-bold">PCC Available</td>
                                <td class="val-col font-weight-bold {{ ($finStatus->pcc_available ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($finStatus->pcc_available ?? 0, 2) }}
                                </td>
                                <td class="btn-col"></td>
                            </tr>
                            <tr>
                                <td class="label-col">PCC Yet to be Rec.</td>
                                <td class="val-col">{{ number_format($finStatus->pcc_yet_to_be_received ?? 0, 2) }}</td>
                                <td class="btn-col"></td>
                            </tr>
                            <tr style="background: rgba(13, 110, 253, 0.05);">
                                <td class="label-col font-weight-bold">PCC Can Be Spent</td>
                                <td class="val-col font-weight-bold text-primary">{{ number_format($finStatus->pcc_can_be_spent ?? 0, 2) }}</td>
                                <td class="btn-col"></td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- Panel 3: CSRF Scope (CF) --}}
                <div class="col-lg-4 col-md-12 mb-3">
                    <div class="scope-panel-csrf shadow-sm h-100 p-0">
                        <div class="bg-success text-white text-center py-2 font-weight-bold">
                            <i class="fas fa-university mr-1"></i> CSRF Scope (CF)
                        </div>
                        <div class="text-center font-weight-bold text-success py-1 font-monospace" style="font-size: 0.95rem;">
                            Share: Rs. {{ number_format($finStatus->csrf_share ?? 0, 2) }}
                        </div>
                        <table class="acc-summary-table bg-white">
                            <tr>
                                <td class="label-col">CSRF Received</td>
                                <td class="val-col">{{ number_format($finStatus->cf_received ?? 0, 2) }}</td>
                                <td class="btn-col">
                                    <a href="{{ route('division.finance-of-project.drilldown', [$selectedHeadId, 'csrf', 'received']) }}"
                                       class="btn-drill btn-drill-green" title="CSRF Received"><i class="fas fa-search"></i></a>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col">CSRF Expenditure</td>
                                <td class="val-col">{{ number_format(abs($finStatus->cf_expenditure ?? 0), 2) }}</td>
                                <td class="btn-col">
                                    <a href="{{ route('division.finance-of-project.drilldown', [$selectedHeadId, 'csrf', 'expenditure']) }}"
                                       class="btn-drill btn-drill-green" title="CSRF Expenditure"><i class="fas fa-search"></i></a>
                                </td>
                            </tr>
                            <tr style="background: rgba(25, 135, 84, 0.08);">
                                <td class="label-col font-weight-bold">CSRF Balance</td>
                                <td class="val-col font-weight-bold {{ ($finStatus->cf_balance ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($finStatus->cf_balance ?? 0, 2) }}
                                </td>
                                <td class="btn-col"></td>
                            </tr>
                            <tr>
                                <td class="label-col">CSRF Commitments</td>
                                <td class="val-col">{{ number_format(abs($finStatus->cf_commitments ?? 0), 2) }}</td>
                                <td class="btn-col">
                                    <a href="{{ route('division.finance-of-project.drilldown', [$selectedHeadId, 'csrf', 'commitments']) }}"
                                       class="btn-drill btn-drill-green" title="CSRF Commitments"><i class="fas fa-search"></i></a>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col">CSRF In Process</td>
                                <td class="val-col">{{ number_format($finStatus->cf_in_process ?? 0, 2) }}</td>
                                <td class="btn-col">
                                    <a href="{{ route('division.finance-of-project.drilldown', [$selectedHeadId, 'csrf', 'in-process']) }}"
                                       class="btn-drill btn-drill-green" title="CSRF In Process"><i class="fas fa-search"></i></a>
                                </td>
                            </tr>
                            <tr style="background: rgba(25, 135, 84, 0.15);">
                                <td class="label-col font-weight-bold">CSRF Available</td>
                                <td class="val-col font-weight-bold {{ ($finStatus->cf_available ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($finStatus->cf_available ?? 0, 2) }}
                                </td>
                                <td class="btn-col"></td>
                            </tr>
                            <tr>
                                <td class="label-col">CSRF Yet to be Rec.</td>
                                <td class="val-col">{{ number_format($finStatus->cf_yet_to_be_received ?? 0, 2) }}</td>
                                <td class="btn-col"></td>
                            </tr>
                            <tr style="background: rgba(25, 135, 84, 0.05);">
                                <td class="label-col font-weight-bold">CSRF Can Be Spent</td>
                                <td class="val-col font-weight-bold text-success">{{ number_format($finStatus->cf_can_be_spent ?? 0, 2) }}</td>
                                <td class="btn-col"></td>
                            </tr>
                        </table>
                    </div>
                </div>

            </div>

            {{-- Row: Receivables & Loans Summary Cards --}}
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="fop-card h-100">
                        <div class="fop-card-header bg-light">
                            <span><i class="fas fa-file-invoice-dollar text-primary mr-1"></i> Receivables Breakdown</span>
                            <span class="badge badge-info">Milestone Linked</span>
                        </div>
                        <div class="p-3">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted">Milestones Completed Receivable:</td>
                                    <td class="text-right font-weight-bold font-monospace">Rs. {{ number_format($finStatus->receivable_completed ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Milestones In-Progress Receivable:</td>
                                    <td class="text-right font-weight-bold font-monospace">Rs. {{ number_format($finStatus->receivable_current ?? 0, 2) }}</td>
                                </tr>
                                <tr style="border-top: 1px solid #dee2e6;">
                                    <td class="font-weight-bold text-success">Available after Receivables:</td>
                                    <td class="text-right font-weight-bold text-success font-monospace">Rs. {{ number_format($finStatus->available_after_receivables ?? 0, 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="fop-card h-100">
                        <div class="fop-card-header bg-light">
                            <span><i class="fas fa-exchange-alt text-warning mr-1"></i> Loans & Inter-Project Netting</span>
                            <span class="badge badge-secondary">Accounting</span>
                        </div>
                        <div class="p-3">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted">PCC Own Expenditure:</td>
                                    <td class="text-right font-weight-bold font-monospace">Rs. {{ number_format(abs($loans->pcc_own_exp ?? 0), 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Others Loans Taken:</td>
                                    <td class="text-right font-weight-bold font-monospace text-danger">Rs. {{ number_format(abs($loans->others_loans_taken ?? 0), 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">PCC Loans Given to Others:</td>
                                    <td class="text-right font-weight-bold font-monospace text-primary">Rs. {{ number_format(abs($loans->pcc_loansgiven ?? 0), 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row: Interactive Charts (Budget Doughnut & Subhead Bar Chart) --}}
            <div class="row mb-4">
                <div class="col-lg-5 mb-3">
                    <div class="fop-card h-100">
                        <div class="fop-card-header">
                            <span><i class="fas fa-chart-pie text-info mr-1"></i> Budget Allocation & Utilization</span>
                        </div>
                        <div class="p-3">
                            <div style="height: 240px; position: relative;">
                                <canvas id="budgetDoughnutChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7 mb-3">
                    <div class="fop-card h-100">
                        <div class="fop-card-header">
                            <span><i class="fas fa-chart-bar text-success mr-1"></i> Subhead Distribution (Allocation vs Spent)</span>
                        </div>
                        <div class="p-3">
                            <div style="height: 240px; position: relative;">
                                <canvas id="subheadBarChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Subheads Breakdown Table (Matching fin_stateshd_temp in VBA) --}}
            <div class="fop-card mb-4">
                <div class="fop-card-header bg-light">
                    <span class="font-weight-bold"><i class="fas fa-layer-group text-primary mr-1"></i> Project Subhead Breakdown</span>
                    <span class="badge badge-primary">{{ count($subheadBreakdown) }} Subheads</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover fop-table mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="15%">Subhead</th>
                                <th class="text-right" width="14%">Allocation (Rs)</th>
                                <th class="text-right" width="14%">Expenditure (Rs)</th>
                                <th class="text-right" width="14%">Commitments (Rs)</th>
                                <th class="text-right" width="14%">In Process (Rs)</th>
                                <th class="text-right" width="15%">Can Be Spent (Rs)</th>
                                <th width="14%">Utilization</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subheadBreakdown as $sh)
                                @php
                                    $shAlloc = $sh['allocation'] ?? 0;
                                    $shExp = $sh['expenditure'] ?? 0;
                                    $shPct = $shAlloc > 0 ? min(100, round(($shExp / $shAlloc) * 100, 1)) : 0;
                                    $shRemaining = $sh['can_be_spent'] ?? 0;
                                @endphp
                                <tr>
                                    <td class="font-weight-bold text-primary">
                                        <i class="fas fa-caret-right mr-1"></i> {{ $sh['name'] }}
                                        @if($sh['name'] === 'HR' && !empty($sh['forecast']))
                                            <span class="badge badge-info ml-1" title="Forecasted salary">Forecast: {{ number_format($sh['forecast']) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right font-monospace font-weight-bold">{{ number_format($shAlloc, 2) }}</td>
                                    <td class="text-right">
                                        <div class="fig-cell">
                                            <span class="fig-val">{{ number_format($shExp, 2) }}</span>
                                            <a href="{{ route('division.finance-of-project.drilldown', [$selectedHeadId, 'subhead', 'expenditure', $sh['name']]) }}"
                                               class="btn-drill btn-drill-blue" title="Subhead Expenditure"><i class="fas fa-search"></i></a>
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        <div class="fig-cell">
                                            <span class="fig-val">{{ number_format($sh['commitments'] ?? 0, 2) }}</span>
                                            <a href="{{ route('division.finance-of-project.drilldown', [$selectedHeadId, 'subhead', 'commitments', $sh['name']]) }}"
                                               class="btn-drill btn-drill-blue" title="Subhead Commitments"><i class="fas fa-search"></i></a>
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        <div class="fig-cell">
                                            <span class="fig-val">{{ number_format($sh['in_process'] ?? 0, 2) }}</span>
                                            <a href="{{ route('division.finance-of-project.drilldown', [$selectedHeadId, 'subhead', 'in-process', $sh['name']]) }}"
                                               class="btn-drill btn-drill-blue" title="Subhead In Process"><i class="fas fa-search"></i></a>
                                        </div>
                                    </td>
                                    <td class="text-right font-monospace font-weight-bold {{ $shRemaining >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($shRemaining, 2) }}
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 16px; border-radius: 8px;">
                                            <div class="progress-bar {{ $shPct > 90 ? 'bg-danger' : ($shPct > 60 ? 'bg-warning' : 'bg-success') }}"
                                                 role="progressbar" style="width: {{ $shPct }}%; font-size: 0.65rem; font-weight: 700;">
                                                {{ $shPct }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">No subhead breakdown found for this head.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        {{-- ======================================================== --}}
        {{-- TAB 2: MTSS STATUS --}}
        {{-- ======================================================== --}}
        @elseif($activeTab === 'mtss' && $selectedHead && $finStatus)

            <div class="fop-card mb-4">
                <div class="fop-card-header bg-light">
                    <span class="font-weight-bold"><i class="fas fa-university text-info mr-1"></i> MTSS STATUS - {{ $selectedHead->hed_code }}</span>
                    <span class="badge badge-secondary">{{ now()->format('d M Y') }}</span>
                </div>
                <div class="p-4">
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded border text-center">
                                <div class="text-muted small font-weight-bold uppercase">Total Project Allocation</div>
                                <div class="h4 font-weight-bold text-dark mt-1 font-monospace">Rs. {{ number_format($finStatus->allocation ?? 0, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded border text-center border-left-danger">
                                <div class="text-muted small font-weight-bold uppercase">MTSS Share (Fund Out)</div>
                                <div class="h4 font-weight-bold text-danger mt-1 font-monospace">Rs. {{ number_format($finStatus->mtss_share ?? 0, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded border text-center border-left-primary">
                                <div class="text-muted small font-weight-bold uppercase">RDW Account Share</div>
                                <div class="h4 font-weight-bold text-primary mt-1 font-monospace">Rs. {{ number_format($finStatus->acc_share ?? 0, 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <h5 class="font-weight-bold mb-3"><i class="fas fa-history text-secondary mr-1"></i> MTSS Transfers History</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Transfer Type</th>
                                    <th>Title</th>
                                    <th>From Head</th>
                                    <th>To Head</th>
                                    <th class="text-right">Amount (Rs)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transfers as $idx => $trf)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td><span class="badge badge-info">{{ $trf->trf_type }}</span></td>
                                        <td class="font-weight-bold">{{ $trf->trf_title }}</td>
                                        <td>{{ $trf->trf_fromhed ?: '-' }}</td>
                                        <td>{{ $trf->trf_tohed ?: '-' }}</td>
                                        <td class="text-right font-monospace font-weight-bold">{{ number_format($trf->trf_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">No MTSS transfers recorded.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        {{-- ======================================================== --}}
        {{-- TAB 3: FUNDINGS & MILESTONES (Matching Legacy Image 2) --}}
        {{-- ======================================================== --}}
        @elseif($activeTab === 'fundings' && $selectedHead && $finStatus)

            <div class="fop-card mb-4">
                <div class="fop-card-header bg-light">
                    <span class="font-weight-bold"><i class="fas fa-money-check-alt text-success mr-1"></i> Fundings - {{ $selectedHead->hed_code }}</span>
                    <button class="btn btn-sm btn-outline-primary" onclick="alert('Milestone costs breakdown');">
                        <i class="fas fa-file-invoice mr-1"></i> Milestone Costs
                    </button>
                </div>
                <div class="p-4">

                    {{-- Fundings Matrix Table matching Image 2 --}}
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered fundings-matrix-table shadow-sm">
                            <thead>
                                <tr>
                                    <th width="15%" class="text-left">Category</th>
                                    <th width="17%">Total</th>
                                    <th width="17%">MTSS Share</th>
                                    <th width="17%">RDW Share</th>
                                    <th width="17%">CSRF Share</th>
                                    <th width="17%">Project Share</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-left font-weight-bold bg-light">Allocation</td>
                                    <td class="font-weight-bold" style="background: #f1f5f9; color: #0f172a;">{{ number_format($finStatus->allocation ?? 0) }}</td>
                                    <td class="text-danger">{{ number_format($finStatus->mtss_share ?? 0) }}</td>
                                    <td class="text-primary font-weight-bold">{{ number_format($finStatus->rdw_share ?? 0) }}</td>
                                    <td class="text-success">{{ number_format($finStatus->csrf_share ?? 0) }}</td>
                                    <td class="text-info">{{ number_format($finStatus->prj_share ?? 0) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-left font-weight-bold bg-light">Received</td>
                                    <td class="font-weight-bold">{{ number_format($finStatus->received ?? 0) }}</td>
                                    <td>0</td>
                                    <td class="text-primary">{{ number_format($finStatus->acc_received ?? 0) }}</td>
                                    <td class="text-success">{{ number_format($finStatus->cf_received ?? 0) }}</td>
                                    <td class="text-info">{{ number_format($finStatus->pcc_received ?? 0) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Milestones Table matching Image 2 right section --}}
                    <h5 class="font-weight-bold mb-3"><i class="fas fa-tasks text-primary mr-1"></i> Project Milestones & Associated Costs</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Milestone Title</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th class="text-right">Total Cost (Rs)</th>
                                    <th class="text-right">Head Cost (Rs)</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($milestones as $msn)
                                    <tr>
                                        <td>{{ $msn->msn_idd ?: $msn->msn_id }}</td>
                                        <td class="font-weight-bold text-primary">{{ $msn->msn_desc ?: ($msn->msn_type ?: 'Milestone') }}</td>
                                        <td>{{ $msn->msn_startdt ? \Carbon\Carbon::parse($msn->msn_startdt)->format('d M Y') : '-' }}</td>
                                        <td>{{ $msn->msn_targetdt ? \Carbon\Carbon::parse($msn->msn_targetdt)->format('d M Y') : ($msn->msn_achvdt ? \Carbon\Carbon::parse($msn->msn_achvdt)->format('d M Y') : '-') }}</td>
                                        <td class="text-right font-monospace">{{ number_format($msn->msn_cost ?? 0, 2) }}</td>
                                        <td class="text-right font-monospace font-weight-bold text-dark">{{ number_format($msn->mct_cost ?? 0, 2) }}</td>
                                        <td class="text-center">
                                            @if($msn->msn_status === 'Completed')
                                                <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Completed</span>
                                            @elseif($msn->msn_status === 'In progress' || $msn->msn_status === 'In Progress')
                                                <span class="badge badge-warning px-2 py-1"><i class="fas fa-clock mr-1"></i> In Progress</span>
                                            @else
                                                <span class="badge badge-secondary px-2 py-1">{{ $msn->msn_status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-3">No milestones defined for this project.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        {{-- ======================================================== --}}
        {{-- TAB 4: LOANS & NETTING --}}
        {{-- ======================================================== --}}
        @elseif($activeTab === 'loans' && $selectedHead && $finStatus)

            <div class="fop-card mb-4">
                <div class="fop-card-header bg-light">
                    <span class="font-weight-bold"><i class="fas fa-hand-holding-usd text-warning mr-1"></i> Loans & Inter-Project Netting - {{ $selectedHead->hed_code }}</span>
                </div>
                <div class="p-4">
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded border text-center">
                                <div class="text-muted small font-weight-bold uppercase">PCC Own Expenditure</div>
                                <div class="h4 font-weight-bold text-dark mt-1 font-monospace">Rs. {{ number_format(abs($loans->pcc_own_exp ?? 0), 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded border text-center border-left-danger">
                                <div class="text-muted small font-weight-bold uppercase">Others Loans Taken (Netting)</div>
                                <div class="h4 font-weight-bold text-danger mt-1 font-monospace">Rs. {{ number_format(abs($loans->others_loans_taken ?? 0), 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded border text-center border-left-primary">
                                <div class="text-muted small font-weight-bold uppercase">PCC Loans Given to Others</div>
                                <div class="h4 font-weight-bold text-primary mt-1 font-monospace">Rs. {{ number_format(abs($loans->pcc_loansgiven ?? 0), 2) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i> Inter-project netting ensures that expenditures incurred by one project account on behalf of another are accurately reconciled without distorting budget allocations.
                    </div>
                </div>
            </div>

        {{-- ======================================================== --}}
        {{-- TAB 5: ALL PROJECTS OVERVIEW (Multi-Project Table) --}}
        {{-- ======================================================== --}}
        @else

            <div class="fop-card mb-4">
                <div class="fop-card-header bg-white">
                    <span class="font-weight-bold"><i class="fas fa-layer-group text-primary mr-1"></i> Division Projects Portfolio (PCC & CSRF Breakdown)</span>
                    <span class="badge badge-info">{{ count($projects) }} Projects</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover fop-table mb-0">
                        <thead>
                            <tr>
                                <th rowspan="2" style="min-width: 190px; vertical-align: middle;">Project</th>
                                <th colspan="5" class="text-center bg-primary text-white" style="font-size: 0.8rem;">
                                    <i class="fas fa-building mr-1"></i> Project Scope (Pcc)
                                </th>
                                <th colspan="5" class="text-center bg-success text-white" style="font-size: 0.8rem;">
                                    <i class="fas fa-university mr-1"></i> CSRF Scope (CF)
                                </th>
                                <th rowspan="2" style="min-width: 100px; vertical-align: middle;" class="text-center">Action</th>
                            </tr>
                            <tr>
                                {{-- Pcc columns --}}
                                <th style="background: rgba(13, 110, 253, 0.08);">Received</th>
                                <th style="background: rgba(13, 110, 253, 0.08);">Expenditure</th>
                                <th style="background: rgba(13, 110, 253, 0.08);">Commitments</th>
                                <th style="background: rgba(13, 110, 253, 0.08);">In Process</th>
                                <th style="background: rgba(13, 110, 253, 0.15);">Available</th>
                                {{-- CSRF columns --}}
                                <th style="background: rgba(25, 135, 84, 0.08);">Received</th>
                                <th style="background: rgba(25, 135, 84, 0.08);">Expenditure</th>
                                <th style="background: rgba(25, 135, 84, 0.08);">Commitments</th>
                                <th style="background: rgba(25, 135, 84, 0.08);">In Process</th>
                                <th style="background: rgba(25, 135, 84, 0.15);">Available</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projects as $prj)
                            <tr>
                                {{-- Project Name --}}
                                <td>
                                    <div>
                                        <a href="{{ route('division.finance-of-project.index', ['head_id' => $prj['head_id'], 'tab' => 'status']) }}"
                                           class="font-weight-bold text-primary text-decoration-none">
                                            {{ $prj['head_code'] }}
                                        </a>
                                        @if(!empty($isGlobalViewer) && !empty($prj['division']))
                                            <span class="badge badge-light border text-secondary ml-1 font-weight-bold" style="font-size: 10px;">{{ $prj['division'] }}</span>
                                        @endif
                                        <br>
                                        <small class="text-muted" title="{{ $prj['title'] }}">{{ \Illuminate\Support\Str::limit($prj['title'], 30) }}</small>
                                    </div>
                                </td>

                                {{-- Pcc Received --}}
                                <td>
                                    <div class="fig-cell">
                                        <span class="fig-val">{{ number_format($prj['pcc_received']) }}</span>
                                        <a href="{{ route('division.finance-of-project.drilldown', ['head_id' => $prj['head_id'], 'scope' => 'pcc', 'figure' => 'received']) }}"
                                           class="btn-drill btn-drill-blue" title="Pcc Received Breakdown"><i class="fas fa-search"></i></a>
                                    </div>
                                </td>

                                {{-- Pcc Expenditure --}}
                                <td>
                                    <div class="fig-cell">
                                        <span class="fig-val">{{ number_format(abs($prj['pcc_expenditure'])) }}</span>
                                        <a href="{{ route('division.finance-of-project.drilldown', ['head_id' => $prj['head_id'], 'scope' => 'pcc', 'figure' => 'expenditure']) }}"
                                           class="btn-drill btn-drill-blue" title="Pcc Expenditure Breakdown"><i class="fas fa-search"></i></a>
                                    </div>
                                </td>

                                {{-- Pcc Commitments --}}
                                <td>
                                    <div class="fig-cell">
                                        <span class="fig-val">{{ number_format(abs($prj['pcc_commitments'])) }}</span>
                                        <a href="{{ route('division.finance-of-project.drilldown', ['head_id' => $prj['head_id'], 'scope' => 'pcc', 'figure' => 'commitments']) }}"
                                           class="btn-drill btn-drill-blue" title="Pcc Commitments"><i class="fas fa-search"></i></a>
                                    </div>
                                </td>

                                {{-- Pcc In Process --}}
                                <td>
                                    <div class="fig-cell">
                                        <span class="fig-val">{{ number_format($prj['pcc_in_process']) }}</span>
                                        <a href="{{ route('division.finance-of-project.drilldown', ['head_id' => $prj['head_id'], 'scope' => 'pcc', 'figure' => 'in-process']) }}"
                                           class="btn-drill btn-drill-blue" title="Pcc In Process"><i class="fas fa-search"></i></a>
                                    </div>
                                </td>

                                {{-- Pcc Available --}}
                                <td class="font-monospace font-weight-bold {{ $prj['pcc_can_be_spent'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($prj['pcc_can_be_spent']) }}
                                </td>

                                {{-- CSRF Received --}}
                                <td>
                                    <div class="fig-cell">
                                        <span class="fig-val">{{ number_format($prj['cf_received']) }}</span>
                                        <a href="{{ route('division.finance-of-project.drilldown', ['head_id' => $prj['head_id'], 'scope' => 'csrf', 'figure' => 'received']) }}"
                                           class="btn-drill btn-drill-green" title="CSRF Received Breakdown"><i class="fas fa-search"></i></a>
                                    </div>
                                </td>

                                {{-- CSRF Expenditure --}}
                                <td>
                                    <div class="fig-cell">
                                        <span class="fig-val">{{ number_format(abs($prj['cf_expenditure'])) }}</span>
                                        <a href="{{ route('division.finance-of-project.drilldown', ['head_id' => $prj['head_id'], 'scope' => 'csrf', 'figure' => 'expenditure']) }}"
                                           class="btn-drill btn-drill-green" title="CSRF Expenditure Breakdown"><i class="fas fa-search"></i></a>
                                    </div>
                                </td>

                                {{-- CSRF Commitments --}}
                                <td>
                                    <div class="fig-cell">
                                        <span class="fig-val">{{ number_format(abs($prj['cf_commitments'])) }}</span>
                                        <a href="{{ route('division.finance-of-project.drilldown', ['head_id' => $prj['head_id'], 'scope' => 'csrf', 'figure' => 'commitments']) }}"
                                           class="btn-drill btn-drill-green" title="CSRF Commitments"><i class="fas fa-search"></i></a>
                                    </div>
                                </td>

                                {{-- CSRF In Process --}}
                                <td>
                                    <div class="fig-cell">
                                        <span class="fig-val">{{ number_format($prj['cf_in_process']) }}</span>
                                        <a href="{{ route('division.finance-of-project.drilldown', ['head_id' => $prj['head_id'], 'scope' => 'csrf', 'figure' => 'in-process']) }}"
                                           class="btn-drill btn-drill-green" title="CSRF In Process"><i class="fas fa-search"></i></a>
                                    </div>
                                </td>

                                {{-- CSRF Available --}}
                                <td class="font-monospace font-weight-bold {{ $prj['cf_can_be_spent'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($prj['cf_can_be_spent']) }}
                                </td>

                                {{-- Action --}}
                                <td class="text-center">
                                    <a href="{{ route('division.finance-of-project.index', ['head_id' => $prj['head_id'], 'tab' => 'status']) }}"
                                       class="btn btn-xs btn-primary shadow-sm px-2 py-1" style="border-radius: 12px; font-size: 0.72rem;">
                                        <i class="fas fa-eye mr-1"></i> Details
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted py-4">
                                    No projects found for this division.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        @endif

    </div>
</div>

{{-- Chart.js Initialization for Detailed Status --}}
@if($activeTab === 'status' && $selectedHead && $finStatus)
@push('scripts')
<script src="{{ asset('plugins/chart.js/Chart.min.js') }}"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Doughnut Chart: Budget Breakdown
    const ctxDoughnut = document.getElementById('budgetDoughnutChart');
    if (ctxDoughnut) {
        const spent = {{ abs($finStatus->expenditure ?? 0) }};
        const commit = {{ abs($finStatus->commitments ?? 0) }};
        const inProcess = {{ abs($finStatus->in_process ?? 0) }};
        const remaining = {{ max(0, $finStatus->can_be_spent ?? 0) }};

        new Chart(ctxDoughnut.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Spent (Actual)', 'Commitments', 'In Process', 'Remaining Available'],
                datasets: [{
                    data: [spent, commit, inProcess, remaining],
                    backgroundColor: ['#dc3545', '#ffc107', '#17a2b8', '#28a745'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'right', labels: { boxWidth: 12, fontSize: 11 } },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            const val = data.datasets[0].data[tooltipItem.index];
                            return data.labels[tooltipItem.index] + ': Rs. ' + Number(val).toLocaleString();
                        }
                    }
                }
            }
        });
    }

    // 2. Bar Chart: Subheads Distribution
    const ctxBar = document.getElementById('subheadBarChart');
    if (ctxBar) {
        @php
            $shLabels = [];
            $shAllocData = [];
            $shExpData = [];
            foreach ($subheadBreakdown as $sh) {
                $shLabels[] = $sh['name'];
                $shAllocData[] = (float)($sh['allocation'] ?? 0);
                $shExpData[] = (float)($sh['expenditure'] ?? 0);
            }
        @endphp

        const labels = {!! json_encode($shLabels) !!};
        const allocData = {!! json_encode($shAllocData) !!};
        const expData = {!! json_encode($shExpData) !!};

        new Chart(ctxBar.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Allocation',
                        backgroundColor: '#0d6efd',
                        data: allocData
                    },
                    {
                        label: 'Expenditure',
                        backgroundColor: '#dc3545',
                        data: expData
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    xAxes: [{ gridLines: { display: false } }],
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function(value) {
                                if (value >= 1000000) return (value/1000000).toFixed(1) + 'M';
                                if (value >= 1000) return (value/1000).toFixed(0) + 'k';
                                return value;
                            }
                        }
                    }]
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            return data.datasets[tooltipItem.datasetIndex].label + ': Rs. ' + Number(tooltipItem.yLabel).toLocaleString();
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endif

@endsection

@push('scripts')
<script>
// Division Filter → Project Dropdown Cascading
(function() {
    var divFilter = document.getElementById('divisionFilter');
    var projectFilter = document.getElementById('projectFilter');
    if (!divFilter || !projectFilter) return;

    // Store all original project options
    var optgroup = document.getElementById('projectOptgroup');
    if (!optgroup) return;
    var allOptions = Array.from(optgroup.querySelectorAll('option')).map(function(o) {
        return { el: o.cloneNode(true), untId: o.getAttribute('data-unt-id') };
    });

    divFilter.addEventListener('change', function() {
        var selectedDiv = this.value;
        // Clear optgroup
        while (optgroup.firstChild) optgroup.removeChild(optgroup.firstChild);

        allOptions.forEach(function(item) {
            if (selectedDiv === 'all' || item.untId === selectedDiv) {
                optgroup.appendChild(item.el.cloneNode(true));
            }
        });

        // Update optgroup label
        if (selectedDiv === 'all') {
            optgroup.label = 'All NRDI Projects (Central Master)';
        } else {
            var divText = divFilter.options[divFilter.selectedIndex].text;
            optgroup.label = 'Projects — ' + divText.trim();
        }
    });
})();
</script>
@endpush
