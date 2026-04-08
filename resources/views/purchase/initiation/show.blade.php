@extends('welcome')

@section('content')
<div class="content-wrapper case-page pt-3">
    {{-- Top Header Section --}}
    <div class="px-4 py-3 mb-2" style="background: linear-gradient(to bottom, #0f161e, transparent);">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge badge-dark text-muted mr-2" style="font-size: 10px; border: 1px solid rgba(255,255,255,0.05);">PC REFERENCE: #{{ $purchase->pcs_id }}</span>
                        <span class="status-pill {{ $canEdit ? 'bg-status-warning' : 'bg-status-primary' }}">
                            <i class="fas fa-{{ $canEdit ? 'pen-nib' : 'lock' }} mr-1"></i> {{ strtoupper($purchase->pcs_status) }}
                        </span>
                    </div>
                    @if($canEdit)
                        <form action="{{ route('purchase.update_core', $purchase->pcs_id) }}" method="POST">
                            @csrf
                            <div class="form-group mb-2">
                                <input type="text" name="pcs_title" class="form-control form-control-sm bg-dark text-white border-primary" value="{{ $purchase->pcs_title }}" style="font-size: 1.5rem; font-weight: bold;">
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <button type="submit" class="btn btn-primary btn-xs px-3">SAVE CHANGES</button>
                                <span class="text-muted small ml-2">Editing enabled (Draft Mode)</span>
                            </div>
                        </form>
                    @else
                        <h2 class="font-weight-bold text-white rajdhani mb-1" style="font-size: 1.8rem;">{{ $purchase->pcs_title }}</h2>
                    @endif
                    <div class="text-muted small">
                        <i class="fas fa-project-diagram mr-1"></i> {{ $purchase->project->prj_title ?? 'General Operational Expenditure' }}
                        <span class="mx-2">|</span>
                        <i class="fas fa-calendar-alt mr-1"></i> Initiated on {{ \Carbon\Carbon::parse($purchase->pcs_date)->format('d M, Y') }}
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    @if($purchase->pcs_status == 'Under Scrutiny')
                        <form action="{{ route('purchase.hold', $purchase->pcs_id) }}" method="POST" class="d-inline-block mr-2">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-sm rounded-pill px-4 rajdhani font-weight-bold shadow-sm text-dark">
                                <i class="fas fa-hand-paper mr-1"></i> HOLD CASE (EDIT)
                            </button>
                        </form>
                    @endif
                    <div class="glass-card d-inline-block p-3">
                        <div class="metric-label text-right" style="font-size: 9px;">Estimated Volume</div>
                        <div class="text-white rajdhani font-weight-bold" style="font-size: 24px;">PKR {{ number_format($purchase->pcs_price) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4">
        @if($canEdit)
            <div class="alert alert-warning border-0 px-4 py-3 d-flex align-items-center mb-4" style="background: rgba(243,156,18,0.1); border-left: 4px solid #f39c12 !important;">
                <i class="fas fa-info-circle mr-3" style="font-size: 24px;"></i>
                <div>
                    <h6 class="mb-0 font-weight-bold rajdhani text-warning">DRAFT / RETURNED MODE</h6>
                    <p class="mb-0 text-muted small">You can edit items and quotations. Headquarters will NOT review this case until you click <strong>'Release to DProc'</strong>.</p>
                </div>
            </div>
        @endif

        <div class="row">
            {{-- Left Column: Core Data --}}
            <div class="col-md-8">
                {{-- Financial Overview --}}
                <div class="glass-card mb-4" style="border-left: 4px solid var(--rd-primary);">
                    <div class="card-title-bar d-flex justify-content-between align-items-center">
                        <span class="rajdhani text-white"><i class="fas fa-wallet mr-2 text-primary"></i> Financial Picture & Head Status</span>
                        @if(isset($head))
                            <span class="small rajdhani" style="color: #64748b;">CURRENT LEDGER BALANCE: <span class="text-success font-weight-bold ml-1" style="font-size: 14px;">PKR {{ number_format($head->hed_balance) }}</span></span>
                        @else
                            <span class="small text-danger italic">Budget data unavailable</span>
                        @endif
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="small text-muted font-weight-bold mb-1">ALLOCATED HEAD</label>
                                <div class="text-white rajdhani font-weight-bold" style="font-size: 0.85rem;">{{ $purchase->project->prj_name ?? 'Operational Head' }}</div>
                            </div>
                            <div class="col-md-3">
                                <label class="small text-muted font-weight-bold mb-1">TOTAL BUDGET</label>
                                <div class="text-white rajdhani font-weight-bold" style="font-size: 0.85rem;">PKR {{ number_format($purchase->project->prj_cost ?? 0) }}</div>
                            </div>
                            <div class="col-md-3">
                                <label class="small text-muted font-weight-bold mb-1">CASE TYPE</label>
                                <div class="badge badge-primary px-3 py-1 mt-1 rajdhani" style="border-radius: 6px; font-size: 10px;">{{ strtoupper($purchase->pcs_type) }}</div>
                            </div>
                            <div class="col-md-3">
                                <label class="small text-muted font-weight-bold mb-1">CASE PRIORITY</label>
                                <div class="text-warning rajdhani font-weight-bold" style="font-size: 0.85rem;">NORMAL</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Quotations Table --}}
                <div class="glass-card mb-4 overflow-hidden">
                    <div class="card-title-bar d-flex justify-content-between align-items-center">
                        <span class="rajdhani text-white"><i class="fas fa-list-ol mr-2 text-primary"></i> Quotation Comparison (Comparative Statement)</span>
                        <div>
                            @if($canEdit)
                                <button class="btn btn-primary btn-xs rajdhani px-3" data-toggle="modal" data-target="#addQuoteModal"><i class="fas fa-plus mr-1"></i> ADD VENDOR</button>
                            @endif
                            <button class="btn btn-outline-light btn-xs rajdhani px-3" data-toggle="modal" data-target="#detailedCSModal"><i class="fas fa-expand-arrows-alt mr-1"></i> FULL SHEET</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="rd-table">
                            <thead>
                                <tr>
                                    <th class="pl-4">Firm Name</th>
                                    <th class="text-right">Price (PKR)</th>
                                    <th class="text-center">Technical</th>
                                    <th class="text-center">L-Ranking</th>
                                    <th class="text-right pr-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchase->quotes->sortBy('qte_price') as $q)
                                <tr>
                                    <td class="pl-4 font-weight-bold text-white">{{ $q->firm->frm_name ?? $q->qte_firmname }}</td>
                                    <td class="text-right font-weight-bold text-primary">Rs. {{ number_format($q->qte_price) }}</td>
                                    <td class="text-center"><span class="badge badge-success px-2 py-1" style="font-size: 9px; opacity: 0.8 !important;">YES</span></td>
                                    <td class="text-center">
                                        <span class="badge {{ $loop->first ? 'badge-success' : 'badge-secondary' }} px-3" style="font-size: 10px;">L{{ $loop->iteration }}</span>
                                    </td>
                                    <td class="text-right pr-4">
                                        @if($loop->first)
                                            <span class="text-success rajdhani small font-weight-bold">WINNER (L1)</span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center py-4 text-muted small">No quotations added yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Right Column: Items & Trail --}}
            <div class="col-md-4">
                 {{-- Decision Trail (HQ Style) --}}
                 @if($purchase->decisions->count() > 0)
                    <div class="glass-card mb-4">
                        <div class="card-title-bar">
                            <span class="rajdhani text-white"><i class="fas fa-history mr-2 text-primary"></i> Decision Trail</span>
                        </div>
                        <div class="p-1" style="max-height: 400px; overflow-y: auto;">
                            @include('approvals._decision_trail')
                        </div>
                    </div>
                @endif

                {{-- Items Summary --}}
                <div class="glass-card mb-4">
                    <div class="card-title-bar d-flex justify-content-between align-items-center">
                        <span class="rajdhani text-white"><i class="fas fa-boxes mr-2 text-primary"></i> Items Profile</span>
                        @if($canEdit)
                            <button class="btn btn-outline-light btn-xs rajdhani" data-toggle="modal" data-target="#addItemModal"><i class="fas fa-plus"></i></button>
                        @endif
                    </div>
                    <div class="p-0 overflow-hidden">
                        <table class="rd-table">
                            <thead>
                                <tr>
                                    <th class="pl-4">Description</th>
                                    <th class="text-right">Qty</th>
                                    <th class="text-right pr-4">Sub Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->items as $item)
                                <tr>
                                    <td class="pl-4 small">{{ Str::limit($item->pci_desc, 35) }}</td>
                                    <td class="text-right font-weight-bold">{{ $item->pci_qty }}</td>
                                    <td class="text-right pr-4 font-weight-bold text-white">{{ number_format($item->pci_qty * $item->pci_price) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Attachments --}}
                <div class="glass-card">
                    <div class="card-title-bar">
                        <span class="rajdhani text-white"><i class="fas fa-paperclip mr-2 text-primary"></i> Related Files</span>
                    </div>
                    <div class="p-3">
                        @forelse($purchase->attachments as $file)
                            <div class="d-flex align-items-center mb-2 p-2 rounded" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                                <i class="far fa-file-pdf text-danger mr-3" style="font-size: 20px;"></i>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="small font-weight-bold text-white text-nowrap" style="overflow: hidden; text-overflow: ellipsis;">{{ $file->pat_filename }}</div>
                                    <div class="text-xs text-muted">{{ \Carbon\Carbon::parse($file->created_at)->format('d M, Y') }}</div>
                                </div>
                                <a href="{{ url('storage/'.$file->pat_path) }}" target="_blank" class="btn btn-xs btn-outline-primary ml-2"><i class="fas fa-download"></i></a>
                            </div>
                        @empty
                            <div class="text-center py-3 text-muted small">No documents attached.</div>
                        @endforelse

                        @if($canEdit)
                            <button class="btn btn-outline-primary btn-block btn-sm rajdhani mt-2" data-toggle="modal" data-target="#caseAttachmentModal">
                                <i class="fas fa-plus mr-1"></i> ATTACH DOCUMENTS
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Action Bar --}}
    @if($canEdit)
        <div class="floating-action-bar">
            <div class="glass-card p-2 d-flex align-items-center shadow-lg" style="border-radius: 50px; background: #0f161e; border: 1px solid rgba(0, 123, 255, 0.4);">
                <button onclick="history.back()" class="btn btn-dark rounded-pill px-4 rajdhani mr-2" style="font-size: 11px;">
                    <i class="fas fa-chevron-left mr-1"></i> BACK TO DASHBOARD
                </button>
                
                <form action="{{ route('purchase.release', $purchase->pcs_id) }}" method="POST" id="mainReleaseForm">
                    @csrf
                    <div class="form-group mb-0 d-none">
                        <textarea name="remarks" id="releaseRemarks">Released by Division</textarea>
                    </div>
                    <button type="button" class="btn btn-primary rounded-pill px-5 rajdhani font-weight-bold shadow-lg" 
                            onclick="showReleaseConfirmation()" 
                            style="font-size: 14px; letter-spacing: 1px;">
                        <i class="fas fa-paper-plane mr-2 text-white"></i> RELEASE TO DPROC
                    </button>
                </form>
            </div>
        </div>
    @elseif($purchase->pcs_status == 'Under Scrutiny')
        <div class="text-center py-5">
             <button onclick="history.back()" class="btn btn-outline-primary rounded-pill px-4 rajdhani">
                <i class="fas fa-chevron-left mr-1"></i> RETURN TO DASHBOARD
            </button>
        </div>
    @else
        <div class="text-center py-5">
             <button onclick="history.back()" class="btn btn-outline-primary rounded-pill px-4 rajdhani">
                <i class="fas fa-chevron-left mr-1"></i> RETURN TO DASHBOARD
            </button>
        </div>
    @endif
