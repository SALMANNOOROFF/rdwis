@extends('welcome')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap" style="gap: 15px;">
            <div>
                <ol class="breadcrumb float-sm-left bg-transparent p-0 mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reversals.open') }}">Reversals</a></li>
                    <li class="breadcrumb-item active">Case #{{ $rev->rev_id }}</li>
                </ol>
                <h1 class="m-0 font-weight-bold text-dark" style="font-family: 'Rajdhani', sans-serif;">
                    <i class="fas fa-file-invoice mr-2 text-primary"></i>Reversal Request #{{ $rev->rev_id }}
                    @if($rev->rev_status === 'Draft')
                        <span class="badge badge-secondary ml-2 font-weight-normal"><i class="fas fa-pencil-alt mr-1"></i>Draft</span>
                    @elseif($rev->rev_status === 'In Process')
                        <span class="badge badge-primary ml-2 font-weight-normal"><i class="fas fa-cog fa-spin mr-1"></i>In Process</span>
                    @elseif($rev->rev_status === 'Under Revision')
                        <span class="badge badge-warning ml-2 font-weight-normal"><i class="fas fa-undo mr-1"></i>Under Revision</span>
                    @elseif($rev->rev_status === 'Fulfilled')
                        <span class="badge badge-success ml-2 font-weight-normal"><i class="fas fa-check mr-1"></i>Fulfilled</span>
                    @elseif($rev->rev_status === 'Cancelled')
                        <span class="badge badge-danger ml-2 font-weight-normal"><i class="fas fa-times mr-1"></i>Cancelled</span>
                    @else
                        <span class="badge badge-light ml-2 font-weight-normal">{{ $rev->rev_status }}</span>
                    @endif
                </h1>
            </div>
            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                <a href="{{ route('admin.reversals.open') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Reversals
                </a>

                {{-- Action Buttons Gated Strictly via DataRevisionPolicy --}}
                @can('release', $rev)
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-toggle="modal" data-target="#releaseModal">
                        <i class="fas fa-paper-plane mr-1"></i> Release Case
                    </button>
                @endcan

                @can('execute', $rev)
                    <button type="button" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm" data-toggle="modal" data-target="#executeModal">
                        <i class="fas fa-cogs mr-1"></i> Execute Reversal
                    </button>
                @endcan

                @can('return', $rev)
                    <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 shadow-sm text-dark font-weight-bold" data-toggle="modal" data-target="#returnModal">
                        <i class="fas fa-undo mr-1"></i> Return Case
                    </button>
                @endcan

                @can('cancel', $rev)
                    <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm" data-toggle="modal" data-target="#cancelModal">
                        <i class="fas fa-times-circle mr-1"></i> Cancel Case
                    </button>
                @endcan
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            {{-- Flash Messages --}}
            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Action could not be completed:</strong>
                    <ul class="mb-0 mt-1 pl-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            {{-- Reversal Header & Metadata Card --}}
            @php
                $typeVal = $rev->rev_type instanceof \App\Enums\RevType ? $rev->rev_type->value : (int) $rev->rev_type;
            @endphp
            <div class="card card-outline card-primary shadow-sm mb-4">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-info-circle mr-1 text-primary"></i> Case Header Details (aud.revs)
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <label class="text-muted small text-uppercase font-weight-bold mb-1 d-block">Reversal ID</label>
                            <span class="font-weight-bold h5 text-dark">#{{ $rev->rev_id }}</span>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <label class="text-muted small text-uppercase font-weight-bold mb-1 d-block">Reversal Type</label>
                            @if($typeVal === 1)
                                <span class="badge badge-info px-2 py-1">Type 1 — Full Cascade Reversal</span>
                            @elseif($typeVal === 2)
                                <span class="badge badge-secondary px-2 py-1">Type 2 — Field-Level Revision</span>
                            @elseif($typeVal === 3)
                                <span class="badge badge-warning px-2 py-1">Type 3 — Linked Cascade Reversal</span>
                            @else
                                <span class="badge badge-light px-2 py-1">Type {{ $typeVal }}</span>
                            @endif
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <label class="text-muted small text-uppercase font-weight-bold mb-1 d-block">Target Object</label>
                            <span class="font-weight-bold text-dark">{{ $rev->rev_obj ?? '—' }}</span>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <label class="text-muted small text-uppercase font-weight-bold mb-1 d-block">Target Object ID</label>
                            <code>{{ $rev->rev_objid ?? '—' }}</code>
                        </div>
                    </div>

                    <hr class="my-2">

                    <div class="row pt-2">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <label class="text-muted small text-uppercase font-weight-bold mb-1 d-block">Current Status</label>
                            <span class="font-weight-bold">{{ $rev->rev_status }}</span>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <label class="text-muted small text-uppercase font-weight-bold mb-1 d-block">Target Unit</label>
                            <span class="font-weight-bold">{{ $rev->unit ? $rev->unit->unt_namesh . ' (' . $rev->unit->unt_name . ')' : $rev->rev_unt_id }}</span>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <label class="text-muted small text-uppercase font-weight-bold mb-1 d-block">Initiating Unit</label>
                            <span class="font-weight-bold">{{ $rev->initiatingUnit ? $rev->initiatingUnit->unt_namesh . ' (' . $rev->initiatingUnit->unt_name . ')' : ($rev->rev_intunt_id ?? '—') }}</span>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <label class="text-muted small text-uppercase font-weight-bold mb-1 d-block">Initiation Date</label>
                            <span>{{ $rev->rev_date ? \Carbon\Carbon::parse($rev->rev_date)->format('d-M-Y') : '—' }}</span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <label class="text-muted small text-uppercase font-weight-bold mb-1 d-block">Released Timestamp</label>
                            <span>{{ $rev->rev_releasedtg ? \Carbon\Carbon::parse($rev->rev_releasedtg)->format('d-M-Y H:i:s') : 'Not yet released' }}</span>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <label class="text-muted small text-uppercase font-weight-bold mb-1 d-block">Closed Timestamp</label>
                            <span>{{ $rev->rev_closedtg ? \Carbon\Carbon::parse($rev->rev_closedtg)->format('d-M-Y H:i:s') : 'Open / Unclosed' }}</span>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label class="text-muted small text-uppercase font-weight-bold mb-1 d-block">Audit Reason & Remarks</label>
                            <div class="bg-light p-2 rounded border" style="white-space: pre-wrap; font-family: monospace; font-size: 0.88rem; max-height: 120px; overflow-y: auto;">
                                {{ $rev->rev_reason ?? 'No reason recorded.' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Type 1 & 3: Component Reversal Breakdown (aud.revcomps) --}}
            @if($typeVal === 1 || $typeVal === 3)
                <div class="card card-outline card-info shadow-sm mb-4">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-layer-group mr-1 text-info"></i> Component Reversals Cascade Breakdown (aud.revcomps)
                        </h3>
                        <span class="badge badge-info float-right">{{ $rev->comps->count() }} components</span>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover table-striped table-sm mb-0">
                            <thead class="thead-light">
                            <tr>
                                <th style="width: 70px;">RVC ID</th>
                                <th>Action Code</th>
                                <th>Target Table</th>
                                <th>Row ID</th>
                                <th>Type</th>
                                <th>Snapshot / Row Detail</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($rev->comps as $comp)
                                <tr>
                                    <td><strong>{{ $comp->rvc_id }}</strong></td>
                                    <td><code>{{ $comp->rvc_action }}</code></td>
                                    <td><code>{{ $comp->rvc_table }}</code></td>
                                    <td><code>{{ $comp->rvc_rowid }}</code></td>
                                    <td><span class="badge badge-light">Type {{ $comp->rvc_type }}</span></td>
                                    <td style="max-width: 480px;">
                                        <div class="text-monospace small" style="white-space: pre-wrap; word-break: break-all; max-height: 80px; overflow-y: auto;">
                                            {{ $comp->rvc_detail }}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No component reversal records attached.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Type 2: Field-Level Data Revisions (aud.revdata) --}}
            @if($typeVal === 2)
                <div class="card card-outline card-secondary shadow-sm mb-4">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-edit mr-1 text-secondary"></i> Field-Level Data Revisions (aud.revdata)
                        </h3>
                        <span class="badge badge-secondary float-right">{{ $rev->data->count() }} field diffs</span>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover table-striped table-sm mb-0">
                            <thead class="thead-light">
                            <tr>
                                <th style="width: 70px;">RVD ID</th>
                                <th>Target Table</th>
                                <th>Row ID</th>
                                <th>Attribute / Column</th>
                                <th>Old Value</th>
                                <th>New Value</th>
                                <th>Operation / Conversion</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($rev->data as $item)
                                <tr>
                                    <td><strong>{{ $item->rvd_id }}</strong></td>
                                    <td><code>{{ $item->rvd_table }}</code></td>
                                    <td><code>{{ $item->rvd_rowid }}</code></td>
                                    <td>
                                        <strong>{{ $item->rvd_attrib ?? '—' }}</strong>
                                        @if($item->rvd_colname && $item->rvd_colname !== $item->rvd_attrib)
                                            <small class="text-muted">({{ $item->rvd_colname }})</small>
                                        @endif
                                    </td>
                                    <td><span class="text-danger font-weight-bold">{{ $item->rvd_oldvalue ?? '—' }}</span></td>
                                    <td><span class="text-success font-weight-bold">{{ $item->rvd_newvalue ?? '—' }}</span></td>
                                    <td><code>{{ $item->rvd_conversion ?? '—' }}</code></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No field revision records attached.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Attachments Panel (Fulfilled status & Authorized Roles only - legacy aud_revs_detail.bas:55-60) --}}
            @if($rev->isFulfilled() && auth()->user()->can('viewAttachments', $rev))
                <div class="card card-outline card-success shadow-sm mb-4" id="reversalAttachmentsPanel">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title font-weight-bold mb-0">
                            <i class="fas fa-paperclip mr-1 text-success"></i> Reversal Attachments &amp; Evidence
                        </h3>
                        <span class="badge badge-success float-right">{{ $rev->attachments->count() }} attached</span>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @forelse($rev->attachments as $att)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas {{ !empty($att->aat_path) ? 'fa-file-alt text-success' : 'fa-clock text-warning' }} mr-2"></i>
                                        <span class="font-weight-bold mr-2">{{ $att->aat_type ?? 'Document' }}</span>
                                        @if(!empty($att->aat_path))
                                            <span class="text-muted small font-monospace">({{ basename($att->aat_path) }})</span>
                                        @else
                                            <span class="badge badge-warning text-dark font-weight-normal">Pending Upload (Slot #{{ $att->aat_id }})</span>
                                        @endif
                                    </div>
                                    <div>
                                        @if(!empty($att->aat_path))
                                            <a href="{{ route('universal.attachment.view', ['module' => 'aud', 'id' => $att->aat_id]) }}" target="_blank" class="btn btn-xs btn-outline-primary mr-1" title="View Document">
                                                <i class="fas fa-eye mr-1"></i> View
                                            </a>
                                            <a href="{{ route('universal.attachment.view', ['module' => 'aud', 'id' => $att->aat_id, 'download' => 1]) }}" class="btn btn-xs btn-outline-secondary" title="Download Document">
                                                <i class="fas fa-download mr-1"></i> Download
                                            </a>
                                        @endif
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item text-center text-muted py-3">
                                    No attachment records or slots attached.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="card-footer bg-light">
                        <h6 class="font-weight-bold mb-2 text-muted" style="font-size: 0.85rem;">
                            <i class="fas fa-upload mr-1 text-primary"></i> Upload Attachment Document
                        </h6>
                        <form action="{{ route('universal.attachment.upload') }}" method="POST" enctype="multipart/form-data" class="form-inline" id="reversalAttachmentUploadForm">
                            @csrf
                            <input type="hidden" name="module" value="aud">
                            <input type="hidden" name="object_id" value="{{ $rev->rev_id }}">
                            
                            <div class="form-group mr-2 mb-2">
                                <label for="doc_type" class="sr-only">Document Type</label>
                                <select name="doc_type" id="doc_type" class="custom-select custom-select-sm" required>
                                    <option value="Data Revision Case" selected>Data Revision Case</option>
                                    <option value="Minute">Minute</option>
                                </select>
                            </div>
                            
                            <div class="form-group mr-2 mb-2">
                                <input type="file" name="file" id="reversalFile" class="form-control-file form-control-sm" required>
                            </div>
                            
                            <button type="submit" class="btn btn-sm btn-success mb-2" id="btnUploadAttachment">
                                <i class="fas fa-cloud-upload-alt mr-1"></i> Upload Document
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>

{{-- MODALS FOR WORKFLOW ACTIONS --}}

{{-- 1. Release Modal --}}
@can('release', $rev)
<div class="modal fade" id="releaseModal" tabindex="-1" role="dialog" aria-labelledby="releaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('admin.reversals.release', $rev->rev_id) }}" class="modal-content">
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="releaseModalLabel">
                    <i class="fas fa-paper-plane mr-2"></i>Release Reversal Case #{{ $rev->rev_id }}
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>The data revision case will be released to the IT department for execution.</p>
                <div class="alert alert-info py-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    <strong>Release Confirmation:</strong> Are you sure you want to release this request?
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill px-3" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold">
                    <i class="fas fa-paper-plane mr-1"></i> Yes, Release Case
                </button>
            </div>
        </form>
    </div>
</div>
@endcan

{{-- 2. Execute Modal --}}
@can('execute', $rev)
<div class="modal fade" id="executeModal" tabindex="-1" role="dialog" aria-labelledby="executeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('admin.reversals.execute', $rev->rev_id) }}" class="modal-content">
            @csrf
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title font-weight-bold" id="executeModalLabel">
                    <i class="fas fa-cogs mr-2"></i>Execute Reversal Case #{{ $rev->rev_id }}
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>The data revision case will be implemented and data will be reversed in the live system.</p>
                <div class="alert alert-warning py-2">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    <strong>Execution Warning:</strong> Are you sure you want to execute this request? This action cannot be automatically undone.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill px-3" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success rounded-pill px-4 font-weight-bold">
                    <i class="fas fa-check-double mr-1"></i> Yes, Execute Reversal
                </button>
            </div>
        </form>
    </div>
</div>
@endcan

{{-- 3. Return Modal --}}
@can('return', $rev)
<div class="modal fade" id="returnModal" tabindex="-1" role="dialog" aria-labelledby="returnModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('admin.reversals.return', $rev->rev_id) }}" class="modal-content">
            @csrf
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title font-weight-bold" id="returnModalLabel">
                    <i class="fas fa-undo mr-2"></i>Return Reversal Case #{{ $rev->rev_id }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Return this revision case to the initiating unit (status will become <strong>Under Revision</strong>).</p>
                <div class="form-group mb-0">
                    <label for="returnRemarks" class="font-weight-bold small text-uppercase">Return Instructions / Remarks (Optional):</label>
                    <textarea name="remarks" id="returnRemarks" class="form-control" rows="3" placeholder="Enter reason or instructions for the initiator..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill px-3" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning rounded-pill px-4 font-weight-bold text-dark">
                    <i class="fas fa-undo mr-1"></i> Return Case
                </button>
            </div>
        </form>
    </div>
</div>
@endcan

{{-- 4. Cancel Modal --}}
@can('cancel', $rev)
<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('admin.reversals.cancel', $rev->rev_id) }}" class="modal-content">
            @csrf
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title font-weight-bold" id="cancelModalLabel">
                    <i class="fas fa-times-circle mr-2"></i>Cancel Reversal Case #{{ $rev->rev_id }}
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if($rev->isDraft())
                    <div class="alert alert-danger py-2">
                        <i class="fas fa-trash-alt mr-1"></i>
                        <strong>Permanent Deletion:</strong> This draft reversal has not been released. Cancelling will permanently remove this case and all associated component snapshots from the database.
                    </div>
                @else
                    <p>The data revision case will be marked as <strong>Cancelled</strong> and closed.</p>
                    <div class="form-group mb-0">
                        <label for="cancelReason" class="font-weight-bold small text-uppercase">Cancellation Reason (Optional):</label>
                        <textarea name="reason" id="cancelReason" class="form-control" rows="3" placeholder="Enter reason for cancelling..."></textarea>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill px-3" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-danger rounded-pill px-4 font-weight-bold">
                    <i class="fas fa-times mr-1"></i> {{ $rev->isDraft() ? 'Permanently Delete Draft' : 'Confirm Cancellation' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endcan

@endsection
