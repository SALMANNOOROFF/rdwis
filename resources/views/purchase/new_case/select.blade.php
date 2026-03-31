  @extends('welcome')
@section('content')

<div class="purchase-wrapper">

<style>
  @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap');

  body, html {
    font-family: 'Outfit', sans-serif;
    background-color: var(--rd-bg);
  }

  .purchase-wrapper {
    min-height: 100vh;
    background: var(--rd-bg);
    font-family: 'Outfit', sans-serif;
    padding: 32px 0 48px;
  }

  /* ─── TYPOGRAPHY & HEADER ─── */
  .page-heading {
    text-align: center;
    margin-bottom: 48px;
    position: relative;
    z-index: 2;
  }

  .page-heading h1 {
    font-weight: 900;
    font-size: 2.3rem;
    color: var(--rd-text1);
    letter-spacing: -0.5px;
    margin-bottom: 8px;
  }
  .page-heading p {
    font-size: 1.1rem;
    color: var(--rd-text3);
    font-weight: 500;
  }

  /* ─── MAIN GLASS CARDS ─── */
  .glass-card {
    background: var(--rd-surface);
    border-radius: 24px;
    padding: 32px 28px;
    border: 1px solid var(--rd-border2);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  .glass-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    border-radius: 20px 20px 0 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  }

  .glass-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
    border-color: var(--rd-accent-soft);
  }

  /* ─── COLOR ACCENT STRIPS ─── */
  /* ROW 1: Material, Outsourcing, Civil → ALL BLUE */
  .glass-card.accent-blue::after  { background: linear-gradient(135deg, #3b82f6, #06b6d4); }

  /* ROW 2: Training + TA/DA → AMBER/YELLOW */
  .glass-card.accent-amber::after { background: linear-gradient(135deg, #f59e0b, #f97316); }

  /* Miscellaneous → GREEN */
  .glass-card.accent-green::after { background: linear-gradient(135deg, #10b981, #059669); }

  .glass-card::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 0 0 20px 20px;
    opacity: 0.7;
    transition: opacity 0.2s ease;
  }

  .glass-card:hover::after { opacity: 1; }

  /* Icon circle */
  .card-icon-wrap {
    width: 64px;
    height: 64px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.7rem;
    margin-bottom: 24px;
    transition: transform 0.3s ease;
  }

  .glass-card:hover .card-icon-wrap {
    transform: scale(1.05) rotate(-3deg);
  }

  /* ROW 1 icons → BLUE */
  .icon-blue   { background: var(--rd-accent-soft); color: var(--rd-accent); }

  /* ROW 2 icons → AMBER */
  .icon-amber  { background: var(--rd-warning-soft); color: var(--rd-warning); }

  /* MISC icon → GREEN */
  .icon-green  { background: var(--rd-success-soft); color: var(--rd-success); }

  /* Typo inside cards */
  .gc-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--rd-text1);
    margin-bottom: 8px;
    line-height: 1.2;
  }
  .gc-sub {
    font-size: 0.95rem;
    color: var(--rd-text2);
    margin-bottom: 24px;
    line-height: 1.4;
    font-weight: 400;
  }

  .gc-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--rd-surface2);
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--rd-text2);
    letter-spacing: 0.3px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.1);
  }

  /* ─── EXPANDABLE SUB-GRID (inline) ─── */
  .sub-grid-wrapper {
    display: none;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1.5px solid rgba(100,116,139,0.1);
    animation: fadeSlideIn 0.28s cubic-bezier(.34,1.56,.64,1);
  }

  .sub-grid-wrapper.open { display: block; }

  @keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(-10px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* ─── SQUARE SUB CARDS ─── */
  .sq-card {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: flex-end;
    height: 110px;
    padding: 16px;
    border-radius: 16px;
    background: var(--rd-surface);
    border: 1.5px solid var(--rd-border);
    box-shadow: 0 2px 12px rgba(0,0,0,0.2);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    text-decoration: none;
    transition: transform 0.2s cubic-bezier(.34,1.56,.64,1),
                box-shadow 0.2s ease,
                background 0.18s ease;
    cursor: pointer;
    margin-bottom: 12px;
  }

  .sq-card:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 8px 28px rgba(0,0,0,0.3);
    background: var(--rd-surface2);
    text-decoration: none;
  }

  .sq-title {
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--rd-text1);
    margin-bottom: 3px;
    line-height: 1.2;
  }

  .sq-sub {
    font-size: 0.74rem;
    color: var(--rd-text3);
    font-weight: 500;
  }

  /* ─── MODAL OVERRIDES ─── */
  .modal-content {
    background: var(--rd-surface) !important;
    backdrop-filter: blur(28px) saturate(1.8) !important;
    -webkit-backdrop-filter: blur(28px) saturate(1.8) !important;
    border: 1.5px solid var(--rd-border2) !important;
    border-radius: 24px !important;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.4) !important;
    font-family: 'Outfit', sans-serif;
  }

  .modal-header {
    border-bottom: 1px solid rgba(100,116,139,0.1) !important;
    padding: 24px 28px 16px !important;
  }

  .modal-title {
    font-weight: 700 !important;
    font-size: 1.1rem !important;
    color: var(--rd-text1) !important;
  }

  .modal-body {
    padding: 20px 28px 28px !important;
  }

  /* Square action buttons inside modal */
  .modal-sq-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 120px;
    border-radius: 18px;
    text-decoration: none;
    font-family: 'Outfit', sans-serif;
    font-weight: 700;
    font-size: 1rem;
    transition: transform 0.2s cubic-bezier(.34,1.56,.64,1), box-shadow 0.2s ease;
    gap: 10px;
    border: none;
  }

  .modal-sq-btn:hover {
    transform: translateY(-4px) scale(1.03);
    text-decoration: none;
  }

  .modal-sq-btn .btn-icon {
    font-size: 1.6rem;
    line-height: 1;
  }

  .modal-sq-btn.btn-primary {
    background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
    box-shadow: 0 8px 24px rgba(59,130,246,0.3);
    color: #fff !important;
  }

  .modal-sq-btn.btn-outline-primary {
    background: rgba(59,130,246,0.08) !important;
    border: 2px solid rgba(59,130,246,0.3) !important;
    color: #3b82f6 !important;
    box-shadow: 0 4px 12px rgba(59,130,246,0.10);
  }

  .modal-sq-btn.btn-primary:hover {
    box-shadow: 0 12px 32px rgba(59,130,246,0.4);
  }

  .modal-sq-btn.btn-outline-primary:hover {
    background: rgba(59,130,246,0.14) !important;
    box-shadow: 0 8px 20px rgba(59,130,246,0.15);
  }

  /* close button */
  .modal-header .close {
    color: #64748b;
    opacity: 1;
    font-size: 1.4rem;
  }

  .modal-header .close:hover { color: var(--rd-text1); }

  /* Row gap fix */
  .card-row { margin-bottom: 24px; }

  /* Elegance Background Accent */
  .page-accent {
      position: absolute; top: 0; left: 0; right: 0; height: 350px;
      background: linear-gradient(180deg, var(--rd-surface2) 0%, transparent 100%);
      pointer-events: none; z-index: 0;
  }
