@extends('welcome')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600&display=swap');
.dg-page { font-family: 'Inter', sans-serif; background: #080b0f; min-height: 100vh; color: #cbd5e0; }
.rajdhani { font-family: 'Rajdhani', sans-serif; letter-spacing: 0.5px; }
.text-gold { color: #f39c12 !important; }
.bg-navy { background-color: #001f3f !important; }
.border-gold { border-top: 3px solid #f39c12 !important; }
.border-left-gold { border-left: 5px solid #f39c12 !important; }

/* ---- Page Header ---- */
.dg-hdr { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:18px; flex-wrap:wrap; gap:10px; }
.dg-back-btn { display:inline-flex; align-items:center; gap:6px; font-size:11px; color:var(--rd-text2); background:var(--rd-surface); border:1px solid var(--rd-border); padding:5px 13px; border-radius:20px; text-decoration:none !important; transition:all .2s; }
.dg-back-btn:hover { border-color:var(--rd-accent); color:var(--rd-accent); }
.dg-status-badge { font-size:10px; font-weight:700; padding:2px 10px; border-radius:20px; background:rgba(255,193,7,0.12); color:var(--rd-warning); border:1px solid rgba(255,193,7,0.28); letter-spacing:.5px; }

/* ---- Section labels ---- */
.dg-sec-label { font-family:'Rajdhani',sans-serif; font-size:11px; font-weight:700; letter-spacing:1.8px; color:var(--rd-accent); text-transform:uppercase; margin-bottom:10px; display:flex; align-items:center; gap:7px; }
.dg-sec-label::before { content:''; width:3px; height:12px; background:var(--rd-accent); border-radius:2px; display:inline-block; }

.glass-card { background: #0d1218; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; }
.card-title-bar { background: #0f161e; border-bottom: 1px solid rgba(255,255,255,0.05); padding: 12px 20px; border-radius: 12px 12px 0 0; }
</style>

<div class="content-wrapper dg-page pt-3">
    <div class="container-fluid px-4">
        {{-- Header Section --}}
        <div style="margin-bottom:20px; background: linear-gradient(to right, #0f172a, transparent); padding: 20px; border-radius: 12px;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="d-flex align-items-center">
                    <span class="badge badge-primary px-3 py-1 rajdhani mr-3" style="font-size: 10px; letter-spacing: 1px;">HQ SCRUTINY AUTHORTIY</span>
                    <a href="{{ route('nrdi.procurement.purchase_cases.index') }}" class="dg-back-btn">
                        <i class="fas fa-arrow-left mr-1"></i> HUB DASHBOARD
                    </a>
                </div>
                <div class="text-right">
                    <span class="dg-status-badge px-4 py-2" style="font-size: 12px;">{{ strtoupper($purchase->pcs_status) }}</span>
                </div>
            </div>
            
            <div class="row align-items-end">
                <div class="col-md-8">
                    <h1 class="rajdhani text-white font-weight-bold m-0" style="font-size: 2.4rem;">{{ $purchase->pcs_title }}</h1>
                    <div class="mt-2 text-muted rajdhani" style="font-size: 14px; letter-spacing: 0.5px;">
                        <span class="text-primary font-weight-bold">UNIT:</span> {{ $divisionName }} 
                        <span class="mx-3 text-dark">|</span>
                        <span class="text-primary font-weight-bold">HEAD:</span> {{ $purchase->project->prj_code ?? 'N/A' }}
                        <span class="mx-3 text-dark">|</span>
                        <span class="text-primary font-weight-bold">DATE:</span> {{ \Carbon\Carbon::parse($purchase->pcs_date)->format('d M, Y') }}
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <div class="glass-card d-inline-block px-4 py-2 shadow-lg" style="border: 2px solid rgba(255,255,255,0.05);">
                        <div class="rajdhani text-muted mb-0" style="font-size: 11px; letter-spacing: 1px; font-weight: 700;">TOTAL ESTIMATED COST</div>
                        <div class="text-white rajdhani font-weight-bold" style="font-size: 28px;">Rs. {{ number_format($purchase->pcs_price) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Left Column: Details & Financials --}}
            <div class="col-md-8">
                
                {{-- Financial Snapshot --}}
                <div class="glass-card mb-4" style="border-left: 4px solid var(--rd-primary);">
                    <div class="card-title-bar d-flex justify-content-between align-items-center">
                        <span class="rajdhani text-white"><i class="fas fa-chart-line mr-2 text-primary"></i> Budget Verification Profile</span>
                        @if(isset($head))
                            <span class="small rajdhani" style="color: #64748b;">CURRENT LEDGER BALANCE: <span class="text-success font-weight-bold ml-1" style="font-size: 14px;">PKR {{ number_format($head->hed_balance) }}</span></span>
                        @endif
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="small text-muted font-weight-bold mb-1 rajdhani">ALLOCATED HEAD</label>
                                <div class="text-white rajdhani font-weight-bold">{{ $purchase->project->prj_name ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-3 text-center">
                                <label class="small text-muted font-weight-bold mb-1 rajdhani">TOTAL ALLOCATION</label>
                                <div class="text-white rajdhani font-weight-bold">PKR {{ number_format($purchase->project->prj_cost ?? 0) }}</div>
                            </div>
                            <div class="col-md-3 text-center">
                                <label class="small text-muted font-weight-bold mb-1 rajdhani">CASE PRIORITY</label>
                                <div class="text-warning rajdhani font-weight-bold"><i class="fas fa-bolt mr-1"></i> NORMAL</div>
                            </div>
                            <div class="col-md-3 text-right">
                                <label class="small text-muted font-weight-bold mb-1 rajdhani">STATUS</label>
                                <div class="text-info rajdhani font-weight-bold">{{ strtoupper($purchase->pcs_status) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Items Table --}}
                <div class="glass-card mb-4 overflow-hidden">
                    <div class="card-title-bar d-flex justify-content-between align-items-center">
                        <span class="rajdhani text-white"><i class="fas fa-layer-group mr-2 text-primary"></i> Itemized Procurement Details</span>
                        <button class="btn btn-outline-primary btn-xs rajdhani px-3" data-toggle="modal" data-target="#detailedCSModal">
                            <i class="fas fa-balance-scale mr-1"></i> FULL COMPARATIVE STATEMENT
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0 dg-case-table" style="background: transparent;">
                            <thead style="background: rgba(255,255,255,0.02);">
                                <tr>
                                    <th class="pl-4 py-3" style="width: 50px;">#</th>
                                    <th class="py-3">Description of Item</th>
                                    <th class="py-3 text-center">Stock</th>
                                    <th class="py-3 text-center">Qty</th>
                                    <th class="py-3 text-right">Unit Price</th>
                                    <th class="py-3 text-right pr-4">Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->items as $item)
                                <tr>
                                    <td class="pl-4 text-muted">{{ $item->pci_serial }}</td>
                                    <td class="text-white font-weight-bold" style="white-space: normal; line-height: 1.4;">{{ $item->pci_desc }}</td>
                                    <td class="text-center text-muted">0</td>
                                    <td class="text-center font-weight-bold text-warning">{{ $item->pci_qty }}</td>
                                    <td class="text-right text-muted">{{ number_format($item->pci_price) }}</td>
                                    <td class="text-right font-weight-bold text-white pr-4">Rs. {{ number_format($item->pci_qty * $item->pci_price) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Decision Trail --}}
                <div class="glass-card mb-4">
                    <div class="card-title-bar">
                        <span class="rajdhani text-white"><i class="fas fa-history mr-2 text-primary"></i> Decision Flow & Scrutiny Path</span>
                    </div>
                    <div class="p-3">
                        @include('approvals._decision_trail')
                    </div>
                </div>
            </div>

            {{-- Right Column: Actions & Assets --}}
            <div class="col-md-4">
                {{-- Action Panel --}}
                <div style="position: sticky; top: 20px;">
                    @include('approvals._action_box')

                    {{-- Attachments --}}
                    <div class="glass-card mt-4 shadow-lg">
                        <div class="card-title-bar"><span class="rajdhani text-white"><i class="fas fa-paperclip mr-2 text-primary"></i> Substantiating Documents</span></div>
                        <div class="p-3">
                            @forelse($purchase->attachments as $file)
                                <div class="d-flex align-items-center mb-2 p-3 rounded" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                                    <div class="mr-3 text-danger"><i class="fas fa-file-pdf fa-2x"></i></div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="small font-weight-bold text-white mb-0" title="{{ $file->pat_filename }}">{{ Str::limit($file->pat_filename, 25) }}</div>
                                        <div class="text-muted" style="font-size: 8px;">SIZE: 2.1 MB</div>
                                    </div>
                                    <a href="{{ url('storage/'.$file->pat_path) }}" target="_blank" class="btn btn-outline-primary btn-xs shadow-inner">VIEW</a>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted rajdhani small italic"><i class="fas fa-folder-open mb-2 d-block"></i> No attachments uploaded.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Compare Statements & Other Modals --}}
@include('purchase.initiation.partials.modals')

@endsection
