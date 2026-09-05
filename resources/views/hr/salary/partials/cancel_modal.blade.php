{{-- resources/views/hr/salary/partials/cancel_modal.blade.php --}}
<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden; background: #ffffff;">
      <form id="cancelModalForm" method="POST" action="">
        @csrf
        <div class="modal-header py-3 px-4 bg-danger text-white">
          <h5 class="modal-title font-weight-bold d-flex align-items-center" id="cancelModalLabel">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <span id="cancel-modal-title">Confirm Cancellation</span>
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body p-4" style="color: #0f172a;">
          <div class="alert alert-warning py-2 px-3 small border-0 d-flex align-items-center mb-3" style="background: #fffbeb; color: #b45309; border-left: 4px solid #f59e0b !important;">
            <i class="fas fa-info-circle mr-2 fa-lg"></i>
            <div>
              <strong>High-Friction Financial Action:</strong>
              <span id="cancel-modal-warning">Cancelling this record will close it permanently and cancel any active awaited commitments.</span>
            </div>
          </div>

          <div class="form-group">
            <label class="font-weight-bold text-xs text-uppercase" style="color: #475569;">Target Record</label>
            <div class="p-2 rounded font-monospace small" id="cancel-target-desc" style="background: #f1f5f9; color: #0f172a; border: 1px solid #e2e8f0;">-</div>
          </div>

          <div class="form-group">
            <label for="cancel_reason" class="font-weight-bold text-xs text-uppercase" style="color: #475569;">
              Reason for Cancellation <span class="text-danger">*</span>
            </label>
            <textarea name="reason" id="cancel_reason" class="form-control" rows="3" placeholder="Provide a detailed operational/administrative reason for cancellation..." required style="border-color: #cbd5e1; font-size: 0.9rem;"></textarea>
            <small class="text-muted">This reason will be logged in operational audit logs.</small>
          </div>

          <div class="form-group mb-0">
            <label for="cancel_confirmation" class="font-weight-bold text-xs text-uppercase" style="color: #dc2626;">
              Type <span class="badge badge-danger">CONFIRM</span> to proceed <span class="text-danger">*</span>
            </label>
            <input type="text" id="cancel_confirmation" class="form-control" placeholder="Type CONFIRM here" autocomplete="off" style="border-color: #cbd5e1;">
          </div>
        </div>
        <div class="modal-footer py-2 px-4" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
          <button type="button" class="btn btn-sm btn-secondary font-weight-bold" data-dismiss="modal">Abort</button>
          <button type="submit" class="btn btn-sm btn-danger font-weight-bold px-3" id="btn-submit-cancel" disabled>
            <i class="fas fa-trash-alt mr-1"></i> Proceed with Cancellation
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  $('#cancel_confirmation, #cancel_reason').on('input', function() {
    const isConfirmed = $('#cancel_confirmation').val().trim() === 'CONFIRM';
    const hasReason = $('#cancel_reason').val().trim().length > 3;
    $('#btn-submit-cancel').prop('disabled', !(isConfirmed && hasReason));
  });

  $('#cancelModal').on('show.bs.modal', function(e) {
    $('#cancel_confirmation').val('');
    $('#cancel_reason').val('');
    $('#btn-submit-cancel').prop('disabled', true);
  });
});
</script>
