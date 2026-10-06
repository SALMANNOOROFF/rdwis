@php
    $hrformsEnabled = config('hrforms.enabled', false);
    $caseId = $caseId ?? ($case->ctc_id ?? null);
    $caseForms = ($hrformsEnabled && $caseId)
        ? \App\Models\HrForms\CaseForm::where('case_id', $caseId)
            ->where('status', '!=', 'Pending Removal (Archived)')
            ->orderBy('id')
            ->get()
        : collect([]);
    $formsCount = $caseForms->count();
    $pendingRemovalForms = $caseForms->filter(fn($f) => $f->status === 'Pending Removal');
@endphp

@if($hrformsEnabled && $caseId && $formsCount > 0)
<div class="hrforms-embedded-section mt-3 pt-3 border-top" id="hrforms-container-{{ $caseId }}" data-case-id="{{ $caseId }}" style="border-top: 2px solid #5F7858 !important; background: #fafbfa;">
    {{-- Section Header --}}
    <div class="d-flex justify-content-between align-items-center px-3 py-2" style="background: #eef3ee; border-radius: 6px; margin: 0 8px 10px 8px;">
        <div class="d-flex align-items-center">
            <i class="fas fa-file-contract text-success mr-2" style="font-size: 14px;"></i>
            <span class="font-weight-bold text-dark" style="font-size: 13px; letter-spacing: 0.3px;">HR Policy Forms (Policy 2026)</span>
            <span class="badge badge-success ml-2 px-2 py-0.5" id="hrforms-badge-count-{{ $caseId }}" style="font-size: 11px;">{{ $formsCount }}</span>
        </div>
        <div class="d-flex align-items-center" style="gap: 5px;">
            <a href="{{ route('hrforms.cases.pdf-dossier', $caseId) }}" target="_blank" onclick="event.stopPropagation();" class="btn btn-xs btn-outline-success font-weight-bold" style="padding: 3px 8px; font-size: 11px; border-radius: 4px;" title="Download Consolidated Policy Dossier PDF">
                <i class="fas fa-file-pdf mr-1"></i> Dossier
            </a>
            <button type="button" class="btn btn-xs btn-light border text-dark font-weight-bold" style="padding: 3px 8px; font-size: 11px; border-radius: 4px;" onclick="event.stopPropagation(); window.HrCaseFile.openTrackerModal({{ $caseId }})" title="Annex C Progress Tracker">
                <i class="fas fa-tasks mr-1"></i> Tracker
            </button>
            <button type="button" class="btn btn-xs btn-light border text-dark font-weight-bold" style="padding: 3px 8px; font-size: 11px; border-radius: 4px;" onclick="event.stopPropagation(); window.HrCaseFile.openExtrasModal({{ $caseId }})" title="Project Metadata & Extras">
                <i class="fas fa-project-diagram mr-1"></i> Extras
            </button>
            <button type="button" class="btn btn-xs btn-light border text-dark font-weight-bold" style="padding: 3px 8px; font-size: 11px; border-radius: 4px;" onclick="event.stopPropagation(); window.HrCaseFile.openAuditModal({{ $caseId }})" title="Audit Trail History">
                <i class="fas fa-history mr-1"></i> Audit
            </button>
        </div>
    </div>

    {{-- Pending Removal Banners --}}
    @if($pendingRemovalForms->count() > 0)
        <div class="px-3 mb-2">
            @foreach($pendingRemovalForms as $prf)
                <div class="alert alert-warning py-2 px-3 mb-2 d-flex justify-content-between align-items-center" style="font-size: 11.5px; border-radius: 6px;">
                    <div>
                        <i class="fas fa-exclamation-triangle text-warning mr-1"></i>
                        <strong>{{ $prf->annex }} ({{ $prf->form_code }}):</strong> Marked for removal due to hiring type change, but has user manual entries.
                    </div>
                    <div class="d-flex" style="gap: 4px;">
                        <button type="button" class="btn btn-xs btn-success py-0 px-2 font-weight-bold" onclick="event.stopPropagation(); window.HrCaseFile.decidePendingRemoval({{ $prf->id }}, 'keep', {{ $caseId }})">
                            <i class="fas fa-check mr-1"></i> Keep
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-danger py-0 px-2 font-weight-bold" onclick="event.stopPropagation(); window.HrCaseFile.decidePendingRemoval({{ $prf->id }}, 'remove', {{ $caseId }})">
                            <i class="fas fa-trash-alt mr-1"></i> Archive
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Forms List --}}
    <div class="list-group list-group-flush px-2 mb-2" id="hrforms-list-{{ $caseId }}" style="font-size: 12px;">
        @foreach($caseForms as $form)
            @php
                $isSubmitted = $form->isSubmitted();
                $statusColor = match ($form->status) {
                    'Submitted'       => '#16a34a',
                    'Ready'           => '#0284c7',
                    'Pending Input'   => '#d97706',
                    'Pending Removal' => '#dc2626',
                    'Scheduled'       => '#64748b',
                    default           => '#475569',
                };
                $statusBg = match ($form->status) {
                    'Submitted'       => '#f0fdf4',
                    'Ready'           => '#f0f9ff',
                    'Pending Input'   => '#fffbeb',
                    'Pending Removal' => '#fef2f2',
                    'Scheduled'       => '#f8fafc',
                    default           => '#ffffff',
                };
                $missingCount = count($form->form_data['missing_fields'] ?? []);
                $warnings = $form->form_data['warnings'] ?? [];
            @endphp
            <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 mb-1 border rounded" style="background: {{ $statusBg }}; border-color: #e2e8f0 !important;">
                <div class="d-flex align-items-center overflow-hidden mr-2" style="flex: 1; min-width: 0;">
                    <span class="badge badge-secondary mr-2 font-weight-bold flex-shrink-0" style="font-size: 10px; background: #334155;">{{ $form->annex }}</span>
                    <div class="text-truncate">
                        <div class="font-weight-bold text-dark text-truncate" style="font-size: 12.5px;" title="{{ $form->form_title }}">
                            {{ $form->form_title }}
                            @if($form->instance_key !== 'main')
                                <span class="text-muted small">({{ $form->instance_key }})</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center mt-0.5" style="gap: 6px; font-size: 11px;">
                            <span class="badge badge-light border" style="color: {{ $statusColor }}; border-color: {{ $statusColor }} !important; font-weight: 600;">
                                {{ $form->status }}
                            </span>
                            @if($isSubmitted)
                                <span class="text-muted" style="font-size: 10.5px;"><i class="fas fa-lock text-success mr-0.5"></i> Locked ({{ $form->submitted_at?->format('d M') }})</span>
                            @elseif($missingCount > 0)
                                <span class="text-danger" style="font-size: 10.5px;"><i class="fas fa-exclamation-circle mr-0.5"></i> {{ $missingCount }} missing</span>
                            @else
                                <span class="text-success" style="font-size: 10.5px;"><i class="fas fa-check-circle mr-0.5"></i> Ready</span>
                            @endif
                            @if(count($warnings) > 0)
                                <span class="text-secondary ml-1" title="{{ implode(' • ', $warnings) }}" style="font-size: 10.5px; max-width: 180px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                    <i class="fas fa-info-circle text-info mr-0.5"></i> {{ $warnings[0] }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex align-items-center flex-shrink-0" style="gap: 4px;">
                    <button type="button" class="btn btn-xs btn-outline-primary font-weight-bold py-1 px-2" style="font-size: 11px; border-radius: 4px;" onclick="event.stopPropagation(); window.HrCaseFile.openForm({{ $form->id }}, {{ $caseId }})" title="Open / Edit Form Fields">
                        <i class="fas {{ $isSubmitted ? 'fa-eye' : 'fa-edit' }}"></i> {{ $isSubmitted ? 'View' : 'Edit' }}
                    </button>
                    <a href="{{ route('hrforms.forms.pdf', $form->id) }}" target="_blank" onclick="event.stopPropagation();" class="btn btn-xs btn-outline-secondary font-weight-bold py-1 px-2" style="font-size: 11px; border-radius: 4px;" title="Download PDF (%PDF-)">
                        <i class="fas fa-download"></i> PDF
                    </a>
                    @if(!$isSubmitted && in_array($form->status, ['Draft', 'Pending Input'], true))
                        <button type="button" class="btn btn-xs btn-light border text-muted py-1 px-1.5" style="font-size: 11px; border-radius: 4px;" onclick="event.stopPropagation(); window.HrCaseFile.refreshLive({{ $form->id }}, {{ $caseId }})" title="Refresh Live Data">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    @endif
                    @if(!$isSubmitted && $form->status === 'Ready')
                        <button type="button" class="btn btn-xs btn-success font-weight-bold py-1 px-2" style="font-size: 11px; border-radius: 4px;" onclick="event.stopPropagation(); window.HrCaseFile.submitFormDirect({{ $form->id }}, {{ $caseId }})" title="Mark Form as Submitted">
                            <i class="fas fa-check"></i> Submit
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- Modals and Unified JavaScript included once on the page --}}
@once
    @include('hrforms.modals', ['caseId' => $caseId])
@endonce
