{{-- resources/views/hr/salary/partials/cancel_modal.blade.php --}}
<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden; background: #ffffff;">
      <form id="cancelModalForm" method="POST" action="">
        @csrf
        <div class="modal-header py-3 px-4 bg-warning text-dark">
          <h5 class="modal-title font-weight-bold d-flex align-items-center" id="cancelModalLabel">
            <i class="fas fa-undo-alt mr-2"></i>
            <span id="cancel-modal-title">Return Salary Order to Division</span>
          </h5>
          <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body p-4" style="color: #0f172a;">
          <div class="alert alert-warning py-2 px-3 small border-0 d-flex align-items-center mb-3" style="background: #fffbeb; color: #b45309; border-left: 4px solid #f59e0b !important;">
            <i class="fas fa-info-circle mr-2 fa-lg"></i>
            <div>
              <strong>Revert Requisition to Division:</strong>
              <span id="cancel-modal-warning">Returning this salary order will revert the requisition back to the Division in <strong>Draft</strong> stage, release In-Process funds back to Remaining budget, and remove this draft order from Finance.</span>
            </div>
          </div>

          <div class="form-group">
            <label class="font-weight-bold text-xs text-uppercase" style="color: #475569;">Target Record</label>
            <div class="p-2 rounded font-monospace small" id="cancel-target-desc" style="background: #f1f5f9; color: #0f172a; border: 1px solid #e2e8f0;">-</div>
          </div>

          <div class="form-group">
            <label for="cancel_reason" class="font-weight-bold text-xs text-uppercase" style="color: #475569;">
              Reason for Return <span class="text-danger">*</span>
            </label>
            <textarea name="reason" id="cancel_reason" class="form-control" rows="3" placeholder="Provide reason for returning to division draft..." required style="border-color: #cbd5e1; font-size: 0.9rem;"></textarea>
            <small class="text-muted">This reason will be recorded on the requisition for division HR.</small>
          </div>

          <div class="form-group mb-0">
            <label for="cancel_confirmation" class="font-weight-bold text-xs text-uppercase" style="color: #d97706;">
              Type <span class="badge badge-warning">RETURN</span> or <span class="badge badge-danger">CONFIRM</span> to proceed <span class="text-danger">*</span>
            </label>
            <input type="text" id="cancel_confirmation" class="form-control" placeholder="Type RETURN here" autocomplete="off" style="border-color: #cbd5e1;">
          </div>
        </div>
        <div class="modal-footer py-2 px-4" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
          <button type="button" class="btn btn-sm btn-secondary font-weight-bold" data-dismiss="modal">Abort</button>
          <button type="submit" class="btn btn-sm btn-warning font-weight-bold px-3 text-dark" id="btn-submit-cancel" disabled>
            <i class="fas fa-undo-alt mr-1"></i> Proceed with Return
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Standalone vanilla JS listener so it works immediately without waiting for jQuery
(function() {
  function initCancelModalValidation() {
    var conf = document.getElementById('cancel_confirmation');
    var reason = document.getElementById('cancel_reason');
    var btn = document.getElementById('btn-submit-cancel');

    if (!conf || !reason || !btn) return;

    function checkValidity() {
      var val = (conf.value || '').trim().toUpperCase();
      var isConfirmed = (val === 'RETURN' || val === 'CONFIRM');
      var hasReason = (reason.value || '').trim().length >= 3;
      btn.disabled = !(isConfirmed && hasReason);
    }

    conf.addEventListener('input', checkValidity);
    reason.addEventListener('input', checkValidity);
    conf.addEventListener('change', checkValidity);
    reason.addEventListener('change', checkValidity);
    conf.addEventListener('keyup', checkValidity);
    reason.addEventListener('keyup', checkValidity);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCancelModalValidation);
  } else {
    initCancelModalValidation();
  }
})();
</script>

@push('scripts')
<script>
$(document).ready(function() {
  $('#cancel_confirmation, #cancel_reason').on('input change keyup', function() {
    const val = $('#cancel_confirmation').val().trim().toUpperCase();
    const isConfirmed = (val === 'RETURN' || val === 'CONFIRM');
    const hasReason = $('#cancel_reason').val().trim().length >= 3;
    $('#btn-submit-cancel').prop('disabled', !(isConfirmed && hasReason));
  });

  $('#cancelModal').on('show.bs.modal', function(e) {
    $('#cancel_confirmation').val('');
    $('#cancel_reason').val('');
    $('#btn-submit-cancel').prop('disabled', true);
  });
});
</script>
@endpush
