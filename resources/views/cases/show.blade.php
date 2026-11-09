

wp{{-- resources/views/cases/show.blade.php --}}
@extends('welcome')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap');

.case-page { font-family: 'Inter', sans-serif; background: #f8fafc; min-height: 100vh; }
.rajdhani { font-family: 'Rajdhani', sans-serif; }

/* 2-Column Grid Layout matching Screenshot 2 */
.case-grid {
    display: grid;
    grid-template-columns: 50% 50%;
    gap: 16px;
    align-items: start;
}
@media (max-width: 1024px) {
    .case-grid { grid-template-columns: 1fr; }
}

/* Card Styling */
.case-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}

/* Section Header Labels matching Screenshot 2 & 3 */
.sec-hdr {
    font-family: 'Rajdhani', sans-serif;
    font-size: 13.5px;
    font-weight: 700;
    letter-spacing: 1px;
    color: #475569;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 6px;
}
.sec-hdr::before {
    content: '';
    width: 3px;
    height: 14px;
    background: #15803d;
    border-radius: 2px;
    display: inline-block;
}

/* Meta Labels & Values */
.meta-label {
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.meta-value {
    font-size: 13.5px;
    font-weight: 600;
    color: #0f172a;
}

/* Send Button matching Screenshot 1, 2 & 5 */
.btn-action-send {
    background: #2563eb !important;
    border: none;
    border-radius: 6px;
    color: #ffffff !important;
    font-family: 'Rajdhani', sans-serif;
    font-weight: 700;
    font-size: 14px;
    letter-spacing: 0.8px;
    height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
    transition: all 0.2s;
}
.btn-action-send:hover {
    background: #1d4ed8 !important;
    box-shadow: 0 4px 12px rgba(29, 78, 216, 0.35);
    transform: translateY(-1px);
}

/* Compact Approve Button matching Screenshot 5 */
.btn-action-approve {
    background: #16a34a !important;
    border: none;
    border-radius: 6px;
    color: #ffffff !important;
    width: 44px;
    height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(22, 163, 74, 0.25);
    transition: all 0.2s;
}
.btn-action-approve:hover {
    background: #15803d !important;
    transform: translateY(-1px);
}

/* Compact Reject Button matching Screenshot 5 */
.btn-action-reject {
    background: #dc2626 !important;
    border: none;
    border-radius: 6px;
    color: #ffffff !important;
    width: 44px;
    height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(220, 38, 38, 0.25);
    transition: all 0.2s;
}
.btn-action-reject:hover {
    background: #b91c1c !important;
    transform: translateY(-1px);
}
</style>

<div class="content-wrapper case-page px-3 py-3">
    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm py-2 px-3 small border-0 mb-3" style="background: #f0fdf4; color: #16a34a; border-left: 4px solid #16a34a !important;">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close text-success" data-dismiss="alert">&times;</button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm py-2 px-3 small border-0 mb-3" style="background: #fef2f2; color: #dc2626; border-left: 4px solid #dc2626 !important;">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
            <button type="button" class="close text-danger" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- Main 2-Column Grid matching Screenshot 2 --}}
    <div class="case-grid">
        {{-- ======================================================== --}}
        {{-- LEFT COLUMN: CASE METADATA & NOTES                       --}}
        {{-- ======================================================== --}}
        <div class="case-left">
            {{-- Top Header Bar matching Screenshot 2 --}}
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="sec-hdr">
                    CASE FILE (E-OFFICE)
                </div>
                <div class="d-flex align-items-center" style="gap: 6px;">
                    @if($canEditCaseDetails)
                        <button type="button" onclick="saveDraft()" id="btnSaveDraftTop" class="btn btn-sm btn-outline-primary font-weight-bold" style="border-radius: 6px; font-size: 11.5px; padding: 4px 10px;">
                            <i class="fas fa-save mr-1"></i> Save Draft
                        </button>
                    @endif
                    <a href="{{ route('cases.print', $case->gcs_id) }}" target="_blank" class="btn btn-sm btn-outline-dark font-weight-bold" style="border-radius: 6px; font-size: 11.5px; padding: 4px 10px;">
                        <i class="fas fa-print mr-1"></i> Print Note
                    </a>
                    <a href="{{ route('cases.index') }}" class="btn btn-sm btn-outline-secondary font-weight-bold" style="border-radius: 6px; font-size: 11.5px; padding: 4px 10px;">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                </div>
            </div>

            {{-- Case Metadata Card matching Screenshot 2 & 4 --}}
            <div class="case-card mb-3">
                <form id="caseDraftForm" method="POST" action="{{ route('cases.update_draft', $case->gcs_id) }}">
                    @csrf
                    @method('PATCH')

                    <div class="row align-items-center mb-2.5">
                        <div class="col-md-3 meta-label">
                            <i class="fas fa-tag text-muted mr-1"></i> CASE TITLE:
                        </div>
                        <div class="col-md-9">
                            @if($canEditCaseDetails)
                                <input type="text" name="title" id="caseTitleInput" class="form-control form-control-sm font-weight-bold text-dark" value="{{ $case->gcs_title }}" placeholder="Enter Case Title..." style="font-size: 14px; border-color: #cbd5e1; border-radius: 6px;">
                            @else
                                <span class="meta-value font-weight-bold" style="font-size: 15px; color: #0f172a;">{{ $case->gcs_title }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-3 meta-label">
                            # CASE ID:
                        </div>
                        <div class="col-md-9">
                            <span class="badge border font-weight-bold px-2 py-1" style="font-family: monospace; font-size: 12px; background: #f8fafc; border-color: #cbd5e1; color: #0f172a;">
                                {{ $case->gcs_file_no }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-3 meta-label">
                            <i class="far fa-calendar-alt text-muted mr-1"></i> DATE:
                        </div>
                        <div class="col-md-9 meta-value" style="font-size: 12.5px;">
                            {{ $case->created_at->format('d M, Y') }}
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-3 meta-label">
                            <i class="fas fa-folder text-muted mr-1"></i> CATEGORY:
                        </div>
                        <div class="col-md-9">
                            @if($canEditCaseDetails)
                                <select name="category" id="caseCategorySelect" class="form-control form-control-sm font-weight-bold" style="font-size: 12.5px; border-radius: 6px; border-color: #cbd5e1;">
                                    @foreach($categories as $catKey => $catLabel)
                                        <option value="{{ $catKey }}" {{ $case->gcs_category === $catKey ? 'selected' : '' }}>
                                            {{ $catLabel }} ({{ $catKey }})
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <span class="badge badge-light border text-dark font-weight-bold px-2 py-1" style="font-size: 12px; background: #f8fafc;">
                                    {{ $case->gcs_category }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-3 meta-label">
                            <i class="fas fa-building text-muted mr-1"></i> DIVISION:
                        </div>
                        <div class="col-md-9 meta-value" style="font-size: 12.5px;">
                            {{ $case->unit->unt_name ?? $user->acc_untname ?? 'Command, Control and Communication (C3)' }}
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-3 meta-label">
                            <i class="fas fa-info-circle text-muted mr-1"></i> CASE STATUS:
                        </div>
                        <div class="col-md-9">
                            @php
                                $statusBadgeStyle = match(strtolower($case->gcs_status)) {
                                    'draft' => 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a;',
                                    'approved' => 'background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;',
                                    'rejected', 'cancelled' => 'background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;',
                                    default => 'background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;',
                                };
                            @endphp
                            <span class="badge px-2 py-1 font-weight-bold" style="{{ $statusBadgeStyle }}; font-size: 11.5px; border-radius: 4px;">
                                {{ $case->gcs_status }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-0">
                        <div class="col-md-3 meta-label">
                            <i class="fas fa-map-marker-alt text-muted mr-1"></i> LOCATION:
                        </div>
                        <div class="col-md-9">
                            <span class="badge px-2 py-1 font-weight-bold" style="background: #e0f2fe; color: #0369a1; border-radius: 4px; font-size: 11.5px;">
                                <i class="fas fa-user mr-1"></i> Currently with: {{ $case->currentHolder->acc_name ?? 'Division (Initiator)' }}
                            </span>
                            @php
                                $latestMovement = $case->movements->first();
                            @endphp
                            @if($latestMovement && $latestMovement->gcm_actor_name && $latestMovement->gcm_action !== 'Initiated')
                                <span class="text-muted small ml-1 font-italic" style="font-size: 10.5px;">
                                    (Forwarded by {{ $latestMovement->gcm_actor_name }})
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Left Side Independent Notes / Particulars Area --}}
                    <div class="mt-3 pt-3 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-1.5">
                            <label class="font-weight-bold text-dark small mb-0">
                                <i class="fas fa-file-alt text-success mr-1"></i> CASE NOTES / SUMMARY:
                            </label>
                            <span id="leftNotesSaveStatus" class="small text-muted font-italic" style="font-size: 11px;"></span>
                        </div>
                        @if($canEditCaseDetails)
                            <textarea name="description" id="caseDescriptionTextarea" class="form-control" rows="8" placeholder="Para 1. Type case background, particulars, details, or notes here..." style="font-size: 13px; line-height: 1.6; border-color: #cbd5e1; border-radius: 6px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">{{ $case->gcs_description ?: '1. ' }}</textarea>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small class="text-muted"><i class="fas fa-keyboard mr-1"></i> Press <strong>Enter</strong> for next paragraph, <strong>Tab</strong> for sub-points (a, b, c).</small>
                                <button type="button" onclick="saveDraft()" class="btn btn-sm btn-outline-success font-weight-bold px-3" style="border-radius: 6px; font-size: 11.5px;">
                                    <i class="fas fa-check mr-1"></i> Save Note
                                </button>
                            </div>
                        @else
                            <div class="p-3 bg-light rounded text-dark text-left" style="font-size: 13px; line-height: 1.6; white-space: pre-wrap; text-align: left !important; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; border: 1px solid #e2e8f0;">{{ trim($case->gcs_description) ?: '1. No additional notes recorded.' }}</div>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Attached Documents Box on Left --}}
            @if($case->attachments->count() > 0)
                <div class="case-card mb-3">
                    <div class="sec-hdr mb-2" style="font-size: 12px;">
                        <i class="fas fa-paperclip text-success mr-1"></i> ATTACHED ENCLOSURES &amp; DOCUMENTS ({{ $case->attachments->count() }})
                    </div>
                    <div class="attachments-list">
                        @foreach($case->attachments as $att)
                            <div class="d-flex justify-content-between align-items-center p-2 mb-1.5 rounded bg-light border" style="font-size: 12px;">
                                <div>
                                    <i class="fas fa-file-alt text-primary mr-1"></i>
                                    <strong class="text-dark">{{ $att->gca_title ?: $att->gca_filename }}</strong>
                                    <div class="text-muted" style="font-size: 10.5px;">
                                        {{ number_format(($att->gca_size ?? 0) / 1024, 1) }} KB &bull; Uploaded by {{ $att->uploader->acc_name ?? 'User' }}
                                    </div>
                                </div>
                                <a href="{{ asset('storage/' . $att->gca_path) }}" target="_blank" class="btn btn-xs btn-outline-primary font-weight-bold px-2 py-1" style="border-radius: 4px;">
                                    <i class="fas fa-eye mr-1"></i> View
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- ======================================================== --}}
        {{-- RIGHT COLUMN: MINUTE PANEL (Screenshots 1, 2, 3, 5)       --}}
        {{-- ======================================================== --}}
        <div class="case-right">
            {{-- Top Header: "| MINUTE" and "[CASE ATTACHMENTS (0) v]" --}}
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="sec-hdr">
                    MINUTE
                </div>

                {{-- Attachments Dropdown Button matching Screenshot 1, 2 & 5 --}}
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle rounded-pill font-weight-bold" type="button" id="attachmentsDropdownBtn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="font-size: 11.5px; padding: 4px 14px; background: #ffffff; border-color: #cbd5e1;">
                        <i class="fas fa-paperclip text-success mr-1"></i> CASE ATTACHMENTS <span class="badge badge-light border text-dark ml-1">{{ $case->attachments->count() }}</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right p-3 shadow-lg border" aria-labelledby="attachmentsDropdownBtn" style="min-width: 320px; border-radius: 8px;">
                        <div class="font-weight-bold small mb-2 text-dark">
                            <i class="fas fa-paperclip text-primary mr-1"></i> Case Attachments
                        </div>
                        <div class="attachments-list mb-2" style="max-height: 180px; overflow-y: auto;">
                            @forelse($case->attachments as $att)
                                <div class="d-flex justify-content-between align-items-center p-1.5 mb-1.5 rounded bg-light border" style="font-size: 11.5px;">
                                    <span class="text-truncate mr-2 text-dark font-weight-bold">{{ $att->gca_title ?: $att->gca_filename }}</span>
                                    <a href="{{ asset('storage/' . $att->gca_path) }}" target="_blank" class="btn btn-xs btn-primary font-weight-bold" style="border-radius: 4px; padding: 1px 6px;">
                                        View
                                    </a>
                                </div>
                            @empty
                                <div class="text-muted small text-center py-2">No attachments yet.</div>
                            @endforelse
                        </div>

                        {{-- Upload Attachment Form --}}
                        <form method="POST" action="{{ route('cases.upload_attachment', $case->gcs_id) }}" enctype="multipart/form-data" class="pt-2 border-top">
                            @csrf
                            <input type="file" name="attachment" required class="form-control-file mb-1.5" style="font-size: 11px;">
                            <input type="text" name="title" class="form-control form-control-sm mb-1.5" placeholder="Document description" style="font-size: 11px; border-radius: 4px;">
                            <button type="submit" class="btn btn-xs btn-primary btn-block font-weight-bold" style="border-radius: 4px;">
                                <i class="fas fa-upload mr-1"></i> Upload Document
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Main Action Box matching Screenshot 1, 2, 3 & 5 --}}
            <div class="case-card mb-3">
                {{-- CASE CURRENTLY LOCKED (If user is not current holder) --}}
                @if(!$isCurrentHolder && !$isDraft && !$isClosed)
                    <div class="mb-4 p-3 border rounded shadow-sm" style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-left: 4px solid #1e40af !important; border-radius: 8px;">
                        <div class="d-flex align-items-center">
                            <div class="mr-3 text-muted">
                                <i class="fas fa-lock" style="font-size: 24px; color: #64748b;"></i>
                            </div>
                            <div>
                                <div class="font-weight-bold text-dark rajdhani" style="font-size: 13.5px; letter-spacing: 0.5px;">
                                    CASE CURRENTLY LOCKED
                                </div>
                                <div class="text-muted" style="font-size: 12px; margin-top: 2px;">
                                    This case has been forwarded to <strong>{{ $case->currentHolder->acc_name ?? 'another officer' }} ({{ $case->currentHolder->acc_desigshort ?: $case->currentHolder->acc_desig }})</strong> and is awaiting action. You cannot submit new decisions until the case is returned or forwarded back to your seat.
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($isClosed)
                    <div class="mb-3 p-3 border rounded text-center small text-muted bg-light">
                        <i class="fas fa-check-circle text-success mr-1"></i> This case is <strong>{{ $case->gcs_status }}</strong>. No pending actions.
                    </div>
                @else
                    {{-- User is Current Holder: Show Scrutiny & Action Form --}}
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="font-weight-bold text-dark rajdhani" style="font-size: 14px;">
                            <i class="fas fa-user-circle text-success mr-1"></i> {{ $user->acc_name }} 
                            <span class="text-muted small ml-1">({{ strtoupper($user->acc_untarea ?: 'PRJ') }})</span>
                            <span class="text-muted mx-1">&bull;</span>
                            <span class="text-secondary small font-weight-bold" style="letter-spacing: 0.5px;">
                                <i class="fas fa-pen mr-1"></i> SCRUTINY &amp; ACTION
                            </span>
                        </div>
                    </div>

                    <form id="caseSendForm" method="POST" action="{{ route('cases.send', $case->gcs_id) }}">
                        @csrf

                        {{-- Hidden fields synced from left side --}}
                        <input type="hidden" name="title" id="sendTitleHidden">
                        <input type="hidden" name="category" id="sendCategoryHidden">
                        <input type="hidden" name="description" id="sendDescriptionHidden">

                        {{-- Remarks & Scrutiny Notes Textarea matching Screenshot 1 & 5 with auto Para number --}}
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="font-weight-bold text-dark small mb-0" style="font-size: 11px; text-transform: uppercase;">
                                    <i class="fas fa-pen-alt text-secondary mr-1"></i> REMARKS &amp; SCRUTINY NOTES
                                </label>
                                <span class="text-muted font-italic" style="font-size: 10px;">
                                    <i class="fas fa-arrows-alt mr-1"></i> Drag corner to resize
                                </span>
                            </div>
                            <textarea id="inlineRemarks" name="remarks" required class="form-control" placeholder="Para {{ $nextRemarkNumber }}. Type your remarks or scrutiny observations here..." style="font-size: 13px; min-height: 110px; height: 110px; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 10px 12px; outline: none; resize: vertical; width: 100%;">{{ $nextRemarkNumber }}. </textarea>
                            
                            {{-- User Quick Remarks Shortcuts matching Screenshot 1 & 5 --}}
                            <div class="mt-2">
                                @if(view()->exists('partials._user_quick_remarks'))
                                    @include('partials._user_quick_remarks', ['targetTextarea' => '#inlineRemarks'])
                                @endif
                            </div>
                        </div>

                        {{-- Send / Forward To Destination Dropdown matching Screenshot 1 & 5 --}}
                        <div class="form-group mb-3 position-relative" id="pcDestDropdownContainer">
                            <label class="font-weight-bold text-muted small mb-1 d-flex justify-content-between" style="font-size: 10px; text-transform: uppercase;">
                                <span><i class="fas fa-paper-plane text-primary mr-1"></i> SEND / FORWARD TO DESTINATION: <span class="text-danger">*</span></span>
                                <span class="text-muted font-italic" style="font-size: 9px; text-transform: none;">Search department, division, or director</span>
                            </label>
                            
                            <input type="hidden" name="target_destination" id="pcTargetDestinationInput" value="" required>

                            {{-- Display Toggle Box --}}
                            <div id="pcDestDropdownToggle" class="form-control form-control-sm d-flex align-items-center justify-content-between" style="font-size: 12px; font-weight: 600; border-radius: 6px; border-color: #cbd5e1; height: 38px; cursor: pointer; background: #ffffff; user-select: none;">
                                <span id="pcDestSelectedLabel" class="text-muted text-truncate font-weight-normal">
                                    <i class="fas fa-search mr-1.5 text-muted"></i> -- Select Destination Department / Authority --
                                </span>
                                <i class="fas fa-chevron-down text-muted ml-2" id="pcDestDropdownChevron" style="font-size: 11px; transition: transform 0.2s;"></i>
                            </div>

                            {{-- Downward Dropdown Menu --}}
                            <div id="pcDestDropdownMenu" class="shadow-lg border bg-white" style="display: none; position: absolute; top: 100% !important; left: 0; right: 0; z-index: 1050; margin-top: 3px; border-radius: 8px; border-color: #cbd5e1 !important; box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;">
                                {{-- Search Input --}}
                                <div class="p-2 border-bottom bg-light">
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0" style="border-color: #cbd5e1;"><i class="fas fa-search text-muted" style="font-size: 11px;"></i></span>
                                        </div>
                                        <input type="text" id="pcDestSearchInput" class="form-control border-left-0" placeholder="Type department, division, or director name..." style="font-size: 12px; border-color: #cbd5e1;" autocomplete="off">
                                    </div>
                                </div>

                                {{-- Scrollable Destination Items --}}
                                <div id="pcDestItemsList" style="max-height: 220px; overflow-y: auto; padding: 4px 0;">
                                    @foreach($destinations as $destCode => $dest)
                                        @php
                                            $searchKeywords = strtolower($dest['name'] . ' ' . ($dest['director'] ?? '') . ' ' . ($dest['desig'] ?? '') . ' ' . $destCode);
                                        @endphp
                                        <div class="pc-dest-option-item px-3 py-2" data-code="{{ $destCode }}" data-name="{{ $dest['name'] }}" data-search="{{ $searchKeywords }}" style="cursor: pointer; transition: background 0.15s; border-bottom: 1px solid #f1f5f9;">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-dark font-weight-bold" style="font-size: 12.5px;">{{ $dest['name'] }}</span>
                                                <span class="badge badge-light border text-muted px-1.5 py-0.5" style="font-size: 9.5px; border-radius: 4px;">{{ $dest['badge'] ?? $destCode }}</span>
                                            </div>
                                            @if(!empty($dest['director']))
                                                <div class="text-muted text-truncate" style="font-size: 11px; margin-top: 1px;">
                                                    <i class="fas fa-user-tie text-secondary mr-1" style="font-size: 9.5px;"></i> {{ $dest['director'] }}
                                                    @if(!empty($dest['desig']))
                                                        <span class="text-muted font-weight-normal">&bull; {{ $dest['desig'] }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                    <div id="pcDestNoResults" class="p-3 text-center text-muted small" style="display: none;">
                                        <i class="fas fa-info-circle mr-1"></i> No matching department, division, or director found.
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons Row: SEND CASE (plus Approve/Reject for Finance/Approver) matching Screenshot 1 & 5 --}}
                        <div class="d-flex align-items-center mb-3" style="gap: 8px;">
                            <button type="submit" onclick="syncLeftToHidden()" id="btnSendCase" class="btn-action-send flex-grow-1">
                                <i class="fas fa-paper-plane mr-2"></i>
                                <span class="font-weight-bold rajdhani">SEND CASE</span>
                            </button>

                            @if($canApprove)
                                {{-- Green Checkmark Approve Button matching Screenshot 5 --}}
                                <button type="button" onclick="submitApprove()" class="btn-action-approve" title="Approve Case File">
                                    <i class="fas fa-check"></i>
                                </button>

                                {{-- Red Cross Reject Button matching Screenshot 5 --}}
                                <button type="button" onclick="submitReject()" class="btn-action-reject" title="Reject / Cancel Case File">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endif
                        </div>
                    </form>

                    {{-- Separate Hidden Form for Approve/Reject by Finance/Authority --}}
                    @if($canApprove)
                        <form id="approveForm" method="POST" action="{{ route('cases.approve', $case->gcs_id) }}" style="display: none;">
                            @csrf
                            <input type="hidden" name="remarks" id="approveRemarksHidden">
                        </form>
                        <form id="rejectForm" method="POST" action="{{ route('cases.cancel', $case->gcs_id) }}" style="display: none;">
                            @csrf
                            <input type="hidden" name="remarks" id="rejectRemarksHidden">
                        </form>
                    @endif
                @endif

                {{-- Remarks History: LATEST ON TOP matching Screenshot 2 & 3 --}}
                <div class="mt-3 pt-3 border-top">
                    @forelse($decisions as $mov)
                        <div class="mb-3 pb-3 border-bottom" style="border-bottom-style: dashed !important;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="font-weight-bold text-dark" style="font-size: 13px;">
                                    <i class="fas fa-user-circle text-success mr-1"></i> {{ $mov->gcm_actor_name }} 
                                    <span class="text-muted">({{ $mov->actor?->acc_desigshort ?: $mov->gcm_actor_rank }})</span>
                                    <span class="text-secondary mx-1">&bull;</span>
                                    <span class="text-primary font-weight-bold" style="font-size: 11px; text-transform: uppercase;">
                                        {{ $mov->gcm_action }} @if($mov->toHolder) to {{ $mov->toHolder->acc_name }} ({{ $mov->toHolder->acc_desigshort ?: $mov->toHolder->acc_untnamesh }}) @endif
                                    </span>
                                </div>
                                <div class="text-muted" style="font-size: 11px;">
                                    {{ $mov->gcm_dtg->format('d M Y, h:i A') }}
                                </div>
                            </div>
                            @php
                                $remarksText = trim($mov->gcm_remarks ?? '');
                                if (!empty($remarksText) && !preg_match('/^\s*\d+[\.\)]/i', $remarksText)) {
                                    $remarksText = ($mov->para_number ?? 1) . ". {$remarksText}";
                                }
                            @endphp
                            <div class="pt-1 text-dark text-left" style="font-size: 13px; line-height: 1.6; white-space: pre-wrap; text-align: left !important; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">{{ $remarksText }}</div>
                        </div>
                    @empty
                        <div class="text-muted small text-center py-2">
                            <em>No remarks yet.</em>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Recent Cases Table matching Screenshot 2 & 3 bottom right --}}
            <div class="case-card">
                <div class="sec-hdr mb-2" style="font-size: 11.5px;">
                    <i class="fas fa-check-circle text-success mr-1"></i> RECENT CASES
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0" style="font-size: 11.5px;">
                        <thead>
                            <tr class="text-muted" style="border-bottom: 1.5px solid #e2e8f0;">
                                <th style="width: 40px;">S.NO</th>
                                <th>CASE TITLE</th>
                                <th style="width: 110px;">CATEGORY</th>
                                <th style="width: 80px;" class="text-right">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentCases as $idx => $rc)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>
                                        <a href="{{ route('cases.show', $rc->gcs_id) }}" class="text-dark font-weight-bold text-truncate d-block" style="max-width: 170px;">
                                            {{ $rc->gcs_title }}
                                        </a>
                                        <span class="text-muted" style="font-size: 10px;">{{ $rc->gcs_file_no }}</span>
                                    </td>
                                    <td class="text-muted">{{ $rc->gcs_category }}</td>
                                    <td class="text-right">
                                        <span class="badge badge-light border text-dark">{{ $rc->gcs_status }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-2">No other cases found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Interactive Scripts --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('pcDestDropdownToggle');
    const menu = document.getElementById('pcDestDropdownMenu');
    const searchInput = document.getElementById('pcDestSearchInput');
    const hiddenInput = document.getElementById('pcTargetDestinationInput');
    const selectedLabel = document.getElementById('pcDestSelectedLabel');
    const chevron = document.getElementById('pcDestDropdownChevron');
    const items = document.querySelectorAll('.pc-dest-option-item');
    const noResults = document.getElementById('pcDestNoResults');

    // Toggle menu
    if (toggle && menu) {
        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = menu.style.display === 'block';
            menu.style.display = isOpen ? 'none' : 'block';
            if (!isOpen && searchInput) {
                setTimeout(() => searchInput.focus(), 50);
            }
            if (chevron) {
                chevron.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
            }
        });

        // Close on click outside
        document.addEventListener('click', function (e) {
            if (!menu.contains(e.target) && !toggle.contains(e.target)) {
                menu.style.display = 'none';
                if (chevron) chevron.style.transform = 'rotate(0deg)';
            }
        });

        // Search filtering
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const query = this.value.toLowerCase().trim();
                let matches = 0;
                items.forEach(item => {
                    const searchData = item.getAttribute('data-search') || '';
                    if (searchData.includes(query)) {
                        item.style.display = 'block';
                        matches++;
                    } else {
                        item.style.display = 'none';
                    }
                });
                if (noResults) {
                    noResults.style.display = (matches === 0) ? 'block' : 'none';
                }
            });
        }

        // Selection
        items.forEach(item => {
            item.addEventListener('click', function () {
                const code = this.getAttribute('data-code');
                const name = this.getAttribute('data-name');
                hiddenInput.value = code;
                selectedLabel.innerHTML = '<i class="fas fa-check-circle text-primary mr-1"></i> ' + name;
                selectedLabel.classList.remove('text-muted');
                selectedLabel.classList.add('text-dark', 'font-weight-bold');
                menu.style.display = 'none';
                if (chevron) chevron.style.transform = 'rotate(0deg)';
            });
        });
    }

    // Attach Smart Numbered Lists to both Side Notes and Remarks textareas
    const notesTextarea = document.getElementById('caseDescriptionTextarea');
    if (notesTextarea) {
        attachSmartNumberedList(notesTextarea, 1);
    }

    const remarksTextarea = document.getElementById('inlineRemarks');
    if (remarksTextarea) {
        attachSmartNumberedList(remarksTextarea, {{ $nextRemarkNumber }});
    }
});

