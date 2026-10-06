{{-- Case File Tab Panel (HR Policy Ammended 2026 - RDWIS 2.0) --}}
@php
    $hrformsEnabled = config('hrforms.enabled', false);
    $caseFormsCount = $hrformsEnabled ? \Illuminate\Support\Facades\DB::table('hrforms.case_forms')->where('case_id', $case->ctc_id)->count() : 0;
    $canShowTab = $hrformsEnabled && ($caseFormsCount > 0);
@endphp

@if($canShowTab)
<div id="hrforms-case-file-wrapper" class="mt-4" data-case-id="{{ $case->ctc_id }}">
    {{-- Main Container Card --}}
    <div class="clean-card shadow-sm border-0" style="border-radius: 14px; background: #FFFFFF;">
        {{-- Header & Sub-Tabs --}}
        <div class="clean-card-header d-flex justify-content-between align-items-center" style="background: #FDFDFC; border-bottom: 1.5px solid var(--rd-neutral-200); padding: 1.2rem 1.8rem;">
            <div class="d-flex align-items-center gap-3">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(95, 120, 88, 0.12); display: flex; align-items: center; justify-content: center; color: var(--rd-primary-600); font-size: 1.2rem;">
                    <i class="fas fa-folder-open"></i>
                </div>
                <div>
                    <h5 class="mb-0 font-weight-bold" style="color: var(--rd-text1); letter-spacing: -0.3px;">HR Policy Case File</h5>
                    <small class="text-muted">Annex C Lifecycle & Policy Forms (RDW/HR POLICY/2026)</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.HrCaseFile.loadTab()" title="Reload case file data">
                    <i class="fas fa-sync-alt mr-1"></i> Refresh
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#modalProjectExtras">
                    <i class="fas fa-project-diagram mr-1"></i> Project Extras
                </button>
            </div>
        </div>

        <div class="p-4">
            {{-- 1. Annex C Progress Tracker --}}
            <div class="mb-4 p-3" style="background: #F8FAF8; border: 1px solid rgba(95, 120, 88, 0.18); border-radius: 12px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="font-weight-bold text-uppercase" style="font-size: 0.78rem; letter-spacing: 0.8px; color: var(--rd-primary-600);">
                        <i class="fas fa-tasks mr-1"></i> Annex C Progress Tracker (<span id="tracker-hiring-type-label">Loading...</span>)
                    </span>
                    <small class="text-muted" id="tracker-summary-note"></small>
                </div>

                {{-- Advertisement 14-day warning alert --}}
                <div id="tracker-ad-warning-alert" class="alert alert-warning d-none mb-3" style="border-radius: 8px; font-size: 0.88rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <span id="tracker-ad-warning-text"></span>
                        </div>
                        <button type="button" class="btn btn-sm btn-warning text-dark font-weight-bold" onclick="window.HrCaseFile.openExemptionModal()">
                            Record Exemption
                        </button>
                    </div>
                </div>

                {{-- Stepper Progress Bar --}}
                <div class="stepper-horizontal-container" id="tracker-stepper-container" style="display: flex; overflow-x: auto; gap: 8px; padding-bottom: 8px;">
                    <!-- Dynamically populated steps -->
                </div>
            </div>

            {{-- 2. Pending Removal Banners Container --}}
            <div id="pending-removal-banners" class="mb-3"></div>

            {{-- 3. Case Forms Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                    <thead style="background: #F9FAFB;">
                        <tr style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.6px; color: var(--rd-text3);">
                            <th style="padding: 0.8rem 1rem;">Annex</th>
                            <th style="padding: 0.8rem 1rem;">Form Title</th>
                            <th style="padding: 0.8rem 1rem;">Instance</th>
                            <th style="padding: 0.8rem 1rem;">Requirement</th>
                            <th style="padding: 0.8rem 1rem;">Status</th>
                            <th style="padding: 0.8rem 1rem;">Missing / Warnings</th>
                            <th style="padding: 0.8rem 1rem; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="case-forms-table-body" style="font-size: 0.88rem;">
                        <tr><td colspan="7" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin mr-2"></i> Loading HR Case File forms...</td></tr>
                    </tbody>
                </table>
            </div>

            {{-- 4. Audit History Collapse --}}
            <div class="mt-4 pt-3 border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <a class="text-muted font-weight-bold" data-toggle="collapse" href="#collapseAuditHistory" role="button" aria-expanded="false" style="text-decoration: none; font-size: 0.84rem;">
                        <i class="fas fa-history mr-1"></i> Audit Trail History (<span id="audit-log-count">0</span> records)
                    </a>
                </div>
                <div class="collapse mt-3" id="collapseAuditHistory">
                    <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-sm table-striped mb-0" style="font-size: 0.8rem;">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody id="audit-history-table-body">
                                <tr><td colspan="4" class="text-muted text-center">No audit logs recorded yet.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Form View & Edit Modal --}}
    <div class="modal fade" id="modalFormViewEdit" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content" style="border-radius: 14px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.18);">
                <div class="modal-header" style="background: #FDFDFC; border-bottom: 1.5px solid var(--rd-neutral-200); padding: 1.2rem 1.8rem;">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge badge-primary px-2 py-1" id="modal-form-annex-badge" style="font-size: 0.8rem;"></span>
                            <h5 class="modal-title font-weight-bold mb-0" id="modal-form-title">Form View</h5>
                        </div>
                        <small class="text-muted" id="modal-form-subtitle"></small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span id="modal-form-status-badge"></span>
                        <button type="button" class="close ml-2" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                <div class="modal-body p-4" id="modal-form-body" style="background: #FAFAFA; min-height: 400px;">
                    <!-- Form fields dynamically rendered -->
                </div>
                <div class="modal-footer" style="background: #FFFFFF; border-top: 1.5px solid var(--rd-neutral-200); padding: 1rem 1.8rem;">
                    <div class="mr-auto" id="modal-form-footer-left"></div>
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-outline-info d-none" id="btn-modal-refresh-live" onclick="window.HrCaseFile.refreshCurrentLive()">
                        <i class="fas fa-sync-alt mr-1"></i> Refresh Live
                    </button>
                    <button type="button" class="btn btn-success d-none" id="btn-modal-submit" onclick="window.HrCaseFile.submitCurrentForm()">
                        <i class="fas fa-check-circle mr-1"></i> Mark as Submitted
                    </button>
                    <button type="button" class="btn btn-primary d-none" id="btn-modal-save" onclick="window.HrCaseFile.saveCurrentForm()">
                        <i class="fas fa-save mr-1"></i> Save Form
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Project Extras Modal --}}
    <div class="modal fade" id="modalProjectExtras" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content" style="border-radius: 12px;">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-project-diagram mr-2 text-primary"></i> Project Metadata & Extras</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="form-project-extras" onsubmit="window.HrCaseFile.saveProjectExtras(event)">
                    <div class="modal-body">
                        <p class="text-muted small">These values are entered once per project and automatically reused across Annex A and Annex B.</p>
                        <div class="form-group">
                            <label class="font-weight-bold small">Work Order Number</label>
                            <input type="text" name="work_order_no" id="pe_work_order_no" class="form-control" placeholder="e.g. WO-2026-091">
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold small">Work Order Date</label>
                            <input type="date" name="work_order_date" id="pe_work_order_date" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold small">Warranty Expiry Date</label>
                            <input type="date" name="warranty_expiry" id="pe_warranty_expiry" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold small">Approved Headcount</label>
                            <input type="number" name="approved_headcount" id="pe_approved_headcount" class="form-control" min="1" placeholder="e.g. 5">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Extras</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Advertisement Exemption Modal --}}
    <div class="modal fade" id="modalAdExemption" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content" style="border-radius: 12px;">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-shield-alt mr-2 text-warning"></i> Advertisement 14-Day Exemption</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="form-ad-exemption" onsubmit="window.HrCaseFile.saveAdExemption(event)">
                    <div class="modal-body">
                        <p class="text-muted small">Policy requires at least 14 days between advertisement and interview (Para 13). Record an authorized exemption reason below to clear the warning.</p>
                        <div class="form-group">
                            <label class="font-weight-bold small">Exemption Justification</label>
                            <textarea name="advertisement_exemption_justification" id="ae_justification" class="form-control" rows="3" required placeholder="State urgent project deadline / management authorization..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning text-dark font-weight-bold"><i class="fas fa-check mr-1"></i> Approve Exemption</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
