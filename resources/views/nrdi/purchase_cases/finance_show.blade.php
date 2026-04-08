@extends('welcome')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600&display=swap');
.case-page { font-family: 'Inter', sans-serif; background: #080b0f; min-height: 100vh; color: #cbd5e0; }
.rajdhani { font-family: 'Rajdhani', sans-serif; letter-spacing: 0.5px; }
.glass-card { background: #0d1218; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; }
.card-title-bar { background: #0f161e; border-bottom: 1px solid rgba(255,255,255,0.05); padding: 12px 20px; border-radius: 12px 12px 0 0; }
.rd-table th { font-family: 'Rajdhani', sans-serif; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #8a96a3; background: #0f161e; }
.rd-table td { font-size: 12px; vertical-align: middle; color: #cbd5e0; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 8px 12px; }
.label-sm { font-family: 'Rajdhani', sans-serif; font-size: 10px; color: #8a96a3; pointer-events: none; }
.value-sm { color: #fff; font-weight: 600; font-size: 13px; }
</style>

<div class="content-wrapper case-page pt-3">
    <!-- Header Area -->
    <div class="px-4 py-3 mb-2" style="background: linear-gradient(to bottom, #0f161e, transparent);">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge badge-success px-3 py-1 mb-2 rajdhani" style="font-size: 10px; background: rgba(40,167,69,0.1); color: #28a745;">FINANCIAL REVIEW PHASE</span>
                        <span class="badge badge-dark text-muted ml-2 mb-2" style="font-size: 10px; border: 1px solid rgba(255,255,255,0.05);">REF: #{{ $purchase->pcs_id }}</span>
                    </div>
                    <h2 class="font-weight-bold text-white rajdhani mt-1 mb-1" style="font-size: 1.8rem;">{{ $purchase->pcs_title }}</h2>
                    <div class="text-muted small">
                        <i class="fas fa-university mr-1"></i> FROM: <strong>{{ $divisionName }}</strong> 
                        <span class="mx-2">|</span>
                        <i class="fas fa-search mr-1"></i> SCRUTINIZED BY: <strong>Director Procurement</strong>
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <div class="glass-card d-inline-block p-3" style="border-right: 4px solid #28a745;">
                        <div class="label-sm text-right">Budget Impact</div>
                        <div class="text-white rajdhani font-weight-bold" style="font-size: 24px;">PKR {{ number_format($purchase->pcs_price) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid px-4">
        <div class="row">
            {{-- Left Column: Details --}}
            <div class="col-md-8">
                <!-- Financial Context -->
                <div class="glass-card mb-4 overflow-hidden border-top-success">
                    <div class="card-title-bar d-flex justify-content-between" style="background: rgba(40,167,69,0.05);">
                        <span class="rajdhani text-white"><i class="fas fa-file-invoice-dollar mr-2 text-success"></i> Comprehensive Budget Head Analysis</span>
                        @if($head)
                            <span class="small text-muted rajdhani">HEAD BALANCE: <span class="text-success font-weight-bold">PKR {{ number_format($head->hed_balance) }}</span></span>
                        @endif
                    </div>
                    <div class="card-body py-3">
                        <div class="row">
                            <div class="col-md-4"><label class="label-sm">Budget Head Code</label><div class="value-sm">{{ $purchase->project->prj_code ?? 'CENTRAL' }}</div></div>
                            <div class="col-md-4"><label class="label-sm">Approved Project Cost</label><div class="value-sm">{{ number_format($purchase->project->prj_cost ?? 0) }}</div></div>
                            <div class="col-md-4"><label class="label-sm">Utilization After Approval</label><div class="value-sm text-success">{{ number_format(($head->hed_balance ?? 0) - $purchase->pcs_price) }} (Projected)</div></div>
                        </div>
                    </div>
                </div>

                <!-- Itemization -->
                <div class="glass-card mb-4">
                    <div class="card-title-bar d-flex justify-content-between align-items-center">
                        <span class="rajdhani text-white"><i class="fas fa-boxes mr-2 text-success"></i> Financial Item Specification</span>
                        <button class="btn btn-outline-light btn-xs rajdhani px-3" data-toggle="modal" data-target="#detailedCSModal">SHEET VIEW</button>
                    </div>
                    <div class="table-responsive">
                        <table class="rd-table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th class="pl-4">#</th>
                                    <th>Service/Goods Description</th>
                                    <th class="text-right">Qty</th>
                                    <th class="text-right">Rate</th>
                                    <th class="text-right pr-4">Total Amt</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->items as $item)
                                <tr>
                                    <td class="pl-4">{{ $item->pci_serial }}</td>
                                    <td>{{ Str::limit($item->pci_desc, 60) }}</td>
                                    <td class="text-right font-weight-bold">{{ $item->pci_qty }}</td>
                                    <td class="text-right">{{ number_format($item->pci_price) }}</td>
                                    <td class="text-right pr-4 font-weight-bold text-white">{{ number_format($item->pci_qty * $item->pci_price) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Decision Trace -->
                <div class="glass-card mb-4">
                    <div class="card-title-bar">
                        <span class="rajdhani text-white"><i class="fas fa-history mr-2 text-success"></i> Workflow Progression History</span>
                    </div>
                    <div class="p-3" style="max-height: 400px; overflow-y: auto;">
                        @include('approvals._decision_trail')
                    </div>
                </div>
            </div>

            {{-- Right Column: Action Box --}}
            <div class="col-md-4">
                {{-- Financial Action Box --}}
                <div class="glass-card mb-4" style="border: 2px solid rgba(40, 167, 69, 0.4);">
                    <div class="card-title-bar" style="background: rgba(40, 167, 69, 0.1);">
                        <span class="rajdhani text-white font-weight-bold"><i class="fas fa-check-circle mr-2 text-success"></i> Financial Review Command</span>
                    </div>
                    <div class="card-body">
                        @include('approvals._action_box')
                    </div>
                </div>

                {{-- Attachments --}}
                <div class="glass-card">
                    <div class="card-title-bar"><span class="rajdhani text-white"><i class="fas fa-paperclip mr-2 text-success"></i> Supporting Documents</span></div>
                    <div class="p-3">
                        @forelse($purchase->attachments as $file)
                            <div class="d-flex align-items-center mb-2 p-2 rounded" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                                <i class="far fa-file-pdf text-danger mr-3" style="font-size: 20px;"></i>
                                <div class="flex-grow-1 overflow-hidden"><div class="small font-weight-bold text-white">{{ $file->pat_filename }}</div></div>
                                <a href="{{ url('storage/'.$file->pat_path) }}" target="_blank" class="btn btn-xs btn-outline-success ml-2"><i class="fas fa-download"></i></a>
                            </div>
                        @empty
                            <div class="text-center py-3 text-muted small">No attachments uploaded.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Confirmation Modals Reused --}}
@include('purchase.initiation.partials.modals')

@endsection