// Reusable Smart Numbered List & Sub-numbering with prefix protection
function attachSmartNumberedList(textarea, defaultStartNum = 1) {
    if (!textarea) return;

    const initialPrefix = defaultStartNum + '. ';

    // Pre-fill if empty or does not start with initialPrefix
    if (!textarea.value || textarea.value.trim() === '') {
        textarea.value = initialPrefix;
    } else if (!textarea.value.startsWith(initialPrefix) && !/^\s*\d+\.\s*/.test(textarea.value)) {
        textarea.value = initialPrefix + textarea.value.trim();
    }

    // Place cursor after prefix on focus if at beginning
    textarea.addEventListener('focus', function () {
        if (!this.value || this.value.trim() === '') {
            this.value = initialPrefix;
        }
        if (this.value.startsWith(initialPrefix) && this.selectionStart < initialPrefix.length) {
            this.setSelectionRange(initialPrefix.length, initialPrefix.length);
        }
    });

    textarea.addEventListener('click', function () {
        if (this.value.startsWith(initialPrefix) && this.selectionStart < initialPrefix.length) {
            this.setSelectionRange(initialPrefix.length, initialPrefix.length);
        }
    });

    // Helper to find previous main number before a given index
    function findPrevMainNumber(val, beforeIndex) {
        const textBefore = val.substring(0, beforeIndex);
        const matches = [...textBefore.matchAll(/(?:^|\n)\s*(\d+)\./g)];
        if (matches.length > 0) {
            return parseInt(matches[matches.length - 1][1], 10);
        }
        return defaultStartNum - 1;
    }

    // Protect the prefix from deletion on input (e.g. select all and delete/cut)
    textarea.addEventListener('input', function () {
        if (!this.value.startsWith(initialPrefix)) {
            const currentVal = this.value;
            if (currentVal.length < initialPrefix.length) {
                this.value = initialPrefix;
            } else {
                this.value = initialPrefix + currentVal.replace(/^(\s*(?:\d+|[a-z])\.\s*)?/i, '');
            }
            this.selectionStart = this.selectionEnd = initialPrefix.length;
        }
    });

    textarea.addEventListener('keydown', function (e) {
        const val = this.value;
        const start = this.selectionStart;
        const end = this.selectionEnd;

        const lastNl = val.lastIndexOf('\n', start - 1);
        const lineStart = lastNl === -1 ? 0 : lastNl + 1;
        const nextNl = val.indexOf('\n', start);
        const lineEnd = nextNl === -1 ? val.length : nextNl;

        const currentLine = val.substring(lineStart, lineEnd);

        // --- PROTECT INITIAL PREFIX FROM BACKSPACE / DELETE / TYPING ---
        if (lineStart === 0) {
            if (start <= initialPrefix.length) {
                if (e.key === 'Backspace' || e.key === 'Delete') {
                    e.preventDefault();
                    return;
                }
                if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey && start < initialPrefix.length) {
                    e.preventDefault();
                    this.selectionStart = this.selectionEnd = initialPrefix.length;
                    return;
                }
            }
        }

        // --- TAB KEY: Indent to sub-item (e.g. 2. -> a. or Shift+Tab outdent) ---
        if (e.key === 'Tab') {
            e.preventDefault();

            if (e.shiftKey) {
                // Outdent: if sub-item like '   a. text' -> convert to main number
                const subMatch = currentLine.match(/^(\s*)([a-z])\.\s*(.*)$/i);
                if (subMatch) {
                    const prevNum = findPrevMainNumber(val, lineStart);
                    const nextMain = (prevNum + 1) + '. ';
                    const rest = subMatch[3];
                    const newLine = nextMain + rest;
                    this.value = val.substring(0, lineStart) + newLine + val.substring(lineEnd);
                    this.selectionStart = this.selectionEnd = lineStart + nextMain.length;
                }
            } else {
                // Line 0 cannot be indented to sub-bullet (First paragraph must remain numbered)
                if (lineStart === 0) {
                    return;
                }
                // Indent: if main item like '2. text' -> convert to sub-item '   a. text'
                const numMatch = currentLine.match(/^(\s*)(\d+)\.\s*(.*)$/);
                if (numMatch) {
                    const indent = '   a. ';
                    const rest = numMatch[3];
                    const newLine = indent + rest;
                    this.value = val.substring(0, lineStart) + newLine + val.substring(lineEnd);
                    this.selectionStart = this.selectionEnd = lineStart + indent.length;
                } else if (!currentLine.match(/^(\s*)([a-z])\.\s*/i)) {
                    const indent = '   a. ';
                    this.value = val.substring(0, lineStart) + indent + currentLine.trimStart() + val.substring(lineEnd);
                    this.selectionStart = this.selectionEnd = lineStart + indent.length;
                }
            }
            return;
        }

        // --- ENTER KEY: Auto-increment number or sub-letter ---
        if (e.key === 'Enter') {
            // 1. Check if sub-item: '   a. text' or '   a. '
            const subMatch = currentLine.match(/^(\s*)([a-z])\.\s*(.*)$/i);
            if (subMatch) {
                e.preventDefault();
                const indent = subMatch[1] || '   ';
                const letter = subMatch[2];
                const content = subMatch[3].trim();

                if (content.length === 0) {
                    // Empty sub-bullet -> unindent to next main number!
                    const prevNum = findPrevMainNumber(val, lineStart);
                    const nextMain = (prevNum + 1) + '. ';
                    this.value = val.substring(0, lineStart) + nextMain + val.substring(lineEnd);
                    this.selectionStart = this.selectionEnd = lineStart + nextMain.length;
                    return;
                } else {
                    const nextLetter = String.fromCharCode(letter.toLowerCase().charCodeAt(0) + 1);
                    const prefix = '\n' + indent + nextLetter + '. ';
                    const before = val.substring(0, start);
                    const after = val.substring(start);
                    this.value = before + prefix + after;
                    this.selectionStart = this.selectionEnd = start + prefix.length;
                    return;
                }
            }

            // 2. Check if main item: '1. text' or '2. '
            const numMatch = currentLine.match(/^(\s*)(\d+)\.\s*(.*)$/);
            if (numMatch) {
                e.preventDefault();
                const num = parseInt(numMatch[2], 10);
                const content = numMatch[3].trim();

                // If lineStart === 0, NEVER clear it!
                if (content.length === 0) {
                    if (lineStart > 0) {
                        this.value = val.substring(0, lineStart) + val.substring(lineEnd);
                        this.selectionStart = this.selectionEnd = lineStart;
                    }
                    return;
                } else {
                    const nextNum = (num + 1) + '. ';
                    const prefix = '\n' + nextNum;
                    const before = val.substring(0, start);
                    const after = val.substring(start);
                    this.value = before + prefix + after;
                    this.selectionStart = this.selectionEnd = start + prefix.length;
                    return;
                }
            }
        }

        // --- BACKSPACE ON NON-FIRST EMPTY BULLET ---
        if (e.key === 'Backspace' && lineStart > 0) {
            const emptyBulletMatch = currentLine.match(/^(\s*(?:\d+|[a-z])\.\s*)$/i);
            if (emptyBulletMatch && start === lineEnd) {
                e.preventDefault();
                this.value = val.substring(0, lineStart) + val.substring(lineEnd);
                this.selectionStart = this.selectionEnd = lineStart;
                return;
            }
        }
    });
}