window.HrCaseFile = (function() {
    const caseId = {{ $case->ctc_id }};
    let currentForms = [];
    let activeForm = null;

    function init() {
        loadTab();
    }

    function loadTab() {
        $.getJSON('/hrforms/cases/' + caseId + '/tab', function(res) {
            if (!res.success) return;
            currentForms = res.forms;
            renderTracker(res.tracker);
            renderFormsTable(res.forms);
            renderAuditHistory(res.audit_history);
            populateProjectExtras(res.project_extras);
        }).fail(function(err) {
            console.error('Failed to load HR Case File tab', err);
        });
    }

    function renderTracker(tracker) {
        $('#tracker-hiring-type-label').text(tracker.hiring_type);
        const container = $('#tracker-stepper-container');
        container.empty();

        // Ad Warning
        if (tracker.ad_warning && tracker.ad_warning.has_warning) {
            $('#tracker-ad-warning-text').text(tracker.ad_warning.message);
            $('#tracker-ad-warning-alert').removeClass('d-none');
        } else {
            $('#tracker-ad-warning-alert').addClass('d-none');
        }

        tracker.steps.forEach(function(step, idx) {
            let statusClass = 'secondary';
            let icon = 'clock';
            if (step.status === 'Completed') {
                statusClass = 'success';
                icon = 'check-circle';
            } else if (step.status === 'In Progress') {
                statusClass = 'primary';
                icon = 'spinner fa-spin';
            } else if (step.status === 'Skipped') {
                statusClass = 'light text-muted';
                icon = 'ban';
            }

            const stepHtml = `
                <div class="tracker-step-card p-2 text-center" style="min-width: 110px; flex: 1; background: #FFFFFF; border: 1px solid var(--rd-neutral-200); border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                    <div style="font-size: 0.7rem; color: var(--rd-text3); font-weight: 700;">STEP ${idx + 1}</div>
                    <div style="font-size: 0.8rem; font-weight: 700; color: var(--rd-text1); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${step.title}">
                        ${step.title}
                    </div>
                    <div class="mt-1">
                        <span class="badge badge-${statusClass} px-2 py-1" style="font-size: 0.72rem;">
                            <i class="fas fa-${icon} mr-1"></i> ${step.status}
                        </span>
                    </div>
                    <div class="mt-1 text-muted" style="font-size: 0.68rem;">
                        ${step.event_date ? step.event_date : (step.source || '')}
                    </div>
                </div>
            `;
            container.append(stepHtml);
        });
    }

    function renderFormsTable(forms) {
        const tbody = $('#case-forms-table-body');
        const bannerContainer = $('#pending-removal-banners');
        tbody.empty();
        bannerContainer.empty();

        if (!forms || forms.length === 0) {
            tbody.html('<tr><td colspan="7" class="text-center py-4 text-muted">No forms generated for this case yet.</td></tr>');
            return;
        }

        forms.forEach(function(form) {
            // Pending Removal Banner
            if (form.status === 'Pending Removal') {
                const bannerHtml = `
                    <div class="alert alert-danger d-flex justify-content-between align-items-center mb-2" style="border-radius: 8px;">
                        <div>
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <strong>${form.form_title} (${form.annex})</strong> is marked for <strong>Pending Removal</strong> due to hiring type change. Contains saved user manual data.
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-danger font-weight-bold" onclick="window.HrCaseFile.resolvePendingRemoval(${form.id}, 'remove')">
                                Remove form (data kept in audit)
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary font-weight-bold" onclick="window.HrCaseFile.resolvePendingRemoval(${form.id}, 'keep')">
                                Keep form
                            </button>
                        </div>
                    </div>
                `;
                bannerContainer.append(bannerHtml);
            }

            // Status badge class
            let badgeClass = 'secondary';
            if (form.status === 'Ready') badgeClass = 'info text-white';
            else if (form.status === 'Pending Input') badgeClass = 'warning text-dark';
            else if (form.status === 'Submitted') badgeClass = 'success';
            else if (form.status === 'Scheduled') badgeClass = 'secondary';
            else if (form.status.includes('Pending Removal')) badgeClass = 'danger';

            // Missing fields badge
            let missingHtml = '<span class="text-success small"><i class="fas fa-check-circle mr-1"></i> None</span>';
            if (form.missing_fields && form.missing_fields.length > 0) {
                missingHtml = `<span class="badge badge-warning text-dark" style="font-size: 0.72rem;"><i class="fas fa-exclamation-circle mr-1"></i> ${form.missing_fields.length} missing</span>`;
            }

            // Warnings
            if (form.warnings && form.warnings.length > 0) {
                missingHtml += ` <span class="badge badge-danger ml-1" title="${form.warnings.join(' | ')}" style="font-size: 0.72rem;"><i class="fas fa-exclamation-triangle"></i> Alert</span>`;
            }

            const tr = `
                <tr class="${form.status.includes('Pending Removal') ? 'table-danger' : ''}">
                    <td class="font-weight-bold text-primary" style="font-size: 0.95rem;">${form.annex}</td>
                    <td>
                        <div class="font-weight-bold text-dark">${form.form_title}</div>
                        <small class="text-muted">${form.form_code}</small>
                    </td>
                    <td><span class="badge badge-light border text-muted">${form.instance_key}</span></td>
                    <td><span class="badge badge-light border" style="font-size: 0.72rem; text-transform: uppercase;">${form.requirement_level}</span></td>
                    <td><span class="badge badge-${badgeClass} px-2 py-1">${form.status}</span></td>
                    <td>${missingHtml}</td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-sm ${form.is_submitted ? 'btn-outline-secondary' : 'btn-outline-primary'}" onclick="window.HrCaseFile.openFormModal(${form.id})">
                            <i class="fas ${form.is_submitted ? 'fa-eye' : 'fa-edit'} mr-1"></i> ${form.is_submitted ? 'View Snapshot' : 'Open / Edit'}
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(tr);
        });
    }

    function renderAuditHistory(logs) {
        const tbody = $('#audit-history-table-body');
        $('#audit-log-count').text(logs ? logs.length : 0);
        tbody.empty();

        if (!logs || logs.length === 0) {
            tbody.html('<tr><td colspan="4" class="text-muted text-center">No audit logs recorded yet.</td></tr>');
            return;
        }

        logs.forEach(function(l) {
            const tr = `
                <tr>
                    <td class="text-muted">${l.created_at ? l.created_at.substring(0, 19).replace('T', ' ') : ''}</td>
                    <td class="font-weight-bold">${l.user_name || 'System'}</td>
                    <td><span class="badge badge-light border">${l.action}</span></td>
                    <td>${l.description}</td>
                </tr>
            `;
            tbody.append(tr);
        });
    }

    function openFormModal(formId) {
        $.getJSON('/hrforms/forms/' + formId, function(res) {
            if (!res.success) return;
            activeForm = res.form;
            const isSubmitted = res.is_submitted;
            const data = res.rendered_data || {};

            $('#modal-form-annex-badge').text('Annex ' + activeForm.annex);
            $('#modal-form-title').text(activeForm.form_title);
            $('#modal-form-subtitle').text(activeForm.form_code + ' (' + activeForm.instance_key + ')');
            
            let statusBadge = `<span class="badge badge-${isSubmitted ? 'success' : 'primary'} px-3 py-1 font-weight-bold">${activeForm.status}</span>`;
            $('#modal-form-status-badge').html(statusBadge);

            // Button visibility
            if (isSubmitted) {
                $('#btn-modal-refresh-live').addClass('d-none');
                $('#btn-modal-save').addClass('d-none');
                $('#btn-modal-submit').addClass('d-none');
                $('#modal-form-footer-left').html('<span class="text-muted small"><i class="fas fa-lock mr-1"></i> Form submitted and locked. Showing immutable snapshot.</span>');
            } else {
                $('#btn-modal-refresh-live').removeClass('d-none');
                $('#btn-modal-save').removeClass('d-none');
                if (activeForm.status === 'Ready') {
                    $('#btn-modal-submit').removeClass('d-none');
                } else {
                    $('#btn-modal-submit').addClass('d-none');
                }
                $('#modal-form-footer-left').html('<span class="text-muted small">Fields with <span class="badge badge-info">live</span> are read-only from RDWIS database.</span>');
            }

            renderFormContent(activeForm.form_code, data, isSubmitted);
            $('#modalFormViewEdit').modal('show');
        });
    }

    function renderFormContent(code, data, isSubmitted) {
        const body = $('#modal-form-body');
        body.empty();

        const live = data.live || {};
        const manual = data.manual || {};
        const warnings = data.warnings || [];
        const missing = data.missing_fields || [];

        // Warnings banner
        if (warnings.length > 0) {
            let warnHtml = '<div class="alert alert-danger mb-3" style="border-radius: 8px;"><strong><i class="fas fa-exclamation-triangle mr-1"></i> Alerts:</strong><ul class="mb-0 pl-3">';
            warnings.forEach(function(w) { warnHtml += `<li>${w}</li>`; });
            warnHtml += '</ul></div>';
            body.append(warnHtml);
        }

        // Form Section renderer by code
        if (code === 'RDW/HR/F-08') {
            renderAnnexM(body, live, manual, isSubmitted);
        } else if (code === 'RDW/HR/F-07') {
            renderAnnexJ(body, live, manual, isSubmitted);
        } else if (code === 'RDW/HR/F-09') {
            renderAnnexN(body, live, manual, isSubmitted);
        } else if (code === 'RDW/HR/F-01') {
            renderAnnexA(body, live, manual, isSubmitted);
        } else if (code === 'RDW/HR/F-02') {
            renderAnnexB(body, live, manual, isSubmitted);
        } else if (code === 'ANNEX-T') {
            renderAnnexT(body, live, manual, isSubmitted);
        } else if (code === 'RDW/HR/F-11') {
            renderAnnexU(body, live, manual, isSubmitted);
        } else {
            renderGenericForm(body, live, manual, isSubmitted);
        }
    }

    // Annex M Form View
    function renderAnnexM(body, live, manual, isSubmitted) {
        const marks = manual.marks || {};
        let criteriaRows = '';
        const criteriaList = live.evaluation_criteria || [
            {key: 'technical_expertise_skills', label: 'Technical Expertise & Skills'},
            {key: 'timely_completion_quality_of_work', label: 'Timely Completion & Quality of Work'},
            {key: 'reliability_dependability', label: 'Reliability & Dependability'},
            {key: 'response_under_pressure', label: 'Response under Pressure'},
            {key: 'team_work_collaboration', label: 'Team Work & Collaboration'},
            {key: 'code_of_conduct', label: 'Code of Conduct (Integrity, Discipline, Attendance, Attire, Punctuality)'}
        ];

        criteriaList.forEach(function(c, i) {
            const val = marks[c.key] !== undefined && marks[c.key] !== null ? marks[c.key] : '';
            criteriaRows += `
                <tr>
                    <td class="font-weight-bold text-center" style="width: 50px;">${i + 1}</td>
                    <td>${c.label}</td>
                    <td style="width: 140px;">
                        <input type="number" min="0" max="10" class="form-control form-control-sm text-center annex-m-mark" 
                               data-key="${c.key}" value="${val}" ${isSubmitted ? 'readonly disabled' : ''} oninput="window.HrCaseFile.calcAnnexMTotal()">
                    </td>
                    <td class="text-center text-muted" style="width: 80px;">/ 10</td>
                </tr>
            `;
        });

        const html = `
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="card p-3 border-0 shadow-sm mb-3">
                        <h6 class="font-weight-bold text-primary border-bottom pb-2">Employee Details</h6>
                        <table class="table table-sm mb-0">
                            <tr><td class="text-muted">Employee:</td><td class="font-weight-bold">${live.employee_name || 'N/A'}</td></tr>
                            <tr><td class="text-muted">Designation:</td><td class="font-weight-bold">${live.designation || 'N/A'} (${live.grade || ''})</td></tr>
                            <tr><td class="text-muted">Review Period:</td><td>${live.review_period || 'N/A'}</td></tr>
                            <tr><td class="text-muted">Current Salary:</td><td>Rs. ${live.current_salary ? Number(live.current_salary).toLocaleString() : 'N/A'}</td></tr>
                            <tr><td class="text-muted">Proposed Salary:</td><td class="text-primary font-weight-bold">Rs. ${live.proposed_salary ? Number(live.proposed_salary).toLocaleString() : 'N/A'}</td></tr>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card p-3 border-0 shadow-sm mb-3">
                        <h6 class="font-weight-bold text-primary border-bottom pb-2">Appraisal Summary & Increment Rule</h6>
                        <table class="table table-sm mb-0">
                            <tr><td class="text-muted">Total Marks:</td><td class="font-weight-bold" id="am-total-display">${manual.total_marks || '0'} / 60</td></tr>
                            <tr><td class="text-muted">Percentage:</td><td class="font-weight-bold" id="am-pct-display">${manual.percentage ? manual.percentage + '%' : '0%'}</td></tr>
                            <tr><td class="text-muted">Performance Rating:</td><td class="font-weight-bold text-info" id="am-rating-display">${manual.performance_rating || 'N/A'}</td></tr>
                            <tr><td class="text-muted">Max Allowed Increment:</td><td class="font-weight-bold text-success" id="am-max-inc-display">${manual.max_allowed_increment_pct || 0}%</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card p-3 border-0 shadow-sm mb-3">
                <h6 class="font-weight-bold text-primary mb-2">Performance Evaluation Criteria (10 Marks Each, Total 60)</h6>
                <table class="table table-bordered table-sm">
                    <thead class="bg-light">
                        <tr><th>#</th><th>Evaluation Parameter</th><th class="text-center">Marks (0-10)</th><th class="text-center">Max</th></tr>
                    </thead>
                    <tbody>${criteriaRows}</tbody>
                </table>
            </div>

            <div class="card p-3 border-0 shadow-sm mb-3">
                <div class="form-group mb-2">
                    <label class="font-weight-bold small">Exceptional Performance Citation (Required for > 10% increment, up to 20%)</label>
                    <textarea class="form-control" id="am-citation" rows="2" ${isSubmitted ? 'readonly disabled' : ''} oninput="window.HrCaseFile.calcAnnexMTotal()">${manual.exceptional_performance_citation || ''}</textarea>
                </div>
                <div class="form-group mb-0">
                    <label class="font-weight-bold small">Remarks by Concerned Director <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="am-dir-remarks" rows="2" ${isSubmitted ? 'readonly disabled' : ''}>${manual.remarks_by_concerned_dir || ''}</textarea>
                </div>
            </div>
        `;
        body.html(html);
    }

    function calcAnnexMTotal() {
        let total = 0;
        let allFilled = true;
        let count = 0;

        $('.annex-m-mark').each(function() {
            count++;
            const v = $(this).val();
            if (v !== '' && !isNaN(v)) {
                total += parseInt(v, 10);
            } else {
                allFilled = false;
            }
        });

        const pct = count > 0 ? ((total / 60) * 100).toFixed(1) : 0;
        $('#am-total-display').text(total + ' / 60');
        $('#am-pct-display').text(pct + '%');

        let rating = 'Needs Improvement';
        if (total >= 56) rating = 'Outstanding';
        else if (total >= 51) rating = 'Very Good';
        else if (total >= 41) rating = 'Above Average';
        else if (total >= 31) rating = 'Average';
        $('#am-rating-display').text(rating);

        const citation = $('#am-citation').val().trim();
        let allowed = 0;
        if (pct >= 70.0) {
            allowed = citation.length > 0 ? 20 : 10;
        }
        $('#am-max-inc-display').text(allowed + '%');
    }

    // Annex J Form View
    function renderAnnexJ(body, live, manual, isSubmitted) {
        const scores = manual.scores || {};
        let criteriaRows = '';
        const list = live.evaluation_criteria || [];

        list.forEach(function(c, i) {
            const val = scores[c.key] !== undefined && scores[c.key] !== null ? scores[c.key] : '';
            criteriaRows += `
                <tr>
                    <td class="font-weight-bold text-center" style="width: 50px;">${i + 1}</td>
                    <td>${c.label}</td>
                    <td style="width: 140px;">
                        <input type="number" min="1" max="5" class="form-control form-control-sm text-center annex-j-score" 
                               data-key="${c.key}" value="${val}" ${isSubmitted ? 'readonly disabled' : ''} oninput="window.HrCaseFile.calcAnnexJTotal()">
                    </td>
                    <td class="text-center text-muted" style="width: 80px;">1 to 5</td>
                </tr>
            `;
        });

        const html = `
            <div class="card p-3 border-0 shadow-sm mb-3">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm mb-0">
                            <tr><td class="text-muted">Candidate Name:</td><td class="font-weight-bold text-primary">${live.candidate_name || 'N/A'}</td></tr>
                            <tr><td class="text-muted">Candidate CNIC:</td><td class="font-weight-bold">${live.candidate_cnic || 'N/A'}</td></tr>
                            <tr><td class="text-muted">Position Applied:</td><td>${live.position_applied || 'N/A'} (${live.grade || ''})</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6 text-right">
                        <div class="p-2 bg-light rounded text-center d-inline-block" style="min-width: 180px;">
                            <small class="text-muted font-weight-bold">TOTAL SCORE</small>
                            <h3 class="font-weight-bold text-primary mb-0" id="aj-total-display">${manual.total_score || '0'} / 60</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card p-3 border-0 shadow-sm mb-3">
                <h6 class="font-weight-bold text-primary mb-2">Screening Criteria (12 Items, Scale 1 to 5)</h6>
                <table class="table table-bordered table-sm">
                    <thead class="bg-light">
                        <tr><th>#</th><th>Parameter</th><th class="text-center">Score (1-5)</th><th class="text-center">Scale</th></tr>
                    </thead>
                    <tbody>${criteriaRows}</tbody>
                </table>
            </div>

            <div class="card p-3 border-0 shadow-sm">
                <div class="form-group mb-2">
                    <label class="font-weight-bold small">Interview Date & Time</label>
                    <input type="text" id="aj-datetime" class="form-control form-control-sm" value="${manual.interview_date_time || ''}" ${isSubmitted ? 'readonly disabled' : ''}>
                </div>
                <div class="form-group mb-2">
                    <label class="font-weight-bold small">Remarks by Concerned Director Representative <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="aj-rep-remarks" rows="2" ${isSubmitted ? 'readonly disabled' : ''}>${manual.remarks_by_concerned_dir_rep || ''}</textarea>
                </div>
                <div class="form-group mb-0">
                    <label class="font-weight-bold small">Remarks by Director HR / SO HR <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="aj-hr-remarks" rows="2" ${isSubmitted ? 'readonly disabled' : ''}>${manual.remarks_by_dir_hr_so_hr || ''}</textarea>
                </div>
            </div>
        `;
        body.html(html);
    }

    function calcAnnexJTotal() {
        let total = 0;
        $('.annex-j-score').each(function() {
            const v = $(this).val();
            if (v !== '' && !isNaN(v)) {
                total += parseInt(v, 10);
            }
        });
        $('#aj-total-display').text(total + ' / 60');
    }

    // Annex N Form View
    function renderAnnexN(body, live, manual, isSubmitted) {
        const isDeficit = !live.hr_balance_sufficient;
        const html = `
            <div class="card p-3 border-0 shadow-sm mb-3">
                <h6 class="font-weight-bold text-primary border-bottom pb-2">Financial & Budget Verification</h6>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm mb-0">
                            <tr><td class="text-muted">Project:</td><td class="font-weight-bold">${live.project_name || 'N/A'} (${live.project_code || ''})</td></tr>
                            <tr><td class="text-muted">Total Budget:</td><td>Rs. ${live.total_project_budget ? Number(live.total_project_budget).toLocaleString() : 'N/A'}</td></tr>
                            <tr><td class="text-muted">HR Allocated:</td><td>Rs. ${live.hr_allocated_budget ? Number(live.hr_allocated_budget).toLocaleString() : 'N/A'}</td></tr>
                            <tr><td class="text-muted">HR Utilized:</td><td>Rs. ${live.hr_budget_utilized_to_date ? Number(live.hr_budget_utilized_to_date).toLocaleString() : 'N/A'}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm mb-0">
                            <tr><td class="text-muted">Case Cost:</td><td class="font-weight-bold text-primary">Rs. ${live.case_cost ? Number(live.case_cost).toLocaleString() : 'N/A'}</td></tr>
                            <tr><td class="text-muted">HR Committed (Total):</td><td>Rs. ${live.hr_budget_committed_total ? Number(live.hr_budget_committed_total).toLocaleString() : 'N/A'}</td></tr>
                            <tr><td class="text-muted">HR Balance:</td><td class="font-weight-bold ${isDeficit ? 'text-danger' : 'text-success'}">Rs. ${live.hr_budget_remaining ? Number(live.hr_budget_remaining).toLocaleString() : 'N/A'}</td></tr>
                            <tr><td class="text-muted">Balance Status:</td><td><span class="badge badge-${isDeficit ? 'danger' : 'success'}">${isDeficit ? 'Deficit / Insufficient' : 'Sufficient'}</span></td></tr>
                        </table>
                    </div>
                </div>
            </div>

            ${isDeficit ? `
                <div class="alert alert-warning mb-3">
                    <i class="fas fa-exclamation-triangle mr-2"></i> HR balance is insufficient. Suggested shortfall: <strong>Rs. ${live.suggested_shortfall ? Number(live.suggested_shortfall).toLocaleString() : '0'}</strong>.
                    Shift amount and justification are mandatory below.
                </div>
            ` : ''}

            <div class="card p-3 border-0 shadow-sm mb-3">
                <h6 class="font-weight-bold text-primary mb-3">Budget Adjustment & Performance Status</h6>
                <div class="form-group">
                    <label class="font-weight-bold small">Performance Status <span class="text-danger">*</span></label>
                    <select id="an-perf-status" class="form-control form-control-sm" ${isSubmitted ? 'readonly disabled' : ''}>
                        <option value="Satisfactory" ${(manual.performance_status || '') === 'Satisfactory' ? 'selected' : ''}>Satisfactory</option>
                        <option value="Unsatisfactory" ${(manual.performance_status || '') === 'Unsatisfactory' ? 'selected' : ''}>Unsatisfactory</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold small">Shift Amount ${isDeficit ? '<span class="text-danger">*</span>' : ''}</label>
                    <input type="number" id="an-shift-amount" class="form-control form-control-sm" value="${manual.shift_amount !== null && manual.shift_amount !== undefined ? manual.shift_amount : ''}" ${isSubmitted ? 'readonly disabled' : ''}>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold small">Shift Justification ${isDeficit ? '<span class="text-danger">*</span>' : ''}</label>
                    <textarea id="an-shift-justification" class="form-control" rows="2" ${isSubmitted ? 'readonly disabled' : ''}>${manual.shift_justification || ''}</textarea>
                </div>
                <div class="form-group mb-0">
                    <label class="font-weight-bold small">Director Remarks</label>
                    <textarea id="an-dir-remarks" class="form-control" rows="2" ${isSubmitted ? 'readonly disabled' : ''}>${manual.director_remarks || ''}</textarea>
                </div>
            </div>
        `;
        body.html(html);
    }

    // Annex A Form View
    function renderAnnexA(body, live, manual, isSubmitted) {
        const html = `
            <div class="card p-3 border-0 shadow-sm mb-3">
                <h6 class="font-weight-bold text-primary border-bottom pb-2">Project & Candidate Live Information</h6>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm mb-0">
                            <tr><td class="text-muted">Division:</td><td>${live.division_name || 'N/A'}</td></tr>
                            <tr><td class="text-muted">Project:</td><td class="font-weight-bold">${live.project_title || 'N/A'} (${live.project_code || ''})</td></tr>
                            <tr><td class="text-muted">Proposed Position:</td><td class="font-weight-bold text-primary">${live.proposed_position || 'N/A'} (${live.proposed_grade || ''})</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm mb-0">
                            <tr><td class="text-muted">Monthly Salary:</td><td class="font-weight-bold">Rs. ${live.proposed_monthly_salary ? Number(live.proposed_monthly_salary).toLocaleString() : 'N/A'}</td></tr>
                            <tr><td class="text-muted">Contract Duration:</td><td>${live.contract_duration_months || 'N/A'} months</td></tr>
                            <tr><td class="text-muted">Total Forecast:</td><td class="font-weight-bold text-success">Rs. ${live.total_forecast ? Number(live.total_forecast).toLocaleString() : 'N/A'}</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card p-3 border-0 shadow-sm mb-3">
                <h6 class="font-weight-bold text-primary mb-3">Project & Additional Cost Heads (Manual Layer - Default Null)</h6>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold small">Work Order No</label>
                        <input type="text" id="aa-work-order" class="form-control form-control-sm" value="${manual.work_order_no || ''}" ${isSubmitted ? 'readonly disabled' : ''}>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold small">Work Order Date</label>
                        <input type="date" id="aa-wo-date" class="form-control form-control-sm" value="${manual.work_order_date || ''}" ${isSubmitted ? 'readonly disabled' : ''}>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold small">Service Charges / Taxes (Rs.)</label>
                        <input type="number" id="aa-service-charges" class="form-control form-control-sm" placeholder="null" value="${manual.service_charges_taxes !== null && manual.service_charges_taxes !== undefined ? manual.service_charges_taxes : ''}" ${isSubmitted ? 'readonly disabled' : ''}>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold small">Infrastructure Development (Rs.)</label>
                        <input type="number" id="aa-infra" class="form-control form-control-sm" placeholder="null" value="${manual.infrastructure_development !== null && manual.infrastructure_development !== undefined ? manual.infrastructure_development : ''}" ${isSubmitted ? 'readonly disabled' : ''}>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold small">Overheads (Rs.)</label>
                        <input type="number" id="aa-overheads" class="form-control form-control-sm" placeholder="null" value="${manual.overheads !== null && manual.overheads !== undefined ? manual.overheads : ''}" ${isSubmitted ? 'readonly disabled' : ''}>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold small">Other Cost Heads (Rs.)</label>
                        <input type="number" id="aa-other-costs" class="form-control form-control-sm" placeholder="null" value="${manual.other_cost_heads !== null && manual.other_cost_heads !== undefined ? manual.other_cost_heads : ''}" ${isSubmitted ? 'readonly disabled' : ''}>
                    </div>
                </div>
            </div>
        `;
        body.html(html);
    }

    // Annex B Form View (Selection Board - F-02)
    function renderAnnexB(body, live, manual, isSubmitted) {
        const shortlist = manual.shortlisted_candidates || live.shortlisted_candidates || [];
        const isSingle = manual.single_candidate_mode !== undefined ? manual.single_candidate_mode : (live.single_candidate || false);
        
        let candidateRows = '';
        for (let i = 1; i <= 3; i++) {
            const cand = shortlist[i - 1] || {};
            candidateRows += `
                <tr class="annex-b-candidate-row" data-index="${i}">
                    <td class="font-weight-bold text-center" style="width: 40px;">${i}</td>
                    <td><input type="text" class="form-control form-control-sm ab-cand-name" value="${cand.name || ''}" placeholder="Candidate ${i} Name" ${isSubmitted ? 'readonly disabled' : ''}></td>
                    <td><input type="text" class="form-control form-control-sm ab-cand-qual" value="${cand.qualification || ''}" placeholder="e.g. BS Software Eng" ${isSubmitted ? 'readonly disabled' : ''}></td>
                    <td><input type="text" class="form-control form-control-sm ab-cand-inst" value="${cand.institute || ''}" placeholder="e.g. NUST / FAST" ${isSubmitted ? 'readonly disabled' : ''}></td>
                    <td><input type="text" class="form-control form-control-sm ab-cand-exp" value="${cand.field_experience || cand.experience || ''}" placeholder="e.g. 3 Years" ${isSubmitted ? 'readonly disabled' : ''}></td>
                </tr>
            `;
        }

        const html = `
            <div class="card p-3 border-0 shadow-sm mb-3">
                <h6 class="font-weight-bold text-primary border-bottom pb-2">Hiring Board Candidate Information</h6>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold small">Principal Candidate Selection <span class="text-danger">*</span></label>
                        <input type="text" id="ab-principal" class="form-control form-control-sm font-weight-bold" value="${manual.principal_candidate || live.principal_candidate?.name || ''}" ${isSubmitted ? 'readonly disabled' : ''} placeholder="Principal candidate full name">
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold small">Standby Candidate Selection</label>
                        <input type="text" id="ab-standby" class="form-control form-control-sm" value="${manual.standby_candidate || manual.standby_candidate_name || ''}" ${isSubmitted ? 'readonly disabled' : ''} placeholder="Standby candidate full name">
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold small">Interview Date <span class="text-danger">*</span></label>
                        <input type="date" id="ab-date" class="form-control form-control-sm" value="${manual.interview_date || ''}" ${isSubmitted ? 'readonly disabled' : ''}>
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold small">Interview Time</label>
                        <input type="text" id="ab-time" class="form-control form-control-sm" value="${manual.interview_time || '10:00'}" ${isSubmitted ? 'readonly disabled' : ''}>
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold small">Interview Venue</label>
                        <input type="text" id="ab-venue" class="form-control form-control-sm" value="${manual.interview_venue || 'RDW Conference Room'}" ${isSubmitted ? 'readonly disabled' : ''}>
                    </div>
                </div>

                <div class="border rounded p-3 bg-light mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="font-weight-bold small mb-0"><i class="fas fa-users mr-1"></i> Shortlisted Candidates Entry (Rule: 3 Candidates, or 1 in Single-Candidate Mode)</label>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="ab-single-cand-mode" ${isSingle ? 'checked' : ''} ${isSubmitted ? 'disabled' : ''} onchange="$('#ab-single-justification-box').toggleClass('d-none', !this.checked)">
                            <label class="custom-control-label small font-weight-bold" for="ab-single-cand-mode">Single Candidate Mode</label>
                        </div>
                    </div>
                    <table class="table table-bordered table-sm mb-2 bg-white">
                        <thead class="thead-light">
                            <tr style="font-size: 0.78rem;">
                                <th>#</th>
                                <th>Candidate Name</th>
                                <th>Qualification</th>
                                <th>Institute</th>
                                <th>Field Experience</th>
                            </tr>
                        </thead>
                        <tbody>${candidateRows}</tbody>
                    </table>
                    <div id="ab-single-justification-box" class="${isSingle ? '' : 'd-none'} mt-2">
                        <label class="font-weight-bold small text-danger">Single Candidate Mode Justification <span class="text-danger">*</span></label>
                        <textarea id="ab-single-justification" class="form-control form-control-sm" rows="2" ${isSubmitted ? 'readonly disabled' : ''} placeholder="Explain why only 1 candidate was evaluated (niche skill set, direct project transfer, etc.).">${manual.single_candidate_justification || ''}</textarea>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label class="font-weight-bold small">Board Recommendations <span class="text-danger">*</span></label>
                    <textarea id="ab-recom" class="form-control" rows="2" ${isSubmitted ? 'readonly disabled' : ''}>${manual.board_recommendations || ''}</textarea>
                </div>
            </div>
        `;
        body.html(html);
    }

    // Annex T Form View (Comparison Matrix of Shortlisted Candidates)
    function renderAnnexT(body, live, manual, isSubmitted) {
        const candidates = manual.candidates || live.candidates || [];
        let rows = '';

        for (let i = 1; i <= 3; i++) {
            const cand = candidates[i - 1] || {};
            rows += `
                <tr class="annex-t-candidate-row" data-index="${i}">
                    <td class="font-weight-bold text-center" style="width: 40px;">${i}</td>
                    <td><input type="text" class="form-control form-control-sm at-cand-name font-weight-bold" value="${cand.name || ''}" placeholder="Candidate ${i}" ${isSubmitted ? 'readonly disabled' : ''}></td>
                    <td><input type="text" class="form-control form-control-sm at-cand-cnic" value="${cand.cnic || ''}" placeholder="CNIC" ${isSubmitted ? 'readonly disabled' : ''}></td>
                    <td><input type="text" class="form-control form-control-sm at-cand-qual" value="${cand.qualification || ''}" placeholder="Degree & Major" ${isSubmitted ? 'readonly disabled' : ''}></td>
                    <td><input type="text" class="form-control form-control-sm at-cand-exp" value="${cand.experience_years || ''}" placeholder="Years" ${isSubmitted ? 'readonly disabled' : ''}></td>
                    <td><input type="text" class="form-control form-control-sm at-cand-skills" value="${cand.skills || ''}" placeholder="Technical Skills" ${isSubmitted ? 'readonly disabled' : ''}></td>
                    <td><input type="text" class="form-control form-control-sm at-cand-remarks" value="${cand.remarks || ''}" placeholder="Selection Suitability" ${isSubmitted ? 'readonly disabled' : ''}></td>
                </tr>
            `;
        }

        const html = `
            <div class="card p-3 border-0 shadow-sm mb-3">
                <h6 class="font-weight-bold text-primary border-bottom pb-2">Comparison Matrix of Shortlisted Candidates</h6>
                <div class="row mb-2">
                    <div class="col-md-6"><small class="text-muted">Position Applied:</small> <span class="font-weight-bold">${live.position_applied || 'N/A'} (${live.grade || ''})</span></div>
                    <div class="col-md-6 text-right"><small class="text-muted">Division / Project:</small> <span class="font-weight-bold">${live.division_name || ''} - ${live.project_name || ''}</span></div>
                </div>

                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="bg-light" style="font-size: 0.78rem;">
                            <tr>
                                <th>#</th>
                                <th>Candidate Name</th>
                                <th>CNIC</th>
                                <th>Qualification</th>
                                <th>Exp (Years)</th>
                                <th>Key Skills</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>

                <div class="form-group mb-3">
                    <label class="font-weight-bold small">Justification for Qualification / Student Relaxation (Para 29) <span class="text-muted">(Required if relaxed)</span></label>
                    <textarea id="at-justification" class="form-control" rows="2" ${isSubmitted ? 'readonly disabled' : ''} placeholder="Enter relaxation justification if student or qualification requirements were relaxed...">${manual.justification_relaxation || ''}</textarea>
                </div>

                <div class="form-group mb-0">
                    <label class="font-weight-bold small">Director Signature / Selection Board Remarks <span class="text-danger">*</span></label>
                    <textarea id="at-dir-remarks" class="form-control" rows="2" ${isSubmitted ? 'readonly disabled' : ''}>${manual.director_signature_remarks || 'Recommended for Selection Board interview'}</textarea>
                </div>
            </div>
        `;
        body.html(html);
    }

    // Annex U Form View (Personal Data Form - F-11)
    function renderAnnexU(body, live, manual, isSubmitted) {
        const sections = [
            '1. Personal Information',
            '2. Next of Kin Details',
            '3. Emergency Contact Details',
            '4. Education (Matric to Highest Degree)',
            '5. Professional Courses & Certifications',
            '6. Professional Experience History',
            '7. Vehicle Ownership Details',
            '8. Bank Account Details (Meezan Bank)',
            '9. Research Publications & Dissertation',
            '10. References (Two Independent Referees)'
        ];

        let sectionList = '';
        sections.forEach(function(s, idx) {
            sectionList += `<li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                <span class="font-weight-500">${s}</span>
                <span class="badge badge-success px-2 py-1"><i class="fas fa-check"></i> Standard Section</span>
            </li>`;
        });

        const html = `
            <div class="card p-3 border-0 shadow-sm mb-3">
                <h6 class="font-weight-bold text-primary border-bottom pb-2">Personal Data Form (RDW/HR/F-11) — 10 Policy Sections</h6>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <table class="table table-sm mb-0">
                            <tr><td class="text-muted">Candidate Name:</td><td class="font-weight-bold text-primary">${live.candidate_name || 'N/A'}</td></tr>
                            <tr><td class="text-muted">CNIC:</td><td class="font-weight-bold">${live.cnic || 'N/A'}</td></tr>
                            <tr><td class="text-muted">Designation & Grade:</td><td>${live.designation || 'N/A'} (${live.grade || ''})</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm mb-0">
                            <tr><td class="text-muted">Project:</td><td class="font-weight-bold">${live.project_title || 'N/A'}</td></tr>
                            <tr><td class="text-muted">Reporting Division:</td><td>${live.division_name || 'N/A'}</td></tr>
                            <tr><td class="text-muted">Bank Head:</td><td>${live.bank_account?.bank_name || 'Meezan Bank Ltd'}</td></tr>
                        </table>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="font-weight-bold small text-muted text-uppercase mb-2">Form Structure Checklist</label>
                    <ul class="list-group list-group-flush border rounded">${sectionList}</ul>
                </div>

                <div class="form-group mb-0">
                    <label class="font-weight-bold small">Candidate Undertaking / Remarks</label>
                    <textarea id="au-undertaking" class="form-control" rows="2" ${isSubmitted ? 'readonly disabled' : ''}>${manual.undertaking || 'I hereby affirm that all information provided in this personal data form is correct and true to the best of my knowledge.'}</textarea>
                </div>
            </div>
        `;
        body.html(html);
    }

    // Generic fallback renderer
    function renderGenericForm(body, live, manual, isSubmitted) {
        let liveFields = '';
        Object.keys(live).forEach(function(k) {
            if (typeof live[k] !== 'object') {
                liveFields += `<tr><td class="text-muted small">${k}</td><td class="font-weight-bold small">${live[k] || 'N/A'}</td></tr>`;
            }
        });

        let manualFields = '';
        Object.keys(manual).forEach(function(k) {
            if (typeof manual[k] !== 'object') {
                manualFields += `
                    <div class="form-group">
                        <label class="font-weight-bold small">${k}</label>
                        <input type="text" class="form-control form-control-sm generic-manual-input" data-key="${k}" value="${manual[k] || ''}" ${isSubmitted ? 'readonly disabled' : ''}>
                    </div>
                `;
            }
        });

        const html = `
            <div class="row">
                <div class="col-md-6">
                    <div class="card p-3 border-0 shadow-sm mb-3">
                        <h6 class="font-weight-bold text-primary border-bottom pb-2">Live Information</h6>
                        <table class="table table-sm mb-0">${liveFields}</table>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card p-3 border-0 shadow-sm mb-3">
                        <h6 class="font-weight-bold text-primary border-bottom pb-2">Manual Layer</h6>
                        ${manualFields || '<p class="text-muted small">No specific manual fields for this form.</p>'}
                    </div>
                </div>
            </div>
        `;
        body.html(html);
    }

    function saveCurrentForm() {
        if (!activeForm) return;
        const code = activeForm.form_code;
        const manual = {};

        if (code === 'RDW/HR/F-08') {
            const marks = {};
            $('.annex-m-mark').each(function() {
                const k = $(this).data('key');
                const v = $(this).val();
                marks[k] = v !== '' ? parseInt(v, 10) : null;
            });
            manual.marks = marks;
            manual.exceptional_performance_citation = $('#am-citation').val();
            manual.remarks_by_concerned_dir = $('#am-dir-remarks').val();
        } else if (code === 'RDW/HR/F-07') {
            const scores = {};
            $('.annex-j-score').each(function() {
                const k = $(this).data('key');
                const v = $(this).val();
                scores[k] = v !== '' ? parseInt(v, 10) : null;
            });
            manual.scores = scores;
            manual.interview_date_time = $('#aj-datetime').val();
            manual.remarks_by_concerned_dir_rep = $('#aj-rep-remarks').val();
            manual.remarks_by_dir_hr_so_hr = $('#aj-hr-remarks').val();
        } else if (code === 'RDW/HR/F-09') {
            manual.performance_status = $('#an-perf-status').val();
            const sa = $('#an-shift-amount').val();
            manual.shift_amount = sa !== '' ? parseFloat(sa) : null;
            manual.shift_justification = $('#an-shift-justification').val();
            manual.director_remarks = $('#an-dir-remarks').val();
        } else if (code === 'RDW/HR/F-01') {
            manual.work_order_no = $('#aa-work-order').val();
            manual.work_order_date = $('#aa-wo-date').val();
            manual.service_charges_taxes = $('#aa-service-charges').val() !== '' ? parseFloat($('#aa-service-charges').val()) : null;
            manual.infrastructure_development = $('#aa-infra').val() !== '' ? parseFloat($('#aa-infra').val()) : null;
            manual.overheads = $('#aa-overheads').val() !== '' ? parseFloat($('#aa-overheads').val()) : null;
            manual.other_cost_heads = $('#aa-other-costs').val() !== '' ? parseFloat($('#aa-other-costs').val()) : null;
        } else if (code === 'RDW/HR/F-02') {
            manual.principal_candidate = $('#ab-principal').val();
            manual.standby_candidate = $('#ab-standby').val();
            manual.interview_date = $('#ab-date').val();
            manual.interview_time = $('#ab-time').val();
            manual.interview_venue = $('#ab-venue').val();
            manual.board_recommendations = $('#ab-recom').val();
            manual.single_candidate_mode = $('#ab-single-cand-mode').is(':checked');
            manual.single_candidate_justification = $('#ab-single-justification').val();

            const shortlist = [];
            $('.annex-b-candidate-row').each(function() {
                const name = $(this).find('.ab-cand-name').val();
                if (name && name.trim().length > 0) {
                    shortlist.push({
                        name: name.trim(),
                        qualification: $(this).find('.ab-cand-qual').val() || null,
                        institute: $(this).find('.ab-cand-inst').val() || null,
                        field_experience: $(this).find('.ab-cand-exp').val() || null,
                    });
                }
            });
            manual.shortlisted_candidates = shortlist;
        } else if (code === 'ANNEX-T') {
            const candidates = [];
            $('.annex-t-candidate-row').each(function() {
                const name = $(this).find('.at-cand-name').val();
                if (name && name.trim().length > 0) {
                    candidates.push({
                        name: name.trim(),
                        cnic: $(this).find('.at-cand-cnic').val() || null,
                        qualification: $(this).find('.at-cand-qual').val() || null,
                        experience_years: $(this).find('.at-cand-exp').val() || null,
                        skills: $(this).find('.at-cand-skills').val() || null,
                        remarks: $(this).find('.at-cand-remarks').val() || null,
                    });
                }
            });
            manual.candidates = candidates;
            manual.justification_relaxation = $('#at-justification').val();
            manual.director_signature_remarks = $('#at-dir-remarks').val();
        } else if (code === 'RDW/HR/F-11') {
            manual.undertaking = $('#au-undertaking').val();
        } else {
            $('.generic-manual-input').each(function() {
                const k = $(this).data('key');
                manual[k] = $(this).val();
            });
        }

        $.ajax({
            url: '/hrforms/forms/' + activeForm.id,
            type: 'PUT',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                manual: manual
            },
            success: function(res) {
                if (res.success) {
                    Swal.fire({icon: 'success', title: 'Saved', text: res.message, timer: 1500, showConfirmButton: false});
                    $('#modalFormViewEdit').modal('hide');
                    loadTab();
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to save form';
                Swal.fire({icon: 'error', title: 'Validation Error', text: msg});
            }
        });
    }

    function refreshCurrentLive() {
        if (!activeForm) return;
        $.post('/hrforms/forms/' + activeForm.id + '/refresh', {
            _token: $('meta[name="csrf-token"]').attr('content')
        }, function(res) {
            if (res.success) {
                Swal.fire({icon: 'success', title: 'Refreshed', text: res.message, timer: 1500, showConfirmButton: false});
                openFormModal(activeForm.id);
            }
        }).fail(function(xhr) {
            Swal.fire({icon: 'error', title: 'Error', text: xhr.responseJSON ? xhr.responseJSON.message : 'Refresh failed'});
        });
    }

    function submitCurrentForm() {
        if (!activeForm) return;
        Swal.fire({
            title: 'Submit and Lock Form?',
            text: 'Once marked as submitted, this form will be frozen into an immutable snapshot and locked from further edits.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Yes, Mark as Submitted'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('/hrforms/forms/' + activeForm.id + '/submit', {
                    _token: $('meta[name="csrf-token"]').attr('content')
                }, function(res) {
                    if (res.success) {
                        Swal.fire({icon: 'success', title: 'Submitted', text: res.message});
                        $('#modalFormViewEdit').modal('hide');
                        loadTab();
                    }
                }).fail(function(xhr) {
                    Swal.fire({icon: 'error', title: 'Cannot Submit', text: xhr.responseJSON ? xhr.responseJSON.message : 'Submission failed'});
                });
            }
        });
    }

    function resolvePendingRemoval(formId, decision) {
        $.post('/hrforms/forms/' + formId + '/pending-action', {
            _token: $('meta[name="csrf-token"]').attr('content'),
            decision: decision
        }, function(res) {
            if (res.success) {
                Swal.fire({icon: 'success', title: 'Action Recorded', text: res.message, timer: 1500, showConfirmButton: false});
                loadTab();
            }
        });
    }

    function populateProjectExtras(pe) {
        if (!pe) return;
        $('#pe_work_order_no').val(pe.work_order_no || '');
        $('#pe_work_order_date').val(pe.work_order_date || '');
        $('#pe_warranty_expiry').val(pe.warranty_expiry || '');
        $('#pe_approved_headcount').val(pe.approved_headcount || '');
    }

    function saveProjectExtras(e) {
        e.preventDefault();
        const data = $('#form-project-extras').serialize();
        $.ajax({
            url: '/hrforms/cases/' + caseId + '/project-extras',
            type: 'PUT',
            data: data + '&_token=' + $('meta[name="csrf-token"]').attr('content'),
            success: function(res) {
                if (res.success) {
                    Swal.fire({icon: 'success', title: 'Saved', text: res.message, timer: 1500, showConfirmButton: false});
                    $('#modalProjectExtras').modal('hide');
                    loadTab();
                }
            }
        });
    }

    function openExemptionModal() {
        $('#modalAdExemption').modal('show');
    }

    function saveAdExemption(e) {
        e.preventDefault();
        const just = $('#ae_justification').val();
        $.ajax({
            url: '/hrforms/forms/' + (currentForms[0] ? currentForms[0].id : 0),
            type: 'PUT',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                case_extra: {
                    advertisement_exemption: true,
                    advertisement_exemption_justification: just
                }
            },
            success: function(res) {
                Swal.fire({icon: 'success', title: 'Exemption Recorded', text: 'Advertisement period warning has been cleared.'});
                $('#modalAdExemption').modal('hide');
                loadTab();
            }
        });
    }

    $(document).ready(function() {
        init();
    });

    return {
        loadTab: loadTab,
        openFormModal: openFormModal,
        saveCurrentForm: saveCurrentForm,
        refreshCurrentLive: refreshCurrentLive,
        submitCurrentForm: submitCurrentForm,
        resolvePendingRemoval: resolvePendingRemoval,
        calcAnnexMTotal: calcAnnexMTotal,
        calcAnnexJTotal: calcAnnexJTotal,
        saveProjectExtras: saveProjectExtras,
        openExemptionModal: openExemptionModal,
        saveAdExemption: saveAdExemption
    };
})();
</script>
@endif
