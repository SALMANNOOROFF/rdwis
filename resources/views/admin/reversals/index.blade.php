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
    .rev-table {
        margin-bottom: 0;
        border-collapse: collapse;
        width: 100%;
    }
    .rev-table thead th.th-main {
        background-color: #f1f5f9;
        color: #334155;
        font-weight: 700;
        font-size: 12.5px;
        padding: 10px 12px;
        border-top: none;
        border-bottom: 1px solid #cbd5e1;
        letter-spacing: 0.2px;
    }
    .rev-table thead tr.filter-row th {
        background-color: #f8fafc;
        padding: 6px 8px;
        border-bottom: 2px solid #cbd5e1;
    }
    .rev-filter-input {
        width: 100%;
        font-size: 11.5px;
        padding: 3px 6px;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        background-color: #ffffff;
        color: #334155;
    }
    .rev-filter-input:focus {
        border-color: #2563eb;
        outline: none;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
    }
    .rev-table tbody tr {
        transition: background-color 0.15s ease-in-out;
    }
    .rev-table tbody tr:nth-of-type(odd) {
        background-color: #ffffff;
    }
    .rev-table tbody tr:nth-of-type(even) {
        background-color: #f8fafc;
    }
    .rev-table tbody tr:hover {
        background-color: #f1f5f9;
    }
    .rev-table tbody td {
        padding: 9px 12px;
        font-size: 13px;
        vertical-align: middle;
        border-top: 1px solid #e2e8f0;
        color: #1e293b;
    }
    .rev-id-link {
        font-weight: 700;
        color: #2563eb;
        text-decoration: none;
        transition: color 0.15s;
    }
    .rev-id-link:hover {
        color: #1d4ed8;
        text-decoration: underline;
    }
    .rev-tab-btn {
        font-size: 13px;
        font-weight: 600;
        padding: 7px 18px;
        border-radius: 20px;
        text-decoration: none;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .rev-tab-btn.active {
        background-color: #2563eb;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.35);
    }
    .rev-tab-btn:not(.active) {
        background-color: #ffffff;
        color: #475569;
        border: 1px solid #cbd5e1;
    }
    .rev-tab-btn:hover:not(.active) {
        background-color: #f1f5f9;
        color: #1e293b;
    }
    .div-filter-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 16px;
        text-decoration: none;
        transition: all 0.15s;
        border: 1px solid #cbd5e1;
        background-color: #ffffff;
        color: #334155;
        margin-right: 6px;
        margin-bottom: 6px;
    }
    .div-filter-pill:hover {
        background-color: #f1f5f9;
        color: #1e293b;
    }
    .div-filter-pill.active {
        background-color: #1e293b;
        color: #ffffff !important;
        border-color: #1e293b;
        box-shadow: 0 2px 4px rgba(30, 41, 59, 0.25);
    }
    .btn-view-action {
        border-radius: 4px;
        padding: 4px 12px;
        font-size: 12px;
        font-weight: 600;
        background-color: #2563eb;
        color: #ffffff;
        border: none;
        transition: all 0.15s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .btn-view-action:hover {
        background-color: #1d4ed8;
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.3);
    }
</style>