// Sync left side inputs to hidden fields before sending case
function syncLeftToHidden() {
    const titleInput = document.getElementById('caseTitleInput');
    const catSelect = document.getElementById('caseCategorySelect');
    const descTextarea = document.getElementById('caseDescriptionTextarea');

    if (titleInput) document.getElementById('sendTitleHidden').value = titleInput.value;
    if (catSelect) document.getElementById('sendCategoryHidden').value = catSelect.value;
    if (descTextarea) document.getElementById('sendDescriptionHidden').value = descTextarea.value;
}

// Save draft via AJAX
function saveDraft() {
    const form = document.getElementById('caseDraftForm');
    if (!form) return;

    const btn = document.getElementById('btnSaveDraftTop');
    const statusEl = document.getElementById('leftNotesSaveStatus');
    if (btn) btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Saving...';
    if (statusEl) statusEl.innerHTML = '<span class="text-primary"><i class="fas fa-spinner fa-spin mr-1"></i> Saving note...</span>';

    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || formData.get('_token')
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (btn) {
            btn.innerHTML = '<i class="fas fa-check mr-1 text-success"></i> Saved';
            setTimeout(() => { btn.innerHTML = '<i class="fas fa-save mr-1"></i> Save Draft'; }, 2000);
        }
        if (statusEl) {
            statusEl.innerHTML = '<span class="text-success"><i class="fas fa-check mr-1"></i> Saved</span>';
            setTimeout(() => { statusEl.innerHTML = ''; }, 2500);
        }
    })
    .catch(err => {
        if (btn) btn.innerHTML = '<i class="fas fa-exclamation-circle mr-1 text-danger"></i> Failed';
        if (statusEl) statusEl.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-circle mr-1"></i> Failed</span>';
    });
}

// Approve Case Handler
function submitApprove() {
    if (!confirm('Are you sure you want to APPROVE this case file?')) return;
    const remarks = document.getElementById('inlineRemarks')?.value || '';
    document.getElementById('approveRemarksHidden').value = remarks;
    document.getElementById('approveForm').submit();
}

// Reject Case Handler
function submitReject() {
    const remarks = prompt('Please enter rejection / cancellation remarks:');
    if (remarks === null) return;
    document.getElementById('rejectRemarksHidden').value = remarks;
    document.getElementById('rejectForm').submit();
}
</script>
@endsection