</style>

<div class="content p-4" style="position:relative; min-height:100vh; background:var(--rd-bg);">
  <div class="page-accent"></div>
  <div class="container-fluid" style="max-width:1200px; position:relative; z-index:1; padding-top:20px;">

    <!-- HEADING -->
    <div class="page-heading">
      <h1>PURCHASE CASE</h1>
      <p>Please select a category to proceed</p>
    </div>

    <!-- ROW 1: Material | Outsourcing | Miscellaneous -->
    <div class="row card-row">
      <!-- Material / Equipment → BLUE -->
      <div class="col-md-4 mb-3">
        <div class="glass-card accent-blue h-100" id="card-material">
          <div class="card-icon-wrap icon-blue"><i class="fas fa-cubes"></i></div>
          <div class="gc-title">Material / Equipment</div>
          <div class="gc-sub">Items, equipment and supplies</div>
          <span class="gc-chip"><i class="fas fa-circle" style="font-size:.4rem;color:#3b82f6"></i> Create RFQ &nbsp;•&nbsp; Group RFQs</span>
        </div>
      </div>

      <!-- Outsourcing → BLUE -->
      <div class="col-md-4 mb-3">
        <div class="glass-card accent-blue h-100" id="card-outsourcing">
          <div class="card-icon-wrap icon-blue"><i class="fas fa-user-tie"></i></div>
          <div class="gc-title">Outsourcing</div>
          <div class="gc-sub">External services procurement</div>
          <span class="gc-chip"><i class="fas fa-circle" style="font-size:.4rem;color:#3b82f6"></i> Services &nbsp;•&nbsp; Consultancy</span>
        </div>
      </div>

      <!-- Miscellaneous → GREEN -->
      <div class="col-md-4 mb-3">
        <div class="glass-card accent-green h-100" id="card-misc" style="cursor:pointer">
          <div class="card-icon-wrap icon-green"><i class="fas fa-shapes"></i></div>
          <div class="gc-title">Miscellaneous</div>
          <div class="gc-sub">Other expense categories</div>
          <span class="gc-chip"><i class="fas fa-circle" style="font-size:.4rem;color:#10b981"></i> Multiple</span>
        </div>
      </div>
    </div>

    <!-- ROW 2: Civil | TA/DA -->
    <div class="row card-row">
      <!-- Civil Works → BLUE -->
      <div class="col-md-6 mb-3">
        <div class="glass-card accent-blue h-100" id="card-civil">
          <div class="card-icon-wrap icon-blue"><i class="fas fa-hard-hat"></i></div>
          <div class="gc-title">Civil Works / Upfit</div>
          <div class="gc-sub">Civil works and facility upfit</div>
          <span class="gc-chip"><i class="fas fa-circle" style="font-size:.4rem;color:#3b82f6"></i> BOQs &nbsp;•&nbsp; Work Orders</span>
        </div>
      </div>

      <!-- TA/DA → AMBER -->
      <div class="col-md-6 mb-3">
        <div class="glass-card accent-amber h-100" id="card-tada">
          <div class="card-icon-wrap icon-amber"><i class="fas fa-suitcase-rolling"></i></div>
          <div class="gc-title">TA / DA</div>
          <div class="gc-sub">Travel and daily allowance</div>
          <span class="gc-chip"><i class="fas fa-circle" style="font-size:.4rem;color:#f59e0b"></i> Create</span>
        </div>
      </div>
    </div>

  </div>
