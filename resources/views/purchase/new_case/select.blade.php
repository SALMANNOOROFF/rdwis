@extends('welcome')
@section('content')
<div class="content-wrapper">
<div class="purchase-select-wrapper">

<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Rajdhani:wght@600;700;800&display=swap');

  .purchase-select-wrapper {
    min-height: calc(100vh - 60px);
    background: #f8fafc;
    font-family: 'Inter', sans-serif;
    padding: 36px 20px 56px;
    position: relative;
  }

  .select-container {
    max-width: 1140px;
    margin: 0 auto;
  }

  /* ─── HEADER ─── */
  .select-header {
    text-align: center;
    margin-bottom: 42px;
  }

  .select-header .category-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    font-family: 'Rajdhani', sans-serif;
    margin-bottom: 12px;
  }

  .select-header h1 {
    font-weight: 800;
    font-size: 2.2rem;
    color: #0f172a !important;
    letter-spacing: -0.5px;
    margin-bottom: 8px;
    font-family: 'Rajdhani', sans-serif;
    text-transform: uppercase;
    line-height: 1.2;
  }

  .select-header p {
    font-size: 0.95rem;
    color: #64748b !important;
    font-weight: 500;
    max-width: 600px;
    margin: 0 auto;
  }

  /* ─── OPTION CARDS ─── */
  .type-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 16px;
    padding: 28px 24px 24px;
    box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05), 0 2px 6px -1px rgba(15, 23, 42, 0.02);
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
    text-decoration: none !important;
    cursor: pointer;
  }

  .type-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 16px 36px -4px rgba(15, 23, 42, 0.10), 0 4px 12px -2px rgba(15, 23, 42, 0.05);
    border-color: #cbd5e1;
  }

  /* Top Border Strip */
  .type-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    transition: height 0.2s ease;
  }
  .type-card:hover::before {
    height: 5px;
  }

  .card-ps::before { background: #2563eb; }
  .card-pt::before { background: #059669; }
  .card-rb::before { background: #d97706; }

  .card-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
  }

  .type-icon {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
  }

  .card-ps .type-icon { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
  .card-pt .type-icon { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
  .card-rb .type-icon { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }

  .type-code-badge {
    font-family: 'Rajdhani', sans-serif;
    font-size: 0.85rem;
    font-weight: 800;
    padding: 3px 10px;
    border-radius: 6px;
    letter-spacing: 0.8px;
  }

  .card-ps .type-code-badge { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
  .card-pt .type-code-badge { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
  .card-rb .type-code-badge { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }

  .type-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 8px;
    font-family: 'Rajdhani', sans-serif;
    letter-spacing: 0.3px;
  }

  .type-desc {
    font-size: 0.84rem;
    color: #64748b;
    line-height: 1.55;
    margin-bottom: 22px;
    min-height: 58px;
  }

  .type-meta {
    border-top: 1px dashed #e2e8f0;
    padding-top: 16px;
    margin-bottom: 22px;
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .meta-item {
    font-size: 0.8rem;
    color: #334155;
    display: flex;
    align-items: center;
    gap: 9px;
  }

  .meta-item i {
    font-size: 0.75rem;
  }

  .btn-select-type {
    width: 100%;
    padding: 10px 16px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.82rem;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: none;
    transition: all 0.2s ease;
    letter-spacing: 0.3px;
    font-family: 'Rajdhani', sans-serif;
  }

  .card-ps .btn-select-type { background: #2563eb; color: #ffffff; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.20); }
  .card-pt .btn-select-type { background: #059669; color: #ffffff; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.20); }
  .card-rb .btn-select-type { background: #d97706; color: #ffffff; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.20); }

  .type-card:hover .btn-select-type {
    transform: translateY(-1px);
    filter: brightness(1.08);
  }
  .card-ps:hover .btn-select-type { box-shadow: 0 6px 18px rgba(37, 99, 235, 0.32); }
  .card-pt:hover .btn-select-type { box-shadow: 0 6px 18px rgba(5, 150, 105, 0.32); }
  .card-rb:hover .btn-select-type { box-shadow: 0 6px 18px rgba(217, 119, 6, 0.32); }

  .btn-back-hub {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 22px;
    border-radius: 30px;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    color: #475569;
    font-size: 0.82rem;
    font-weight: 600;
    text-decoration: none !important;
    transition: all 0.2s;
  }
  .btn-back-hub:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
    color: #0f172a;
    transform: translateY(-1px);
  }
</style>

<div class="select-container">

  <!-- HEADING -->
  <div class="select-header">
    <div class="category-pill">
      <i class="fas fa-layer-group"></i> PURCHASE WORKFLOW
    </div>
    <h1>Purchase Case Initiation</h1>
    <p>Select the purchase case type to begin your procurement documentation</p>
  </div>

  <!-- 3 LEGACY OPTIONS -->
  <div class="row">
    <!-- OPTION 1: PS (Major Purchase With Quotes) -->
    <div class="col-lg-4 col-md-6 mb-4">
      <a href="{{ route('purchase.unified.create', ['type' => 'Ps']) }}" class="type-card card-ps">
        <div>
          <div class="card-header-row">
            <div class="type-icon"><i class="fas fa-boxes"></i></div>
            <span class="type-code-badge">Type: Ps</span>
          </div>
          <div class="type-title">Major Purchase (With Quotes)</div>
          <div class="type-desc">
            Procurement requiring competitive quotations from multiple firms, formal comparative statement, and lowest-bid recommendation.
          </div>
        </div>
        <div>
          <div class="type-meta">
            <div class="meta-item"><i class="fas fa-check-circle" style="color: #2563eb;"></i> Multi-Firm Quotation Workflow</div>
            <div class="meta-item"><i class="fas fa-check-circle" style="color: #2563eb;"></i> Comparative Statement (CS)</div>
            <div class="meta-item"><i class="fas fa-file-alt" style="color: #2563eb;"></i> Approval: Minute + Market Research</div>
          </div>
          <div class="btn-select-type">
            <span>INITIATE MAJOR PURCHASE</span> <i class="fas fa-arrow-right ml-1"></i>
          </div>
        </div>
      </a>
    </div>

    <!-- OPTION 2: PT (Incidental Expenditure Without Quotes) -->
    <div class="col-lg-4 col-md-6 mb-4">
      <a href="{{ route('purchase.unified.create', ['type' => 'Pt']) }}" class="type-card card-pt">
        <div>
          <div class="card-header-row">
            <div class="type-icon"><i class="fas fa-receipt"></i></div>
            <span class="type-code-badge">Type: Pt</span>
          </div>
          <div class="type-title">Incidental Exp. (Without Quotes)</div>
          <div class="type-desc">
            Routine departmental expenditures, supplies, or urgent single-source expenses where competitive quotations are not required.
          </div>
        </div>
        <div>
          <div class="type-meta">
            <div class="meta-item"><i class="fas fa-check-circle" style="color: #059669;"></i> Direct Line Item Entry</div>
            <div class="meta-item"><i class="fas fa-check-circle" style="color: #059669;"></i> Single Vendor / Firm Selection</div>
            <div class="meta-item"><i class="fas fa-file-alt" style="color: #059669;"></i> Approval: Single Form Slot</div>
          </div>
          <div class="btn-select-type">
            <span>INITIATE INCIDENTAL EXP.</span> <i class="fas fa-arrow-right ml-1"></i>
          </div>
        </div>
      </a>
    </div>

    <!-- OPTION 3: RB (TA/DA) -->
    <div class="col-lg-4 col-md-12 mb-4">
      <a href="{{ route('purchase.unified.create', ['type' => 'Rb']) }}" class="type-card card-rb">
        <div>
          <div class="card-header-row">
            <div class="type-icon"><i class="fas fa-plane-departure"></i></div>
            <span class="type-code-badge">Type: Rb</span>
          </div>
          <div class="type-title">TA / DA</div>
          <div class="type-desc">
            Official travel and daily allowance claims, transportation costs, and boarding disbursements for officers and staff.
          </div>
        </div>
        <div>
          <div class="type-meta">
            <div class="meta-item"><i class="fas fa-check-circle" style="color: #d97706;"></i> Division Contract Employee Selection</div>
            <div class="meta-item"><i class="fas fa-check-circle" style="color: #d97706;"></i> Auto-Calculated TA/DA Amount</div>
            <div class="meta-item"><i class="fas fa-file-alt" style="color: #d97706;"></i> Meezan Account / Cheque Verification</div>
          </div>
          <div class="btn-select-type">
            <span>INITIATE TA/DA CLAIM</span> <i class="fas fa-arrow-right ml-1"></i>
          </div>
        </div>
      </a>
    </div>
  </div>

  <div class="text-center mt-4">
    <a href="{{ route('purchase.initiation.index') }}" class="btn-back-hub shadow-sm">
      <i class="fas fa-arrow-left"></i> Return to Purchase Hub
    </a>
  </div>

</div>
</div>
</div>
@endsection
