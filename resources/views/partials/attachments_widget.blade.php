@php
    /**
     * Reusable Attachments Widget
     *
     * Expected variables:
     * @var string $module 'prj' | 'pur' | 'emp' | 'ctc' | 'aud' | 'ina'
     * @var int|string $objectId Parent record ID
     * @var string|null $title Widget title (default: 'Attachments')
     * @var array|null $defaultSlots Array of default document type strings
     * @var \Illuminate\Support\Collection|array|null $attachments Existing attachments collection
     * @var bool|null $canEdit Whether current user can upload/delete (default: true)
     */
    $module = $module ?? 'prj';
    $title = $title ?? 'Attachments';
    $canEdit = $canEdit ?? true;
    $widgetId = 'att_widget_' . $module . '_' . $objectId . '_' . \Illuminate\Support\Str::random(4);

    // Default slots per module if not provided
    if (!isset($defaultSlots)) {
        $defaultSlots = match ($module) {
            'prj' => ['Project Proposal', 'URD', 'Work Order', 'PPF'],
            'pur' => ['Purchase Case', 'Quotation Document', 'Market Research Report', 'Financial Status', 'Minute'],
            'emp' => ['Appointment Letter', 'Form', 'CV', 'Minute'],
            'ctc' => ['CV', 'Approval', 'Form', 'Minute'],
            'aud' => ['Data Revision Case', 'Minute'],
            default => ['Document 1', 'Document 2'],
        };
    }

    // Standardize attachments to collection
    $attachmentCollection = collect($attachments ?? []);

    // Helper to get pk and path keys based on module
    $pkKey = match ($module) {
        'prj' => 'jat_id',
        'pur' => 'pat_id',
        'emp' => 'eat_id',
        'ctc' => 'cat_id',
        'aud' => 'aat_id',
        'ina' => 'iat_id',
        default => 'id',
    };
    $typeKey = match ($module) {
        'prj' => 'jat_type',
        'pur' => 'pat_type',
        'emp' => 'eat_type',
        'ctc' => 'cat_type',
        'aud' => 'aat_type',
        'ina' => 'iat_type',
        default => 'type',
    };
    $pathKey = match ($module) {
        'prj' => 'jat_path',
        'pur' => 'pat_path',
        'emp' => 'eat_path',
        'ctc' => 'cat_path',
        'aud' => 'aat_path',
        'ina' => 'iat_path',
        default => 'path',
    };

    // Calculate uploaded count
    $totalUploaded = $attachmentCollection->filter(fn($a) => !empty(data_get($a, $pathKey)))->count();
@endphp

