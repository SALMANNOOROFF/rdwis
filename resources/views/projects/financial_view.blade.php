@extends('welcome')
@section('content')
<div class="content-wrapper pt-3 pb-5" style="background: var(--rd-bg, #f4f6f9); min-height: 100vh; color: #1e293b;">
    <style>
        :root {
            --fin-primary: #0284c7;
            --fin-success: #16a34a;
            --fin-warning: #d97706;
            --fin-danger: #dc2626;
            --fin-purple: #9333ea;
            --fin-card-bg: #ffffff;
            --fin-border: #e2e8f0;
            --fin-text-main: #0f172a;
            --fin-text-muted: #64748b;
        }

        /* Typography */
        .rajdhani { font-family: 'Rajdhani', sans-serif; }
        .font-mono { font-family: 'Consolas', 'Courier New', monospace; }

        /* Modern Crisp Cards */
        .fin-stat-card {
            background: #ffffff;
            border: 1px solid var(--fin-border);
            border-radius: 12px;
            padding: 16px 20px;
            transition: all 0.25s ease;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
        }
        .fin-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            border-color: #94a3b8;
        }

        .fin-label {
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            margin-bottom: 4px;
        }

        .fin-val-lg {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            line-height: 1.1;
            color: #0f172a;
        }

        .fin-table-card {
            background: #ffffff;
            border: 1px solid var(--fin-border);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
        }

        /* High-Contrast Table Styling */
        .fin-table {
            font-family: 'Rajdhani', sans-serif;
            margin-bottom: 0;
            font-size: 0.96rem;
            font-weight: 700;
        }
        .fin-table thead th {
            background: #f8fafc;
            color: #334155;
            font-size: 0.84rem;
            font-weight: 800;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            padding: 13px 14px;
            border-bottom: 2px solid #cbd5e1;
        }
        .fin-table tbody td {
            padding: 11px 14px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
            font-size: 0.98rem;
        }
        .fin-table tbody tr:hover {
            background: #f8fafc;
        }

        .subhead-gauge-box {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px;
            text-align: center;
            transition: all 0.2s ease;
        }
        .subhead-gauge-box:hover {
            background: #ffffff;
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .chart-box-main {
            height: 280px;
            position: relative;
        }

        /* Interactive Drill-Down Search Buttons */
        .btn-drill-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 6px;
            font-size: 0.72rem;
            margin-left: 6px;
            transition: all 0.2s ease;
            text-decoration: none !important;
            border: 1px solid currentColor;
            opacity: 0.9;
        }
        .btn-drill-link:hover {
            opacity: 1;
            transform: scale(1.15);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .btn-drill-cyan { color: #0284c7; background: #e0f2fe; border-color: #7dd3fc; }
        .btn-drill-amber { color: #b45309; background: #fef3c7; border-color: #fcd34d; }
        .btn-drill-red { color: #dc2626; background: #fee2e2; border-color: #fca5a5; }
        .btn-drill-green { color: #16a34a; background: #dcfce7; border-color: #86efac; }
        .btn-drill-gray { color: #475569; background: #f1f5f9; border-color: #cbd5e1; }

        /* Custom Navigation Pills */
        .nav-fin-tabs .nav-link {
            border-radius: 20px;
            padding: 8px 18px;
            font-weight: 700;
            font-size: 0.88rem;
            color: #475569;
            border: 1.5px solid #cbd5e1;
            background: #ffffff;
            transition: all 0.2s;
            margin-right: 8px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
        }
        .nav-fin-tabs .nav-link:hover {
            background: #f8fafc;
            color: #0284c7;
            border-color: #0284c7;
        }
        .nav-fin-tabs .nav-link.active {
            background: linear-gradient(135deg, #0284c7, #0369a1) !important;
            color: #ffffff !important;
            border-color: #0284c7 !important;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);
        }

        /* Fundings Matrix Table matching Legacy Image 2 */
        .fundings-matrix-table th {
            background: #f1f5f9;
            color: #0f172a;
            font-size: 0.85rem;
            font-weight: 800;
            text-align: center;
            padding: 10px;
        }
        .fundings-matrix-table td {
            text-align: center;
            font-size: 0.95rem;
            font-weight: 700;
            padding: 10px;
            font-family: 'Consolas', 'Courier New', monospace;
        }

        @media screen {
            .print-document-container {
                display: none !important;
            }
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm 10mm;
            }

            /* Hide everything that belongs to the normal screen layout */
            body * {
                visibility: hidden !important;
            }

            .main-header,
            .main-sidebar,
            .main-footer,
            .control-sidebar,
            nav,
            aside,
            footer,
            .no-print,
            .modal,
            .modal-backdrop,
            .screen-view-container,
            .screen-view-container * {
                display: none !important;
                visibility: hidden !important;
            }

            html, body, .wrapper, .content-wrapper {
                background: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                min-height: auto !important;
            }

            /* Make the printable document visible */
            .print-document-container,
            .print-document-container * {
                visibility: visible !important;
            }

            .print-document-container {
                display: block !important;
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                color: #000000 !important;
            }
        }
    </style>

    <div class="container-fluid px-3 px-md-4">
        
        {{-- ================= TOP HEADER & ACTIONS ================= --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom no-print" style="border-color: #e2e8f0 !important;">
            <div class="d-flex align-items-center mb-2 mb-md-0">
                <a href="{{ $backUrl ?? route('projects.show', $project->prj_id) }}" class="btn btn-outline-secondary btn-sm rounded-pill mr-3 font-weight-bold" style="padding: 6px 16px;">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Project Details
                </a>
                <div>
                    <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                        <span class="badge badge-primary px-3 py-1 font-weight-bold rajdhani" style="font-size: 0.9rem; letter-spacing: 0.5px;">
                            <i class="fas fa-barcode mr-1"></i> CODE: {{ $project->prj_code }}
                        </span>
                        @if($headRecord)
                        <span class="badge badge-info px-3 py-1 font-weight-bold rajdhani" style="font-size: 0.9rem; letter-spacing: 0.5px;">
                            <i class="fas fa-coins mr-1"></i> HEAD: {{ $headRecord->hed_code }}
                        </span>
                        @endif
                        <span class="badge badge-light border text-dark font-weight-bold" style="font-size: 0.8rem;">
                            <i class="fas fa-building mr-1 text-muted"></i> {{ $project->unit?->unt_name ?? 'N/A' }}
                        </span>
                        <span class="badge badge-success px-2.5 py-1 text-uppercase font-weight-bold" style="font-size: 0.78rem;">{{ $project->prj_status }}</span>
                        <span class="text-muted small font-weight-bold ml-1">
                            <i class="fas fa-calendar-alt mr-1"></i> {{ now()->format('d M Y') }}
                            <span class="text-secondary ml-1">
                                {{ (($headRecord->hed_transtype ?? 1) == 1) ? '(Rupees without GST)' : '(Rupees with GST)' }}
                            </span>
                        </span>
                    </div>
                    <h3 class="font-weight-bold text-dark mb-0 mt-2 rajdhani" style="letter-spacing: 0.5px;">
                        {{ $project->prj_title }} <span class="text-primary font-weight-bold" style="font-size: 1.25rem;">— Complete Financial View</span>
                    </h3>
                </div>
            </div>

            <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                <a href="{{ route('projects.show', $project->prj_id) }}" class="btn btn-outline-primary btn-sm font-weight-bold rounded-pill px-3 shadow-sm">
                    <i class="fas fa-folder-open mr-1"></i> Project Profile & Overview
                </a>
                <a href="{{ route('projecthistory', ['project_id' => $project->prj_id]) }}" class="btn btn-outline-info btn-sm font-weight-bold rounded-pill px-3">
                    <i class="fas fa-history mr-1"></i> Log History
                </a>
                <a href="{{ route('projects.print_financial_summary', $project->prj_id) }}" target="_blank" class="btn btn-primary btn-sm font-weight-bold rounded-pill px-3 shadow-sm" style="background: #0f172a; border-color: #0f172a;">
                    <i class="fas fa-print mr-1"></i> Print Report
                </a>
            </div>
        </div>

        @if($head)
        {{-- ================= HERO STATS ROW (4 TOP COLUMNS - ALLOCATION, MTSS, RDW, CSRF) ================= --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="fin-stat-card h-100" style="border-left: 5px solid #0284c7;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fin-label font-weight-bold" style="color: #0284c7;">Total Allocation</span>
                        <i class="fas fa-wallet fa-lg opacity-75" style="color: #0284c7;"></i>
                    </div>
                    <div class="fin-val-lg mt-1">Rs. {{ number_format($head->allocation) }}</div>
                    <small class="text-muted d-block mt-1 font-weight-bold">Gross Sanctioned Project Budget</small>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="fin-stat-card h-100" style="border-left: 5px solid #dc2626;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fin-label font-weight-bold" style="color: #dc2626;">MTSS Share</span>
                        <i class="fas fa-file-invoice-dollar fa-lg opacity-75" style="color: #dc2626;"></i>
                    </div>
                    <div class="fin-val-lg mt-1" style="color: #dc2626;">Rs. {{ number_format($head->mtss_share) }}</div>
                    <small class="text-muted d-block mt-1 font-weight-bold">Mandatory Institutional Deductions</small>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="fin-stat-card h-100" style="border-left: 5px solid #16a34a;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fin-label font-weight-bold" style="color: #16a34a;">RDW Net Share</span>
                        <i class="fas fa-shield-alt fa-lg opacity-75" style="color: #16a34a;"></i>
                    </div>
                    <div class="fin-val-lg mt-1" style="color: #16a34a;">Rs. {{ number_format($head->rdw_share ?? 0) }}</div>
                    <small class="text-muted d-block mt-1 font-weight-bold">Net Project Allocation after MTSS</small>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="fin-stat-card h-100" style="border-left: 5px solid #d97706;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fin-label font-weight-bold" style="color: #d97706;">CSRF Share</span>
                        <i class="fas fa-hand-holding-usd fa-lg opacity-75" style="color: #d97706;"></i>
                    </div>
                    <div class="fin-val-lg mt-1" style="color: #d97706;">Rs. {{ number_format($head->csrf_share ?? 0) }}</div>
                    <small class="text-muted d-block mt-1 font-weight-bold">Dedicated CSRF Component</small>
                </div>
            </div>
        </div>

        {{-- ================= NAVIGATION PILLS / TABS ================= --}}
        <div class="mb-4 no-print">
            <ul class="nav nav-pills nav-fin-tabs" id="finTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tab-status-link" data-toggle="pill" href="#tab-status" role="tab">
                        <i class="fas fa-chart-line"></i> Financial Status & Snapshot
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-subheads-link" data-toggle="pill" href="#tab-subheads" role="tab">
                        <i class="fas fa-layer-group"></i> All Subheads Breakdown ({{ count($subheads) }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-milestones-link" data-toggle="pill" href="#tab-milestones" role="tab">
                        <i class="fas fa-tasks"></i> Milestones & Fundings ({{ count($milestones) }})
                    </a>
                </li>
                @if($loans)
                <li class="nav-item">
                    <a class="nav-link" id="tab-loans-link" data-toggle="pill" href="#tab-loans" role="tab">
                        <i class="fas fa-exchange-alt"></i> Loans & Inter-Project Netting
                    </a>
                </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link" id="tab-docs-link" data-toggle="pill" href="#tab-docs" role="tab">
                        <i class="fas fa-paperclip"></i> Files & Attachments ({{ count($allAttachments ?? []) }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-charts-link" data-toggle="pill" href="#tab-charts" role="tab">
                        <i class="fas fa-chart-pie"></i> Charts & Analytics
                    </a>
                </li>
            </ul>
        </div>

        {{-- ================= TAB CONTENT CONTAINER ================= --}}
        <div class="tab-content" id="finTabsContent">
            
            {{-- ======================================================== --}}
            {{-- TAB 1: FINANCIAL STATUS & SNAPSHOT --}}
            {{-- ======================================================== --}}
            <div class="tab-pane fade show active" id="tab-status" role="tabpanel">
                <div class="row">
                    
                    {{-- LEFT COLUMN: SNAPSHOT TABLE & RECEIVABLES --}}
                    <div class="col-xl-7 col-lg-12 mb-4">
                        
                        {{-- 1. PROJECT SNAPSHOT TABLE WITH INTERACTIVE EXPAND / DRILLDOWN BUTTONS --}}
                        <div class="fin-table-card mb-4">
                            <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="background: #f8fafc; border-color: #e2e8f0 !important;">
                                <h5 class="font-weight-bold text-dark mb-0 rajdhani" style="letter-spacing: 1px;">
                                    <i class="fas fa-table text-primary mr-2"></i> COMPLETE FINANCIAL SNAPSHOT
                                </h5>
                                <span class="badge badge-light border text-primary px-2.5 py-1 font-weight-bold" style="border-color: #0284c7 !important;">
                                    <i class="fas fa-search mr-1"></i> ALL METRICS WITH DRILLDOWN
                                </span>
                            </div>

                            <div class="table-responsive">
                                <table class="table fin-table w-100 m-0">
                                    <thead>
                                        <tr>
                                            <th class="pl-3" style="width: 28%;">METRIC</th>
                                            <th class="text-right" style="color: #0f172a; width: 24%;">PROJECT TOTAL AMOUNT</th>
                                            <th class="text-right" style="color: #0284c7; width: 24%;">PROJECT SHARE</th>
                                            <th class="text-right pr-3" style="color: #d97706; width: 24%;">CSRF SHARE</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- ALLOCATED ROW --}}
                                        <tr style="background: #f0f9ff; border-bottom: 2px solid #bae6fd;">
                                            <td class="pl-3 font-weight-bold text-dark" style="font-size: 1.05rem;">
                                                <i class="fas fa-coins text-warning mr-1.5"></i> Allocated
                                            </td>
                                            <td class="text-right font-weight-bold text-dark" style="font-size: 1.15rem;">{{ number_format($head->rdw_share ?? 0) }}</td>
                                            <td class="text-right font-weight-bold" style="color: #0284c7; font-size: 1.15rem;">{{ number_format($head->pcc_share ?? 0) }}</td>
                                            <td class="text-right pr-3 font-weight-bold" style="color: #d97706; font-size: 1.15rem;">{{ number_format($head->csrf_share ?? 0) }}</td>
                                        </tr>

                                        {{-- RECEIVED ROW (WITH DRILLDOWN BUTTONS ACROSS ALL SCOPES) --}}
                                        <tr>
                                            <td class="pl-3 font-weight-bold text-dark">Received (Cash Inflow)</td>
                                            <td class="text-right font-weight-bold text-dark" style="font-size: 1.02rem;">
                                                <span>{{ number_format($head->acc_received ?? 0) }}</span>
                                                @if($headRecord)
                                                <a href="{{ route('division.finance-of-project.drilldown', [$headRecord->hed_id, 'acc', 'received']) }}" target="_blank" class="btn-drill-link btn-drill-cyan" title="Expand Account Received Transactions"><i class="fas fa-search"></i></a>
                                                @endif
                                            </td>
                                            <td class="text-right font-weight-bold" style="color: #0284c7; font-size: 1.02rem;">
                                                <span>{{ number_format($head->pcc_received ?? 0) }}</span>
                                                @if($headRecord)
                                                <a href="{{ route('division.finance-of-project.drilldown', [$headRecord->hed_id, 'pcc', 'received']) }}" target="_blank" class="btn-drill-link btn-drill-cyan" title="Expand PCC Received Transactions"><i class="fas fa-search"></i></a>
                                                @endif
                                            </td>
                                            <td class="text-right pr-3 font-weight-bold" style="color: #d97706; font-size: 1.02rem;">
                                                <span>{{ number_format($head->cf_received ?? 0) }}</span>
                                                @if($headRecord)
                                                <a href="{{ route('division.finance-of-project.drilldown', [$headRecord->hed_id, 'csrf', 'received']) }}" target="_blank" class="btn-drill-link btn-drill-amber" title="Expand CSRF Received Transactions"><i class="fas fa-search"></i></a>
                                                @endif
                                            </td>
                                        </tr>

                                        {{-- EXPENDITURE ROW (WITH DRILLDOWN BUTTONS ACROSS ALL SCOPES) --}}
                                        <tr>
                                            <td class="pl-3 font-weight-bold text-dark">Expenditure (Spent)</td>
                                            <td class="text-right font-weight-bold text-danger" style="font-size: 1.02rem;">
                                                <span>{{ number_format(abs($head->acc_expenditure ?? 0)) }}</span>
                                                @if($headRecord)
                                                <a href="{{ route('division.finance-of-project.drilldown', [$headRecord->hed_id, 'acc', 'expenditure']) }}" target="_blank" class="btn-drill-link btn-drill-red" title="Expand Account Expenditure Breakdown"><i class="fas fa-search"></i></a>
                                                @endif
                                            </td>
                                            <td class="text-right font-weight-bold text-danger" style="font-size: 1.02rem;">
                                                <span>{{ number_format(abs($head->pcc_expenditure ?? 0)) }}</span>
                                                @if($headRecord)
                                                <a href="{{ route('division.finance-of-project.drilldown', [$headRecord->hed_id, 'pcc', 'expenditure']) }}" target="_blank" class="btn-drill-link btn-drill-red" title="Expand PCC Expenditure Breakdown"><i class="fas fa-search"></i></a>
                                                @endif
                                            </td>
                                            <td class="text-right pr-3 font-weight-bold text-danger" style="font-size: 1.02rem;">
                                                <span>{{ number_format(abs($head->cf_expenditure ?? 0)) }}</span>
                                                @if($headRecord)
                                                <a href="{{ route('division.finance-of-project.drilldown', [$headRecord->hed_id, 'csrf', 'expenditure']) }}" target="_blank" class="btn-drill-link btn-drill-red" title="Expand CSRF Expenditure Breakdown"><i class="fas fa-search"></i></a>
                                                @endif
                                            </td>
                                        </tr>

                                        {{-- BALANCE ROW --}}
                                        <tr style="background: #f8fafc;">
                                            <td class="pl-3 font-weight-bold text-primary" style="font-size: 1.02rem;">Balance</td>
                                            <td class="text-right font-weight-bold text-primary" style="font-size: 1.06rem;">{{ number_format($head->balance ?? 0) }}</td>
                                            <td class="text-right font-weight-bold text-primary" style="font-size: 1.06rem;">{{ number_format($head->pcc_balance ?? 0) }}</td>
                                            <td class="text-right pr-3 font-weight-bold text-primary" style="font-size: 1.06rem;">{{ number_format($head->cf_balance ?? 0) }}</td>
                                        </tr>

                                        {{-- COMMITMENTS ROW (WITH DRILLDOWN BUTTONS ACROSS ALL SCOPES) --}}
                                        <tr>
                                            <td class="pl-3 font-weight-bold text-dark">Commitments</td>
                                            <td class="text-right font-weight-bold" style="color: #d97706; font-size: 1.02rem;">
                                                <span>{{ number_format(abs($head->acc_commitments ?? 0)) }}</span>
                                                @if($headRecord)
                                                <a href="{{ route('division.finance-of-project.drilldown', [$headRecord->hed_id, 'acc', 'commitments']) }}" target="_blank" class="btn-drill-link btn-drill-amber" title="Expand Account Commitments Breakdown"><i class="fas fa-search"></i></a>
                                                @endif
                                            </td>
                                            <td class="text-right font-weight-bold" style="color: #d97706; font-size: 1.02rem;">
                                                <span>{{ number_format(abs($head->pcc_commitments ?? 0)) }}</span>
                                                @if($headRecord)
                                                <a href="{{ route('division.finance-of-project.drilldown', [$headRecord->hed_id, 'pcc', 'commitments']) }}" target="_blank" class="btn-drill-link btn-drill-amber" title="Expand PCC Commitments Breakdown"><i class="fas fa-search"></i></a>
                                                @endif
                                            </td>
                                            <td class="text-right pr-3 font-weight-bold" style="color: #d97706; font-size: 1.02rem;">
                                                <span>{{ number_format(abs($head->cf_commitments ?? 0)) }}</span>
                                                @if($headRecord)
                                                <a href="{{ route('division.finance-of-project.drilldown', [$headRecord->hed_id, 'csrf', 'commitments']) }}" target="_blank" class="btn-drill-link btn-drill-amber" title="Expand CSRF Commitments Breakdown"><i class="fas fa-search"></i></a>
                                                @endif
                                            </td>
                                        </tr>

                                        {{-- IN PROCESS ROW (WITH DRILLDOWN BUTTONS ACROSS ALL SCOPES) --}}
                                        <tr>
                                            <td class="pl-3 font-weight-bold text-dark">In Process (Pipeline)</td>
                                            <td class="text-right font-weight-bold text-secondary" style="font-size: 1.02rem;">
                                                <span>{{ number_format($head->acc_in_process ?? 0) }}</span>
                                                @if($headRecord)
                                                <a href="{{ route('division.finance-of-project.drilldown', [$headRecord->hed_id, 'acc', 'in-process']) }}" target="_blank" class="btn-drill-link btn-drill-gray" title="Expand Account In-Process Cases"><i class="fas fa-search"></i></a>
                                                @endif
                                            </td>
                                            <td class="text-right font-weight-bold text-secondary" style="font-size: 1.02rem;">
                                                <span>{{ number_format($head->pcc_in_process ?? 0) }}</span>
                                                @if($headRecord)
                                                <a href="{{ route('division.finance-of-project.drilldown', [$headRecord->hed_id, 'pcc', 'in-process']) }}" target="_blank" class="btn-drill-link btn-drill-gray" title="Expand PCC In-Process Cases"><i class="fas fa-search"></i></a>
                                                @endif
                                            </td>
                                            <td class="text-right pr-3 font-weight-bold text-secondary" style="font-size: 1.02rem;">
                                                <span>{{ number_format($head->cf_in_process ?? 0) }}</span>
                                                @if($headRecord)
                                                <a href="{{ route('division.finance-of-project.drilldown', [$headRecord->hed_id, 'csrf', 'in-process']) }}" target="_blank" class="btn-drill-link btn-drill-gray" title="Expand CSRF In-Process Cases"><i class="fas fa-search"></i></a>
                                                @endif
                                            </td>
                                        </tr>

                                        {{-- AVAILABLE BUDGET ROW --}}
                                        <tr style="background: #f0fdf4; border-top: 1.5px solid #bbf7d0; border-bottom: 1.5px solid #bbf7d0;">
                                            <td class="pl-3 font-weight-bold" style="color: #16a34a; font-size: 1.05rem;">Available Budget</td>
                                            <td class="text-right font-weight-bold" style="color: #16a34a; font-size: 1.12rem;">{{ number_format($head->available ?? 0) }}</td>
                                            <td class="text-right font-weight-bold" style="color: #16a34a; font-size: 1.12rem;">{{ number_format($head->pcc_available ?? 0) }}</td>
                                            <td class="text-right pr-3 font-weight-bold" style="color: #16a34a; font-size: 1.12rem;">{{ number_format($head->cf_available ?? 0) }}</td>
                                        </tr>

                                        {{-- YET TO BE RECEIVED ROW --}}
                                        <tr>
                                            <td class="pl-3 font-weight-bold text-dark">Yet to be Received</td>
                                            <td class="text-right font-weight-bold text-dark" style="font-size: 1.02rem;">{{ number_format($head->yet_to_be_received ?? 0) }}</td>
                                            <td class="text-right font-weight-bold" style="color: #0284c7; font-size: 1.02rem;">{{ number_format($head->pcc_yet_to_be_received ?? 0) }}</td>
                                            <td class="text-right pr-3 font-weight-bold" style="color: #d97706; font-size: 1.02rem;">{{ number_format($head->cf_yet_to_be_received ?? 0) }}</td>
                                        </tr>

                                        {{-- REMAINING ROW --}}
                                        <tr style="background: #f8fafc; border-top: 2px solid #cbd5e1;">
                                            <td class="pl-3 font-weight-bold text-dark" style="font-size: 1.12rem;">Total Spendable Remaining</td>
                                            <td class="text-right font-weight-bold text-dark" style="font-size: 1.22rem;">{{ number_format($head->can_be_spent ?? 0) }}</td>
                                            <td class="text-right font-weight-bold text-dark" style="font-size: 1.22rem;">{{ number_format($head->pcc_can_be_spent ?? 0) }}</td>
                                            <td class="text-right pr-3 font-weight-bold text-dark" style="font-size: 1.22rem;">{{ number_format($head->cf_can_be_spent ?? 0) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- 2. MILESTONE-BASED RECEIVABLES CARD --}}
                        <div class="fin-table-card p-4">
                            <h5 class="font-weight-bold text-dark mb-3 rajdhani" style="letter-spacing: 1px;">
                                <i class="fas fa-hand-holding-usd text-warning mr-2"></i> MILESTONE-BASED RECEIVABLES
                            </h5>
                            <div class="row align-items-center">
                                <div class="col-md-4 mb-3 mb-md-0 border-right" style="border-color: #e2e8f0 !important;">
                                    <span class="fin-label font-weight-bold">Completed Milestones</span>
                                    <div class="h4 font-weight-bold text-dark rajdhani mb-0">Rs. {{ number_format($head->receivable_completed ?? 0) }}</div>
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0 border-right" style="border-color: #e2e8f0 !important;">
                                    <span class="fin-label font-weight-bold">Current Milestone</span>
                                    <div class="h4 font-weight-bold text-dark rajdhani mb-0">Rs. {{ number_format($head->receivable_current ?? 0) }}</div>
                                </div>
                                <div class="col-md-4">
                                    <span class="fin-label font-weight-bold" style="color: #16a34a;">Available after Rcv.</span>
                                    <div class="h4 font-weight-bold rajdhani mb-0" style="color: #16a34a;">Rs. {{ number_format($head->available_after_receivables ?? 0) }}</div>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- RIGHT COLUMN: SUBHEAD UTILIZATION RINGS, LOANS & CHARTS --}}
                    <div class="col-xl-5 col-lg-12">
                        
                        {{-- 1. SUBHEAD CATEGORY METRICS GAUGE CARDS --}}
                        <div class="fin-table-card p-4 mb-4">
                            <h5 class="font-weight-bold text-dark mb-3 rajdhani" style="letter-spacing: 1px;">
                                <i class="fas fa-chart-pie text-success mr-2"></i> CORE SUBHEAD UTILIZATION
                            </h5>
                            
                            <div class="row">
                                {{-- EQUIPMENT --}}
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="subhead-gauge-box h-100" style="padding: 16px 10px;">
                                        <div class="d-flex justify-content-center mb-2">
                                            <svg width="70" height="70" viewBox="0 0 54 54" style="transform: rotate(-90deg);">
                                                <circle cx="27" cy="27" r="22" stroke="#e2e8f0" stroke-width="5" fill="none" />
                                                <circle cx="27" cy="27" r="22" stroke="#16a34a" stroke-width="5" fill="none"
                                                        stroke-dasharray="138.23" stroke-dashoffset="{{ 138.23 * (1 - ($finData['equip_pct'] / 100)) }}"
                                                        stroke-linecap="round" />
                                                <text x="27" y="-23" text-anchor="middle" fill="#16a34a" font-size="12" font-weight="bold" font-family="'Rajdhani', sans-serif" style="transform: rotate(90deg);">{{ $finData['equip_pct'] }}%</text>
                                            </svg>
                                        </div>
                                        <div class="font-weight-bold text-dark rajdhani mb-2" style="font-size: 1.15rem; letter-spacing: 0.5px;">EQUIPMENT</div>

                                        {{-- CLEAN TEXT-FORM BREAKDOWN --}}
                                        <div class="pt-2 border-top text-left" style="border-color: #e2e8f0 !important; font-size: 11.5px; line-height: 1.8;">
                                            <div class="d-flex justify-content-between align-items-center" style="gap: 6px;">
                                                <span class="text-muted font-weight-bold" style="white-space: nowrap;">Alloc:</span>
                                                <span class="font-weight-bold text-dark font-mono" style="white-space: nowrap;">{{ number_format($finData['equip_alloc'] ?? 0) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center" style="gap: 6px;">
                                                <span class="text-muted font-weight-bold" style="white-space: nowrap;">Spent:</span>
                                                <div class="d-inline-flex align-items-center">
                                                    <span class="font-weight-bold text-danger font-mono" style="white-space: nowrap;">{{ number_format($finData['equip'] ?? 0) }}</span>
                                                    @if($headRecord)
                                                    <a href="{{ route('division.finance-of-project.drilldown', [$headRecord->hed_id, 'subhead', 'expenditure', 'Equipment']) }}" target="_blank" class="btn-drill-link btn-drill-red ml-1" style="width: 17px; height: 17px; font-size: 0.6rem;" title="View Equipment Expenditure"><i class="fas fa-search"></i></a>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center" style="gap: 6px;">
                                                <span class="text-muted font-weight-bold" style="white-space: nowrap;">Commit:</span>
                                                <div class="d-inline-flex align-items-center">
                                                    <span class="font-weight-bold font-mono" style="color: #d97706; white-space: nowrap;">{{ number_format($finData['equip_cmt'] ?? 0) }}</span>
                                                    @if($headRecord)
                                                    <a href="{{ route('division.finance-of-project.drilldown', [$headRecord->hed_id, 'subhead', 'commitments', 'Equipment']) }}" target="_blank" class="btn-drill-link btn-drill-amber ml-1" style="width: 17px; height: 17px; font-size: 0.6rem;" title="View Equipment Commitments"><i class="fas fa-search"></i></a>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center" style="gap: 6px;">
                                                <span class="text-muted font-weight-bold" style="white-space: nowrap;">In Process:</span>
                                                <div class="d-inline-flex align-items-center">
                                                    <span class="font-weight-bold text-secondary font-mono" style="white-space: nowrap;">{{ number_format($finData['equip_ipc'] ?? 0) }}</span>
                                                    @if($headRecord)
                                                    <a href="{{ route('division.finance-of-project.drilldown', [$headRecord->hed_id, 'subhead', 'in-process', 'Equipment']) }}" target="_blank" class="btn-drill-link btn-drill-gray ml-1" style="width: 17px; height: 17px; font-size: 0.6rem;" title="View Equipment In Process"><i class="fas fa-search"></i></a>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center border-top mt-1 pt-1" style="border-color: #e2e8f0 !important; gap: 6px;">
                                                <span class="text-muted font-weight-bold" style="white-space: nowrap;">Remaining:</span>
                                                <span class="font-weight-bold font-mono {{ ($finData['equip_remaining'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}" style="white-space: nowrap;">
                                                    {{ number_format($finData['equip_remaining'] ?? 0) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- HR --}}
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="subhead-gauge-box h-100" style="padding: 16px 10px;">
                                        <div class="d-flex justify-content-center mb-2">
                                            <svg width="70" height="70" viewBox="0 0 54 54" style="transform: rotate(-90deg);">
                                                <circle cx="27" cy="27" r="22" stroke="#e2e8f0" stroke-width="5" fill="none" />
                                                <circle cx="27" cy="27" r="22" stroke="#0284c7" stroke-width="5" fill="none"
                                                        stroke-dasharray="138.23" stroke-dashoffset="{{ 138.23 * (1 - ($finData['hr_pct'] / 100)) }}"
                                                        stroke-linecap="round" />
                                                <text x="27" y="-23" text-anchor="middle" fill="#0284c7" font-size="12" font-weight="bold" font-family="'Rajdhani', sans-serif" style="transform: rotate(90deg);">{{ $finData['hr_pct'] }}%</text>
                                            </svg>
                                        </div>
                                        <div class="font-weight-bold text-dark rajdhani mb-2" style="font-size: 1.15rem; letter-spacing: 0.5px;">HR / STAFF</div>

                                        {{-- CLEAN TEXT-FORM BREAKDOWN --}}
                                        <div class="pt-2 border-top text-left" style="border-color: #e2e8f0 !important; font-size: 11.5px; line-height: 1.8;">
                                            <div class="d-flex justify-content-between align-items-center" style="gap: 6px;">
                                                <span class="text-muted font-weight-bold" style="white-space: nowrap;">Alloc:</span>
                                                <span class="font-weight-bold text-dark font-mono" style="white-space: nowrap;">{{ number_format($finData['hr_alloc'] ?? 0) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center" style="gap: 6px;">
                                                <span class="text-muted font-weight-bold" style="white-space: nowrap;">Spent:</span>
                                                <div class="d-inline-flex align-items-center">
                                                    <span class="font-weight-bold text-danger font-mono" style="white-space: nowrap;">{{ number_format($finData['hr'] ?? 0) }}</span>
                                                    @if($headRecord)
                                                    <a href="{{ route('division.finance-of-project.drilldown', [$headRecord->hed_id, 'subhead', 'expenditure', 'HR']) }}" target="_blank" class="btn-drill-link btn-drill-red ml-1" style="width: 17px; height: 17px; font-size: 0.6rem;" title="View HR Expenditure"><i class="fas fa-search"></i></a>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center" style="gap: 6px;">
                                                <span class="text-muted font-weight-bold" style="white-space: nowrap;">Commit:</span>
                                                <div class="d-inline-flex align-items-center">
                                                    <span class="font-weight-bold font-mono" style="color: #d97706; white-space: nowrap;">{{ number_format($finData['hr_cmt'] ?? 0) }}</span>
                                                    @if($headRecord)
                                                    <a href="{{ route('division.finance-of-project.drilldown', [$headRecord->hed_id, 'subhead', 'commitments', 'HR']) }}" target="_blank" class="btn-drill-link btn-drill-amber ml-1" style="width: 17px; height: 17px; font-size: 0.6rem;" title="View HR Commitments"><i class="fas fa-search"></i></a>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center" style="gap: 6px;">
                                                <span class="text-muted font-weight-bold" style="white-space: nowrap;">In Process:</span>
                                                <div class="d-inline-flex align-items-center">
                                                    <span class="font-weight-bold text-secondary font-mono" style="white-space: nowrap;">{{ number_format($finData['hr_ipc'] ?? 0) }}</span>
                                                    @if($headRecord)
                                                    <a href="{{ route('division.finance-of-project.drilldown', [$headRecord->hed_id, 'subhead', 'in-process', 'HR']) }}" target="_blank" class="btn-drill-link btn-drill-gray ml-1" style="width: 17px; height: 17px; font-size: 0.6rem;" title="View HR In Process"><i class="fas fa-search"></i></a>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center border-top mt-1 pt-1" style="border-color: #e2e8f0 !important; gap: 6px;">
                                                <span class="text-muted font-weight-bold" style="white-space: nowrap;">Remaining:</span>
                                                <span class="font-weight-bold font-mono {{ ($finData['hr_remaining'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}" style="white-space: nowrap;">
                                                    {{ number_format($finData['hr_remaining'] ?? 0) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- MISC --}}
                                <div class="col-md-4">
                                    <div class="subhead-gauge-box h-100" style="padding: 16px 10px;">
                                        <div class="d-flex justify-content-center mb-2">
                                            <svg width="70" height="70" viewBox="0 0 54 54" style="transform: rotate(-90deg);">
                                                <circle cx="27" cy="27" r="22" stroke="#e2e8f0" stroke-width="5" fill="none" />
                                                <circle cx="27" cy="27" r="22" stroke="#d97706" stroke-width="5" fill="none"
                                                        stroke-dasharray="138.23" stroke-dashoffset="{{ 138.23 * (1 - ($finData['misc_pct'] / 100)) }}"
                                                        stroke-linecap="round" />
                                                <text x="27" y="-23" text-anchor="middle" fill="#d97706" font-size="12" font-weight="bold" font-family="'Rajdhani', sans-serif" style="transform: rotate(90deg);">{{ $finData['misc_pct'] }}%</text>
                                            </svg>
                                        </div>
                                        <div class="font-weight-bold text-dark rajdhani mb-2" style="font-size: 1.15rem; letter-spacing: 0.5px;">MISC</div>

                                        {{-- CLEAN TEXT-FORM BREAKDOWN --}}
                                        <div class="pt-2 border-top text-left" style="border-color: #e2e8f0 !important; font-size: 11.5px; line-height: 1.8;">
                                            <div class="d-flex justify-content-between align-items-center" style="gap: 6px;">
                                                <span class="text-muted font-weight-bold" style="white-space: nowrap;">Alloc:</span>
                                                <span class="font-weight-bold text-dark font-mono" style="white-space: nowrap;">{{ number_format($finData['misc_alloc'] ?? 0) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center" style="gap: 6px;">
                                                <span class="text-muted font-weight-bold" style="white-space: nowrap;">Spent:</span>
                                                <div class="d-inline-flex align-items-center">
                                                    <span class="font-weight-bold text-danger font-mono" style="white-space: nowrap;">{{ number_format($finData['misc'] ?? 0) }}</span>
                                                    @if($headRecord)
                                                    <a href="{{ route('division.finance-of-project.drilldown', [$headRecord->hed_id, 'subhead', 'expenditure', 'Misc']) }}" target="_blank" class="btn-drill-link btn-drill-red ml-1" style="width: 17px; height: 17px; font-size: 0.6rem;" title="View Misc Expenditure"><i class="fas fa-search"></i></a>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center" style="gap: 6px;">
                                                <span class="text-muted font-weight-bold" style="white-space: nowrap;">Commit:</span>
                                                <div class="d-inline-flex align-items-center">
                                                    <span class="font-weight-bold font-mono" style="color: #d97706; white-space: nowrap;">{{ number_format($finData['misc_cmt'] ?? 0) }}</span>
                                                    @if($headRecord)
                                                    <a href="{{ route('division.finance-of-project.drilldown', [$headRecord->hed_id, 'subhead', 'commitments', 'Misc']) }}" target="_blank" class="btn-drill-link btn-drill-amber ml-1" style="width: 17px; height: 17px; font-size: 0.6rem;" title="View Misc Commitments"><i class="fas fa-search"></i></a>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center" style="gap: 6px;">
                                                <span class="text-muted font-weight-bold" style="white-space: nowrap;">In Process:</span>
                                                <div class="d-inline-flex align-items-center">
                                                    <span class="font-weight-bold text-secondary font-mono" style="white-space: nowrap;">{{ number_format($finData['misc_ipc'] ?? 0) }}</span>
                                                    @if($headRecord)
                                                    <a href="{{ route('division.finance-of-project.drilldown', [$headRecord->hed_id, 'subhead', 'in-process', 'Misc']) }}" target="_blank" class="btn-drill-link btn-drill-gray ml-1" style="width: 17px; height: 17px; font-size: 0.6rem;" title="View Misc In Process"><i class="fas fa-search"></i></a>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center border-top mt-1 pt-1" style="border-color: #e2e8f0 !important; gap: 6px;">
                                                <span class="text-muted font-weight-bold" style="white-space: nowrap;">Remaining:</span>
                                                <span class="font-weight-bold font-mono {{ ($finData['misc_remaining'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}" style="white-space: nowrap;">
                                                    {{ number_format($finData['misc_remaining'] ?? 0) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

            {{-- ======================================================== --}}
            {{-- TAB 2: ALL SUBHEADS BREAKDOWN (WITH INDIVIDUAL DRILLDOWN BUTTONS) --}}
            {{-- ======================================================== --}}
            <div class="tab-pane fade" id="tab-subheads" role="tabpanel">
                <div class="fin-table-card mb-4">
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="background: #f8fafc; border-color: #e2e8f0 !important;">
                        <h5 class="font-weight-bold text-dark mb-0 rajdhani" style="letter-spacing: 1px;">
                            <i class="fas fa-layer-group text-primary mr-2"></i> COMPLETE PROJECT SUBHEAD BREAKDOWN
                        </h5>
                        <span class="badge badge-primary px-3 py-1 font-weight-bold rajdhani" style="font-size: 0.85rem;">
                            {{ count($subheads) }} Subheads Configured
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table fin-table w-100 m-0">
                            <thead>
                                <tr>
                                    <th class="pl-3" style="width: 18%;">SUBHEAD</th>
                                    <th class="text-right" style="width: 14%; color: #0284c7;">ALLOCATION (Rs)</th>
                                    <th class="text-right" style="width: 15%; color: #dc2626;">EXPENDITURE (Rs)</th>
                                    <th class="text-right" style="width: 15%; color: #d97706;">COMMITMENTS (Rs)</th>
                                    <th class="text-right" style="width: 14%; color: #64748b;">IN PROCESS (Rs)</th>
                                    <th class="text-right" style="width: 14%; color: #16a34a;">CAN BE SPENT (Rs)</th>
                                    <th class="pr-3 text-center" style="width: 10%;">UTILIZATION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subheads as $sh)
                                    @php
                                        $shName = is_array($sh) ? ($sh['name'] ?? 'N/A') : ($sh->name ?? 'N/A');
                                        $shAlloc = (float)(is_array($sh) ? ($sh['allocation'] ?? 0) : ($sh->allocation ?? 0));
                                        $shExp = (float)(is_array($sh) ? ($sh['expenditure'] ?? 0) : ($sh->expenditure ?? 0));
                                        $shCmt = (float)(is_array($sh) ? ($sh['commitments'] ?? 0) : ($sh->commitments ?? 0));
                                        $shInProcess = (float)(is_array($sh) ? ($sh['in_process'] ?? 0) : ($sh->in_process ?? 0));
                                        $shRemaining = (float)(is_array($sh) ? ($sh['can_be_spent'] ?? ($sh['remaining'] ?? 0)) : ($sh->can_be_spent ?? ($sh->remaining ?? 0)));
                                        $shPct = $shAlloc > 0 ? min(100, round(($shExp / $shAlloc) * 100, 1)) : 0;
                                        $forecast = is_array($sh) ? ($sh['forecast'] ?? null) : ($sh->forecast ?? null);
                                    @endphp
                                    <tr>
                                        <td class="pl-3 font-weight-bold text-dark" style="font-size: 1.05rem;">
                                            <i class="fas fa-caret-right text-primary mr-1.5"></i> {{ $shName }}
                                            @if($shName === 'HR' && !empty($forecast))
                                                <span class="badge badge-info ml-1" title="Forecasted salary">Forecast: {{ number_format($forecast) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-right font-weight-bold" style="color: #0284c7; font-size: 1.05rem;">
                                            {{ number_format($shAlloc, 2) }}
                                        </td>
                                        <td class="text-right font-weight-bold text-danger" style="font-size: 1.05rem;">
                                            <span>{{ number_format($shExp, 2) }}</span>
                                            @if($headRecord)
                                            <a href="{{ route('division.finance-of-project.drilldown', [$headRecord->hed_id, 'subhead', 'expenditure', $shName]) }}" target="_blank" class="btn-drill-link btn-drill-red" title="Expand {{ $shName }} Expenditure"><i class="fas fa-search"></i></a>
                                            @endif
                                        </td>
                                        <td class="text-right font-weight-bold" style="color: #d97706; font-size: 1.05rem;">
                                            <span>{{ number_format($shCmt, 2) }}</span>
                                            @if($headRecord)
                                            <a href="{{ route('division.finance-of-project.drilldown', [$headRecord->hed_id, 'subhead', 'commitments', $shName]) }}" target="_blank" class="btn-drill-link btn-drill-amber" title="Expand {{ $shName }} Commitments"><i class="fas fa-search"></i></a>
                                            @endif
                                        </td>
                                        <td class="text-right font-weight-bold text-secondary" style="font-size: 1.05rem;">
                                            <span>{{ number_format($shInProcess, 2) }}</span>
                                            @if($headRecord)
                                            <a href="{{ route('division.finance-of-project.drilldown', [$headRecord->hed_id, 'subhead', 'in-process', $shName]) }}" target="_blank" class="btn-drill-link btn-drill-gray" title="Expand {{ $shName }} In Process"><i class="fas fa-search"></i></a>
                                            @endif
                                        </td>
                                        <td class="text-right font-weight-bold {{ $shRemaining >= 0 ? 'text-success' : 'text-danger' }}" style="font-size: 1.08rem;">
                                            {{ number_format($shRemaining, 2) }}
                                        </td>
                                        <td class="pr-3 text-center">
                                            <div class="progress" style="height: 18px; border-radius: 9px; background: #e2e8f0;">
                                                <div class="progress-bar font-weight-bold {{ $shPct > 90 ? 'bg-danger' : ($shPct > 60 ? 'bg-warning' : 'bg-success') }}"
                                                     role="progressbar" style="width: {{ $shPct }}%; font-size: 0.72rem;">
                                                    {{ $shPct }}%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4 font-weight-bold">No subhead breakdown found for this head.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @php
                                $totSubAlloc = collect($subheads)->sum(fn($s) => (float)(is_array($s) ? ($s['allocation'] ?? 0) : ($s->allocation ?? 0)));
                                $totSubExp = collect($subheads)->sum(fn($s) => (float)(is_array($s) ? ($s['expenditure'] ?? 0) : ($s->expenditure ?? 0)));
                                $totSubCmt = collect($subheads)->sum(fn($s) => (float)(is_array($s) ? ($s['commitments'] ?? 0) : ($s->commitments ?? 0)));
                                $totSubIpc = collect($subheads)->sum(fn($s) => (float)(is_array($s) ? ($s['in_process'] ?? 0) : ($s->in_process ?? 0)));
                                $totSubRem = collect($subheads)->sum(fn($s) => (float)(is_array($s) ? ($s['can_be_spent'] ?? ($s['remaining'] ?? 0)) : ($s->can_be_spent ?? ($s->remaining ?? 0))));
                                $totSubPct = $totSubAlloc > 0 ? min(100, round(($totSubExp / $totSubAlloc) * 100, 1)) : 0;
                            @endphp
                            @if(count($subheads) > 0)
                            <tfoot>
                                <tr style="background: #f8fafc; border-top: 2.5px solid #cbd5e1; border-bottom: 2px solid #cbd5e1;">
                                    <td class="pl-3 font-weight-bold text-dark text-uppercase rajdhani" style="font-size: 1.15rem; letter-spacing: 0.5px;">
                                        <i class="fas fa-calculator text-primary mr-1.5"></i> TOTAL
                                    </td>
                                    <td class="text-right font-weight-bold" style="color: #0284c7; font-size: 1.12rem;">
                                        {{ number_format($totSubAlloc, 2) }}
                                    </td>
                                    <td class="text-right font-weight-bold text-danger" style="font-size: 1.12rem;">
                                        {{ number_format($totSubExp, 2) }}
                                    </td>
                                    <td class="text-right font-weight-bold" style="color: #d97706; font-size: 1.12rem;">
                                        {{ number_format($totSubCmt, 2) }}
                                    </td>
                                    <td class="text-right font-weight-bold text-secondary" style="font-size: 1.12rem;">
                                        {{ number_format($totSubIpc, 2) }}
                                    </td>
                                    <td class="text-right font-weight-bold {{ $totSubRem >= 0 ? 'text-success' : 'text-danger' }}" style="font-size: 1.15rem;">
                                        {{ number_format($totSubRem, 2) }}
                                    </td>
                                    <td class="pr-3 text-center">
                                        <div class="progress" style="height: 18px; border-radius: 9px; background: #e2e8f0;">
                                            <div class="progress-bar font-weight-bold {{ $totSubPct > 90 ? 'bg-danger' : ($totSubPct > 60 ? 'bg-warning' : 'bg-success') }}"
                                                 role="progressbar" style="width: {{ $totSubPct }}%; font-size: 0.75rem;">
                                                {{ $totSubPct }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            {{-- ======================================================== --}}
            {{-- TAB 3: CHARTS & FINANCIAL ANALYTICS (COMPREHENSIVE SUITE) --}}
            {{-- ======================================================== --}}
            <div class="tab-pane fade" id="tab-charts" role="tabpanel">
                
                {{-- QUICK KPI SUMMARY ROW --}}
                <div class="row mb-4">
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                        <div class="fin-stat-card h-100" style="border-top: 4px solid #0284c7; padding: 12px 16px;">
                            <div class="fin-label text-truncate" style="color: #0284c7;">Allocation</div>
                            <div class="fin-val-lg" style="font-size: 1.35rem; color: #0284c7;">Rs. {{ number_format($head->allocation ?? 0) }}</div>
                            <small class="text-muted font-weight-bold">Sanctioned Budget</small>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                        <div class="fin-stat-card h-100" style="border-top: 4px solid #0369a1; padding: 12px 16px;">
                            <div class="fin-label text-truncate" style="color: #0369a1;">Received</div>
                            <div class="fin-val-lg" style="font-size: 1.35rem; color: #0369a1;">Rs. {{ number_format($head->received ?? 0) }}</div>
                            <small class="text-muted font-weight-bold">{{ ($head->allocation ?? 0) > 0 ? round((($head->received ?? 0) / $head->allocation) * 100, 1) : 0 }}% Inflow</small>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                        <div class="fin-stat-card h-100" style="border-top: 4px solid #dc2626; padding: 12px 16px;">
                            <div class="fin-label text-truncate" style="color: #dc2626;">Expenditure</div>
                            <div class="fin-val-lg" style="font-size: 1.35rem; color: #dc2626;">Rs. {{ number_format($head->expenditure ?? 0) }}</div>
                            <small class="text-muted font-weight-bold">{{ ($head->allocation ?? 0) > 0 ? round((($head->expenditure ?? 0) / $head->allocation) * 100, 1) : 0 }}% Spent</small>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                        <div class="fin-stat-card h-100" style="border-top: 4px solid #d97706; padding: 12px 16px;">
                            <div class="fin-label text-truncate" style="color: #d97706;">Commitments</div>
                            <div class="fin-val-lg" style="font-size: 1.35rem; color: #d97706;">Rs. {{ number_format($head->commitments ?? 0) }}</div>
                            <small class="text-muted font-weight-bold">Active Orders</small>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                        <div class="fin-stat-card h-100" style="border-top: 4px solid #64748b; padding: 12px 16px;">
                            <div class="fin-label text-truncate" style="color: #64748b;">In Process</div>
                            <div class="fin-val-lg" style="font-size: 1.35rem; color: #64748b;">Rs. {{ number_format($head->in_process ?? 0) }}</div>
                            <small class="text-muted font-weight-bold">Pending Approval</small>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                        <div class="fin-stat-card h-100" style="border-top: 4px solid #16a34a; padding: 12px 16px;">
                            <div class="fin-label text-truncate" style="color: #16a34a;">Spendable</div>
                            <div class="fin-val-lg" style="font-size: 1.35rem; color: #16a34a;">Rs. {{ number_format($head->pcc_can_be_spent ?? ($head->remaining ?? 0)) }}</div>
                            <small class="text-muted font-weight-bold">Net Remaining</small>
                        </div>
                    </div>
                </div>

                {{-- ROW 1: MACRO CASH FLOW & BUDGET STATUS DOUGHNUT --}}
                <div class="row">
                    <div class="col-xl-7 col-lg-12 mb-4">
                        <div class="fin-table-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="font-weight-bold text-dark mb-0 rajdhani" style="letter-spacing: 1px;">
                                    <i class="fas fa-chart-bar text-primary mr-2"></i> MACRO CASH FLOW & FINANCIAL BALANCE
                                </h5>
                                <span class="badge badge-light border text-primary px-2.5 py-1 font-weight-bold">
                                    Inflow vs Outflow
                                </span>
                            </div>
                            <div style="height: 310px; position: relative;">
                                <canvas id="finDetailedChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-5 col-lg-12 mb-4">
                        <div class="fin-table-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="font-weight-bold text-dark mb-0 rajdhani" style="letter-spacing: 1px;">
                                    <i class="fas fa-chart-pie text-success mr-2"></i> BUDGET UTILIZATION BREAKDOWN
                                </h5>
                                <span class="badge badge-light border text-success px-2.5 py-1 font-weight-bold">
                                    Allocation %
                                </span>
                            </div>
                            <div style="height: 310px; position: relative;">
                                <canvas id="budgetUtilizationDoughnutChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ROW 2: SUBHEADS MULTI-BAR COMPARISON & EXPENDITURE SHARE PIE --}}
                <div class="row">
                    <div class="col-xl-8 col-lg-12 mb-4">
                        <div class="fin-table-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="font-weight-bold text-dark mb-0 rajdhani" style="letter-spacing: 1px;">
                                    <i class="fas fa-layer-group text-primary mr-2"></i> SUBHEADS MULTI-DIMENSIONAL COMPARISON
                                </h5>
                                <span class="badge badge-light border text-info px-2.5 py-1 font-weight-bold">
                                    Alloc vs Spent vs Commit vs Remaining
                                </span>
                            </div>
                            <div style="height: 330px; position: relative;">
                                <canvas id="subheadComparisonChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-12 mb-4">
                        <div class="fin-table-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="font-weight-bold text-dark mb-0 rajdhani" style="letter-spacing: 1px;">
                                    <i class="fas fa-chart-pie text-danger mr-2"></i> SUBHEAD EXPENDITURE SHARE
                                </h5>
                                <span class="badge badge-light border text-danger px-2.5 py-1 font-weight-bold">
                                    Spent Distribution
                                </span>
                            </div>
                            <div style="height: 330px; position: relative;">
                                <canvas id="subheadExpenditurePieChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ROW 3: FUNDING SHARES & SUBHEAD UTILIZATION RATES --}}
                <div class="row">
                    <div class="col-xl-6 col-lg-12 mb-4">
                        <div class="fin-table-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="font-weight-bold text-dark mb-0 rajdhani" style="letter-spacing: 1px;">
                                    <i class="fas fa-university text-warning mr-2"></i> MULTI-SCOPE FUNDING SHARES
                                </h5>
                                <span class="badge badge-light border text-warning px-2.5 py-1 font-weight-bold">
                                    MTSS vs RDW vs CSRF
                                </span>
                            </div>
                            <div style="height: 290px; position: relative;">
                                <canvas id="fundingSharesChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 col-lg-12 mb-4">
                        <div class="fin-table-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="font-weight-bold text-dark mb-0 rajdhani" style="letter-spacing: 1px;">
                                    <i class="fas fa-tasks text-success mr-2"></i> SUBHEAD BUDGET UTILIZATION RATES (%)
                                </h5>
                                <span class="badge badge-light border text-success px-2.5 py-1 font-weight-bold">
                                    Burn Rates %
                                </span>
                            </div>
                            <div style="height: 290px; position: relative;">
                                <canvas id="subheadUtilizationBarChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ======================================================== --}}
            {{-- TAB 3: MILESTONES & FUNDINGS MATRIX --}}
            {{-- ======================================================== --}}
            <div class="tab-pane fade" id="tab-milestones" role="tabpanel">
                
                {{-- 1. FUNDINGS MATRIX TABLE MATCHING LEGACY IMAGE 2 --}}
                <div class="fin-table-card mb-4">
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="background: #f8fafc; border-color: #e2e8f0 !important;">
                        <h5 class="font-weight-bold text-dark mb-0 rajdhani" style="letter-spacing: 1px;">
                            <i class="fas fa-money-check-alt text-success mr-2"></i> FUNDINGS MATRIX (ALLOCATION & INFLOW BY SHARE)
                        </h5>
                        <span class="badge badge-success px-3 py-1 font-weight-bold rajdhani" style="font-size: 0.85rem;">
                            Multi-Scope Funding
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered fundings-matrix-table m-0">
                            <thead>
                                <tr>
                                    <th width="15%" class="text-left">CATEGORY</th>
                                    <th width="17%">TOTAL ALLOCATION</th>
                                    <th width="17%" style="color: #dc2626;">MTSS SHARE</th>
                                    <th width="17%" style="color: #0284c7;">RDW SHARE</th>
                                    <th width="17%" style="color: #d97706;">CSRF SHARE</th>
                                    <th width="17%" style="color: #16a34a;">PROJECT SHARE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-left font-weight-bold bg-light">Allocation</td>
                                    <td class="font-weight-bold text-dark" style="background: #f1f5f9;">Rs. {{ number_format($head->allocation ?? 0) }}</td>
                                    <td class="text-danger font-weight-bold">Rs. {{ number_format($head->mtss_share ?? 0) }}</td>
                                    <td class="font-weight-bold" style="color: #0284c7;">Rs. {{ number_format($head->rdw_share ?? 0) }}</td>
                                    <td class="font-weight-bold" style="color: #d97706;">Rs. {{ number_format($head->csrf_share ?? 0) }}</td>
                                    <td class="font-weight-bold" style="color: #16a34a;">Rs. {{ number_format($head->prj_share ?? 0) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-left font-weight-bold bg-light">Received (Inflow)</td>
                                    <td class="font-weight-bold text-dark">Rs. {{ number_format($head->received ?? 0) }}</td>
                                    <td class="text-muted">Rs. 0</td>
                                    <td class="font-weight-bold" style="color: #0284c7;">Rs. {{ number_format($head->acc_received ?? 0) }}</td>
                                    <td class="font-weight-bold" style="color: #d97706;">Rs. {{ number_format($head->cf_received ?? 0) }}</td>
                                    <td class="font-weight-bold" style="color: #16a34a;">Rs. {{ number_format($head->pcc_received ?? 0) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 2. MILESTONES TABLE --}}
                <div class="fin-table-card mb-4">
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="background: #f8fafc; border-color: #e2e8f0 !important;">
                        <h5 class="font-weight-bold text-dark mb-0 rajdhani" style="letter-spacing: 1px;">
                            <i class="fas fa-tasks text-primary mr-2"></i> PROJECT MILESTONES & COST ALLOCATION
                        </h5>
                        <span class="badge badge-info px-3 py-1 font-weight-bold rajdhani" style="font-size: 0.85rem;">
                            {{ count($milestones) }} Total Milestones
                        </span>
                    </div>

                    @php
                        $grossAlloc = (float)($head->allocation ?? 0);
                        $rdwShareRatio = $grossAlloc > 0 ? ((float)($head->rdw_share ?? 0) / $grossAlloc) : 0;
                        $mtssShareRatio = $grossAlloc > 0 ? ((float)($head->mtss_share ?? 0) / $grossAlloc) : 0;
                        $csrfShareRatio = $grossAlloc > 0 ? ((float)($head->csrf_share ?? 0) / $grossAlloc) : 0;

                        $totMilestoneAmt = 0;
                        $totRdwShare = 0;
                        $totMtssShare = 0;
                        $totCsrfShare = 0;
                    @endphp
                    <div class="table-responsive">
                        <table class="table fin-table w-100 m-0">
                            <thead>
                                <tr>
                                    <th class="pl-3" style="width: 7%;">MS #</th>
                                    <th style="width: 27%;">DESCRIPTION</th>
                                    <th class="text-right" style="width: 14%; color: #0284c7;">MILESTONE AMOUNT</th>
                                    <th class="text-right" style="width: 12%; color: #16a34a;">RDW SHARE</th>
                                    <th class="text-right" style="width: 11%; color: #dc2626;">MTSS SHARE</th>
                                    <th class="text-right" style="width: 11%; color: #d97706;">CSRF SHARE</th>
                                    <th style="width: 9%;">TARGET DATE</th>
                                    <th class="pr-3 text-center" style="width: 9%;">STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($milestones as $m)
                                    @php
                                        $mCost = (float)($m->mct_cost ?: $m->msn_cost);
                                        $mRdw = $mCost * $rdwShareRatio;
                                        $mMtss = $mCost * $mtssShareRatio;
                                        $mCsrf = $mCost * $csrfShareRatio;

                                        $totMilestoneAmt += $mCost;
                                        $totRdwShare += $mRdw;
                                        $totMtssShare += $mMtss;
                                        $totCsrfShare += $mCsrf;
                                    @endphp
                                    <tr>
                                        <td class="pl-3 font-weight-bold text-primary">MS-{{ $loop->iteration }}</td>
                                        <td class="font-weight-bold text-dark">{{ $m->msn_desc }}</td>
                                        <td class="text-right font-weight-bold text-primary" style="font-size: 1.02rem;">
                                            Rs. {{ number_format($mCost, 2) }}
                                        </td>
                                        <td class="text-right font-weight-bold text-success">
                                            Rs. {{ number_format($mRdw, 2) }}
                                        </td>
                                        <td class="text-right font-weight-bold text-danger">
                                            Rs. {{ number_format($mMtss, 2) }}
                                        </td>
                                        <td class="text-right font-weight-bold" style="color: #d97706;">
                                            Rs. {{ number_format($mCsrf, 2) }}
                                        </td>
                                        <td class="text-dark font-weight-bold small">
                                            {{ $m->msn_targetdt ? \Carbon\Carbon::parse($m->msn_targetdt)->format('d M Y') : 'N/A' }}
                                        </td>
                                        <td class="pr-3 text-center">
                                            @if(strtolower(trim($m->msn_status ?? '')) === 'achieved' || strtolower(trim($m->msn_status ?? '')) === 'completed')
                                                <span class="badge badge-success px-2.5 py-1 font-weight-bold">COMPLETED</span>
                                            @elseif(strtolower(trim($m->msn_status ?? '')) === 'in progress')
                                                <span class="badge badge-warning px-2.5 py-1 font-weight-bold text-dark">IN PROGRESS</span>
                                            @else
                                                <span class="badge badge-secondary px-2.5 py-1 font-weight-bold">{{ strtoupper($m->msn_status ?: 'PENDING') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4 font-weight-bold">No milestones recorded for this project.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if(count($milestones) > 0)
                            <tfoot style="background: #f8fafc; border-top: 2px solid #cbd5e1;">
                                <tr class="font-weight-bold">
                                    <td colspan="2" class="pl-3 text-dark text-uppercase rajdhani" style="font-size: 1rem; letter-spacing: 0.5px;">TOTAL MILESTONES COST</td>
                                    <td class="text-right text-primary" style="font-size: 1.05rem;">Rs. {{ number_format($totMilestoneAmt, 2) }}</td>
                                    <td class="text-right text-success" style="font-size: 1.05rem;">Rs. {{ number_format($totRdwShare, 2) }}</td>
                                    <td class="text-right text-danger" style="font-size: 1.05rem;">Rs. {{ number_format($totMtssShare, 2) }}</td>
                                    <td class="text-right" style="color: #d97706; font-size: 1.05rem;">Rs. {{ number_format($totCsrfShare, 2) }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>

                {{-- 3. TRANCHES & INSTALLMENTS TABLE --}}
                @if(count($installments) > 0)
                <div class="fin-table-card mb-4">
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="background: #f8fafc; border-color: #e2e8f0 !important;">
                        <h5 class="font-weight-bold text-dark mb-0 rajdhani" style="letter-spacing: 1px;">
                            <i class="fas fa-money-check-alt text-success mr-2"></i> FUNDING TRANCHES & INSTALLMENTS
                        </h5>
                        <span class="badge badge-success px-3 py-1 font-weight-bold rajdhani" style="font-size: 0.85rem;">
                            {{ count($installments) }} Tranches
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table fin-table w-100 m-0">
                            <thead>
                                <tr>
                                    <th class="pl-3" style="width: 8%;">ID</th>
                                    <th style="width: 15%;">TRANSACTION DATE</th>
                                    <th style="width: 32%;">TRANCHE / MILESTONE REF</th>
                                    <th class="text-right" style="width: 15%; color: #0284c7;">PCC SHARE</th>
                                    <th class="text-right" style="width: 15%; color: #d97706;">CSRF SHARE</th>
                                    <th class="text-right pr-3" style="width: 15%; color: #16a34a;">TOTAL AMOUNT</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($installments as $ins)
                                    @php
                                        $tot = (float)($ins->shi_prj ?: (($ins->shi_pcc ?? 0) + ($ins->shi_cf ?? 0)));
                                        $dateStr = !empty($ins->trn_date) ? \Carbon\Carbon::parse($ins->trn_date)->format('d M Y') : 'N/A';
                                    @endphp
                                    <tr>
                                        <td class="pl-3 font-weight-bold text-primary">#{{ $ins->shi_id }}</td>
                                        <td class="font-weight-bold text-dark">{{ $dateStr }}</td>
                                        <td class="font-weight-bold text-dark">
                                            {{ $ins->msn_desc ?: ($ins->trn_desc ?: 'Funding Tranche #' . $ins->shi_id) }}
                                            @if(!empty($ins->trn_docref))
                                                <span class="badge badge-light border text-muted ml-1">{{ $ins->trn_docref }}</span>
                                            @endif
                                        </td>
                                        <td class="text-right font-weight-bold" style="color: #0284c7;">
                                            Rs. {{ number_format($ins->shi_pcc ?? 0, 2) }}
                                        </td>
                                        <td class="text-right font-weight-bold" style="color: #d97706;">
                                            Rs. {{ number_format($ins->shi_cf ?? 0, 2) }}
                                        </td>
                                        <td class="text-right pr-3 font-weight-bold text-success" style="font-size: 1.05rem;">
                                            Rs. {{ number_format($tot, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

            </div>

            {{-- ======================================================== --}}
            {{-- TAB 4: LOANS & INTER-PROJECT NETTING --}}
            {{-- ======================================================== --}}
            @if($loans)
            <div class="tab-pane fade" id="tab-loans" role="tabpanel">
                <div class="fin-table-card p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="font-weight-bold text-dark mb-1 rajdhani" style="letter-spacing: 0.5px;">
                                <i class="fas fa-exchange-alt text-warning mr-2"></i> INTER-PROJECT LOANS & NETTING RECONCILIATION
                            </h4>
                            <p class="text-muted mb-0 font-weight-bold">Detailed breakdown of funds borrowed from or lent to other project heads.</p>
                        </div>
                        @if($headRecord)
                        <div class="d-flex gap-2">
                            <a href="{{ route('division.finance-of-project.drilldown', [$headRecord->hed_id, 'loans', 'loansgiven']) }}" target="_blank" class="btn btn-outline-info font-weight-bold px-3 py-1.5 rounded-pill">
                                <i class="fas fa-search mr-1"></i> Expand Loans Given
                            </a>
                            <a href="{{ route('division.finance-of-project.drilldown', [$headRecord->hed_id, 'loans', 'loanstaken']) }}" target="_blank" class="btn btn-outline-danger font-weight-bold px-3 py-1.5 rounded-pill ml-2">
                                <i class="fas fa-search mr-1"></i> Expand Loans Taken
                            </a>
                        </div>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="fin-stat-card h-100" style="border-left: 5px solid #0284c7;">
                                <span class="fin-label" style="color: #0284c7;">PCC Own Expenditure</span>
                                <div class="fin-val-lg mt-1">Rs. {{ number_format($loans->pcc_own_exp ?? 0, 2) }}</div>
                                <small class="text-muted d-block mt-1 font-weight-bold">Actual direct spending from own head</small>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="fin-stat-card h-100" style="border-left: 5px solid #d97706;">
                                <span class="fin-label" style="color: #d97706;">Loans Given to Others</span>
                                <div class="fin-val-lg mt-1" style="color: #d97706;">Rs. {{ number_format($loans->pcc_loansgiven ?? 0, 2) }}</div>
                                <small class="text-muted d-block mt-1 font-weight-bold">Funds temporarily financed to other heads</small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="fin-stat-card h-100" style="border-left: 5px solid #dc2626;">
                                <span class="fin-label text-danger">Loans Taken from Others</span>
                                <div class="fin-val-lg text-danger mt-1">Rs. {{ number_format($loans->others_loans_taken ?? 0, 2) }}</div>
                                <small class="text-muted d-block mt-1 font-weight-bold">Funds borrowed from other heads</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif



            {{-- ======================================================== --}}
            {{-- TAB 6: PROJECT FILES & ATTACHMENTS --}}
            {{-- ======================================================== --}}
            {{-- ======================================================== --}}
            {{-- TAB: PROJECT FILES & ATTACHMENTS (SINGLE UNIFIED SECTION) --}}
            {{-- ======================================================== --}}
            <div class="tab-pane fade" id="tab-docs" role="tabpanel">
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="fin-table-card">
                            <div class="p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap" style="background: #f8fafc; border-color: #e2e8f0 !important; gap: 10px;">
                                <div class="d-flex align-items-center" style="gap: 10px;">
                                    <h5 class="font-weight-bold text-dark mb-0 rajdhani" style="letter-spacing: 1px;">
                                        <i class="fas fa-folder-open text-primary mr-2"></i> ALL PROJECT FILES & ATTACHMENTS
                                    </h5>
                                    <span class="badge badge-primary px-3 py-1 font-weight-bold rajdhani" style="font-size: 0.85rem;" id="projectAttCountBadge">
                                        {{ count($allAttachments ?? []) }} Document(s) Available
                                    </span>
                                </div>
                                
                                @if(Auth::check())
                                <button type="button" class="btn btn-sm btn-primary font-weight-bold rajdhani px-3 shadow-sm rounded-pill d-flex align-items-center" data-toggle="modal" data-target="#modalUploadProjectFinAttachment" style="font-size: 13px; letter-spacing: 0.5px;">
                                    <i class="fas fa-plus mr-1.5"></i> UPLOAD / ADD ATTACHMENT
                                </button>
                                @endif
                            </div>

                            <div class="table-responsive">
                                <table class="table fin-table w-100 m-0" id="finProjectAttachmentsTable">
                                    <thead>
                                        <tr>
                                            <th class="pl-3" style="width: 6%;">#</th>
                                            <th style="width: 34%;">DOCUMENT TITLE / TYPE</th>
                                            <th style="width: 26%;">FILE NAME</th>
                                            <th style="width: 18%;">UPLOAD DATE</th>
                                            <th class="pr-3 text-center" style="width: 16%;">ACTION</th>
                                        </tr>
                                    </thead>
                                    <tbody id="finProjectAttachmentsTbody">
                                        @forelse($allAttachments ?? [] as $index => $att)
                                            @php
                                                $path = $att->jat_path ?? '';
                                                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                                                $icon = match($ext) {
                                                    'pdf' => 'fa-file-pdf text-danger',
                                                    'doc', 'docx' => 'fa-file-word text-primary',
                                                    'xls', 'xlsx' => 'fa-file-excel text-success',
                                                    'jpg', 'jpeg', 'png' => 'fa-file-image text-warning',
                                                    default => 'fa-file-alt text-info'
                                                };
                                            @endphp
                                            <tr>
                                                <td class="pl-3 font-weight-bold text-primary">{{ $loop->iteration }}</td>
                                                <td class="font-weight-bold text-dark">
                                                    <i class="fas {{ $icon }} fa-lg mr-2"></i>
                                                    <span class="doc-type-text">{{ $att->jat_type ?: 'Attachment #' . $att->jat_id }}</span>
                                                </td>
                                                <td class="text-muted font-weight-bold font-mono" style="font-size: 0.85rem;">
                                                    {{ basename($path) ?: 'Document File' }}
                                                </td>
                                                <td class="text-dark font-weight-bold" style="font-size: 0.85rem;">
                                                    {{ $att->created_at ? \Carbon\Carbon::parse($att->created_at)->format('d M Y') : '-' }}
                                                </td>
                                                <td class="pr-3 text-center">
                                                    @php
                                                        $fileUrl = \App\Facades\FileStorage::url($path) ?: route('attachment.view', $att->jat_id);
                                                    @endphp
                                                    <a href="{{ $fileUrl }}" onclick="window.openLiveDocument('{{ $fileUrl }}', '{{ addslashes($att->jat_type ?: basename($path)) }}'); return false;" class="rd-live-file-view btn btn-xs btn-primary font-weight-bold px-2.5 py-1 rounded shadow-sm" title="View Document Live">
                                                        <i class="fas fa-eye mr-1"></i> View File
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr id="noAttachmentsRow">
                                                <td colspan="5" class="text-center text-muted py-4 font-weight-bold">
                                                    <i class="fas fa-folder-open fa-2x mb-2 text-muted" style="opacity: 0.3;"></i>
                                                    <p class="mb-0">No attachments uploaded yet for this project.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- UPLOAD ATTACHMENT MODAL FOR PROJECT FINANCIAL VIEW --}}
            @if(Auth::check())
            <div class="modal fade" id="modalUploadProjectFinAttachment" tabindex="-1" role="dialog" aria-labelledby="modalUploadProjectFinAttachmentLabel" aria-hidden="true" style="z-index: 1065;">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                        <div class="modal-header py-3 px-4" style="background: #0f172a; color: #ffffff;">
                            <h6 class="modal-title font-weight-bold rajdhani mb-0" id="modalUploadProjectFinAttachmentLabel" style="font-size: 15px; letter-spacing: 0.5px;">
                                <i class="fas fa-paperclip text-primary mr-2"></i> UPLOAD PROJECT ATTACHMENT / FILE
                            </h6>
                            <button type="button" class="close text-white opacity-75" data-dismiss="modal" aria-label="Close" style="outline: none;">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form id="formUploadProjectFinAttachment" action="{{ route('universal.attachment.upload') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="module" value="prj">
                            <input type="hidden" name="object_id" value="{{ $project->prj_id }}">
                            <div class="modal-body p-4" style="background: #ffffff;">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-dark rajdhani" style="font-size: 13px;">DOCUMENT TITLE / TYPE <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <select class="form-control" id="selectDocTypeQuick" onchange="if(this.value){ document.getElementById('inputDocTypeCustom').value = this.value; }" style="font-size: 13px;">
                                            <option value="">-- Quick Select Standard Type or Type Custom Below --</option>
                                            <option value="Project Proposal">Project Proposal</option>
                                            <option value="URD">URD (User Requirement Document)</option>
                                            <option value="Work Order">Work Order</option>
                                            <option value="PPF">PPF</option>
                                            <option value="Financial Status">Financial Status</option>
                                            <option value="Approval Letter">Approval Letter</option>
                                            <option value="Sanction Order">Sanction Order</option>
                                            <option value="Minute">Minute</option>
                                        </select>
                                    </div>
                                    <input type="text" name="doc_type" id="inputDocTypeCustom" class="form-control mt-2" placeholder="Or enter custom document title (e.g. Inception Report, Audit Note)" required style="font-size: 13px; border-radius: 6px;">
                                </div>

                                <div class="form-group mb-2">
                                    <label class="font-weight-bold text-dark rajdhani" style="font-size: 13px;">SELECT ATTACHMENT FILE <span class="text-danger">*</span></label>
                                    <input type="file" name="file" id="fileFinAttachmentInput" class="form-control-file border p-2" style="border-radius: 6px; background: #f8fafc; font-size: 12.5px;" required>
                                    <small class="text-muted d-block mt-1 font-weight-bold" style="font-size: 11px;">
                                        Supported: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Max 20MB)
                                    </small>
                                </div>
                            </div>
                            <div class="modal-footer py-2.5 px-4 bg-light border-top d-flex justify-content-between">
                                <button type="button" class="btn btn-sm btn-secondary font-weight-bold px-3" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-sm btn-primary font-weight-bold rajdhani px-4 shadow-sm" id="btnSubmitFinAttachment" style="letter-spacing: 0.5px; font-size: 13px;">
                                    <i class="fas fa-cloud-upload-alt mr-1"></i> UPLOAD FILE
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            {{-- ======================================================== --}}
            {{-- TAB: CHARTS & FINANCIAL ANALYTICS (VERY LAST TAB) --}}
            {{-- ======================================================== --}}
            <div class="tab-pane fade" id="tab-charts" role="tabpanel">
                
                {{-- QUICK KPI SUMMARY ROW --}}
                <div class="row mb-4">
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                        <div class="fin-stat-card h-100" style="border-top: 4px solid #0284c7; padding: 12px 16px;">
                            <div class="fin-label text-truncate" style="color: #0284c7;">Allocation</div>
                            <div class="fin-val-lg" style="font-size: 1.35rem; color: #0284c7;">Rs. {{ number_format($head->allocation ?? 0) }}</div>
                            <small class="text-muted font-weight-bold">Sanctioned Budget</small>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                        <div class="fin-stat-card h-100" style="border-top: 4px solid #0369a1; padding: 12px 16px;">
                            <div class="fin-label text-truncate" style="color: #0369a1;">Received</div>
                            <div class="fin-val-lg" style="font-size: 1.35rem; color: #0369a1;">Rs. {{ number_format($head->received ?? 0) }}</div>
                            <small class="text-muted font-weight-bold">{{ ($head->allocation ?? 0) > 0 ? round((($head->received ?? 0) / $head->allocation) * 100, 1) : 0 }}% Inflow</small>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                        <div class="fin-stat-card h-100" style="border-top: 4px solid #dc2626; padding: 12px 16px;">
                            <div class="fin-label text-truncate" style="color: #dc2626;">Expenditure</div>
                            <div class="fin-val-lg" style="font-size: 1.35rem; color: #dc2626;">Rs. {{ number_format($head->expenditure ?? 0) }}</div>
                            <small class="text-muted font-weight-bold">{{ ($head->allocation ?? 0) > 0 ? round((($head->expenditure ?? 0) / $head->allocation) * 100, 1) : 0 }}% Spent</small>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                        <div class="fin-stat-card h-100" style="border-top: 4px solid #d97706; padding: 12px 16px;">
                            <div class="fin-label text-truncate" style="color: #d97706;">Commitments</div>
                            <div class="fin-val-lg" style="font-size: 1.35rem; color: #d97706;">Rs. {{ number_format($head->commitments ?? 0) }}</div>
                            <small class="text-muted font-weight-bold">Active Orders</small>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                        <div class="fin-stat-card h-100" style="border-top: 4px solid #64748b; padding: 12px 16px;">
                            <div class="fin-label text-truncate" style="color: #64748b;">In Process</div>
                            <div class="fin-val-lg" style="font-size: 1.35rem; color: #64748b;">Rs. {{ number_format($head->in_process ?? 0) }}</div>
                            <small class="text-muted font-weight-bold">Pending Approval</small>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                        <div class="fin-stat-card h-100" style="border-top: 4px solid #16a34a; padding: 12px 16px;">
                            <div class="fin-label text-truncate" style="color: #16a34a;">Spendable</div>
                            <div class="fin-val-lg" style="font-size: 1.35rem; color: #16a34a;">Rs. {{ number_format($head->pcc_can_be_spent ?? ($head->remaining ?? 0)) }}</div>
                            <small class="text-muted font-weight-bold">Net Remaining</small>
                        </div>
                    </div>
                </div>

                {{-- ROW 1: MACRO CASH FLOW & BUDGET STATUS DOUGHNUT --}}
                <div class="row">
                    <div class="col-xl-7 col-lg-12 mb-4">
                        <div class="fin-table-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="font-weight-bold text-dark mb-0 rajdhani" style="letter-spacing: 1px;">
                                    <i class="fas fa-chart-bar text-primary mr-2"></i> MACRO CASH FLOW & FINANCIAL BALANCE
                                </h5>
                                <span class="badge badge-light border text-primary px-2.5 py-1 font-weight-bold">
                                    Inflow vs Outflow
                                </span>
                            </div>
                            <div style="height: 310px; position: relative;">
                                <canvas id="finDetailedChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-5 col-lg-12 mb-4">
                        <div class="fin-table-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="font-weight-bold text-dark mb-0 rajdhani" style="letter-spacing: 1px;">
                                    <i class="fas fa-chart-pie text-success mr-2"></i> BUDGET UTILIZATION BREAKDOWN
                                </h5>
                                <span class="badge badge-light border text-success px-2.5 py-1 font-weight-bold">
                                    Allocation %
                                </span>
                            </div>
                            <div style="height: 310px; position: relative;">
                                <canvas id="budgetUtilizationDoughnutChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ROW 2: SUBHEADS MULTI-BAR COMPARISON & EXPENDITURE SHARE PIE --}}
                <div class="row">
                    <div class="col-xl-8 col-lg-12 mb-4">
                        <div class="fin-table-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="font-weight-bold text-dark mb-0 rajdhani" style="letter-spacing: 1px;">
                                    <i class="fas fa-layer-group text-primary mr-2"></i> SUBHEADS MULTI-DIMENSIONAL COMPARISON
                                </h5>
                                <span class="badge badge-light border text-info px-2.5 py-1 font-weight-bold">
                                    Alloc vs Spent vs Commit vs Remaining
                                </span>
                            </div>
                            <div style="height: 330px; position: relative;">
                                <canvas id="subheadComparisonChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-12 mb-4">
                        <div class="fin-table-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="font-weight-bold text-dark mb-0 rajdhani" style="letter-spacing: 1px;">
                                    <i class="fas fa-chart-pie text-danger mr-2"></i> SUBHEAD EXPENDITURE SHARE
                                </h5>
                                <span class="badge badge-light border text-danger px-2.5 py-1 font-weight-bold">
                                    Spent Distribution
                                </span>
                            </div>
                            <div style="height: 330px; position: relative;">
                                <canvas id="subheadExpenditurePieChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ROW 3: FUNDING SHARES & SUBHEAD UTILIZATION RATES --}}
                <div class="row">
                    <div class="col-xl-6 col-lg-12 mb-4">
                        <div class="fin-table-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="font-weight-bold text-dark mb-0 rajdhani" style="letter-spacing: 1px;">
                                    <i class="fas fa-university text-warning mr-2"></i> MULTI-SCOPE FUNDING SHARES
                                </h5>
                                <span class="badge badge-light border text-warning px-2.5 py-1 font-weight-bold">
                                    MTSS vs RDW vs CSRF
                                </span>
                            </div>
                            <div style="height: 290px; position: relative;">
                                <canvas id="fundingSharesChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 col-lg-12 mb-4">
                        <div class="fin-table-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="font-weight-bold text-dark mb-0 rajdhani" style="letter-spacing: 1px;">
                                    <i class="fas fa-tasks text-success mr-2"></i> SUBHEAD BUDGET UTILIZATION RATES (%)
                                </h5>
                                <span class="badge badge-light border text-success px-2.5 py-1 font-weight-bold">
                                    Burn Rates %
                                </span>
                            </div>
                            <div style="height: 290px; position: relative;">
                                <canvas id="subheadUtilizationBarChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        @else
        <div class="fin-table-card p-5 text-center my-4">
            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
            <h4 class="text-dark font-weight-bold rajdhani">No Financial Head Linked</h4>
            <p class="text-muted mb-0 font-weight-bold">This project is not linked to an active financial head in the CEN database yet.</p>
        </div>
        @endif

    </div>

    {{-- OFFICIAL PRINTABLE DOCUMENT CONTAINER (Rendered exclusively when printing) --}}
    @if($head)
    <div class="print-document-container">
        @include('projects.partials.printable_financial_summary')
    </div>
    @endif

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-activate tab from URL hash (e.g. #tab-milestones, #tab-docs)
    var hash = window.location.hash;
    if (hash) {
        var tabLink = document.querySelector('a[href="' + hash + '"]');
        if (tabLink) {
            // Use jQuery if available (Bootstrap 4 tabs)
            if (typeof $ !== 'undefined') {
                $(tabLink).tab('show');
            } else {
                tabLink.click();
            }
            // Scroll to top after tab switch
            setTimeout(function() {
                var tabContent = document.querySelector(hash);
                if (tabContent) tabContent.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 200);
        }
    }
    @if($head)
    const head = @json($head);
    const subheads = @json($subheads);
    let finChartsRendered = false;

    function renderAllFinCharts() {
        if (finChartsRendered) return;
        finChartsRendered = true;

        // 1. MAIN DETAILED CASH FLOW BAR CHART
        const canvasMain = document.getElementById('finDetailedChart');
        if (canvasMain) {
            const ctxMain = canvasMain.getContext('2d');
            new Chart(ctxMain, {
                type: 'bar',
                data: {
                    labels: ['Received (Inflow)', 'Expenditure (Spent)', 'Commitments (Active)', 'Remaining (Spendable)'],
                    datasets: [{
                        label: 'PKR Amount',
                        data: [
                            head.received || 0,
                            head.expenditure || 0,
                            head.commitments || 0,
                            head.pcc_can_be_spent || head.remaining || 0
                        ],
                        backgroundColor: ['#0284c7', '#dc2626', '#d97706', '#16a34a'],
                        borderColor: ['#0369a1', '#b91c1c', '#b45309', '#15803d'],
                        borderWidth: 1.5,
                        barThickness: 38
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { display: false },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return ' Rs. ' + Number(tooltipItem.yLabel || tooltipItem.value).toLocaleString();
                            }
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                fontColor: '#475569',
                                fontFamily: "'Rajdhani', sans-serif",
                                callback: function(value) {
                                    return 'Rs. ' + (value >= 1000000 ? (value/1000000).toFixed(1) + 'M' : (value/1000).toFixed(0) + 'k');
                                }
                            },
                            gridLines: { color: '#e2e8f0' }
                        }],
                        xAxes: [{
                            gridLines: { display: false },
                            ticks: { fontColor: '#1e293b', fontFamily: "'Rajdhani', sans-serif", fontSize: 12 }
                        }]
                    }
                }
            });
        }

        // 2. BUDGET UTILIZATION BREAKDOWN (DOUGHNUT CHART)
        const canvasDoughnut = document.getElementById('budgetUtilizationDoughnutChart');
        if (canvasDoughnut) {
            const ctxDoughnut = canvasDoughnut.getContext('2d');
            const totalAlloc = Number(head.allocation || 0);
            const spentVal = Number(head.expenditure || 0);
            const cmtVal = Number(head.commitments || 0);
            const ipcVal = Number(head.in_process || 0);
            const remVal = Math.max(0, Number(head.pcc_can_be_spent || head.remaining || 0));

            new Chart(ctxDoughnut, {
                type: 'doughnut',
                data: {
                    labels: ['Expenditure (Spent)', 'Commitments (Reserved)', 'In Process (Pending)', 'Spendable Balance'],
                    datasets: [{
                        data: [spentVal, cmtVal, ipcVal, remVal],
                        backgroundColor: ['#dc2626', '#d97706', '#64748b', '#16a34a'],
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        position: 'bottom',
                        labels: { fontColor: '#1e293b', fontFamily: "'Rajdhani', sans-serif", fontSize: 11, boxWidth: 14 }
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                const val = data.datasets[0].data[tooltipItem.index];
                                const pct = totalAlloc > 0 ? ((val / totalAlloc) * 100).toFixed(1) : 0;
                                return ' ' + data.labels[tooltipItem.index] + ': Rs. ' + Number(val).toLocaleString() + ' (' + pct + '%)';
                            }
                        }
                    },
                    cutoutPercentage: 62
                }
            });
        }

        // 3. SUBHEADS COMPARISON MULTI-BAR CHART
        const canvasSub = document.getElementById('subheadComparisonChart');
        if (canvasSub && Array.isArray(subheads) && subheads.length > 0) {
            const ctxSub = canvasSub.getContext('2d');
            const shLabels = subheads.map(s => s.name || 'N/A');
            const shAllocData = subheads.map(s => Number(s.allocation || 0));
            const shExpData = subheads.map(s => Number(s.expenditure || 0));
            const shCmtData = subheads.map(s => Number(s.commitments || 0));
            const shRemData = subheads.map(s => Math.max(0, Number(s.can_be_spent || s.remaining || 0)));

            new Chart(ctxSub, {
                type: 'bar',
                data: {
                    labels: shLabels,
                    datasets: [
                        {
                            label: 'Allocation',
                            data: shAllocData,
                            backgroundColor: '#0284c7',
                            borderColor: '#0369a1',
                            borderWidth: 1
                        },
                        {
                            label: 'Expenditure',
                            data: shExpData,
                            backgroundColor: '#dc2626',
                            borderColor: '#b91c1c',
                            borderWidth: 1
                        },
                        {
                            label: 'Commitments',
                            data: shCmtData,
                            backgroundColor: '#d97706',
                            borderColor: '#b45309',
                            borderWidth: 1
                        },
                        {
                            label: 'Can Be Spent',
                            data: shRemData,
                            backgroundColor: '#16a34a',
                            borderColor: '#15803d',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        position: 'top',
                        labels: { fontColor: '#1e293b', fontFamily: "'Rajdhani', sans-serif", fontSize: 12, boxWidth: 12 }
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                const datasetLabel = data.datasets[tooltipItem.datasetIndex].label || '';
                                return ' ' + datasetLabel + ': Rs. ' + Number(tooltipItem.yLabel).toLocaleString();
                            }
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                fontColor: '#475569',
                                fontFamily: "'Rajdhani', sans-serif",
                                callback: function(value) {
                                    return 'Rs. ' + (value >= 1000000 ? (value/1000000).toFixed(1) + 'M' : (value/1000).toFixed(0) + 'k');
                                }
                            },
                            gridLines: { color: '#e2e8f0' }
                        }],
                        xAxes: [{
                            gridLines: { display: false },
                            ticks: { fontColor: '#1e293b', fontFamily: "'Rajdhani', sans-serif", fontSize: 12 }
                        }]
                    }
                }
            });
        }

        // 4. SUBHEAD EXPENDITURE SHARE (PIE CHART)
        const canvasPie = document.getElementById('subheadExpenditurePieChart');
        if (canvasPie && Array.isArray(subheads) && subheads.length > 0) {
            const ctxPie = canvasPie.getContext('2d');
            const filteredSh = subheads.filter(s => Number(s.expenditure || 0) > 0);
            const targetList = filteredSh.length > 0 ? filteredSh : subheads;
            const pLabels = targetList.map(s => s.name || 'N/A');
            const pData = targetList.map(s => Number(s.expenditure || 0));
            const pColors = ['#0284c7', '#dc2626', '#d97706', '#16a34a', '#8b5cf6', '#06b6d4', '#ec4899', '#f97316'];

            new Chart(ctxPie, {
                type: 'pie',
                data: {
                    labels: pLabels,
                    datasets: [{
                        data: pData,
                        backgroundColor: pColors.slice(0, pLabels.length),
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        position: 'bottom',
                        labels: { fontColor: '#1e293b', fontFamily: "'Rajdhani', sans-serif", fontSize: 11, boxWidth: 12 }
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                const val = data.datasets[0].data[tooltipItem.index];
                                const sum = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                const pct = sum > 0 ? ((val / sum) * 100).toFixed(1) : 0;
                                return ' ' + data.labels[tooltipItem.index] + ': Rs. ' + Number(val).toLocaleString() + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            });
        }

        // 5. FUNDING SHARES DISTRIBUTION
        const canvasFund = document.getElementById('fundingSharesChart');
        if (canvasFund) {
            const ctxFund = canvasFund.getContext('2d');
            new Chart(ctxFund, {
                type: 'doughnut',
                data: {
                    labels: ['MTSS Share', 'RDW Net Share', 'CSRF Share'],
                    datasets: [{
                        data: [
                            Number(head.mtss_share || 0),
                            Number(head.rdw_share || 0),
                            Number(head.csrf_share || 0)
                        ],
                        backgroundColor: ['#dc2626', '#16a34a', '#d97706'],
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        position: 'bottom',
                        labels: { fontColor: '#1e293b', fontFamily: "'Rajdhani', sans-serif", fontSize: 11, boxWidth: 14 }
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                const val = data.datasets[0].data[tooltipItem.index];
                                const sum = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                const pct = sum > 0 ? ((val / sum) * 100).toFixed(1) : 0;
                                return ' ' + data.labels[tooltipItem.index] + ': Rs. ' + Number(val).toLocaleString() + ' (' + pct + '%)';
                            }
                        }
                    },
                    cutoutPercentage: 55
                }
            });
        }

        // 6. SUBHEAD UTILIZATION HORIZONTAL BAR CHART
        const canvasUtil = document.getElementById('subheadUtilizationBarChart');
        if (canvasUtil && Array.isArray(subheads) && subheads.length > 0) {
            const ctxUtil = canvasUtil.getContext('2d');
            const uLabels = subheads.map(s => s.name || 'N/A');
            const uPercentages = subheads.map(s => {
                const alloc = Number(s.allocation || 0);
                const exp = Number(s.expenditure || 0);
                return alloc > 0 ? Number(((exp / alloc) * 100).toFixed(1)) : 0;
            });
            const uColors = uPercentages.map(p => p > 90 ? '#dc2626' : (p > 60 ? '#d97706' : '#16a34a'));

            new Chart(ctxUtil, {
                type: 'horizontalBar',
                data: {
                    labels: uLabels,
                    datasets: [{
                        label: 'Utilization %',
                        data: uPercentages,
                        backgroundColor: uColors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { display: false },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return ' ' + tooltipItem.xLabel + '% Utilized';
                            }
                        }
                    },
                    scales: {
                        xAxes: [{
                            ticks: {
                                beginAtZero: true,
                                max: Math.max(100, Math.ceil(Math.max(...uPercentages, 100) / 10) * 10),
                                fontColor: '#475569',
                                fontFamily: "'Rajdhani', sans-serif",
                                callback: function(val) { return val + '%'; }
                            },
                            gridLines: { color: '#e2e8f0' }
                        }],
                        yAxes: [{
                            gridLines: { display: false },
                            ticks: { fontColor: '#1e293b', fontFamily: "'Rajdhani', sans-serif", fontSize: 12 }
                        }]
                    }
                }
            });
        }
    }

    // Tab Activation Listener for Charts
    $('a[data-toggle="pill"]').on('shown.bs.tab', function(e) {
        if (e.target.getAttribute('href') === '#tab-charts') {
            renderAllFinCharts();
        }
    });

    if (window.location.hash === '#tab-charts' || $('#tab-charts').hasClass('active')) {
        setTimeout(renderAllFinCharts, 250);
    }
    @endif

    // AJAX Form Upload for Project Financial View Attachments
    const uploadForm = document.getElementById('formUploadProjectFinAttachment');
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = document.getElementById('btnSubmitFinAttachment');
            const origHtml = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span> Uploading...';
            }

            const formData = new FormData(this);
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = origHtml;
                }
                $('#modalUploadProjectFinAttachment').modal('hide');
                uploadForm.reset();

                if (data.success) {
                    // Update table
                    const tbody = document.getElementById('finProjectAttachmentsTbody');
                    const noRow = document.getElementById('noAttachmentsRow');
                    if (noRow) noRow.remove();

                    const rowCount = tbody ? tbody.querySelectorAll('tr').length + 1 : 1;
                    const docType = formData.get('doc_type') || 'Attachment';
                    const fileInput = document.getElementById('fileFinAttachmentInput');
                    const fileName = fileInput && fileInput.files[0] ? fileInput.files[0].name : 'Document File';
                    const fileUrl = data.url || '#';
                    const ext = (fileName.split('.').pop() || '').toLowerCase();
                    let iconClass = 'fa-file-alt text-info';
                    if (ext === 'pdf') iconClass = 'fa-file-pdf text-danger';
                    else if (['doc', 'docx'].includes(ext)) iconClass = 'fa-file-word text-primary';
                    else if (['xls', 'xlsx'].includes(ext)) iconClass = 'fa-file-excel text-success';
                    else if (['jpg', 'jpeg', 'png'].includes(ext)) iconClass = 'fa-file-image text-warning';

                    const newRow = `
                        <tr style="background: #f0fdf4;">
                            <td class="pl-3 font-weight-bold text-primary">${rowCount}</td>
                            <td class="font-weight-bold text-dark">
                                <i class="fas ${iconClass} fa-lg mr-2"></i>
                                <span class="doc-type-text">${docType}</span>
                            </td>
                            <td class="text-muted font-weight-bold font-mono" style="font-size: 0.85rem;">
                                ${fileName}
                            </td>
                            <td class="text-dark font-weight-bold" style="font-size: 0.85rem;">
                                Just now
                            </td>
                            <td class="pr-3 text-center">
                                <a href="${fileUrl}" onclick="window.openLiveDocument('${fileUrl}', '${docType.replaceAll("'", "\\'")}'); return false;" class="rd-live-file-view btn btn-xs btn-primary font-weight-bold px-2.5 py-1 rounded shadow-sm" title="View Document Live">
                                    <i class="fas fa-eye mr-1"></i> View File
                                </a>
                            </td>
                        </tr>
                    `;

                    if (tbody) tbody.insertAdjacentHTML('afterbegin', newRow);

                    // Update count badges
                    const badge = document.getElementById('projectAttCountBadge');
                    if (badge) {
                        badge.textContent = (rowCount) + ' Document(s) Available';
                    }
                    const pillLink = document.getElementById('tab-docs-link');
                    if (pillLink) {
                        pillLink.innerHTML = `<i class="fas fa-paperclip"></i> Files & Attachments (${rowCount})`;
                    }

                    if (window.Swal) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: data.message || 'File uploaded successfully!',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    } else {
                        alert(data.message || 'File uploaded successfully!');
                    }
                } else {
                    throw new Error(data.message || 'Upload failed');
                }
            })
            .catch(err => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = origHtml;
                }
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Upload Failed',
                        text: err.message || 'Could not upload attachment. Please try again.'
                    });
                } else {
                    alert('Upload failed: ' + (err.message || 'Please try again.'));
                }
            });
        });
    }
});
</script>
@endpush
@endsection
