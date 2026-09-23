@extends('welcome')

@section('content')
<style>
    /* Clean Light Header (Not Dark) */
    .rev-card-header {
        background-color: #ffffff;
        color: #1e293b;
        padding: 16px 22px;
        border-bottom: 2px solid #e2e8f0;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }
    .rev-card-title {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 20px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        letter-spacing: -0.2px;
    }
    .rev-form-label {
        font-size: 13px;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 2px;
    }
    .rev-form-val {
        font-size: 14px;
        color: #0f172a;
        font-weight: 500;
    }
    .rev-id-box {
        display: inline-block;
        background-color: #f1f5f9;
        border: 1px solid #cbd5e1;
        padding: 3px 12px;
        font-weight: 700;
        font-size: 14px;
        color: #1e293b;
        border-radius: 4px;
    }
    .rev-subtable-header th {
        background-color: #f1f5f9 !important;
        color: #334155 !important;
        font-size: 12.5px;
        font-weight: 700;
        padding: 10px 14px;
        border-top: none;
        border-bottom: 1px solid #cbd5e1;
    }
    .rev-subtable td {
        padding: 9px 14px;
        font-size: 13px;
        vertical-align: middle;
        border-top: 1px solid #e2e8f0;
    }
    .rev-subtable tr:nth-of-type(odd) {
        background-color: #ffffff;
    }
    .rev-subtable tr:nth-of-type(even) {
        background-color: #f8fafc;
    }
    .rev-action-btn {
        min-width: 90px;
        font-size: 13px;
        font-weight: 600;
        padding: 6px 16px;
        border-radius: 4px;
    }
    .rev-attach-box {
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        border-radius: 6px;
        padding: 10px 14px;
    }
</style>

<div class="content-wrapper">
    <div class="content-header pb-2">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent p-0 mb-1" style="font-size: 12px;">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-muted">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.reversals.open') }}" class="text-muted">Data Reversals</a></li>
                        <li class="breadcrumb-item active text-dark font-weight-bold">Case #{{ $rev->rev_id }}</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('admin.reversals.index', ['tab' => $rev->isDraft() ? 'draft' : ($rev->isFulfilled() || $rev->isCancelled() ? 'closed' : 'open')]) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Listing
                </a>
            </div>
        </div>
    </div>

    <section class="content pt-1">
        <div class="container-fluid">
            {{-- Flash Messages --}}
            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" style="border-left: 4px solid #10b981;">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" style="border-left: 4px solid #ef4444;">
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

            @php
                $typeVal = $rev->rev_type instanceof \App\Enums\RevType ? $rev->rev_type->value : (int) $rev->rev_type;
                $displayType = ($typeVal === 2) ? 'Data Change' : 'Data Reversal';
                $displayDate = $rev->rev_date ? \Carbon\Carbon::parse($rev->rev_date)->format('d M y') : '—';
                $initiatorName = $rev->initiatingUnit->unt_namesh ?? ($rev->rev_intunt_id ?? '');
                $targetDivName = $rev->unit->unt_namesh ?? ($rev->rev_unt_id ?? '');
                $isDraftOrUnderRev = ($rev->isDraft() || $rev->isUnderRevision());
                $isClosedCase = ($rev->isFulfilled() || $rev->isCancelled());
            @endphp

            <div class="card shadow-sm border mb-4" style="border-radius: 8px; border-color: #e2e8f0 !important; overflow: hidden; background: #ffffff;">
                {{-- Clean Light Header Banner (Not Dark) --}}
                <div class="rev-card-header d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
                    <div class="d-flex align-items-center" style="gap: 10px;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: #eff6ff; color: #2563eb;">
                            <i class="fas fa-file-invoice" style="font-size: 16px;"></i>
                        </div>
                        <h2 class="rev-card-title">Data Revision Case #{{ $rev->rev_id }}</h2>
                    </div>
                    <div>
                        @if($rev->rev_status === 'Draft')
                            <span class="badge badge-secondary px-3 py-1 font-weight-bold" style="font-size: 13px;">Draft</span>
                        @elseif($rev->rev_status === 'In Process')
                            <span class="badge badge-primary px-3 py-1 font-weight-bold" style="font-size: 13px;"><i class="fas fa-cog fa-spin mr-1"></i>In Process</span>
                        @elseif($rev->rev_status === 'Under Revision')
                            <span class="badge badge-warning px-3 py-1 font-weight-bold text-dark" style="font-size: 13px;"><i class="fas fa-undo mr-1"></i>Under Revision</span>
                        @elseif($rev->rev_status === 'Fulfilled')
                            <span class="badge badge-success px-3 py-1 font-weight-bold" style="font-size: 13px;"><i class="fas fa-check mr-1"></i>Fulfilled</span>
                        @elseif($rev->rev_status === 'Cancelled')
                            <span class="badge badge-danger px-3 py-1 font-weight-bold" style="font-size: 13px;"><i class="fas fa-times mr-1"></i>Cancelled</span>
                        @else
                            <span class="badge badge-light px-3 py-1" style="font-size: 13px;">{{ $rev->rev_status }}</span>
                        @endif
                    </div>
                </div>

                <div class="card-body p-4" style="background-color: #ffffff;">
                    <div class="row">
                        {{-- Left Form Metadata --}}
                        <div class="{{ ($isClosedCase && auth()->user()->can('viewAttachments', $rev)) ? 'col-lg-8' : 'col-lg-9' }} col-md-12">
                            <div class="row mb-3 align-items-center">
                                <div class="col-sm-2 col-4 rev-form-label">Rev. ID</div>
                                <div class="col-sm-3 col-8">
                                    <span class="rev-id-box">#{{ $rev->rev_id }}</span>
                                </div>

                                <div class="col-sm-2 col-4 rev-form-label">Date</div>
                                <div class="col-sm-2 col-8 rev-form-val">{{ $displayDate }}</div>

                                <div class="col-sm-1 col-4 rev-form-label">Initiator</div>
                                <div class="col-sm-2 col-8 rev-form-val">{{ $initiatorName ?: '—' }}</div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-sm-2 col-4 rev-form-label">Data</div>
                                <div class="col-sm-3 col-8 rev-form-val text-primary font-weight-bold">
                                    {{ $rev->rev_obj }} ({{ $rev->rev_objid }})
                                </div>

                                <div class="col-sm-2 col-4 rev-form-label">Type</div>
                                <div class="col-sm-2 col-8 rev-form-val">
                                    <span class="badge {{ $typeVal === 2 ? 'badge-secondary' : 'badge-info' }} px-2 py-1">
                                        {{ $displayType }}
                                    </span>
                                </div>

                                <div class="col-sm-1 col-4 rev-form-label">Division</div>
                                <div class="col-sm-2 col-8 rev-form-val">{{ $targetDivName ?: '—' }}</div>
                            </div>

                            {{-- Reason Section --}}
                            <div class="row mb-3 align-items-start">
                                <div class="col-sm-2 col-4 rev-form-label pt-1">Reason</div>
                                <div class="col-sm-10 col-8">
                                    @if($isDraftOrUnderRev && auth()->user()->can('release', $rev))
                                        <form id="reasonUpdateForm" method="POST" action="{{ route('admin.reversals.update', $rev->rev_id) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="input-group">
                                                <input type="text" name="rev_reason" id="rev_reason_input" class="form-control form-control-sm bg-white" 
                                                       value="{{ old('rev_reason', $rev->rev_reason) }}" 
                                                       placeholder="Enter reason for data revision..." style="border: 1px solid #cbd5e1; border-radius: 4px;" required>
                                                <div class="input-group-append">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Save Reason">
                                                        <i class="fas fa-save mr-1"></i> Save
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    @else
                                        <div class="p-2 bg-light rounded" style="border: 1px solid #e2e8f0; min-height: 32px; font-size: 13.5px; color: #1e293b;">
                                            {{ $rev->rev_reason ?: '—' }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-sm-2 col-4 rev-form-label">Status</div>
                                <div class="col-sm-10 col-8 rev-form-val">
                                    {{ $rev->rev_status }}
                                </div>
                            </div>
                        </div>

                        {{-- Right Action Buttons / Attachments Box --}}
                        <div class="{{ ($isClosedCase && auth()->user()->can('viewAttachments', $rev)) ? 'col-lg-4' : 'col-lg-3' }} col-md-12 text-lg-right text-left mb-3">
                            {{-- Draft Actions: Release & Cancel --}}
                            @if($isDraftOrUnderRev)
                                <div class="d-flex justify-content-lg-end justify-content-start flex-wrap" style="gap: 8px;">
                                    @can('release', $rev)
                                        <form method="POST" action="{{ route('admin.reversals.release', $rev->rev_id) }}" id="formReleaseCase" style="display: inline;">
                                            @csrf
                                            <input type="hidden" name="rev_reason" id="hidden_release_reason" value="{{ $rev->rev_reason }}">
                                            <button type="button" class="btn btn-sm btn-primary rev-action-btn shadow-sm" onclick="handleReleaseSubmit()">
                                                <i class="fas fa-paper-plane mr-1"></i> Release
                                            </button>
                                        </form>
                                    @endcan

                                    @can('cancel', $rev)
                                        <form method="POST" action="{{ route('admin.reversals.cancel', $rev->rev_id) }}" id="formCancelCase" style="display: inline;">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-outline-danger rev-action-btn shadow-sm" onclick="handleCancelSubmit()">
                                                <i class="fas fa-trash-alt mr-1"></i> Cancel
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            @endif

                            {{-- Open / In Process Actions for SO IT --}}
                            @if($rev->isInProcess())
                                <div class="d-flex justify-content-lg-end justify-content-start flex-wrap" style="gap: 8px;">
                                    @can('execute', $rev)
                                        <form method="POST" action="{{ route('admin.reversals.execute', $rev->rev_id) }}" id="formExecuteCase" style="display: inline;">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-success rev-action-btn shadow-sm" onclick="handleExecuteSubmit()">
                                                <i class="fas fa-check-double mr-1"></i> Execute
                                            </button>
                                        </form>
                                    @endcan

                                    @can('return', $rev)
                                        <button type="button" class="btn btn-sm btn-warning rev-action-btn shadow-sm text-dark" data-toggle="modal" data-target="#returnModal">
                                            <i class="fas fa-undo mr-1"></i> Return
                                        </button>
                                    @endcan

                                    @can('cancel', $rev)
                                        <form method="POST" action="{{ route('admin.reversals.cancel', $rev->rev_id) }}" id="formCancelCase" style="display: inline;">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-outline-danger rev-action-btn shadow-sm" onclick="handleCancelSubmit()">
                                                <i class="fas fa-times mr-1"></i> Cancel
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            @endif

                            {{-- Closed Attachments Box (Live Document & Upload for SO IT) --}}
                            @if($isClosedCase && auth()->user()->can('viewAttachments', $rev))
                                <div class="rev-attach-box text-left shadow-sm mt-1">
                                    <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                        <span class="font-weight-bold" style="font-size: 13px; color: #1e293b;">
                                            <i class="fas fa-paperclip text-primary mr-1"></i> Attached Documents
                                        </span>
                                        <button type="button" class="btn btn-xs btn-primary font-weight-bold" data-toggle="modal" data-target="#uploadAttachmentModal" title="Upload / Attach Document">
                                            <i class="fas fa-upload mr-1"></i> Attach
                                        </button>
                                    </div>
                                    <div style="font-size: 12.5px;">
                                        @php
                                            $hasAnyFile = false;
                                        @endphp
                                        @foreach($rev->attachments as $att)
                                            @if(!empty($att->aat_path))
                                                @php $hasAnyFile = true; @endphp
                                                <div class="d-flex justify-content-between align-items-center py-2 px-2 mb-1 bg-white rounded border">
                                                    <span class="font-weight-600 text-dark">
                                                        <i class="far fa-file-pdf mr-1 text-danger"></i>
                                                        {{ $att->aat_type ?? 'Data Revision Case' }}
                                                    </span>
                                                    @php
                                                        $docExt = strtolower(pathinfo($att->aat_path ?? '', PATHINFO_EXTENSION) ?: 'pdf');
                                                        $docTitle = ($att->aat_type ?? 'Data Revision Case') . ' #' . $rev->rev_id;
                                                        $docUrl = route('universal.attachment.view', ['module' => 'aud', 'id' => $att->aat_id]);
                                                    @endphp
                                                    <button type="button" 
                                                            class="btn btn-xs btn-success font-weight-bold shadow-sm" 
                                                            style="border-radius: 4px; padding: 2px 8px; cursor: pointer;"
                                                            onclick="openReversalLiveDoc('{{ $docUrl }}', '{{ addslashes($docTitle) }}', '{{ $docExt }}')"
                                                            title="View Document Live on Screen">
                                                        <i class="fas fa-eye mr-1"></i> Live View
                                                    </button>
                                                </div>
                                            @endif
                                        @endforeach

                                        @if(!$hasAnyFile)
                                            <div class="py-2 px-2 text-center rounded" style="background-color: #fef3c7; border: 1px solid #fde68a;">
                                                <div class="text-warning font-weight-bold mb-1" style="color: #b45309 !important; font-size: 12px;">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i> No Document Attached
                                                </div>
                                                <button type="button" class="btn btn-xs btn-primary font-weight-bold" data-toggle="modal" data-target="#uploadAttachmentModal">
                                                    <i class="fas fa-plus mr-1"></i> Upload Document Now
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Sub-table section --}}
                    @if($typeVal === 2)
                        {{-- Type 2: Data Changes --}}
                        <div class="mt-4">
                            <h4 class="font-weight-600 mb-2 text-dark" style="font-size: 15px;">
                                <i class="fas fa-exchange-alt mr-1 text-primary"></i> Data Changes
                            </h4>
                            <div class="table-responsive border" style="background: #ffffff; border-radius: 6px; max-height: 380px; overflow-y: auto;">
                                <table class="table rev-subtable mb-0 text-nowrap">
                                    <thead class="rev-subtable-header">
                                        <tr>
                                            <th style="width: 80px;">Id</th>
                                            <th>Field</th>
                                            <th>Old Value</th>
                                            <th>New Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($rev->data as $item)
                                        <tr>
                                            <td class="font-weight-bold text-muted">{{ $item->rvd_id }}</td>
                                            <td class="font-weight-600">{{ $item->rvd_colname ?? $item->rvd_attrib }}</td>
                                            <td class="text-danger font-monospace">{{ $item->rvd_oldvalue ?? '(Blank)' }}</td>
                                            <td class="text-success font-weight-bold font-monospace">{{ $item->rvd_newvalue ?? '(Blank)' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                No field change records found for this revision.
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        {{-- Type 1 or 3: Data Reversals --}}
                        <div class="mt-4">
                            <h4 class="font-weight-600 mb-2 text-dark" style="font-size: 15px;">
                                <i class="fas fa-list mr-1 text-primary"></i> Data Reversals
                            </h4>
                            <div class="table-responsive border" style="background: #ffffff; border-radius: 6px; max-height: 420px; overflow-y: auto;">
                                <table class="table rev-subtable mb-0">
                                    <thead class="rev-subtable-header">
                                        <tr>
                                            <th style="width: 100px;">Data ID</th>
                                            <th>Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($rev->comps as $comp)
                                        <tr>
                                            <td class="font-weight-bold text-muted align-top">{{ $comp->rvc_rowid }}</td>
                                            <td>
                                                <div class="font-weight-bold text-dark mb-1" style="font-size: 13.5px;">
                                                    {{ $comp->rvc_table }}
                                                </div>
                                                <div class="text-secondary small font-monospace" style="word-break: break-all; line-height: 1.45;">
                                                    {{ $comp->rvc_detail }}
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center py-4 text-muted">
                                                No reversal component records registered for this case.
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- Bottom: Implementation DTG --}}
                    <div class="mt-4 pt-2 d-flex align-items-center" style="font-size: 13px; color: #475569;">
                        <span class="mr-3 font-weight-600">Implementation DTG:</span>
                        <span class="font-weight-bold text-dark font-monospace">
                            {{ $rev->rev_closedtg ? \Carbon\Carbon::parse($rev->rev_closedtg)->format('d M y H:i') : '—' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

{{-- Return Modal for SO IT --}}
@can('return', $rev)
<div class="modal fade" id="returnModal" tabindex="-1" role="dialog" aria-labelledby="returnModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" action="{{ route('admin.reversals.return', $rev->rev_id) }}" class="modal-content">
            @csrf
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title font-weight-bold text-dark" id="returnModalLabel">
                    <i class="fas fa-undo text-warning mr-1"></i> Return Data Revision Case #{{ $rev->rev_id }}
                </h5>
                <button type="button" class="close text-muted" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-secondary">The data revision case will be returned to the initiating division for review/amendment.</p>
                <div class="form-group">
                    <label for="return_remarks" class="font-weight-bold text-dark" style="font-size: 13px;">Return Remarks / Reason:</label>
                    <textarea name="remarks" id="return_remarks" rows="3" class="form-control" placeholder="Optional notes for initiating division..."></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light border-top">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-warning btn-sm text-dark font-weight-bold">Confirm Return</button>
            </div>
        </form>
    </div>
</div>
@endcan

{{-- Upload Attachment Modal (for closed cases) --}}
@if($isClosedCase && auth()->user()->can('viewAttachments', $rev))
<div class="modal fade" id="uploadAttachmentModal" tabindex="-1" role="dialog" aria-labelledby="uploadAttModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('universal.attachment.upload') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <input type="hidden" name="module" value="aud">
            <input type="hidden" name="object_id" value="{{ $rev->rev_id }}">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title font-weight-bold text-dark" id="uploadAttModalLabel">
                    <i class="fas fa-paperclip text-primary mr-1"></i> Upload Attachment Document
                </h5>
                <button type="button" class="close text-muted" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="doc_type" class="font-weight-bold text-dark" style="font-size: 13px;">Document Type</label>
                    <select name="doc_type" id="doc_type" class="custom-select custom-select-sm" required>
                        <option value="Data Revision Case" selected>Data Revision Case</option>
                        <option value="Minute">Minute</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="file_upload" class="font-weight-bold text-dark" style="font-size: 13px;">Choose Document File (PDF / Image)</label>
                    <input type="file" name="file" id="file_upload" class="form-control-file" required>
                </div>
            </div>
            <div class="modal-footer bg-light border-top">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm font-weight-bold">
                    <i class="fas fa-upload mr-1"></i> Upload Document
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
    function openReversalLiveDoc(url, title, ext) {
        if (!url) return;
        if (typeof window.openLiveDocument === 'function') {
            window.openLiveDocument(url, title || 'Data Revision Case Document', ext || 'pdf');
        } else {
            var modal = $('#rdLiveDocViewerModal');
            $('#rdLiveDocViewerTitle').text((title || 'Document').toUpperCase());
            $('#rdLiveDocExtBadge').text((ext || 'pdf').toUpperCase());
            $('#rdLiveDocOpenNewTab').attr('href', url);
            var dlUrl = url.includes('?') ? (url + '&download=1') : (url + '?download=1');
            $('#rdLiveDocDownloadBtn').attr('href', dlUrl);
            
            var iframe = document.getElementById('rdLiveDocIframe');
            if (iframe) {
                iframe.src = url;
                iframe.style.display = 'block';
            }
            modal.modal('show');
        }
    }

    function handleReleaseSubmit() {
        var reasonInput = document.getElementById('rev_reason_input');
        var reasonVal = reasonInput ? reasonInput.value.trim() : "{{ addslashes($rev->rev_reason ?? '') }}";
        
        if (!reasonVal) {
            alert('Please enter reason for data revision');
            if (reasonInput) reasonInput.focus();
            return;
        }

        var confirmed = confirm("The data revision case will be released. Are you sure you want to release this request?");
        if (confirmed) {
            var hiddenReason = document.getElementById('hidden_release_reason');
            if (hiddenReason) {
                hiddenReason.value = reasonVal;
            }
            document.getElementById('formReleaseCase').submit();
        }
    }

    function handleCancelSubmit() {
        var confirmed = confirm("The data revision case will be cancelled. Are you sure you want to cancel this request?");
        if (confirmed) {
            document.getElementById('formCancelCase').submit();
        }
    }

    function handleExecuteSubmit() {
        var confirmed = confirm("The data revision case will be implemented and data will be reversed. Are you sure you want to execute this request?");
        if (confirmed) {
            document.getElementById('formExecuteCase').submit();
        }
    }
</script>
@endsection
