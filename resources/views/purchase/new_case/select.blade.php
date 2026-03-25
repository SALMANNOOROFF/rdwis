  @extends('welcome')
@section('content')

<div class="purchase-wrapper">

<style>
  @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap');

  .purchase-wrapper {
    min-height: 100vh;
    background: #f0f4f8;
    font-family: 'Outfit', sans-serif;
    padding: 32px 0 48px;
  }

  .page-heading {
    text-align: center;
    margin-bottom: 36px;
  }

  .page-heading h1 {
    font-size: 2rem;
    font-weight: 800;
    color: #1e293b;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
  }

  .page-heading p {
    color: #64748b;
    font-size: 1rem;
    font-weight: 500;
  }

  /* ─── MAIN GLASS CARDS ─── */
  .glass-card {
    position: relative;
    background: rgba(255, 255, 255, 0.72);
    border-radius: 20px;
    border: 1.5px solid rgba(255, 255, 255, 0.9);
    box-shadow:
      0 4px 24px rgba(100, 116, 139, 0.10),
      0 1.5px 4px rgba(100, 116, 139, 0.08),
      inset 0 1px 0 rgba(255,255,255,0.95);
    backdrop-filter: blur(20px) saturate(1.6);
    -webkit-backdrop-filter: blur(20px) saturate(1.6);
    padding: 28px 26px 24px;
    cursor: pointer;
    transition: transform 0.22s cubic-bezier(.34,1.56,.64,1),
                box-shadow 0.22s ease,
                background 0.18s ease;
    overflow: hidden;
  }

  .glass-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    border-radius: 20px 20px 0 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.8), transparent);
  }

  .glass-card:hover {
    transform: translateY(-5px) scale(1.01);
    box-shadow:
      0 12px 40px rgba(100, 116, 139, 0.18),
      0 4px 12px rgba(100, 116, 139, 0.10),
      inset 0 1px 0 rgba(255,255,255,0.95);
    background: rgba(255, 255, 255, 0.88);
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

  .card-icon-wrap {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
    font-size: 1.4rem;
  }

  /* ROW 1 icons → BLUE */
  .icon-blue   { background: rgba(59,130,246,0.12); color: #3b82f6; }

  /* ROW 2 icons → AMBER */
  .icon-amber  { background: rgba(245,158,11,0.12); color: #f59e0b; }

  /* MISC icon → GREEN */
  .icon-green  { background: rgba(16,185,129,0.12); color: #10b981; }

  .gc-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 4px;
  }

  .gc-sub {
    font-size: 0.84rem;
    color: #64748b;
    font-weight: 500;
    margin-bottom: 16px;
  }

  .gc-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 999px;
    background: rgba(100,116,139,0.09);
    color: #475569;
    font-size: 0.76rem;
    font-weight: 600;
    letter-spacing: 0.2px;
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
    background: rgba(255,255,255,0.75);
    border: 1.5px solid rgba(255,255,255,0.9);
    box-shadow:
      0 2px 12px rgba(100,116,139,0.09),
      inset 0 1px 0 rgba(255,255,255,0.9);
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
    box-shadow: 0 8px 28px rgba(100,116,139,0.15);
    background: rgba(255,255,255,0.92);
    text-decoration: none;
  }

  .sq-title {
    font-size: 0.88rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 3px;
    line-height: 1.2;
  }

  .sq-sub {
    font-size: 0.74rem;
    color: #94a3b8;
    font-weight: 500;
  }

  /* ─── MODAL OVERRIDES ─── */
  .modal-content {
    background: rgba(255, 255, 255, 0.85) !important;
    backdrop-filter: blur(28px) saturate(1.8) !important;
    -webkit-backdrop-filter: blur(28px) saturate(1.8) !important;
    border: 1.5px solid rgba(255, 255, 255, 0.9) !important;
    border-radius: 24px !important;
    box-shadow: 0 24px 64px rgba(30, 41, 59, 0.16) !important;
    font-family: 'Outfit', sans-serif;
  }

  .modal-header {
    border-bottom: 1px solid rgba(100,116,139,0.1) !important;
    padding: 24px 28px 16px !important;
  }

  .modal-title {
    font-weight: 700 !important;
    font-size: 1.1rem !important;
    color: #1e293b !important;
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

  .modal-header .close:hover { color: #1e293b; }

  /* Row gap fix */
  .card-row { margin-bottom: 20px; }

</style>

<div class="content p-4">
  <div class="container-fluid" style="max-width:1000px">

    <!-- HEADING -->
    <div class="page-heading">
      <h1>PURCHASE CASES</h1>
      <p>Please select a category to proceed</p>
    </div>

    <!-- ROW 1: Material | Outsourcing | Civil → ALL BLUE -->
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

          <!-- Inline sub-grid (kept for structure, hidden by default) -->
          <div class="sub-grid-wrapper" id="outsourcingSubGrid">
            <div class="row">
              <div class="col-6">
                <a class="sq-card" href="{{ route('createnewcase', ['type'=>'services']) }}">
                  <div class="sq-title">Services</div>
                  <div class="sq-sub">External services</div>
                </a>
              </div>
              <div class="col-6">
                <a class="sq-card" href="{{ route('purnew.consultancy.create') }}">
                  <div class="sq-title">Consultancy</div>
                  <div class="sq-sub">Professional advice</div>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Civil Works → BLUE -->
      <div class="col-md-4 mb-3">
        <div class="glass-card accent-blue h-100" id="card-civil">
          <div class="card-icon-wrap icon-blue"><i class="fas fa-hard-hat"></i></div>
          <div class="gc-title">Civil Works / Upfit</div>
          <div class="gc-sub">Civil works and facility upfit</div>
          <span class="gc-chip"><i class="fas fa-circle" style="font-size:.4rem;color:#3b82f6"></i> BOQs &nbsp;•&nbsp; Work Orders</span>
        </div>
      </div>
    </div>

    <!-- ROW 2: Training + TA/DA → AMBER/YELLOW -->
    <div class="row card-row">
      <div class="col-md-6 mb-3">
        <div class="glass-card accent-amber h-100" id="card-training">
          <div class="card-icon-wrap icon-amber"><i class="fas fa-chalkboard-teacher"></i></div>
          <div class="gc-title">Training</div>
          <div class="gc-sub">Workshops and trainings</div>
          <span class="gc-chip"><i class="fas fa-circle" style="font-size:.4rem;color:#f59e0b"></i> Create</span>
        </div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="glass-card accent-amber h-100" id="card-tada">
          <div class="card-icon-wrap icon-amber"><i class="fas fa-suitcase-rolling"></i></div>
          <div class="gc-title">TA / DA</div>
          <div class="gc-sub">Travel and daily allowance</div>
          <span class="gc-chip"><i class="fas fa-circle" style="font-size:.4rem;color:#f59e0b"></i> Create</span>
        </div>
      </div>
    </div>

    <!-- ROW 3: Miscellaneous → GREEN -->
    <div class="row card-row">
      <div class="col-md-12">
        <div class="glass-card accent-green" id="card-misc" style="cursor:pointer">
          <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center" style="gap:14px">
              <div class="card-icon-wrap icon-green" style="margin-bottom:0"><i class="fas fa-shapes"></i></div>
              <div>
                <div class="gc-title" style="margin-bottom:2px">Miscellaneous</div>
                <div class="gc-sub" style="margin-bottom:0">Other expense categories</div>
              </div>
            </div>
            <span class="gc-chip" id="misc-toggle-icon"><i class="fas fa-chevron-down" style="font-size:.75rem"></i></span>
          </div>

          <div class="sub-grid-wrapper" id="misc-grid"></div>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- ─── OUTSOURCING MODAL ─── -->
<div class="modal fade" id="outsourcingModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:460px" role="document">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <div>
          <div style="font-size:.75rem;font-weight:600;color:#94a3b8;letter-spacing:.5px;text-transform:uppercase;margin-bottom:4px">Outsourcing</div>
          <h5 class="modal-title">Choose a type</h5>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row" style="gap:0">
          <div class="col-6 pr-2">
            <a href="{{ route('createnewcase', ['type'=>'incidental']) }}" class="modal-sq-btn btn-outline-primary d-flex w-100">
              <span class="btn-icon"><i class="fas fa-concierge-bell"></i></span>
              <span>Services</span>
            </a>
          </div>
          <div class="col-6 pl-2">
            <a href="{{ route('purnew.consultancy.create') }}" class="modal-sq-btn btn-outline-primary d-flex w-100">
              <span class="btn-icon"><i class="fas fa-user-tie"></i></span>
              <span>Consultancy</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ─── CIVIL MODAL ─── -->
<div class="modal fade" id="civilModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:420px" role="document">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <div>
          <div style="font-size:.75rem;font-weight:600;color:#94a3b8;letter-spacing:.5px;text-transform:uppercase;margin-bottom:4px">Civil Works / Upfit</div>
          <h5 class="modal-title">Proceed</h5>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <a href="{{ route('createnewcase', ['type'=>'major']) }}" class="modal-sq-btn btn-primary d-flex w-100">
          <span class="btn-icon"><i class="fas fa-hard-hat"></i></span>
          <span>Create Case</span>
        </a>
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
          <div style="font-size:.75rem;font-weight:600;color:#94a3b8;letter-spacing:.5px;text-transform:uppercase;margin-bottom:4px">Miscellaneous</div>
          <h5 class="modal-title">Select a sub-category</h5>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-12 col-md-4 mb-2">
            <a href="{{ route('createnewcase', ['type'=>'incidental']) }}" class="modal-sq-btn btn-outline-primary d-flex w-100">
              <span class="btn-icon"><i class="fas fa-shuttle-van"></i></span>
              <span>Transport</span>
            </a>
          </div>
          <div class="col-12 col-md-4 mb-2">
            <a href="{{ route('createnewcase', ['type'=>'incidental']) }}" class="modal-sq-btn btn-outline-primary d-flex w-100">
              <span class="btn-icon"><i class="fas fa-book"></i></span>
              <span>Books</span>
            </a>
          </div>
          <div class="col-12 col-md-4 mb-2">
            <a href="{{ route('createnewcase', ['type'=>'incidental']) }}" class="modal-sq-btn btn-outline-primary d-flex w-100">
              <span class="btn-icon"><i class="fas fa-file-signature"></i></span>
              <span>License Fees</span>
            </a>
          </div>
        </div>
        <div class="row">
          <div class="col-12 col-md-4 mb-2">
            <a href="{{ route('createnewcase', ['type'=>'incidental']) }}" class="modal-sq-btn btn-outline-primary d-flex w-100">
              <span class="btn-icon"><i class="fas fa-wifi"></i></span>
              <span>Phone / Internet</span>
            </a>
          </div>
          <div class="col-12 col-md-4 mb-2">
            <a href="{{ route('createnewcase', ['type'=>'incidental']) }}" class="modal-sq-btn btn-outline-primary d-flex w-100">
              <span class="btn-icon"><i class="fas fa-newspaper"></i></span>
              <span>Pub. / Reg. Fees</span>
            </a>
          </div>
          <div class="col-12 col-md-4 mb-2">
            <a href="{{ route('createnewcase', ['type'=>'incidental']) }}" class="modal-sq-btn btn-outline-primary d-flex w-100">
              <span class="btn-icon"><i class="fas fa-pen"></i></span>
              <span>Stationery</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ─── TRAINING MODAL ─── -->
<div class="modal fade" id="trainingModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:420px" role="document">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <div>
          <div style="font-size:.75rem;font-weight:600;color:#94a3b8;letter-spacing:.5px;text-transform:uppercase;margin-bottom:4px">Training</div>
          <h5 class="modal-title">Proceed</h5>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <a href="{{ route('createnewcase', ['type'=>'incidental']) }}" class="modal-sq-btn btn-primary d-flex w-100">
          <span class="btn-icon"><i class="fas fa-chalkboard-teacher"></i></span>
          <span>Create Case</span>
        </a>
      </div>
    </div>
  </div>
</div>

<!-- ─── TA/DA MODAL ─── -->
<div class="modal fade" id="tadaModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:420px" role="document">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <div>
          <div style="font-size:.75rem;font-weight:600;color:#94a3b8;letter-spacing:.5px;text-transform:uppercase;margin-bottom:4px">TA / DA</div>
          <h5 class="modal-title">Proceed</h5>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <a href="{{ route('createnewcase', ['type'=>'tada']) }}" class="modal-sq-btn btn-primary d-flex w-100">
          <span class="btn-icon"><i class="fas fa-suitcase-rolling"></i></span>
          <span>Create Case</span>
        </a>
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
          <div style="font-size:.75rem;font-weight:600;color:#94a3b8;letter-spacing:.5px;text-transform:uppercase;margin-bottom:4px">Material / Equipment</div>
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

  // Outsourcing card → modal
  document.getElementById('card-outsourcing').addEventListener('click', function () {
    $('#outsourcingModal').modal('show');
  });

  // Civil card → modal
  document.getElementById('card-civil').addEventListener('click', function () {
    $('#civilModal').modal('show');
  });

  // Misc card → modal
  document.getElementById('card-misc').addEventListener('click', function () {
    $('#miscModal').modal('show');
  });

  // Training card → modal
  document.getElementById('card-training').addEventListener('click', function () {
    $('#trainingModal').modal('show');
  });

  // TA/DA card → modal
  document.getElementById('card-tada').addEventListener('click', function () {
    $('#tadaModal').modal('show');
  });
</script>

</div>
@endsection