</div>

{{-- Confirmation Modal --}}
<div class="modal fade" id="releaseConfirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card" style="border: 2px solid rgba(0, 123, 255, 0.4);">
            <div class="modal-header border-0">
                <h5 class="modal-title rajdhani text-white">Confirm Case Release</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p class="text-muted rajdhani" style="font-size: 14px;">You are about to release this case to HQ (Director Procurement) for formal scrutiny. Please provide your initiation remarks below:</p>
                <label class="text-warning">Your Official Remarks</label>
                <textarea id="modalRemarksInput" class="form-control form-control-rd mb-3" rows="4" placeholder="Enter reason for purchase or expert justification..."></textarea>
                
                <div class="alert alert-info border-0 shadow-sm rounded-lg d-flex align-items-center p-3" style="background: rgba(0,123,255,0.1);">
                    <i class="fas fa-lock mr-2 text-primary"></i>
                    <span class="small text-muted">Upon release, the case details will be <strong>locked</strong> until further action by HQ.</span>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-dark rajdhani px-4" data-dismiss="modal">CANCEL</button>
                <button type="button" class="btn btn-primary rajdhani px-5 font-weight-bold" onclick="executeFinalRelease()">
                    PROCEED & RELEASE
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Include Shared Modals (Add Vendor, Add Item, etc.) --}}
@include('purchase.initiation.partials.modals')

@endsection

@section('scripts')
<script>
    function showReleaseConfirmation() {
        $('#releaseConfirmModal').modal('show');
    }

    function executeFinalRelease() {
        const remarks = $('#modalRemarksInput').val();
        if(!remarks || remarks.trim().length < 5) {
            alert('Please provide detailed initiation remarks (at least 5 characters).');
            return;
        }
        
        // Hide modal and submit form
        $('#releaseConfirmModal').modal('hide');
        $('<input>').attr({
            type: 'hidden',
            name: 'remarks',
            value: remarks
        }).appendTo('#mainReleaseForm');
        
        $('#mainReleaseForm').submit();
    }
</script>
@endsection
