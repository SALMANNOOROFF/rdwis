{{-- HR Policy Forms Unified Modals & Script --}}
<div id="hrforms-modals-container">
    {{-- 1. Form View & Edit Modal --}}
    <div class="modal fade" id="modalFormViewEdit" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" style="z-index: 1065;">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                <div class="modal-header" style="background: #1e293b; color: #ffffff; padding: 1rem 1.5rem;">
                    <div>
                        <div class="d-flex align-items-center" style="gap: 8px;">
                            <span class="badge badge-primary px-2 py-1" id="modal-form-annex-badge" style="font-size: 0.8rem; background: #5F7858;"></span>
                            <h5 class="modal-title font-weight-bold mb-0 text-white" id="modal-form-title" style="letter-spacing: 0.3px;">Form View</h5>
                        </div>
                        <small class="text-light" id="modal-form-subtitle" style="opacity: 0.85;"></small>
                    </div>
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <span id="modal-form-status-badge"></span>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="outline: none;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                <div class="modal-body p-4" id="modal-form-body" style="background: #f8fafc; min-height: 420px;">
                    <!-- Dynamically rendered fields -->
                </div>
                <div class="modal-footer" style="background: #ffffff; border-top: 1px solid #e2e8f0; padding: 0.8rem 1.5rem;">
                    <div class="mr-auto" id="modal-form-footer-left"></div>
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-sm btn-outline-info d-none" id="btn-modal-refresh-live" onclick="window.HrCaseFile.refreshCurrentLive()">
                        <i class="fas fa-sync-alt mr-1"></i> Refresh Live
                    </button>
                    <button type="button" class="btn btn-sm btn-success d-none" id="btn-modal-submit" onclick="window.HrCaseFile.submitCurrentForm()">
                        <i class="fas fa-check-circle mr-1"></i> Submit Form
                    </button>
                    <button type="button" class="btn btn-sm btn-primary d-none" id="btn-modal-save" onclick="window.HrCaseFile.saveCurrentForm()" style="background: #5F7858; border-color: #5F7858;">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Annex C Progress Tracker Modal --}}
    <div class="modal fade" id="modalAnnexCTracker" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1065;">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                <div class="modal-header" style="background: #1e293b; color: #ffffff;">
                    <h6 class="modal-title font-weight-bold mb-0">
                        <i class="fas fa-tasks text-success mr-2"></i> Annex C Hiring Lifecycle Tracker
                    </h6>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-4" style="background: #f8fafc;">
                    <div id="tracker-ad-warning-alert" class="alert alert-warning d-none mb-3" style="border-radius: 6px; font-size: 12px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <span id="tracker-ad-warning-text"></span>
                            </div>
                            <button type="button" class="btn btn-xs btn-warning text-dark font-weight-bold" onclick="window.HrCaseFile.openExemptionModal()">
                                Record Exemption
                            </button>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="font-weight-bold text-dark small" id="tracker-modal-hiring-type"></span>
                        <small class="text-muted" id="tracker-modal-summary"></small>
                    </div>
                    <div id="tracker-stepper-container" class="d-flex flex-wrap" style="gap: 8px;">
                        <!-- Stepper cards dynamically populated -->
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-white border-top">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Project Extras Modal --}}
    <div class="modal fade" id="modalProjectExtras" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1065;">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                <div class="modal-header" style="background: #1e293b; color: #ffffff;">
                    <h6 class="modal-title font-weight-bold mb-0">
                        <i class="fas fa-project-diagram text-primary mr-2"></i> Project Metadata & Extras
                    </h6>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="form-project-extras" onsubmit="window.HrCaseFile.saveProjectExtras(event)">
                    <div class="modal-body p-4">
                        <p class="text-muted small mb-3">These values are shared across Annex A and Annex B for this project.</p>
                        <div class="form-group mb-2">
                            <label class="font-weight-bold small text-dark">Work Order Number</label>
                            <input type="text" name="work_order_no" id="pe_work_order_no" class="form-control form-control-sm" placeholder="e.g. WO-2026-091">
                        </div>
                        <div class="form-group mb-2">
                            <label class="font-weight-bold small text-dark">Work Order Date</label>
                            <input type="date" name="work_order_date" id="pe_work_order_date" class="form-control form-control-sm">
                        </div>
                        <div class="form-group mb-2">
                            <label class="font-weight-bold small text-dark">Warranty Expiry Date</label>
                            <input type="date" name="warranty_expiry" id="pe_warranty_expiry" class="form-control form-control-sm">
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold small text-dark">Approved Headcount</label>
                            <input type="number" name="approved_headcount" id="pe_approved_headcount" class="form-control form-control-sm" min="1" placeholder="e.g. 5">
                        </div>
                    </div>
                    <div class="modal-footer py-2 px-3 bg-light border-top">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary px-3 font-weight-bold" style="background: #5F7858; border-color: #5F7858;">Save Extras</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 4. Advertisement Exemption Modal --}}
    <div class="modal fade" id="modalAdExemption" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1065;">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                <div class="modal-header" style="background: #1e293b; color: #ffffff;">
                    <h6 class="modal-title font-weight-bold mb-0">
                        <i class="fas fa-shield-alt text-warning mr-2"></i> Advertisement 14-Day Exemption
                    </h6>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="form-ad-exemption" onsubmit="window.HrCaseFile.saveAdExemption(event)">
                    <div class="modal-body p-4">
                        <p class="text-muted small mb-3">Policy requires at least 14 days between advertisement and interview (Para 13). Record an authorized exemption reason below to clear the warning.</p>
                        <div class="form-group mb-2">
                            <label class="font-weight-bold small text-dark">Exemption Reason <span class="text-danger">*</span></label>
                            <textarea id="ad_exemption_reason" class="form-control form-control-sm" rows="3" placeholder="Enter reason approved by MD RDW..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer py-2 px-3 bg-light border-top">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-warning font-weight-bold">Save Exemption</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 5. Audit History Modal --}}
    <div class="modal fade" id="modalAuditHistory" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1065;">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                <div class="modal-header" style="background: #1e293b; color: #ffffff;">
                    <h6 class="modal-title font-weight-bold mb-0">
                        <i class="fas fa-history text-info mr-2"></i> HR Policy Forms Audit Trail
                    </h6>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-3">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-striped mb-0" style="font-size: 11.5px;">
                            <thead class="bg-light">
                                <tr>
                                    <th>Date & Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody id="modal-audit-history-tbody">
                                <tr><td colspan="4" class="text-center text-muted py-3">Loading audit trail...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light border-top">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.HrCaseFile = (function() {
    let activeForm = null;
    let currentCaseId = {{ $caseId ?? 0 }};

    function openForm(formId, caseId) {
        currentCaseId = caseId || currentCaseId;
        $.getJSON('/hrforms/forms/' + formId, function(res) {
            if (!res.success) return;
            activeForm = res.form;
            const isSubmitted = res.is_submitted;
            const data = res.rendered_data || {};

            $('#modal-form-annex-badge').text('Annex ' + (activeForm.annex || ''));
            $('#modal-form-title').text(activeForm.form_title || 'HR Policy Form');
            $('#modal-form-subtitle').text(activeForm.form_code + ' (' + (activeForm.instance_key || 'main') + ')');

            let statusBadge = `<span class="badge badge-${isSubmitted ? 'success' : 'primary'} px-3 py-1 font-weight-bold">${activeForm.status}</span>`;
            $('#modal-form-status-badge').html(statusBadge);

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
                $('#modal-form-footer-left').html('<span class="text-muted small">Fields with <span class="badge badge-info">LIVE</span> are read-only from RDWIS database.</span>');
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

        if (warnings.length > 0) {
            let warnHtml = '<div class="alert alert-info py-2 px-3 mb-3" style="border-radius: 6px; font-size: 12px;"><strong><i class="fas fa-info-circle mr-1"></i> Policy Guidance:</strong><ul class="mb-0 pl-3">';
            warnings.forEach(function(w) { warnHtml += `<li>${w}</li>`; });
            warnHtml += '</ul></div>';
            body.append(warnHtml);
        }

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
                    <td class="font-weight-bold text-center" style="width: 40px;">${i + 1}</td>
                    <td>${c.label}</td>
                    <td style="width: 140px;">
                        <input type="number" min="0" max="10" class="form-control form-control-sm text-center annex-m-mark" 
                               data-key="${c.key}" value="${val}" ${isSubmitted ? 'readonly disabled' : ''} oninput="window.HrCaseFile.calcAnnexMTotal()">
                    </td>
                    <td class="text-center text-muted" style="width: 70px;">/ 10</td>
                </tr>
            `;
        });

        const html = `
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="card p-3 border shadow-sm mb-3 bg-white">
                        <h6 class="font-weight-bold text-primary border-bottom pb-2">Employee Details</h6>
                        <table class="table table-sm mb-0" style="font-size: 12px;">
                            <tr><td class="text-muted">Employee:</td><td class="font-weight-bold">${live.employee_name || 'N/A'}</td></tr>
                            <tr><td class="text-muted">Designation:</td><td class="font-weight-bold">${live.designation || 'N/A'} (${live.grade || ''})</td></tr>
                            <tr><td class="text-muted">Review Period:</td><td>${live.review_period || 'N/A'}</td></tr>
                            <tr><td class="text-muted">Current Salary:</td><td>Rs. ${live.current_salary ? Number(live.current_salary).toLocaleString() : 'N/A'}</td></tr>
                            <tr><td class="text-muted">Proposed Salary:</td><td class="text-primary font-weight-bold">Rs. ${live.proposed_salary ? Number(live.proposed_salary).toLocaleString() : 'N/A'}</td></tr>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card p-3 border shadow-sm mb-3 bg-white">
                        <h6 class="font-weight-bold text-primary border-bottom pb-2">Appraisal Summary & Increment Rule</h6>
                        <table class="table table-sm mb-0" style="font-size: 12px;">
                            <tr><td class="text-muted">Total Marks:</td><td class="font-weight-bold" id="am-total-display">${manual.total_marks || '0'} / 60</td></tr>
                            <tr><td class="text-muted">Percentage:</td><td class="font-weight-bold" id="am-pct-display">${manual.percentage ? manual.percentage + '%' : '0%'}</td></tr>
                            <tr><td class="text-muted">Performance Rating:</td><td class="font-weight-bold text-info" id="am-rating-display">${manual.performance_rating || 'N/A'}</td></tr>
                            <tr><td class="text-muted">Max Allowed Increment:</td><td class="font-weight-bold text-success" id="am-max-inc-display">${manual.max_allowed_increment_pct || 0}%</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card p-3 border shadow-sm mb-3 bg-white">
                <h6 class="font-weight-bold text-primary mb-2">Performance Evaluation Criteria (10 Marks Each, Total 60)</h6>
                <table class="table table-bordered table-sm mb-0" style="font-size: 12px;">
                    <thead class="bg-light">
                        <tr><th>#</th><th>Evaluation Parameter</th><th class="text-center">Marks (0-10)</th><th class="text-center">Max</th></tr>
                    </thead>
                    <tbody>${criteriaRows}</tbody>
                </table>
            </div>

            <div class="card p-3 border shadow-sm mb-3 bg-white">
                <div class="form-group mb-2">
                    <label class="font-weight-bold small">Exceptional Performance Citation (Required for > 10% increment, up to 20%)</label>
                    <textarea class="form-control form-control-sm" id="am-citation" rows="2" ${isSubmitted ? 'readonly disabled' : ''} oninput="window.HrCaseFile.calcAnnexMTotal()">${manual.exceptional_performance_citation || ''}</textarea>
                </div>
                <div class="form-group mb-0">
                    <label class="font-weight-bold small">Remarks by Concerned Director <span class="text-danger">*</span></label>
                    <textarea class="form-control form-control-sm" id="am-dir-remarks" rows="2" ${isSubmitted ? 'readonly disabled' : ''}>${manual.remarks_by_concerned_dir || ''}</textarea>
                </div>
            </div>
        `;
        body.html(html);
    }

    function calcAnnexMTotal() {
        let total = 0;
        let count = 0;
        $('.annex-m-mark').each(function() {
            count++;
            const v = $(this).val();
            if (v !== '' && !isNaN(v)) {
                total += parseInt(v, 10);
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

        const citation = ($('#am-citation').val() || '').trim();
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
                    <td class="font-weight-bold text-center" style="width: 40px;">${i + 1}</td>
                    <td>${c.label}</td>
                    <td style="width: 140px;">
                        <input type="number" min="1" max="5" class="form-control form-control-sm text-center annex-j-score" 
                               data-key="${c.key}" value="${val}" ${isSubmitted ? 'readonly disabled' : ''} oninput="window.HrCaseFile.calcAnnexJTotal()">
                    </td>
                    <td class="text-center text-muted" style="width: 70px;">1 to 5</td>
                </tr>
            `;
        });

        const html = `
            <div class="card p-3 border shadow-sm mb-3 bg-white">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm mb-0" style="font-size: 12px;">
                            <tr><td class="text-muted">Candidate Name:</td><td class="font-weight-bold text-primary">${live.candidate_name || 'N/A'}</td></tr>
                            <tr><td class="text-muted">Candidate CNIC:</td><td class="font-weight-bold">${live.candidate_cnic || 'N/A'}</td></tr>
                            <tr><td class="text-muted">Position Applied:</td><td>${live.position_applied || 'N/A'} (${live.grade || ''})</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6 text-right">
                        <div class="p-2 bg-light rounded text-center d-inline-block border" style="min-width: 160px;">
                            <small class="text-muted font-weight-bold">TOTAL SCORE</small>
                            <h4 class="font-weight-bold text-primary mb-0" id="aj-total-display">${manual.total_score || '0'} / 60</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card p-3 border shadow-sm mb-3 bg-white">
                <h6 class="font-weight-bold text-primary mb-2">Screening Criteria (12 Items, Scale 1 to 5)</h6>
                <table class="table table-bordered table-sm mb-0" style="font-size: 12px;">
                    <thead class="bg-light">
                        <tr><th>#</th><th>Parameter</th><th class="text-center">Score (1-5)</th><th class="text-center">Scale</th></tr>
                    </thead>
                    <tbody>${criteriaRows}</tbody>
                </table>
            </div>

            <div class="card p-3 border shadow-sm bg-white">
                <div class="form-group mb-2">
                    <label class="font-weight-bold small">Interview Date & Time</label>
                    <input type="text" id="aj-datetime" class="form-control form-control-sm" value="${manual.interview_date_time || ''}" ${isSubmitted ? 'readonly disabled' : ''}>
                </div>
                <div class="form-group mb-2">
                    <label class="font-weight-bold small">Remarks by Concerned Director Representative <span class="text-danger">*</span></label>
                    <textarea class="form-control form-control-sm" id="aj-rep-remarks" rows="2" ${isSubmitted ? 'readonly disabled' : ''}>${manual.remarks_by_concerned_dir_rep || ''}</textarea>
                </div>
                <div class="form-group mb-0">
                    <label class="font-weight-bold small">Remarks by Director HR / SO HR <span class="text-danger">*</span></label>
                    <textarea class="form-control form-control-sm" id="aj-hr-remarks" rows="2" ${isSubmitted ? 'readonly disabled' : ''}>${manual.remarks_by_dir_hr_so_hr || ''}</textarea>
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
            <div class="card p-3 border shadow-sm mb-3 bg-white">
                <h6 class="font-weight-bold text-primary border-bottom pb-2">Financial & Budget Verification</h6>
                <div class="row" style="font-size: 12px;">
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
                <div class="alert alert-warning py-2 px-3 mb-3" style="font-size: 12px; border-radius: 6px;">
                    <i class="fas fa-exclamation-triangle mr-1"></i> HR balance is insufficient. Suggested shortfall: <strong>Rs. ${live.suggested_shortfall ? Number(live.suggested_shortfall).toLocaleString() : '0'}</strong>.
                    Shift amount and justification are mandatory below.
                </div>
            ` : ''}

            <div class="card p-3 border shadow-sm mb-3 bg-white">
                <h6 class="font-weight-bold text-primary mb-3">Budget Adjustment & Performance Status</h6>
                <div class="form-group mb-2">
                    <label class="font-weight-bold small">Performance Status <span class="text-danger">*</span></label>
                    <select id="an-perf-status" class="form-control form-control-sm" ${isSubmitted ? 'readonly disabled' : ''}>
                        <option value="Satisfactory" ${(manual.performance_status || '') === 'Satisfactory' ? 'selected' : ''}>Satisfactory</option>
                        <option value="Unsatisfactory" ${(manual.performance_status || '') === 'Unsatisfactory' ? 'selected' : ''}>Unsatisfactory</option>
                    </select>
                </div>
                <div class="form-group mb-2">
                    <label class="font-weight-bold small">Shift Amount ${isDeficit ? '<span class="text-danger">*</span>' : ''}</label>
                    <input type="number" id="an-shift-amount" class="form-control form-control-sm" value="${manual.shift_amount !== null && manual.shift_amount !== undefined ? manual.shift_amount : ''}" ${isSubmitted ? 'readonly disabled' : ''}>
                </div>
                <div class="form-group mb-2">
                    <label class="font-weight-bold small">Shift Justification ${isDeficit ? '<span class="text-danger">*</span>' : ''}</label>
                    <textarea id="an-shift-justification" class="form-control form-control-sm" rows="2" ${isSubmitted ? 'readonly disabled' : ''}>${manual.shift_justification || ''}</textarea>
                </div>
                <div class="form-group mb-0">
                    <label class="font-weight-bold small">Director Remarks</label>
                    <textarea id="an-dir-remarks" class="form-control form-control-sm" rows="2" ${isSubmitted ? 'readonly disabled' : ''}>${manual.director_remarks || ''}</textarea>
                </div>
            </div>
        `;
        body.html(html);
    }

    // Annex A Form View
    function renderAnnexA(body, live, manual, isSubmitted) {
        const html = `
            <div class="card p-3 border shadow-sm mb-3 bg-white">
                <h6 class="font-weight-bold text-primary border-bottom pb-2">Project & Candidate Live Information</h6>
                <div class="row" style="font-size: 12px;">
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

            <div class="card p-3 border shadow-sm mb-3 bg-white">
                <h6 class="font-weight-bold text-primary mb-3">Project & Additional Cost Heads (Manual Layer - Default Null)</h6>
                <div class="row">
                    <div class="col-md-6 form-group mb-2">
                        <label class="font-weight-bold small">Work Order No</label>
                        <input type="text" id="aa-work-order" class="form-control form-control-sm" value="${manual.work_order_no || ''}" ${isSubmitted ? 'readonly disabled' : ''}>
                    </div>
                    <div class="col-md-6 form-group mb-2">
                        <label class="font-weight-bold small">Work Order Date</label>
                        <input type="date" id="aa-wo-date" class="form-control form-control-sm" value="${manual.work_order_date || ''}" ${isSubmitted ? 'readonly disabled' : ''}>
                    </div>
                    <div class="col-md-6 form-group mb-2">
                        <label class="font-weight-bold small">Service Charges / Taxes (Rs.)</label>
                        <input type="number" id="aa-service-charges" class="form-control form-control-sm" placeholder="null" value="${manual.service_charges_taxes !== null && manual.service_charges_taxes !== undefined ? manual.service_charges_taxes : ''}" ${isSubmitted ? 'readonly disabled' : ''}>
                    </div>
                    <div class="col-md-6 form-group mb-2">
                        <label class="font-weight-bold small">Infrastructure Development (Rs.)</label>
                        <input type="number" id="aa-infra" class="form-control form-control-sm" placeholder="null" value="${manual.infrastructure_development !== null && manual.infrastructure_development !== undefined ? manual.infrastructure_development : ''}" ${isSubmitted ? 'readonly disabled' : ''}>
                    </div>
                    <div class="col-md-6 form-group mb-2">
                        <label class="font-weight-bold small">Overheads (Rs.)</label>
                        <input type="number" id="aa-overheads" class="form-control form-control-sm" placeholder="null" value="${manual.overheads !== null && manual.overheads !== undefined ? manual.overheads : ''}" ${isSubmitted ? 'readonly disabled' : ''}>
                    </div>
                    <div class="col-md-6 form-group mb-0">
                        <label class="font-weight-bold small">Other Cost Heads (Rs.)</label>
                        <input type="number" id="aa-other-costs" class="form-control form-control-sm" placeholder="null" value="${manual.other_cost_heads !== null && manual.other_cost_heads !== undefined ? manual.other_cost_heads : ''}" ${isSubmitted ? 'readonly disabled' : ''}>
                    </div>
                </div>
            </div>
        `;
        body.html(html);
    }

    // Annex B Form View
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
            <div class="card p-3 border shadow-sm mb-3 bg-white">
                <h6 class="font-weight-bold text-primary border-bottom pb-2">Hiring Board Candidate Information</h6>
                <div class="row">
                    <div class="col-md-6 form-group mb-2">
                        <label class="font-weight-bold small">Principal Candidate Selection <span class="text-danger">*</span></label>
                        <input type="text" id="ab-principal" class="form-control form-control-sm font-weight-bold" value="${manual.principal_candidate || live.principal_candidate?.name || ''}" ${isSubmitted ? 'readonly disabled' : ''} placeholder="Principal candidate full name">
                    </div>
                    <div class="col-md-6 form-group mb-2">
                        <label class="font-weight-bold small">Standby Candidate Selection</label>
                        <input type="text" id="ab-standby" class="form-control form-control-sm" value="${manual.standby_candidate || manual.standby_candidate_name || ''}" ${isSubmitted ? 'readonly disabled' : ''} placeholder="Standby candidate full name">
                    </div>
                    <div class="col-md-4 form-group mb-2">
                        <label class="font-weight-bold small">Interview Date <span class="text-danger">*</span></label>
                        <input type="date" id="ab-date" class="form-control form-control-sm" value="${manual.interview_date || ''}" ${isSubmitted ? 'readonly disabled' : ''}>
                    </div>
                    <div class="col-md-4 form-group mb-2">
                        <label class="font-weight-bold small">Interview Time</label>
                        <input type="text" id="ab-time" class="form-control form-control-sm" value="${manual.interview_time || '10:00'}" ${isSubmitted ? 'readonly disabled' : ''}>
                    </div>
                    <div class="col-md-4 form-group mb-2">
                        <label class="font-weight-bold small">Interview Venue</label>
                        <input type="text" id="ab-venue" class="form-control form-control-sm" value="${manual.interview_venue || 'RDW Conference Room'}" ${isSubmitted ? 'readonly disabled' : ''}>
                    </div>
                </div>

                <div class="border rounded p-3 bg-light mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="font-weight-bold small mb-0"><i class="fas fa-users mr-1"></i> Shortlisted Candidates (Rule: 3 Candidates, or 1 in Single Mode)</label>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="ab-single-cand-mode" ${isSingle ? 'checked' : ''} ${isSubmitted ? 'disabled' : ''} onchange="$('#ab-single-justification-box').toggleClass('d-none', !this.checked)">
                            <label class="custom-control-label small font-weight-bold" for="ab-single-cand-mode">Single Candidate Mode</label>
                        </div>
                    </div>
                    <table class="table table-bordered table-sm mb-2 bg-white" style="font-size: 12px;">
                        <thead class="thead-light">
                            <tr>
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
                        <textarea id="ab-single-justification" class="form-control form-control-sm" rows="2" ${isSubmitted ? 'readonly disabled' : ''} placeholder="Explain why only 1 candidate was evaluated...">${manual.single_candidate_justification || ''}</textarea>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label class="font-weight-bold small">Board Recommendations <span class="text-danger">*</span></label>
                    <textarea id="ab-recom" class="form-control form-control-sm" rows="2" ${isSubmitted ? 'readonly disabled' : ''}>${manual.board_recommendations || ''}</textarea>
                </div>
            </div>
        `;
        body.html(html);
    }

    // Annex T Form View
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
            <div class="card p-3 border shadow-sm mb-3 bg-white">
                <h6 class="font-weight-bold text-primary border-bottom pb-2">Comparison Matrix of Shortlisted Candidates</h6>
                <div class="row mb-2" style="font-size: 12px;">
                    <div class="col-md-6"><small class="text-muted">Position Applied:</small> <span class="font-weight-bold">${live.position_applied || 'N/A'} (${live.grade || ''})</span></div>
                    <div class="col-md-6 text-right"><small class="text-muted">Division / Project:</small> <span class="font-weight-bold">${live.division_name || ''} - ${live.project_name || ''}</span></div>
                </div>

                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm mb-0" style="font-size: 12px;">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Candidate Name</th>
                                <th>CNIC</th>
                                <th>Qualification</th>
                                <th>Exp</th>
                                <th>Key Skills</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>

                <div class="form-group mb-2">
                    <label class="font-weight-bold small">Justification for Qualification / Student Relaxation (Para 29) <span class="text-muted">(Optional)</span></label>
                    <textarea id="at-justification" class="form-control form-control-sm" rows="2" ${isSubmitted ? 'readonly disabled' : ''} placeholder="Enter relaxation justification if student or qualification requirements were relaxed...">${manual.justification_relaxation || ''}</textarea>
                </div>

                <div class="form-group mb-0">
                    <label class="font-weight-bold small">Director Signature / Selection Board Remarks <span class="text-danger">*</span></label>
                    <textarea id="at-dir-remarks" class="form-control form-control-sm" rows="2" ${isSubmitted ? 'readonly disabled' : ''}>${manual.director_signature_remarks || 'Recommended for Selection Board interview'}</textarea>
                </div>
            </div>
        `;
        body.html(html);
    }

    // Annex U Form View
    function renderAnnexU(body, live, manual, isSubmitted) {
        const sections = [
            '1. Personal Information', '2. Next of Kin Details', '3. Emergency Contact Details',
            '4. Education History', '5. Professional Courses', '6. Experience History',
            '7. Vehicle Ownership', '8. Bank Account Details', '9. Research Publications', '10. Referees'
        ];
        let sectionList = '';
        sections.forEach(function(s) {
            sectionList += `<li class="list-group-item d-flex justify-content-between align-items-center py-1.5 px-3" style="font-size: 12px;">
                <span>${s}</span>
                <span class="badge badge-success px-2 py-0.5"><i class="fas fa-check"></i> Standard Section</span>
            </li>`;
        });

        const html = `
            <div class="card p-3 border shadow-sm mb-3 bg-white">
                <h6 class="font-weight-bold text-primary border-bottom pb-2">Personal Data Form (10 Standard Sections)</h6>
                <ul class="list-group list-group-flush mb-3">${sectionList}</ul>
                <div class="form-group mb-0">
                    <label class="font-weight-bold small">Applicant Signature / Submission Notes <span class="text-danger">*</span></label>
                    <textarea id="au-notes" class="form-control form-control-sm" rows="2" ${isSubmitted ? 'readonly disabled' : ''}>${manual.applicant_signature_notes || 'All 10 sections completed as per RDW/HR/F-11 standard format.'}</textarea>
                </div>
            </div>
        `;
        body.html(html);
    }

    function renderGenericForm(body, live, manual, isSubmitted) {
        let html = '<div class="card p-3 border shadow-sm bg-white"><h6 class="font-weight-bold text-primary mb-3">Form Manual Layer Fields</h6>';
        html += '<p class="text-muted small">Enter required manual fields below:</p>';
        html += `<div class="form-group"><label class="small font-weight-bold">Notes / Justification</label><textarea id="gen-notes" class="form-control" rows="3" ${isSubmitted ? 'readonly disabled' : ''}>${manual.notes || ''}</textarea></div></div>`;
        body.html(html);
    }

    function collectManualData(code) {
        const out = {};
        if (code === 'RDW/HR/F-08') {
            out.marks = {};
            $('.annex-m-mark').each(function() {
                const k = $(this).data('key');
                const v = $(this).val();
                out.marks[k] = (v !== '' && !isNaN(v)) ? parseInt(v, 10) : null;
            });
            out.exceptional_performance_citation = $('#am-citation').val();
            out.remarks_by_concerned_dir = $('#am-dir-remarks').val();
        } else if (code === 'RDW/HR/F-07') {
            out.scores = {};
            $('.annex-j-score').each(function() {
                const k = $(this).data('key');
                const v = $(this).val();
                out.scores[k] = (v !== '' && !isNaN(v)) ? parseInt(v, 10) : null;
            });
            out.interview_date_time = $('#aj-datetime').val();
            out.remarks_by_concerned_dir_rep = $('#aj-rep-remarks').val();
            out.remarks_by_dir_hr_so_hr = $('#aj-hr-remarks').val();
        } else if (code === 'RDW/HR/F-09') {
            out.performance_status = $('#an-perf-status').val();
            const sa = $('#an-shift-amount').val();
            out.shift_amount = (sa !== '' && !isNaN(sa)) ? parseFloat(sa) : null;
            out.shift_justification = $('#an-shift-justification').val();
            out.director_remarks = $('#an-dir-remarks').val();
        } else if (code === 'RDW/HR/F-01') {
            out.work_order_no = $('#aa-work-order').val();
            out.work_order_date = $('#aa-wo-date').val();
            const sc = $('#aa-service-charges').val();
            out.service_charges_taxes = (sc !== '' && !isNaN(sc)) ? parseFloat(sc) : null;
            const inf = $('#aa-infra').val();
            out.infrastructure_development = (inf !== '' && !isNaN(inf)) ? parseFloat(inf) : null;
            const oh = $('#aa-overheads').val();
            out.overheads = (oh !== '' && !isNaN(oh)) ? parseFloat(oh) : null;
            const oth = $('#aa-other-costs').val();
            out.other_cost_heads = (oth !== '' && !isNaN(oth)) ? parseFloat(oth) : null;
        } else if (code === 'RDW/HR/F-02') {
            out.principal_candidate = $('#ab-principal').val();
            out.standby_candidate = $('#ab-standby').val();
            out.interview_date = $('#ab-date').val();
            out.interview_time = $('#ab-time').val();
            out.interview_venue = $('#ab-venue').val();
            out.single_candidate_mode = $('#ab-single-cand-mode').is(':checked');
            out.single_candidate_justification = $('#ab-single-justification').val();
            out.board_recommendations = $('#ab-recom').val();

            out.shortlisted_candidates = [];
            $('.annex-b-candidate-row').each(function() {
                out.shortlisted_candidates.push({
                    name: $(this).find('.ab-cand-name').val(),
                    qualification: $(this).find('.ab-cand-qual').val(),
                    institute: $(this).find('.ab-cand-inst').val(),
                    field_experience: $(this).find('.ab-cand-exp').val(),
                });
            });
        } else if (code === 'ANNEX-T') {
            out.candidates = [];
            $('.annex-t-candidate-row').each(function() {
                out.candidates.push({
                    name: $(this).find('.at-cand-name').val(),
                    cnic: $(this).find('.at-cand-cnic').val(),
                    qualification: $(this).find('.at-cand-qual').val(),
                    experience_years: $(this).find('.at-cand-exp').val(),
                    skills: $(this).find('.at-cand-skills').val(),
                    remarks: $(this).find('.at-cand-remarks').val(),
                });
            });
            out.justification_relaxation = $('#at-justification').val();
            out.director_signature_remarks = $('#at-dir-remarks').val();
        } else if (code === 'RDW/HR/F-11') {
            out.applicant_signature_notes = $('#au-notes').val();
        } else {
            out.notes = $('#gen-notes').val();
        }
        return out;
    }

    function saveCurrentForm() {
        if (!activeForm) return;
        const manual = collectManualData(activeForm.form_code);
        const payload = {
            manual: manual,
            _token: $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
        };

        $.ajax({
            url: '/hrforms/forms/' + activeForm.id,
            method: 'PUT',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            success: function(res) {
                if (res.success) {
                    if (window.Swal) {
                        Swal.fire({toast: true, position: 'top-end', icon: 'success', title: 'Form saved successfully', showConfirmButton: false, timer: 2000});
                    }
                    $('#modalFormViewEdit').modal('hide');
                    refreshSection(currentCaseId);
                } else {
                    alert(res.message || 'Error saving form');
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Error saving form');
            }
        });
    }

    function refreshCurrentLive() {
        if (!activeForm) return;
        refreshLive(activeForm.id, currentCaseId);
    }

    function refreshLive(formId, caseId) {
        currentCaseId = caseId || currentCaseId;
        $.ajax({
            url: '/hrforms/forms/' + formId + '/refresh',
            method: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}' },
            success: function(res) {
                if (res.success) {
                    if (window.Swal) {
                        Swal.fire({toast: true, position: 'top-end', icon: 'success', title: 'Live data refreshed', showConfirmButton: false, timer: 2000});
                    }
                    if ($('#modalFormViewEdit').hasClass('show')) {
                        openForm(formId, currentCaseId);
                    } else {
                        refreshSection(currentCaseId);
                    }
                }
            }
        });
    }

    function submitCurrentForm() {
        if (!activeForm) return;
        submitFormDirect(activeForm.id, currentCaseId);
    }

    function submitFormDirect(formId, caseId) {
        currentCaseId = caseId || currentCaseId;
        const doSubmit = function() {
            $.ajax({
                url: '/hrforms/forms/' + formId + '/submit',
                method: 'POST',
                data: { _token: $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}' },
                success: function(res) {
                    if (res.success) {
                        if (window.Swal) {
                            Swal.fire({icon: 'success', title: 'Form Submitted', text: 'Form locked with immutable snapshot.'});
                        }
                        $('#modalFormViewEdit').modal('hide');
                        refreshSection(currentCaseId);
                    }
                },
                error: function(xhr) {
                    alert(xhr.responseJSON?.message || 'Cannot submit form');
                }
            });
        };

        if (window.Swal) {
            Swal.fire({
                title: 'Submit and Lock Form?',
                text: 'This will lock all fields and generate the final immutable PDF copy.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Submit',
                confirmButtonColor: '#16a34a'
            }).then((res) => { if (res.isConfirmed) doSubmit(); });
        } else {
            if (confirm('Submit and lock this form?')) doSubmit();
        }
    }

    function decidePendingRemoval(formId, decision, caseId) {
        currentCaseId = caseId || currentCaseId;
        $.ajax({
            url: '/hrforms/forms/' + formId + '/pending-action',
            method: 'POST',
            data: {
                decision: decision,
                _token: $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
            },
            success: function(res) {
                if (res.success) {
                    refreshSection(currentCaseId);
                }
            }
        });
    }

    function openTrackerModal(caseId) {
        currentCaseId = caseId || currentCaseId;
        $.getJSON('/hrforms/cases/' + currentCaseId + '/tab', function(res) {
            if (!res.success) return;
            const tracker = res.tracker || {};
            $('#tracker-modal-hiring-type').text('Hiring Type: ' + (res.hiring_type || 'Fresh'));
            $('#tracker-modal-summary').text(tracker.summary || '');

            const container = $('#tracker-stepper-container');
            container.empty();

            if (tracker.ad_warning) {
                $('#tracker-ad-warning-alert').removeClass('d-none');
                $('#tracker-ad-warning-text').text(tracker.ad_warning);
            } else {
                $('#tracker-ad-warning-alert').addClass('d-none');
            }

            (tracker.steps || []).forEach(function(s, idx) {
                let badgeClass = s.status === 'Completed' ? 'success' : (s.status === 'In Progress' ? 'primary' : 'secondary');
                container.append(`
                    <div class="card p-2 text-center bg-white border" style="min-width: 130px; flex: 1;">
                        <small class="text-muted font-weight-bold">STEP ${idx + 1}</small>
                        <div class="font-weight-bold text-dark my-1" style="font-size: 11.5px;">${s.title}</div>
                        <div><span class="badge badge-${badgeClass} px-2 py-0.5" style="font-size: 10px;">${s.status}</span></div>
                        <small class="text-muted mt-1" style="font-size: 10px;">${s.event_date || s.source || ''}</small>
                    </div>
                `);
            });

            $('#modalAnnexCTracker').modal('show');
        });
    }

    function openExtrasModal(caseId) {
        currentCaseId = caseId || currentCaseId;
        $.getJSON('/hrforms/cases/' + currentCaseId + '/tab', function(res) {
            if (!res.success) return;
            const pe = res.project_extras || {};
            $('#pe_work_order_no').val(pe.work_order_no || '');
            $('#pe_work_order_date').val(pe.work_order_date || '');
            $('#pe_warranty_expiry').val(pe.warranty_expiry || '');
            $('#pe_approved_headcount').val(pe.approved_headcount || '');
            $('#modalProjectExtras').modal('show');
        });
    }

    function saveProjectExtras(e) {
        e.preventDefault();
        const payload = {
            work_order_no: $('#pe_work_order_no').val(),
            work_order_date: $('#pe_work_order_date').val(),
            warranty_expiry: $('#pe_warranty_expiry').val(),
            approved_headcount: $('#pe_approved_headcount').val(),
            _token: $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
        };

        $.ajax({
            url: '/hrforms/cases/' + currentCaseId + '/project-extras',
            method: 'PUT',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            success: function(res) {
                if (res.success) {
                    $('#modalProjectExtras').modal('hide');
                    if (window.Swal) Swal.fire({toast: true, position: 'top-end', icon: 'success', title: 'Extras saved', showConfirmButton: false, timer: 2000});
                    refreshSection(currentCaseId);
                }
            }
        });
    }

    function openExemptionModal() {
        $('#modalAdExemption').modal('show');
    }

    function saveAdExemption(e) {
        e.preventDefault();
        const reason = $('#ad_exemption_reason').val();
        $.ajax({
            url: '/hrforms/cases/' + currentCaseId + '/milestones',
            method: 'POST',
            data: {
                step_code: 'advertisement',
                status: 'Completed',
                note: 'Exemption recorded: ' + reason,
                _token: $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
            },
            success: function(res) {
                if (res.success) {
                    $('#modalAdExemption').modal('hide');
                    $('#tracker-ad-warning-alert').addClass('d-none');
                    openTrackerModal(currentCaseId);
                }
            }
        });
    }

    function openAuditModal(caseId) {
        currentCaseId = caseId || currentCaseId;
        const tbody = $('#modal-audit-history-tbody');
        tbody.html('<tr><td colspan="4" class="text-center text-muted py-3">Loading audit trail...</td></tr>');
        $('#modalAuditHistory').modal('show');

        $.getJSON('/hrforms/cases/' + currentCaseId + '/tab', function(res) {
            if (!res.success) return;
            tbody.empty();
            const logs = res.audit_logs || [];
            if (logs.length === 0) {
                tbody.html('<tr><td colspan="4" class="text-center text-muted py-3">No audit entries found.</td></tr>');
                return;
            }
            logs.forEach(function(l) {
                tbody.append(`
                    <tr>
                        <td class="text-muted">${l.created_at ? l.created_at.substring(0, 19).replace('T', ' ') : ''}</td>
                        <td class="font-weight-bold text-dark">${l.user_name || 'System'}</td>
                        <td><span class="badge badge-light border">${l.action}</span></td>
                        <td>${l.description}</td>
                    </tr>
                `);
            });
        });
    }

    function refreshSection(caseId) {
        // Quick reload to update form badges and list states
        window.location.reload();
    }

    return {
        openForm: openForm,
        saveCurrentForm: saveCurrentForm,
        refreshCurrentLive: refreshCurrentLive,
        refreshLive: refreshLive,
        submitCurrentForm: submitCurrentForm,
        submitFormDirect: submitFormDirect,
        decidePendingRemoval: decidePendingRemoval,
        openTrackerModal: openTrackerModal,
        openExtrasModal: openExtrasModal,
        openAuditModal: openAuditModal,
        openExemptionModal: openExemptionModal,
        saveProjectExtras: saveProjectExtras,
        saveAdExemption: saveAdExemption,
        calcAnnexMTotal: calcAnnexMTotal,
        calcAnnexJTotal: calcAnnexJTotal,
    };
})();
</script>
