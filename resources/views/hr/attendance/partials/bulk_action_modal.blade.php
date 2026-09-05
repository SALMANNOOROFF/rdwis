{{-- resources/views/hr/attendance/partials/bulk_action_modal.blade.php --}}
<div class="modal fade" id="bulkActionModal" tabindex="-1" role="dialog" aria-labelledby="bulkActionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="background-color: #ffffff; border-radius: 12px; overflow: hidden;">
      <div class="modal-header py-3 px-4" style="background: #ffffff; border-bottom: 1px solid #e2e8f0;">
        <h5 class="modal-title font-weight-bold d-flex align-items-center" id="bulkActionModalLabel" style="font-family: 'Rajdhani', sans-serif; color: #0f172a;">
          <i class="fas fa-layer-group text-primary mr-2"></i>
          <span>Bulk Attendance Action</span>
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #64748b;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form method="POST" action="{{ route('divhr.attendance.bulk_action') }}" id="bulk-action-form">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}">

        <div class="modal-body p-4" style="background: #ffffff; color: #0f172a;">
          <!-- Action Mode Selector -->
          <div class="form-group mb-3">
            <label class="font-weight-bold text-sm mb-1" style="color: #1e293b;">Action Type</label>
            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
              <label class="btn btn-outline-primary active py-2 text-sm font-weight-bold">
                <input type="radio" name="action" id="action-fill" value="fill" autocomplete="off" checked>
                <i class="fas fa-fill-drip mr-1"></i> Bulk-Fill Code
              </label>
              <label class="btn btn-outline-warning py-2 text-sm font-weight-bold" style="color: #b45309; border-color: #f59e0b;">
                <input type="radio" name="action" id="action-holiday" value="toggle_holiday" autocomplete="off">
                <i class="fas fa-umbrella-beach mr-1"></i> Public Holiday (Z)
              </label>
            </div>
          </div>

          <!-- Code Selection (Visible only for fill) -->
          <div class="form-group mb-3" id="group-code-select">
            <label for="bulk_code" class="font-weight-bold text-sm mb-1" style="color: #1e293b;">Select Attendance Code</label>
            <select name="code" id="bulk_code" class="form-control font-weight-bold" style="background: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; border-radius: 6px;">
              <option value="P" selected>P - Present</option>
              <option value="W">W - Work from Home</option>
              <option value="T">T - Ty Duty</option>
              <option value="A">A - Absent</option>
              <option value="L">L - Leave</option>
              <option value="U">U - Unpaid Leave</option>
              <option value="N">N - Not Applicable</option>
            </select>
          </div>

          <!-- Holiday State Selection (Visible only for holiday toggle) -->
          <div class="form-group mb-3" id="group-holiday-select" style="display: none;">
            <label for="is_holiday" class="font-weight-bold text-sm mb-1" style="color: #1e293b;">Holiday State</label>
            <select name="is_holiday" id="is_holiday" class="form-control font-weight-bold" style="background: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; border-radius: 6px;">
              <option value="1" selected>Mark as Public Holiday (Z)</option>
              <option value="0">Unmark Public Holiday (Revert to default)</option>
            </select>
            <small class="text-muted d-block mt-1"><i class="fas fa-info-circle mr-1"></i> Saturdays and Sundays are permanently weekends and cannot be modified.</small>
          </div>

          <!-- Date Range Inputs -->
          <div class="row mb-3">
            <div class="col-6">
              <label for="start_day" class="font-weight-bold text-sm mb-1" style="color: #1e293b;">Start Day</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text font-weight-bold" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">Day</span>
                </div>
                <input type="number" name="start_day" id="start_day" class="form-control font-weight-bold text-center" style="background: #ffffff; color: #0f172a; border: 1px solid #cbd5e1;" min="1" max="{{ $days }}" value="1" required>
              </div>
            </div>
            <div class="col-6">
              <label for="end_day" class="font-weight-bold text-sm mb-1" style="color: #1e293b;">End Day</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text font-weight-bold" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">Day</span>
                </div>
                <input type="number" name="end_day" id="end_day" class="form-control font-weight-bold text-center" style="background: #ffffff; color: #0f172a; border: 1px solid #cbd5e1;" min="1" max="{{ $days }}" value="{{ $days }}" required>
              </div>
            </div>
          </div>

          <!-- Scope Context -->
          <div class="form-group mb-3">
            <label class="font-weight-bold text-sm mb-1" style="color: #1e293b;">Employee Scope</label>
            <div class="p-2 rounded text-xs" style="background: #f8fafc; border: 1px solid #e2e8f0; color: #334155;">
              <i class="fas fa-users text-primary mr-1"></i>
              <span>Applies to all <strong>{{ count($list) }}</strong> employee(s) currently displayed in your unit scope.</span>
            </div>
          </div>

          <!-- Interactive Locked-Cell Preview Box -->
          <div id="bulk-preview-box" class="card mb-0 shadow-sm" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
            <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center" style="background: #f1f5f9; border-bottom: 1px solid #e2e8f0;">
              <span class="text-xs font-weight-bold text-uppercase" style="color: #1e293b;"><i class="fas fa-search mr-1 text-primary"></i> Scope Impact Preview</span>
              <span id="preview-badge" class="badge badge-success px-2 py-1">Ready</span>
            </div>
            <div class="card-body p-3" style="background: #f8fafc;">
              <div class="d-flex justify-content-between mb-2 small">
                <span style="color: #64748b;">Total target cells in range:</span>
                <span class="font-weight-bold" style="color: #0f172a;" id="prev-total-cells">0</span>
              </div>
              <div class="d-flex justify-content-between mb-2 small">
                <span style="color: #16a34a;"><i class="fas fa-check-circle mr-1"></i> Cells to be updated:</span>
                <span class="font-weight-bold" style="color: #16a34a;" id="prev-update-cells">0</span>
              </div>
              <div class="d-flex justify-content-between small">
                <span style="color: #b45309;"><i class="fas fa-lock mr-1"></i> Locked cells (WILL BE SKIPPED):</span>
                <span class="font-weight-bold" style="color: #b45309;" id="prev-locked-cells">0</span>
              </div>

              <!-- Locked Warning Alert -->
              <div id="prev-locked-alert" class="alert mt-3 mb-0 py-2 px-3 small" style="display: none; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; border-radius: 6px;">
                <div class="font-weight-bold mb-1"><i class="fas fa-exclamation-triangle mr-1"></i> Cutoff / Lock Protection Active:</div>
                <div><span id="prev-locked-count">0</span> cell(s) fall into locked payroll periods (pre-cutoff day {{ $cutoff_day ?? 26 }} or month lock) and will be <strong>skipped and preserved unmodified</strong> by the server.</div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer py-2 px-4" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-success font-weight-bold px-3 shadow-sm" id="btn-apply-bulk">
            <i class="fas fa-bolt mr-1"></i> Apply Bulk Action
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
