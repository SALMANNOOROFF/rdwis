@extends('welcome')
@section('content')
<div class="content-wrapper p-0">
  <div class="container-fluid p-0">
    <div class="sinc-wrapper" style="min-height:100vh;background:#f5f7fa;font-family:'DM Sans',sans-serif;padding:32px 32px 56px">
      <style>
        .sinc-card{background:#fff;border:1.5px solid #e8edf4;border-radius:16px;overflow:hidden}
        .sinc-card-head{display:flex;align-items:center;justify-content:space-between;padding:14px 20px 12px;border-bottom:1.5px solid #f1f5f9}
        .sinc-card-title{font-size:.88rem;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:8px}
        .soft-form{padding:18px 20px}
        .soft-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        .soft-group{margin-bottom:14px}
        .soft-label{font-size:.78rem;font-weight:700;color:#64748b;margin-bottom:6px}
        .soft-input,.soft-textarea{width:100%;border:1.5px solid #e2e8f0;border-radius:10px;background:#f8fafc;padding:10px 12px;font-size:.85rem;color:#0f172a}
        .soft-input:focus,.soft-textarea:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.1)}
        .soft-textarea{min-height:110px;resize:vertical}
        .section-title{font-size:.82rem;font-weight:800;color:#0f172a;margin:4px 0 10px;display:flex;align-items:center;gap:8px}
        .mp-list{display:flex;flex-direction:column;gap:10px}
        .mp-row{display:grid;grid-template-columns:1fr 1fr 40px;gap:12px;align-items:end;padding:12px;border:1.5px solid #e8edf4;border-radius:14px;background:#fff}
        .mp-col .soft-label{margin-bottom:6px}
        .btn-add-row{display:inline-flex;align-items:center;gap:8px;padding:9px 12px;border-radius:10px;background:#3b82f6;color:#fff;font-weight:700;border:none;cursor:pointer}
        .btn-add-row:hover{background:#2563eb}
        .btn-rm-row{width:40px;height:40px;border-radius:12px;border:1.5px solid #e2e8f0;background:#fff;color:#64748b;display:inline-flex;align-items:center;justify-content:center;cursor:pointer}
        .btn-rm-row:hover{border-color:#ef4444;color:#ef4444}
        .checkbox-row{display:flex;flex-wrap:wrap;gap:10px}
        .checkbox-pill{display:inline-flex;align-items:center;gap:8px;border:1.5px solid #e2e8f0;border-radius:999px;background:#fff;color:#0f172a;padding:8px 12px;font-size:.82rem;font-weight:600}
        .checkbox-pill input{transform:scale(1.1)}
        .hardware-types{margin-top:10px;padding-top:10px;border-top:1px dashed #e2e8f0;display:none}
        .btn-submit{display:inline-flex;align-items:center;gap:8px;padding:10px 16px;border-radius:10px;background:#cbd5e1;color:#fff;font-weight:700;border:none;cursor:not-allowed}
        .sinc-topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
        .sinc-page-title{font-size:1.1rem;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:10px}
        .title-icon{width:34px;height:34px;border-radius:10px;background:#eff6ff;color:#3b82f6;display:flex;align-items:center;justify-content:center;font-size:.9rem}
        @media (max-width: 800px){
          .soft-row{grid-template-columns:1fr}
          .mp-row{grid-template-columns:1fr}
        }
      </style>
      <div class="sinc-topbar">
        <div>
          <div class="sinc-page-title">
            <span class="title-icon"><i class="fas fa-user-tie"></i></span>
            Consultancy — Outsourcing
          </div>
          <div style="font-size:.76rem;color:#94a3b8;font-weight:500;margin-top:1px">Define scope, timeline, milestones and payment terms</div>
        </div>
        <a href="{{ route('purchase.select') }}" class="sinc-nav-btn outline" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border:1.5px solid #e2e8f0;border-radius:10px;background:#fff;color:#64748b;font-size:.82rem;font-weight:600;text-decoration:none"> 
          <i class="fas fa-arrow-left" style="font-size:.72rem"></i> Back
        </a>
      </div>
      <div class="sinc-card">
        <div class="sinc-card-head">
          <div class="sinc-card-title"><i class="fas fa-clipboard-list" style="color:#3b82f6"></i> Consultancy Form</div>
          <span style="font-size:.72rem;font-weight:700;color:#64748b"></span>
        </div>
        <form class="soft-form">
          <div class="soft-row">
            <div class="soft-group">
              <div class="soft-label">Scope</div>
              <textarea class="soft-textarea" placeholder="Describe consultancy scope"></textarea>
            </div>
            <div class="soft-group">
              <div class="soft-label">Duration</div>
              <input class="soft-input" placeholder="e.g. 6 weeks (Jan 10 – Feb 21)">
            </div>
          </div>
          <div class="soft-group">
            <div class="section-title"><i class="fas fa-flag-checkered" style="color:#3b82f6"></i> Milestones &amp; Payment Terms</div>
            <div id="mp-list" class="mp-list">
              <div class="mp-row" data-idx="0">
                <div class="mp-col">
                  <div class="soft-label">Milestone</div>
                  <input class="soft-input" name="milestones[0][name]" placeholder="e.g. Requirement gathering">
                </div>
                <div class="mp-col">
                  <div class="soft-label">Payment Term</div>
                  <input class="soft-input" name="milestones[0][payment]" placeholder="e.g. 20% advance">
                </div>
                <button type="button" class="btn-rm-row" style="visibility:hidden" tabindex="-1" aria-hidden="true">
                  <i class="fas fa-times" style="font-size:.75rem"></i>
                </button>
              </div>
            </div>
            <div style="margin-top:10px">
              <button type="button" class="btn-add-row" id="btn-add-mp">
                <i class="fas fa-plus" style="font-size:.75rem"></i> Add Milestone
              </button>
            </div>
          </div>
          <div class="soft-group">
            <div class="section-title"><i class="fas fa-cubes" style="color:#3b82f6"></i> Hardware (optional)</div>
            <div class="checkbox-row">
              <label class="checkbox-pill"><input type="checkbox" id="chk-hw"> Include hardware</label>
            </div>
            <div id="hw-types" class="hardware-types">
              <div class="soft-label">Select hardware types</div>
              <div class="checkbox-row">
                <label class="checkbox-pill"><input type="checkbox"> Network Hardware</label>
                <label class="checkbox-pill"><input type="checkbox"> End-User Devices</label>
              </div>
            </div>
          </div>
          <div style="display:flex;justify-content:flex-end;margin-top:10px">
            <button type="button" class="btn-submit" disabled><i class="fas fa-save"></i> Submit (disabled)</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  (function(){
    const chk = document.getElementById('chk-hw');
    const types = document.getElementById('hw-types');
    if(chk){ chk.addEventListener('change', function(){ types.style.display = this.checked ? 'block' : 'none'; }); }

    const list = document.getElementById('mp-list');
    const addBtn = document.getElementById('btn-add-mp');
    function nextIdx(){
      const rows = list ? Array.from(list.querySelectorAll('.mp-row')) : [];
      if(rows.length === 0){ return 0; }
      return Math.max.apply(null, rows.map(r => parseInt(r.getAttribute('data-idx') || '0', 10))) + 1;
    }
    function wireRemove(btn){
      btn.addEventListener('click', function(){
        const row = btn.closest('.mp-row');
        if(!row){ return; }
        row.remove();
        const rows = Array.from(list.querySelectorAll('.mp-row'));
        if(rows.length === 1){
          const firstRm = rows[0].querySelector('.btn-rm-row');
          if(firstRm){ firstRm.style.visibility = 'hidden'; firstRm.setAttribute('tabindex','-1'); firstRm.setAttribute('aria-hidden','true'); }
        }
      });
    }
    if(addBtn && list){
      addBtn.addEventListener('click', function(){
        const i = nextIdx();
        const row = document.createElement('div');
        row.className = 'mp-row';
        row.setAttribute('data-idx', String(i));
        row.innerHTML = `
          <div class="mp-col">
            <div class="soft-label">Milestone</div>
            <input class="soft-input" name="milestones[${i}][name]" placeholder="e.g. Design & approval">
          </div>
          <div class="mp-col">
            <div class="soft-label">Payment Term</div>
            <input class="soft-input" name="milestones[${i}][payment]" placeholder="e.g. Pay on milestone completion">
          </div>
          <button type="button" class="btn-rm-row" aria-label="Remove">
            <i class="fas fa-times" style="font-size:.75rem"></i>
          </button>
        `;
        list.appendChild(row);
        const rm = row.querySelector('.btn-rm-row');
        if(rm){ wireRemove(rm); }
        const first = list.querySelector('.mp-row .btn-rm-row');
        if(first && list.querySelectorAll('.mp-row').length > 1){
          first.style.visibility = 'visible';
          first.removeAttribute('tabindex');
          first.removeAttribute('aria-hidden');
        }
      });
      const firstRm = list.querySelector('.mp-row .btn-rm-row');
      if(firstRm){
        firstRm.style.visibility = 'hidden';
        firstRm.setAttribute('tabindex','-1');
        firstRm.setAttribute('aria-hidden','true');
      }
    }
  })();
</script>
@endsection