<div class="content-wrapper">
    <div class="content-header pb-2">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent p-0 mb-1" style="font-size: 12px;">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-muted">Home</a></li>
                        <li class="breadcrumb-item active text-dark font-weight-bold">Data Reversals</li>
                    </ol>
                </nav>
            </div>
            
            {{-- Tabs --}}
            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                @if($canViewDraft)
                    <a href="{{ route('admin.reversals.draft') }}" class="rev-tab-btn {{ ($tab ?? 'open') === 'draft' ? 'active' : '' }}">
                        <i class="fas fa-file-alt"></i> Draft
                        <span class="badge badge-pill {{ ($tab ?? 'open') === 'draft' ? 'badge-light text-dark' : 'badge-secondary' }}">{{ $reversalsDraftCount ?? 0 }}</span>
                    </a>
                @endif
                <a href="{{ route('admin.reversals.open') }}" class="rev-tab-btn {{ ($tab ?? 'open') === 'open' ? 'active' : '' }}">
                    <i class="fas fa-folder-open"></i> Open
                    <span class="badge badge-pill {{ ($tab ?? 'open') === 'open' ? 'badge-light text-dark' : 'badge-primary' }}">{{ $reversalsOpenCount ?? 0 }}</span>
                </a>
                <a href="{{ route('admin.reversals.closed') }}" class="rev-tab-btn {{ ($tab ?? 'open') === 'closed' ? 'active' : '' }}">
                    <i class="fas fa-check-circle"></i> Closed
                    <span class="badge badge-pill {{ ($tab ?? 'open') === 'closed' ? 'badge-light text-dark' : 'badge-success' }}">{{ $reversalsClosedCount ?? 0 }}</span>
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
                    <strong>Action failed:</strong>
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

            {{-- SO IT Division-wise Filter Bar --}}
            @if($isItStaff && !empty($divisionBreakdown))
                <div class="card shadow-sm border mb-3" style="border-radius: 8px; border-color: #e2e8f0 !important;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap mb-2" style="gap: 8px;">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-building text-primary mr-2" style="font-size: 15px;"></i>
                                <span class="font-weight-bold text-dark" style="font-size: 13px;">Filter by Division / Department:</span>
                            </div>
                            @if(!empty($selectedDivision))
                                <a href="{{ route('admin.reversals.' . $tab) }}" class="btn btn-xs btn-outline-danger">
                                    <i class="fas fa-times mr-1"></i> Clear Division Filter
                                </a>
                            @endif
                        </div>

                        <div class="d-flex flex-wrap align-items-center pt-1">
                            {{-- All Divisions Pill --}}
                            <a href="{{ route('admin.reversals.' . $tab) }}" class="div-filter-pill {{ empty($selectedDivision) ? 'active' : '' }}">
                                <i class="fas fa-layer-group"></i> All Divisions
                                <span class="badge {{ empty($selectedDivision) ? 'badge-light text-dark' : 'badge-secondary' }} ml-1">
                                    {{ $tab === 'closed' ? $reversalsClosedCount : $reversalsOpenCount }}
                                </span>
                            </a>

                            {{-- Division Pills with Counts --}}
                            @foreach($divisionBreakdown as $div)
                                <a href="{{ route('admin.reversals.' . $tab, ['division' => $div['unit_id']]) }}" 
                                   class="div-filter-pill {{ ((string)$selectedDivision === (string)$div['unit_id']) ? 'active' : '' }}"
                                   title="{{ $div['name'] }} Cases">
                                    {{ $div['name'] }}
                                    <span class="badge {{ ((string)$selectedDivision === (string)$div['unit_id']) ? 'badge-light text-dark' : 'badge-primary' }} ml-1">
                                        {{ $div['count'] }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Main Table Card with Clean Light Header (Not Dark) --}}
            <div class="card shadow-sm border mb-4" style="border-radius: 8px; border-color: #e2e8f0 !important; overflow: hidden;">
                {{-- Clean Light Card Header --}}
                <div class="rev-card-header d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
                    <div class="d-flex align-items-center" style="gap: 10px;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: #eff6ff; color: #2563eb;">
                            <i class="fas fa-history" style="font-size: 16px;"></i>
                        </div>
                        <div>
                            <h2 class="rev-card-title">
                                @if(($tab ?? 'open') === 'draft')
                                    Data Revision Cases — Draft
                                @elseif(($tab ?? 'open') === 'closed')
                                    Data Revision Cases — Closed
                                @else
                                    Data Revision Cases — Open
                                @endif
                                @if(!empty($selectedDivision))
                                    @php
                                        $selName = collect($divisionBreakdown)->firstWhere('unit_id', $selectedDivision)['name'] ?? 'Division';
                                    @endphp
                                    <span class="badge badge-primary ml-2 font-weight-bold" style="font-size: 12px; vertical-align: middle;">
                                        {{ $selName }}
                                    </span>
                                @endif
                            </h2>
                            <span class="text-muted small">
                                Showing page {{ $reversals->currentPage() }} of {{ $reversals->lastPage() }} ({{ $reversals->total() }} total cases)
                            </span>
                        </div>
                    </div>

                    {{-- Global Quick Search and Reset Filter --}}
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-right-0" style="border-color: #cbd5e1;"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" id="globalSearchInput" class="form-control border-left-0" style="border-color: #cbd5e1;" placeholder="Quick search..." onkeyup="filterReversalsTable()">
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetAllFilters()" title="Reset Filters">
                            <i class="fas fa-sync-alt"></i> Reset
                        </button>
                    </div>
                </div>

                {{-- Table with Column Filters and Live Document Column --}}
                <div class="table-responsive p-0">
                    <table class="table rev-table text-nowrap" id="reversalsDataTable">
                        <thead>
                            <tr>
                                <th class="th-main" style="width: 80px;">ID</th>
                                <th class="th-main" style="width: 105px;">Date</th>
                                <th class="th-main" style="width: 130px;">Initiator</th>
                                <th class="th-main">Data</th>
                                <th class="th-main" style="width: 100px;">Data ID</th>
                                <th class="th-main" style="width: 120px;">Div</th>
                                <th class="th-main" style="width: 100px;">Type</th>
                                <th class="th-main" style="width: 120px;">Status</th>
                                <th class="th-main text-center" style="width: 140px;">Attachment</th>
                                <th class="th-main text-center" style="width: 90px;">Action</th>
                            </tr>
                            {{-- Column Filter Inputs Row --}}
                            <tr class="filter-row">
                                <th>
                                    <input type="text" class="rev-filter-input" data-col="0" placeholder="Filter ID" onkeyup="filterReversalsTable()">
                                </th>
                                <th>
                                    <input type="text" class="rev-filter-input" data-col="1" placeholder="Filter Date" onkeyup="filterReversalsTable()">
                                </th>
                                <th>
                                    <input type="text" class="rev-filter-input" data-col="2" placeholder="Filter Initiator" onkeyup="filterReversalsTable()">
                                </th>
                                <th>
                                    <input type="text" class="rev-filter-input" data-col="3" placeholder="Filter Data" onkeyup="filterReversalsTable()">
                                </th>
                                <th>
                                    <input type="text" class="rev-filter-input" data-col="4" placeholder="Filter Data ID" onkeyup="filterReversalsTable()">
                                </th>
                                <th>
                                    <input type="text" class="rev-filter-input" data-col="5" placeholder="Filter Div" onkeyup="filterReversalsTable()">
                                </th>
                                <th>
                                    <select class="rev-filter-input" data-col="6" onchange="filterReversalsTable()">
                                        <option value="">All Types</option>
                                        <option value="Reversal">Reversal</option>
                                        <option value="Change">Change</option>
                                    </select>
                                </th>
                                <th>
                                    <select class="rev-filter-input" data-col="7" onchange="filterReversalsTable()">
                                        <option value="">All Statuses</option>
                                        <option value="Draft">Draft</option>
                                        <option value="In Process">In Process</option>
                                        <option value="Under Revision">Under Revision</option>
                                        <option value="Fulfilled">Fulfilled</option>
                                        <option value="Cancelled">Cancelled</option>
                                    </select>
                                </th>
                                <th>
                                    <select class="rev-filter-input" data-col="8" onchange="filterReversalsTable()">
                                        <option value="">All Docs</option>
                                        <option value="View Doc">Attached</option>
                                        <option value="Missing Doc">Missing Doc</option>
                                    </select>
                                </th>
                                <th class="text-center">
                                    <span class="text-muted small font-weight-normal"><i class="fas fa-filter mr-1"></i>Filters</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="reversalsTableBody">
                        @forelse($reversals as $rev)
                            @php
                                $typeVal = $rev->rev_type instanceof \App\Enums\RevType ? $rev->rev_type->value : (int) $rev->rev_type;
                                $displayType = ($typeVal === 2) ? 'Change' : 'Reversal';
                                $displayDate = $rev->rev_date ? \Carbon\Carbon::parse($rev->rev_date)->format('d M y') : '—';
                                $initiatorName = $rev->initiatingUnit->unt_namesh ?? ($rev->rev_intunt_id ?? '');
                                $targetDivName = $rev->unit->unt_namesh ?? ($rev->rev_unt_id ?? '');
                                $attachedDoc = $rev->attachments->first(fn($a) => !empty($a->aat_path));
                                $isClosedCase = ($rev->isFulfilled() || $rev->isCancelled());
                            @endphp
                            <tr class="rev-row">
                                {{-- ID Permalink --}}
                                <td>
                                    <a href="{{ route('admin.reversals.show', $rev->rev_id) }}" class="rev-id-link" title="Open Case #{{ $rev->rev_id }}">
                                        #{{ $rev->rev_id }}
                                    </a>
                                </td>
                                <td>{{ $displayDate }}</td>
                                <td>{{ $initiatorName ?: '—' }}</td>
                                <td class="font-weight-600 text-dark">
                                    {{ $rev->rev_obj }}
                                </td>
                                <td><code>{{ $rev->rev_objid }}</code></td>
                                <td>{{ $targetDivName ?: '—' }}</td>
                                <td>
                                    @if($displayType === 'Change')
                                        <span class="badge badge-secondary px-2 py-1">Change</span>
                                    @else
                                        <span class="badge badge-info px-2 py-1">Reversal</span>
                                    @endif
                                </td>
                                <td>
                                    @if($rev->rev_status === 'Draft')
                                        <span class="badge badge-secondary px-2 py-1"><i class="fas fa-pencil-alt mr-1"></i>Draft</span>
                                    @elseif($rev->rev_status === 'In Process')
                                        <span class="badge badge-primary px-2 py-1"><i class="fas fa-cog fa-spin mr-1"></i>In Process</span>
                                    @elseif($rev->rev_status === 'Under Revision')
                                        <span class="badge badge-warning px-2 py-1 text-dark"><i class="fas fa-undo mr-1"></i>Under Revision</span>
                                    @elseif($rev->rev_status === 'Fulfilled')
                                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i>Fulfilled</span>
                                    @elseif($rev->rev_status === 'Cancelled')
                                        <span class="badge badge-danger px-2 py-1"><i class="fas fa-times mr-1"></i>Cancelled</span>
                                    @else
                                        <span class="badge badge-light px-2 py-1">{{ $rev->rev_status }}</span>
                                    @endif
                                </td>

                                {{-- Live Attachment Column with Missing Doc Indicator --}}
                                <td class="text-center">
                                    @if($attachedDoc)
                                        @php
                                            $attExt = strtolower(pathinfo($attachedDoc->aat_path ?? '', PATHINFO_EXTENSION) ?: 'pdf');
                                            $attTitle = ($attachedDoc->aat_type ?? 'Data Revision Case') . ' #' . $rev->rev_id;
                                            $attUrl = route('universal.attachment.view', ['module' => 'aud', 'id' => $attachedDoc->aat_id]);
                                        @endphp
                                        <button type="button" 
                                                class="btn btn-xs btn-outline-success font-weight-bold shadow-sm" 
                                                style="border-radius: 4px; padding: 2px 8px; cursor: pointer;"
                                                onclick="openReversalLiveDoc('{{ $attUrl }}', '{{ addslashes($attTitle) }}', '{{ $attExt }}')"
                                                title="View document live on screen">
                                            <i class="fas fa-file-pdf mr-1 text-danger"></i> View Doc
                                        </button>
                                    @elseif($isClosedCase)
                                        <div class="d-inline-flex align-items-center" style="gap: 4px;">
                                            <span class="badge font-weight-bold px-2 py-1" style="font-size: 11px; background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a;" title="Document not yet attached">
                                                <i class="fas fa-exclamation-triangle mr-1 text-warning"></i> Missing Doc
                                            </span>
                                            @if($isItStaff)
                                                <button type="button" class="btn btn-xs btn-outline-primary" style="padding: 1px 6px; font-size: 11px;" onclick="openQuickUploadModal({{ $rev->rev_id }})" title="Attach Document Now">
                                                    <i class="fas fa-plus"></i> Attach
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>

                                {{-- Proper View Action Button --}}
                                <td class="text-center">
                                    <a href="{{ route('admin.reversals.show', $rev->rev_id) }}" class="btn-view-action" title="View details of #{{ $rev->rev_id }}">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr id="noRecordsRow">
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 text-secondary d-block" style="opacity: 0.35;"></i>
                                    <strong>No data reversal cases found in this tab.</strong>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if($reversals->hasPages())
                    <div class="card-footer bg-white border-top py-2 d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                        <span class="small text-muted" id="tableRecordCount">
                            Showing {{ $reversals->firstItem() ?? 0 }} to {{ $reversals->lastItem() ?? 0 }} of {{ $reversals->total() }} cases
                        </span>
                        <div>
                            {{ $reversals->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>

{{-- Quick Upload Attachment Modal for SO IT --}}
@if($isItStaff)
<div class="modal fade" id="quickUploadModal" tabindex="-1" role="dialog" aria-labelledby="quickUploadModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('universal.attachment.upload') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <input type="hidden" name="module" value="aud">
            <input type="hidden" name="object_id" id="quickUploadObjectId" value="">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title font-weight-bold text-dark" id="quickUploadModalLabel">
                    <i class="fas fa-paperclip text-primary mr-1"></i> Attach Document for Case #<span id="quickUploadRevIdText"></span>
                </h5>
                <button type="button" class="close text-muted" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="quick_doc_type" class="font-weight-bold text-dark" style="font-size: 13px;">Document Type</label>
                    <select name="doc_type" id="quick_doc_type" class="custom-select custom-select-sm" required>
                        <option value="Data Revision Case" selected>Data Revision Case</option>
                        <option value="Minute">Minute</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="quick_file_upload" class="font-weight-bold text-dark" style="font-size: 13px;">Choose Document File (PDF / Image)</label>
                    <input type="file" name="file" id="quick_file_upload" class="form-control-file" required>
                </div>
            </div>
            <div class="modal-footer bg-light border-top">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm font-weight-bold">
                    <i class="fas fa-upload mr-1"></i> Upload &amp; Attach
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

    function openQuickUploadModal(revId) {
        document.getElementById('quickUploadObjectId').value = revId;
        document.getElementById('quickUploadRevIdText').innerText = revId;
        $('#quickUploadModal').modal('show');
    }

    function filterReversalsTable() {
        var globalVal = (document.getElementById('globalSearchInput').value || '').toLowerCase().trim();
        var filterInputs = document.querySelectorAll('.rev-filter-input');
        var colFilters = {};

        filterInputs.forEach(function(input) {
            var colIdx = input.getAttribute('data-col');
            var val = (input.value || '').toLowerCase().trim();
            if (val) {
                colFilters[colIdx] = val;
            }
        });

        var rows = document.querySelectorAll('#reversalsTableBody tr.rev-row');
        var visibleCount = 0;

        rows.forEach(function(row) {
            var cells = row.getElementsByTagName('td');
            var rowMatches = true;

            // Global search filter check
            if (globalVal) {
                var rowText = row.innerText.toLowerCase();
                if (rowText.indexOf(globalVal) === -1) {
                    rowMatches = false;
                }
            }

            // Column-specific filter check
            if (rowMatches) {
                for (var colIdx in colFilters) {
                    var cell = cells[colIdx];
                    if (cell) {
                        var cellText = (cell.textContent || cell.innerText || '').toLowerCase().trim();
                        if (cellText.indexOf(colFilters[colIdx]) === -1) {
                            rowMatches = false;
                            break;
                        }
                    }
                }
            }

            if (rowMatches) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Update record count text
        var countEl = document.getElementById('tableRecordCount');
        if (countEl) {
            if (globalVal || Object.keys(colFilters).length > 0) {
                countEl.innerText = visibleCount + ' matching cases found';
            }
        }
    }

    function resetAllFilters() {
        var globalInput = document.getElementById('globalSearchInput');
        if (globalInput) globalInput.value = '';

        var filterInputs = document.querySelectorAll('.rev-filter-input');
        filterInputs.forEach(function(input) {
            input.value = '';
        });

        filterReversalsTable();
    }
</script>
@endsection