<div class="rdwis-attachment-widget card border shadow-sm mb-3" id="{{ $widgetId }}" style="border-radius: 8px; overflow: hidden; background: #ffffff;">
    {{-- Header --}}
    <div class="card-header d-flex justify-content-between align-items-center py-2 px-3" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
        <div class="d-flex align-items-center">
            <i class="fas fa-paperclip text-primary mr-2" style="font-size: 14px;"></i>
            <span class="font-weight-bold text-dark" style="font-size: 14px; letter-spacing: 0.3px;">{{ $title }}</span>
            <span class="badge badge-secondary badge-pill ml-2" style="font-size: 11px; font-weight: 500;">{{ $totalUploaded }}</span>
        </div>
        @if($canEdit)
            <button type="button" class="btn btn-sm btn-outline-primary" style="padding: 2px 8px; font-size: 12px; border-radius: 4px; line-height: 1.2;" data-toggle="modal" data-target="#modal_add_{{ $widgetId }}" title="Add New Attachment">
                <i class="fas fa-plus"></i>
            </button>
        @endif
    </div>

    {{-- Slots List --}}
    <div class="list-group list-group-flush" style="font-size: 13px;">
        {{-- 1. Default Standard Slots --}}
        @foreach($defaultSlots as $slotIndex => $slotName)
            @php
                $existing = $attachmentCollection->first(function($item) use ($typeKey, $slotName) {
                    return strcasecmp(trim(data_get($item, $typeKey) ?? ''), trim($slotName)) === 0;
                });
                $filePath = $existing ? data_get($existing, $pathKey) : null;
                $slotId = $existing ? data_get($existing, $pkKey) : null;
                $hasFile = !empty($filePath);
                $fileUrl = $hasFile ? \App\Facades\FileStorage::url($filePath) : null;
                $inputId = "file_input_{$widgetId}_{$slotIndex}";
            @endphp

            <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 border-bottom" style="background: {{ $hasFile ? '#f0fdf4' : '#ffffff' }}; transition: background 0.15s ease;">
                <div class="d-flex align-items-center overflow-hidden mr-2">
                    <i class="fas {{ $hasFile ? 'fa-check-circle text-success' : 'fa-file-alt text-muted' }} mr-2" style="font-size: 13px; width: 16px;"></i>
                    <span class="text-truncate {{ $hasFile ? 'font-weight-bold text-dark' : 'text-secondary' }}" title="{{ $slotName }}" style="font-size: 13px;">
                        {{ $slotName }}
                    </span>
                </div>

                <div class="d-flex align-items-center">
                    @if($hasFile)
                        {{-- View Button --}}
                        <a href="{{ $fileUrl }}" target="_blank" class="btn btn-xs btn-outline-success mr-1 shadow-none" style="padding: 3px 8px; font-size: 11px; border-radius: 4px;" title="View File">
                            <i class="fas fa-eye mr-1"></i> View
                        </a>

                        @if($canEdit)
                            {{-- Replace Form / Button --}}
                            <label for="{{ $inputId }}" class="btn btn-xs btn-outline-secondary mb-0 mr-1 shadow-none" style="padding: 3px 7px; font-size: 11px; cursor: pointer; border-radius: 4px;" title="Replace Document">
                                <i class="fas fa-sync-alt"></i>
                            </label>
                            <form action="{{ route('universal.attachment.upload') }}" method="POST" enctype="multipart/form-data" class="d-none" id="form_{{ $inputId }}">
                                @csrf
                                <input type="hidden" name="module" value="{{ $module }}">
                                <input type="hidden" name="object_id" value="{{ $objectId }}">
                                <input type="hidden" name="doc_type" value="{{ $slotName }}">
                                <input type="file" id="{{ $inputId }}" name="file" onchange="document.getElementById('form_{{ $inputId }}').submit()">
                            </form>

                            {{-- Delete Form / Button --}}
                            <form action="{{ route('universal.attachment.delete', ['module' => $module, 'id' => $slotId]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this attachment?');">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-outline-danger shadow-none" style="padding: 3px 7px; font-size: 11px; border-radius: 4px;" title="Delete Attachment">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        @endif
                    @else
                        {{-- Upload Button for Empty Slot --}}
                        @if($canEdit)
                            <label for="{{ $inputId }}" class="btn btn-xs btn-light border mb-0 shadow-none text-muted" style="padding: 4px 10px; font-size: 12px; cursor: pointer; border-radius: 4px; background: #f1f5f9; border-color: #cbd5e1 !important;" title="Click to Upload {{ $slotName }}">
                                <i class="fas fa-upload text-primary mr-1"></i> Upload
                            </label>
                            <form action="{{ route('universal.attachment.upload') }}" method="POST" enctype="multipart/form-data" class="d-none" id="form_{{ $inputId }}">
                                @csrf
                                <input type="hidden" name="module" value="{{ $module }}">
                                <input type="hidden" name="object_id" value="{{ $objectId }}">
                                <input type="hidden" name="doc_type" value="{{ $slotName }}">
                                <input type="file" id="{{ $inputId }}" name="file" onchange="document.getElementById('form_{{ $inputId }}').submit()">
                            </form>
                        @else
                            <span class="badge badge-light text-muted" style="font-size: 11px;">Not Uploaded</span>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach

        {{-- 2. Custom Additional Attachments (uploaded via +) --}}
        @php
            $customAttachments = $attachmentCollection->reject(function($item) use ($defaultSlots, $typeKey) {
                $t = trim(data_get($item, $typeKey) ?? '');
                foreach ($defaultSlots as $ds) {
                    if (strcasecmp(trim($ds), $t) === 0) return true;
                }
                return false;
            });
        @endphp

        @foreach($customAttachments as $cIndex => $customAtt)
            @php
                $slotName = data_get($customAtt, $typeKey) ?: 'Additional Attachment';
                $filePath = data_get($customAtt, $pathKey);
                $slotId = data_get($customAtt, $pkKey);
                $hasFile = !empty($filePath);
                $fileUrl = $hasFile ? \App\Facades\FileStorage::url($filePath) : null;
            @endphp
            <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 border-bottom" style="background: #f8fafc;">
                <div class="d-flex align-items-center overflow-hidden mr-2">
                    <i class="fas fa-paperclip text-info mr-2" style="font-size: 13px; width: 16px;"></i>
                    <span class="text-truncate font-weight-bold text-dark" title="{{ $slotName }}" style="font-size: 13px;">
                        {{ $slotName }}
                    </span>
                </div>
                <div class="d-flex align-items-center">
                    @if($hasFile)
                        <a href="{{ $fileUrl }}" target="_blank" class="btn btn-xs btn-outline-info mr-1 shadow-none" style="padding: 3px 8px; font-size: 11px; border-radius: 4px;" title="View File">
                            <i class="fas fa-eye mr-1"></i> View
                        </a>
                        @if($canEdit)
                            <form action="{{ route('universal.attachment.delete', ['module' => $module, 'id' => $slotId]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this attachment?');">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-outline-danger shadow-none" style="padding: 3px 7px; font-size: 11px; border-radius: 4px;" title="Delete Attachment">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- Modal for Add (+) Button --}}
    @if($canEdit)
    <div class="modal fade" id="modal_add_{{ $widgetId }}" tabindex="-1" role="dialog" aria-labelledby="modalLabel_{{ $widgetId }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 8px; overflow: hidden; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
                <div class="modal-header py-3 px-4" style="background: #1e293b; color: #ffffff;">
                    <h6 class="modal-title font-weight-bold mb-0" id="modalLabel_{{ $widgetId }}">
                        <i class="fas fa-file-upload mr-2 text-primary"></i> Upload Attachment
                    </h6>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.8;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('universal.attachment.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="module" value="{{ $module }}">
                    <input type="hidden" name="object_id" value="{{ $objectId }}">
                    <div class="modal-body p-4">
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark" style="font-size: 13px;">Document Type / Title <span class="text-danger">*</span></label>
                            <input type="text" name="doc_type" class="form-control form-control-sm" placeholder="e.g. PPF, Minutes, Site Photos, Approval Letter" required style="border-radius: 4px;">
                        </div>
                        <div class="form-group mb-2">
                            <label class="font-weight-bold text-dark" style="font-size: 13px;">Select File <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control-file border p-2" style="border-radius: 4px; background: #f8fafc; font-size: 12px;" required>
                            <small class="text-muted d-block mt-1">Supported formats: PDF, JPG, PNG, DOC, DOCX, XLS, XLSX (Max: 20MB)</small>
                        </div>
                    </div>
                    <div class="modal-footer py-2 px-4 bg-light border-top">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary px-3">
                            <i class="fas fa-upload mr-1"></i> Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