</div>



<!-- ─── MISC MODAL ─── -->
<div class="modal fade" id="miscModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:700px" role="document">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <div>
          <div style="font-size:.75rem;font-weight:600;color:var(--rd-text3);letter-spacing:.5px;text-transform:uppercase;margin-bottom:4px">Miscellaneous</div>
          <h5 class="modal-title">Select a sub-category</h5>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-12 col-md-4 mb-2">
            <a href="{{ route('purchase.unified.create', ['type'=>'transport']) }}" class="modal-sq-btn btn-outline-primary d-flex w-100">
              <span class="btn-icon"><i class="fas fa-shuttle-van"></i></span>
              <span>Transport</span>
            </a>
          </div>
          <div class="col-12 col-md-4 mb-2">
            <a href="{{ route('purchase.unified.create', ['type'=>'internet']) }}" class="modal-sq-btn btn-outline-primary d-flex w-100">
              <span class="btn-icon"><i class="fas fa-wifi"></i></span>
              <span>Phone / Internet</span>
            </a>
          </div>
          <div class="col-12 col-md-4 mb-2">
            <a href="{{ route('purchase.unified.create', ['type'=>'publishing']) }}" class="modal-sq-btn btn-outline-primary d-flex w-100">
              <span class="btn-icon"><i class="fas fa-newspaper"></i></span>
              <span>Pub. / Reg. Fees</span>
            </a>
          </div>
          <div class="col-12 col-md-4 mb-2">
            <a href="{{ route('purchase.unified.create', ['type'=>'stationery']) }}" class="modal-sq-btn btn-outline-primary d-flex w-100">
              <span class="btn-icon"><i class="fas fa-pen"></i></span>
              <span>Stationery</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>



<!-- ─── MATERIAL MODAL ─── -->
<div class="modal fade" id="materialModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:400px" role="document">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <div>
          <div style="font-size:.75rem;font-weight:600;color:var(--rd-text3);letter-spacing:.5px;text-transform:uppercase;margin-bottom:4px">Material / Equipment</div>
          <h5 class="modal-title">What would you like to do?</h5>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row" style="gap:0">
          <div class="col-6 pr-2">
            <a href="{{ route('purnew.create') }}" class="modal-sq-btn btn-primary d-flex w-100">
              <span class="btn-icon"><i class="fas fa-plus-circle"></i></span>
              <span>Create RFQ</span>
            </a>
          </div>
          <div class="col-6 pl-2">
            <a href="{{ route('purnew.groups') }}" class="modal-sq-btn btn-outline-primary d-flex w-100">
              <span class="btn-icon"><i class="fas fa-layer-group"></i></span>
              <span>Grouping</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  // Material card → modal
  document.getElementById('card-material').addEventListener('click', function () {
    $('#materialModal').modal('show');
  });

  // Outsourcing card → redirect directly to consultancy
  document.getElementById('card-outsourcing').addEventListener('click', function () {
    window.location.href = "{{ route('purchase.unified.create', ['type'=>'consultancy']) }}";
  });

  // Civil card → redirect
  document.getElementById('card-civil').addEventListener('click', function () {
    window.location.href = "{{ route('purchase.unified.create', ['type'=>'civil']) }}";
  });

  // Misc card → modal
  document.getElementById('card-misc').addEventListener('click', function () {
    $('#miscModal').modal('show');
  });

  // TA/DA card → redirect
  document.getElementById('card-tada').addEventListener('click', function () {
    window.location.href = "{{ route('purchase.unified.create', ['type'=>'tada']) }}";
  });
</script>

</div>
@endsection
