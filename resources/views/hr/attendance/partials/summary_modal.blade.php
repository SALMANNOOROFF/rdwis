{{-- resources/views/hr/attendance/partials/summary_modal.blade.php --}}
<div class="modal fade" id="summaryModal" tabindex="-1" role="dialog" aria-labelledby="summaryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="background-color: #ffffff; border-radius: 12px; overflow: hidden;">
      <div class="modal-header py-3 px-4" style="background: #ffffff; border-bottom: 1px solid #e2e8f0;">
        <h5 class="modal-title font-weight-bold d-flex align-items-center" id="summaryModalLabel" style="font-family: 'Rajdhani', sans-serif; color: #0f172a;">
          <i class="fas fa-chart-pie text-primary mr-2"></i>
          <span>Monthly Attendance Summary</span>
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #64748b;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-4" style="background: #ffffff; color: #0f172a;">
        <!-- Spinner Loading State -->
        <div id="summary-loader" class="text-center py-5">
          <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="sr-only">Loading...</span>
          </div>
          <div class="mt-3 font-weight-bold" style="color: #64748b;">Calculating stitched attendance summary...</div>
        </div>

        <!-- Summary Content Container -->
        <div id="summary-content" style="display: none;">
          <!-- Employee Info Banner -->
          <div class="card mb-3 shadow-sm" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
            <div class="card-body p-3">
              <div class="row align-items-center">
                <div class="col-md-7">
                  <h5 class="mb-1 font-weight-bold" id="sum-emp-name" style="color: #0f172a;">-</h5>
                  <div class="small" style="color: #64748b;">
                    <span class="mr-3"><i class="fas fa-id-badge text-primary mr-1"></i> <span id="sum-emp-id" class="font-monospace font-weight-bold" style="color: #0369a1;">-</span></span>
                    <span class="mr-3"><i class="fas fa-address-card mr-1"></i> <span id="sum-emp-cnic">-</span></span>
                  </div>
                </div>
                <div class="col-md-5 text-md-right mt-2 mt-md-0">
                  <span class="badge px-2 py-1 mr-1 font-weight-bold" id="sum-emp-unit" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">-</span>
                  <span class="badge px-2 py-1 font-weight-bold" id="sum-period" style="background: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc;">-</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Metrics Cards -->
          <div class="row mb-3">
            <div class="col-6 col-md-3 mb-2">
              <div class="card text-center border-0 shadow-sm" style="background: #f0fdf4; border-left: 4px solid #16a34a !important; border-radius: 6px;">
                <div class="card-body p-2">
                  <div class="text-xs text-uppercase font-weight-bold" style="color: #15803d;">Present (P)</div>
                  <div class="h3 mb-0 font-weight-bold" style="color: #16a34a;" id="sum-count-P">0</div>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-3 mb-2">
              <div class="card text-center border-0 shadow-sm" style="background: #fef2f2; border-left: 4px solid #dc2626 !important; border-radius: 6px;">
                <div class="card-body p-2">
                  <div class="text-xs text-uppercase font-weight-bold" style="color: #b91c1c;">Absent (A)</div>
                  <div class="h3 mb-0 font-weight-bold" style="color: #dc2626;" id="sum-count-A">0</div>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-3 mb-2">
              <div class="card text-center border-0 shadow-sm" style="background: #fffbeb; border-left: 4px solid #d97706 !important; border-radius: 6px;">
                <div class="card-body p-2">
                  <div class="text-xs text-uppercase font-weight-bold" style="color: #b45309;">Leave (L/U)</div>
                  <div class="h3 mb-0 font-weight-bold" style="color: #d97706;" id="sum-count-leaves">0</div>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-3 mb-2">
              <div class="card text-center border-0 shadow-sm" style="background: #eff6ff; border-left: 4px solid #2563eb !important; border-radius: 6px;">
                <div class="card-body p-2">
                  <div class="text-xs text-uppercase font-weight-bold" style="color: #1d4ed8;">Working Days</div>
                  <div class="h3 mb-0 font-weight-bold" style="color: #2563eb;" id="sum-count-working">0</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Detailed Counts Table -->
          <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0 text-center" style="border-color: #e2e8f0;">
              <thead style="background: #f8fafc; color: #1e293b; border-bottom: 2px solid #cbd5e1;">
                <tr>
                  <th class="font-weight-bold" title="Present">P (Present)</th>
                  <th class="font-weight-bold" title="Work from Home">W (WFH)</th>
                  <th class="font-weight-bold" title="Ty Duty">T (Ty Duty)</th>
                  <th class="font-weight-bold" title="Absent">A (Absent)</th>
                  <th class="font-weight-bold" title="Leave">L (Leave)</th>
                  <th class="font-weight-bold" title="Unpaid Leave">U (Unpaid)</th>
                  <th class="font-weight-bold" title="Not Applicable">N (N/A)</th>
                  <th class="font-weight-bold" title="Weekend / Holiday">Weekend / Holiday</th>
                  <th class="font-weight-bold">Total Days</th>
                </tr>
              </thead>
              <tbody>
                <tr class="font-weight-bold" style="background: #ffffff; color: #0f172a;">
                  <td id="tbl-P" style="color: #15803d;">0</td>
                  <td id="tbl-W" style="color: #0369a1;">0</td>
                  <td id="tbl-T" style="color: #4338ca;">0</td>
                  <td id="tbl-A" style="color: #b91c1c;">0</td>
                  <td id="tbl-L" style="color: #b45309;">0</td>
                  <td id="tbl-U" style="color: #c2410c;">0</td>
                  <td id="tbl-N" style="color: #64748b;">0</td>
                  <td id="tbl-Z" style="color: #7e22ce;">0</td>
                  <td id="tbl-total" class="font-weight-bolder" style="color: #0f172a;">0</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Stitching Info Banner -->
          <div class="alert mt-3 mb-0 py-2 px-3 small d-flex align-items-center" style="background: #f8fafc; border: 1px solid #e2e8f0; color: #475569; border-radius: 6px;">
            <i class="fas fa-info-circle text-primary mr-2"></i>
            <span>Numbers computed using legacy cross-month holiday absorption rules (Attendance.bas:400-478).</span>
          </div>
        </div>
      </div>
      <div class="modal-footer py-2 px-4" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
        <button type="button" class="btn btn-sm btn-secondary font-weight-bold" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
