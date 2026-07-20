@extends('welcome')
@section('content')
<div class="content-wrapper">
<div class="contract-wrapper">

<style>
  @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap');

  body, html {
    font-family: 'Outfit', sans-serif;
    background-color: var(--rd-bg);
  }

  .contract-wrapper {
    min-height: 100vh;
    background: var(--rd-bg);
    font-family: 'Outfit', sans-serif;
    padding: 20px 10px 48px;
  }

  /* ─── TYPOGRAPHY & HEADER ─── */
  .page-heading {
    text-align: center;
    margin-bottom: 36px;
    position: relative;
    z-index: 2;
  }

  .page-heading h1 {
    font-weight: 900;
    font-size: calc(1.5rem + 1.5vw);
    color: var(--rd-text1);
    letter-spacing: -0.5px;
    margin-bottom: 8px;
  }
  .page-heading p {
    font-size: 1rem;
    color: var(--rd-text3);
    font-weight: 500;
  }

  /* ─── MAIN GLASS CARDS ─── */
  .glass-card {
    background: var(--rd-surface);
    border-radius: 24px;
    padding: 28px 24px;
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
    min-height: 250px;
    cursor: pointer;
    text-decoration: none !important;
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
    transform: translateY(-6px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
    border-color: var(--rd-accent-soft);
  }

  /* ─── COLOR ACCENT STRIPS ─── */
  .glass-card.accent-blue::after   { background: linear-gradient(135deg, #3b82f6, #06b6d4); }
  .glass-card.accent-amber::after  { background: linear-gradient(135deg, #f59e0b, #f97316); }
  .glass-card.accent-green::after  { background: linear-gradient(135deg, #10b981, #059669); }
  .glass-card.accent-red::after    { background: linear-gradient(135deg, #ef4444, #f43f5e); }

  .glass-card::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 4px;
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
    font-size: 1.8rem;
    margin-bottom: 24px;
    transition: transform 0.3s ease;
  }

  .glass-card:hover .card-icon-wrap {
    transform: scale(1.08) rotate(-3deg);
  }

  /* Icon wrapper colors */
  .icon-blue   { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
  .icon-amber  { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
  .icon-green  { background: rgba(16, 185, 129, 0.1); color: #10b981; }
  .icon-red    { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

  /* Typo inside cards */
  .gc-title {
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--rd-text1);
    margin-bottom: 10px;
    line-height: 1.2;
    letter-spacing: -0.2px;
  }
  .gc-sub {
    font-size: 0.95rem;
    color: var(--rd-text2);
    margin-bottom: 24px;
    line-height: 1.5;
    font-weight: 400;
    flex-grow: 1;
  }

  .gc-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--rd-surface2);
    padding: 8px 18px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--rd-text2);
    letter-spacing: 0.3px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.06);
    border: 1px solid var(--rd-border);
    width: fit-content;
    transition: all 0.2s ease;
  }

  .glass-card:hover .gc-chip {
    background: var(--rd-surface);
    color: var(--rd-text1);
    border-color: rgba(255, 255, 255, 0.15);
  }

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
      <span class="badge badge-success px-3 py-1 mb-2" style="font-size: 10px; letter-spacing: 1px; background: rgba(40, 167, 69, 0.1); border: 1px solid rgba(40, 167, 69, 0.2); color: var(--rd-success);">HR CONTRACT OPERATIONS</span>
      <h1>INITIATE CONTRACT CASE</h1>
      <p>Select the contract category you would like to initiate for an employee</p>
    </div>

    <!-- CARDS GRID -->
    <div class="row card-row">
      
      <!-- Fresh Hiring → BLUE -->
      <div class="col-md-6 mb-4">
        <a href="#" class="glass-card accent-blue h-100">
          <div>
            <div class="card-icon-wrap icon-blue"><i class="fas fa-user-plus"></i></div>
            <div class="gc-title">Fresh Hiring</div>
            <div class="gc-sub">
              The said term is used for 'New Hiring'. If an employee is being hired for the very first time for a period up to 1 - Year.
            </div>
          </div>
          <span class="gc-chip">
            <i class="fas fa-circle" style="font-size:.4rem;color:#3b82f6"></i> Initiate Case &nbsp;•&nbsp; New Hiring
          </span>
        </a>
      </div>

      <!-- Contract Extension → AMBER -->
      <div class="col-md-6 mb-4">
        <a href="#" class="glass-card accent-amber h-100">
          <div>
            <div class="card-icon-wrap icon-amber"><i class="fas fa-clock"></i></div>
            <div class="gc-title">Contract Extension</div>
            <div class="gc-sub">
              The said term is used if the contract is being extended up to 1 - Year on the same salary and designation.
            </div>
          </div>
          <span class="gc-chip">
            <i class="fas fa-circle" style="font-size:.4rem;color:#f59e0b"></i> Extend &nbsp;•&nbsp; Same Salary & Desig
          </span>
        </a>
      </div>

    </div>

    <div class="row card-row">

      <!-- Contract Renewal → GREEN -->
      <div class="col-md-6 mb-4">
        <a href="#" class="glass-card accent-green h-100">
          <div>
            <div class="card-icon-wrap icon-green"><i class="fas fa-award"></i></div>
            <div class="gc-title">Contract Renewal</div>
            <div class="gc-sub">
              The said term is used if contract is being renewed for 01 x year based on performance appraisal or promotion.
            </div>
          </div>
          <span class="gc-chip">
            <i class="fas fa-circle" style="font-size:.4rem;color:#10b981"></i> Renew &nbsp;•&nbsp; Appraisal / Promotion
          </span>
        </a>
      </div>

      <!-- Rehiring → RED -->
      <div class="col-md-6 mb-4">
        <a href="#" class="glass-card accent-red h-100">
          <div>
            <div class="card-icon-wrap icon-red"><i class="fas fa-undo-alt"></i></div>
            <div class="gc-title">Rehiring</div>
            <div class="gc-sub">
              The said term is used if a former employee is being hired again for a period of 1 - Year.
            </div>
          </div>
          <span class="gc-chip">
            <i class="fas fa-circle" style="font-size:.4rem;color:#ef4444"></i> Rehire &nbsp;•&nbsp; Former Employee
          </span>
        </a>
      </div>

    </div>

  </div>
</div>

</div>
</div>
@endsection
