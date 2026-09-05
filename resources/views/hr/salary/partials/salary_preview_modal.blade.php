{{-- resources/views/hr/salary/partials/salary_preview_modal.blade.php --}}
<div class="modal fade" id="salaryPreviewModal" tabindex="-1" role="dialog" aria-labelledby="salaryPreviewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden; background: #ffffff;">
      <div class="modal-header py-3 px-4" style="background: #ffffff; border-bottom: 1px solid #e2e8f0;">
        <h5 class="modal-title font-weight-bold d-flex align-items-center" id="salaryPreviewModalLabel" style="color: #0f172a;">
          <i class="fas fa-search-dollar text-primary mr-2"></i>
          <span>Salary Generation Preview & 7-Check Audit</span>
        </h5>
        <button type="button" class="close text-muted" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-4" style="color: #0f172a;">
        <!-- Metrics Cards -->
        <div class="row mb-3">
          <div class="col-md-4 mb-2">
            <div class="card border-0 shadow-sm" style="background: #f8fafc; border-left: 4px solid #64748b !important; border-radius: 8px;">
              <div class="card-body p-3">
                <div class="text-xs text-uppercase font-weight-bold" style="color: #64748b;">Total Candidates Scanned</div>
                <div class="h3 font-weight-bold mb-0" id="preview-total-count" style="color: #0f172a;">0</div>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-2">
            <div class="card border-0 shadow-sm" style="background: #f0fdf4; border-left: 4px solid #16a34a !important; border-radius: 8px;">
              <div class="card-body p-3">
                <div class="text-xs text-uppercase font-weight-bold" style="color: #15803d;">Eligible Candidates (Included)</div>
                <div class="h3 font-weight-bold mb-0" id="preview-eligible-count" style="color: #16a34a;">0</div>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-2">
            <div class="card border-0 shadow-sm" style="background: #fef2f2; border-left: 4px solid #dc2626 !important; border-radius: 8px;">
              <div class="card-body p-3">
                <div class="text-xs text-uppercase font-weight-bold" style="color: #b91c1c;">Excluded Candidates (Failed Checks)</div>
                <div class="h3 font-weight-bold mb-0" id="preview-excluded-count" style="color: #dc2626;">0</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tabs for Eligible vs Excluded -->
        <ul class="nav nav-tabs font-weight-bold mb-3" id="previewTabs" role="tablist">
          <li class="nav-item">
            <a class="nav-link active text-success" id="tab-eligible-link" data-toggle="tab" href="#tab-eligible" role="tab">
              <i class="fas fa-check-circle mr-1"></i> Eligible Candidates (<span id="badge-eligible-count">0</span>)
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-danger" id="tab-excluded-link" data-toggle="tab" href="#tab-excluded" role="tab">
              <i class="fas fa-ban mr-1"></i> Excluded by Reason (<span id="badge-excluded-count">0</span>)
            </a>
          </li>
        </ul>

        <div class="tab-content" id="previewTabContent">
          <!-- Eligible Candidates Tab -->
          <div class="tab-pane fade show active" id="tab-eligible" role="tabpanel">
            <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
              <table class="table table-sm table-hover table-bordered mb-0" style="border-color: #e2e8f0;">
                <thead style="background: #f8fafc; color: #1e293b; position: sticky; top: 0; z-index: 2;">
                  <tr>
                    <th style="width: 40px;" class="text-center">#</th>
                    <th>Employee ID</th>
                    <th>Name / Designation</th>
                    <th>Unit</th>
                    <th>Contract Salary</th>
                    <th>Deductions</th>
                    <th>Net Salary</th>
                    <th>Payment Mode & Bank Match</th>
                  </tr>
                </thead>
                <tbody id="preview-eligible-tbody">
                  <tr><td colspan="8" class="text-center text-muted py-3">No candidates loaded.</td></tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Excluded Candidates Tab with 7 Check Filters -->
          <div class="tab-pane fade" id="tab-excluded" role="tabpanel">
            <div class="d-flex align-items-center mb-2" style="gap: 8px;">
              <span class="small font-weight-bold text-muted">Filter by Exclusion Reason:</span>
              <select id="filter-exclusion-reason" class="form-control form-control-sm" style="width: auto; max-width: 320px;">
                <option value="all">All Exclusion Reasons (7 Checks)</option>
                <option value="Future Month">1. Future Month</option>
                <option value="Already Generated">2. Already Generated (Duplicate Guard)</option>
                <option value="No Contract/Plan">3. No Contract/Plan</option>
                <option value="Salary Head Not Set">4. Salary Head Not Set</option>
                <option value="Contract Not Verified">5. Contract Not Verified</option>
                <option value="Multiple Bank Accounts">6. Multiple Bank Accounts</option>
                <option value="Net Salary Zero or Negative">7. Net Salary Zero or Negative</option>
              </select>
            </div>
            <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
              <table class="table table-sm table-hover table-bordered mb-0" style="border-color: #e2e8f0;">
                <thead style="background: #f8fafc; color: #1e293b; position: sticky; top: 0; z-index: 2;">
                  <tr>
                    <th style="width: 40px;" class="text-center">#</th>
                    <th>Employee ID</th>
                    <th>Name / Designation</th>
                    <th>Unit</th>
                    <th>Specific Exclusion Reason</th>
                  </tr>
                </thead>
                <tbody id="preview-excluded-tbody">
                  <tr><td colspan="5" class="text-center text-muted py-3">No excluded candidates.</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer py-2 px-4" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
        <button type="button" class="btn btn-sm btn-secondary font-weight-bold" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
