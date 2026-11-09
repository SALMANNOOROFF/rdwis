{{-- resources/views/hr/salary/partials/split_modal.blade.php --}}
<div class="modal fade" id="splitSalaryModal" tabindex="-1" role="dialog" aria-labelledby="splitSalaryModalLabel" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document" style="max-width: 860px;">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden; background: #ffffff;">
      <form id="splitSalaryModalForm" method="POST" action="">
        @csrf
        
        {{-- Modal Header --}}
        <div class="modal-header py-3 px-4 bg-dark text-white d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
          <div class="d-flex align-items-center" style="gap: 10px;">
            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(59, 130, 246, 0.2); color: #60a5fa;">
              <i class="fas fa-cut"></i>
            </div>
            <div>
              <h5 class="modal-title font-weight-bold mb-0 text-white" id="splitSalaryModalLabel" style="font-size: 16px; letter-spacing: 0.5px;">
                Split Salary Requisition
              </h5>
              <span class="small text-muted" style="color: #94a3b8 !important; font-size: 11px;">
                Allocate employee salary across multiple project heads (Legacy Parity)
              </span>
            </div>
          </div>
          <button type="button" class="close text-white opacity-75" data-dismiss="modal" aria-label="Close" style="font-size: 22px;">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body p-4" style="color: #0f172a;">

          {{-- 1. Employee & Requisition Particulars (Exact Legacy hr_salreq_split header) --}}
          <div class="p-3 mb-3 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
            <div class="row align-items-center" style="row-gap: 8px;">
              <div class="col-md-4">
                <span class="text-muted small text-uppercase font-weight-bold d-block" style="font-size: 10.5px;">Employee</span>
                <span class="font-weight-bold text-dark" id="modal-split-emp-name" style="font-size: 13.5px;">-</span>
                <span class="small text-muted d-block" id="modal-split-emp-id"></span>
              </div>
              <div class="col-md-2">
                <span class="text-muted small text-uppercase font-weight-bold d-block" style="font-size: 10.5px;">Salary Month</span>
                <span class="badge badge-light border text-dark font-weight-bold px-2 py-1" id="modal-split-month" style="font-size: 11.5px;">-</span>
              </div>
              <div class="col-md-3">
                <span class="text-muted small text-uppercase font-weight-bold d-block" style="font-size: 10.5px;">Contract Salary</span>
                <span class="font-weight-600 text-dark" id="modal-split-ctr-salary" style="font-size: 13px;">Rs. 0</span>
              </div>
              <div class="col-md-3 text-md-right">
                <span class="text-muted small text-uppercase font-weight-bold d-block" style="font-size: 10.5px;">Total Payable</span>
                <span class="font-weight-bold text-danger font-monospace" id="modal-split-total-salary" style="font-size: 16px;">Rs. 0</span>
              </div>
              <div class="col-12 mt-2 pt-2 border-top" style="border-color: #e2e8f0 !important;">
                <div class="d-flex align-items-center justify-content-between small text-muted">
                  <div>
                    <i class="fas fa-university text-secondary mr-1"></i>
                    <strong>Meezan Account:</strong> <span id="modal-split-bank-account" class="font-monospace text-dark font-weight-bold">-</span>
                    <span class="mx-1 text-muted">|</span>
                    <span id="modal-split-bank-title" class="text-secondary"></span>
                  </div>
                  <div>
                    <strong>Requisition ID:</strong> <span class="badge badge-secondary px-2 py-0.5" id="modal-split-req-id">#0</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- 2. Primary / Current Requisition Portion (Parent) --}}
          <div class="card mb-3 border" style="border-color: #cbd5e1 !important; border-radius: 8px;">
            <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center" style="background: #f1f5f9; border-bottom: 1px solid #cbd5e1;">
              <div class="d-flex align-items-center" style="gap: 8px;">
                <span class="font-weight-bold text-dark small text-uppercase">
                  <i class="fas fa-sitemap text-primary mr-1"></i> 1. Parent Requisition (Current Record)
                </span>
                <span class="badge badge-warning text-dark px-2 py-0.5 font-weight-bold" id="modal-split-funding-badge" style="font-size: 10.5px; display: none;">CSRF</span>
              </div>
            </div>
            <div class="card-body p-3">
              <div class="row align-items-center">
                <div class="col-md-7">
                  <label class="small text-muted font-weight-bold mb-1 d-block">Original Effective Head Code</label>
                  <div class="font-weight-bold text-primary font-monospace" id="modal-split-parent-head-name" style="font-size: 14px;">-</div>
                  <small class="text-muted">This existing requisition #<span id="modal-split-parent-id-sub">0</span> will be retained with the adjusted amount below.</small>
                </div>
                <div class="col-md-5">
                  <label for="split_parent_salary_input" class="small text-dark font-weight-bold mb-1 d-block">
                    Retained Amount (Rs.) <span class="text-danger">*</span>
                  </label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text font-weight-bold text-muted" style="font-size: 12px;">Rs.</span>
                    </div>
                    <input type="number" 
                           name="parent_salary" 
                           id="split_parent_salary_input" 
                           class="form-control font-weight-bold text-right text-dark" 
                           style="font-size: 14px;" 
                           min="1" 
                           step="1" 
                           required 
                           oninput="recalcSplitTotals()">
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- 3. Dynamic Split Allocations (Child Requisitions) --}}
          <div class="card mb-3 border" style="border-color: #cbd5e1 !important; border-radius: 8px;">
            <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center" style="background: #f1f5f9; border-bottom: 1px solid #cbd5e1;">
              <span class="font-weight-bold text-dark small text-uppercase">
                <i class="fas fa-code-branch text-info mr-1"></i> 2. Split Portions (New Child Requisitions)
              </span>
              <button type="button" class="btn btn-xs btn-primary font-weight-bold px-2 py-1 shadow-sm" id="btn-add-split-item" onclick="addSplitRow()">
                <i class="fas fa-plus mr-1"></i> Add Split Head
              </button>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive mb-0">
                <table class="table table-sm table-bordered mb-0" id="table-split-items" style="font-size: 12.5px;">
                  <thead class="bg-light text-muted small text-uppercase font-weight-bold">
                    <tr>
                      <th style="width: 40px;" class="text-center">#</th>
                      <th>Target Head Code <span class="text-danger">*</span></th>
                      <th style="width: 240px;" class="text-right">Split Amount (Rs.) <span class="text-danger">*</span></th>
                      <th style="width: 50px;" class="text-center">Action</th>
                    </tr>
                  </thead>
                  <tbody id="split-items-tbody">
                    {{-- Dynamic Rows injected by JavaScript --}}
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          {{-- 4. Live Integrity & Balance Indicator Bar (Legacy txtCheck parity) --}}
          <div class="p-3 rounded mb-2 border" id="split-balance-bar" style="background: #f8fafc; border-color: #e2e8f0 !important;">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
              <div class="d-flex align-items-center" style="gap: 16px;">
                <div>
                  <span class="text-muted small d-block">Original Payable</span>
                  <span class="font-weight-bold text-dark font-monospace" id="split-stat-original" style="font-size: 13px;">Rs. 0</span>
                </div>
                <div class="text-muted font-weight-bold">&minus;</div>
                <div>
                  <span class="text-muted small d-block">Total Allocated</span>
                  <span class="font-weight-bold text-primary font-monospace" id="split-stat-allocated" style="font-size: 13px;">Rs. 0</span>
                </div>
                <div class="text-muted font-weight-bold">&equals;</div>
                <div>
                  <span class="text-muted small d-block">Difference (Unallocated)</span>
                  <span class="font-weight-bold font-monospace" id="split-stat-diff" style="font-size: 14px;">Rs. 0</span>
                </div>
              </div>

              <div>
                <span class="badge px-3 py-2 font-weight-bold shadow-sm" id="split-status-badge" style="font-size: 12px; border-radius: 6px;">
                  Checking balance...
                </span>
              </div>
            </div>
          </div>

          <div class="small text-muted">
            <i class="fas fa-info-circle text-primary mr-1"></i>
            When saved, the original requisition will be set to <strong>Parent (#<span id="modal-split-footer-id">0</span>)</strong> and child requisitions will be created with remarks <em>"Split Salary."</em>.
          </div>

        </div>

        {{-- Modal Footer --}}
        <div class="modal-footer py-2 px-4 d-flex justify-content-between align-items-center" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
          <button type="button" class="btn btn-sm btn-secondary font-weight-bold px-3" data-dismiss="modal">
            Cancel
          </button>
          <button type="submit" class="btn btn-sm btn-success font-weight-bold px-4 shadow-sm" id="btn-submit-split" disabled>
            <i class="fas fa-check-circle mr-1"></i> Save &amp; Split Requisition
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
