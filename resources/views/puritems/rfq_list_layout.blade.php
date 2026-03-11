@extends('welcome')
@section('content')

<div class="content-wrapper p-0">
  <div class="container-fluid p-0">
    <div class="sinc-wrapper">
<style>
  @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap');

  .sinc-wrapper {
    min-height: 100vh;
    background: #f5f7fa;
    font-family: 'DM Sans', sans-serif;
    padding: 32px 32px 56px;
  }

  /* ── Top bar ── */
  .sinc-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    gap: 12px;
    flex-wrap: wrap;
  }

  .sinc-page-title {
    font-size: 1.2rem;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -.3px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .sinc-page-title .title-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    background: #eff6ff;
    color: #3b82f6;
    display: flex; align-items: center; justify-content: center;
    font-size: .9rem;
    flex-shrink: 0;
  }

  .sinc-page-sub {
    font-size: .76rem;
    color: #94a3b8;
    font-weight: 500;
    margin-top: 1px;
    margin-left: 46px;
  }

  .nav-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
  }

  .sinc-nav-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 9px;
    font-family: 'DM Sans', sans-serif;
    font-size: .78rem;
    font-weight: 700;
    border: 1.5px solid transparent;
    cursor: pointer;
    text-decoration: none;
    transition: all .15s ease;
    white-space: nowrap;
  }

  .sinc-nav-btn.primary {
    background: #3b82f6;
    color: #fff;
    border-color: #3b82f6;
    box-shadow: 0 2px 8px rgba(59,130,246,.22);
  }

  .sinc-nav-btn.primary:hover { background: #2563eb; color: #fff; text-decoration: none; }

  .sinc-nav-btn.active {
    background: #eff6ff;
    color: #3b82f6;
    border-color: #bfdbfe;
  }

  .sinc-nav-btn.active:hover { background: #dbeafe; text-decoration: none; color: #2563eb; }

  .sinc-nav-btn.outline {
    background: #fff;
    color: #475569;
    border-color: #e2e8f0;
  }

  .sinc-nav-btn.outline:hover { border-color: #3b82f6; color: #3b82f6; text-decoration: none; }

  /* ── Card ── */
  .sinc-card {
    background: #fff;
    border: 1.5px solid #e8edf4;
    border-radius: 16px;
    overflow: hidden;
  }

  /* ── Search bar ── */
  .search-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    border-bottom: 1.5px solid #f1f5f9;
    background: #fff;
    flex-wrap: wrap;
  }

  .search-box {
    display: flex;
    align-items: center;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    background: #f8fafc;
    transition: border-color .15s ease;
    flex: 1;
    max-width: 360px;
    min-width: 200px;
  }

  .search-box:focus-within { border-color: #3b82f6; background: #fff; }

  .search-box input {
    flex: 1;
    border: none;
    background: transparent;
    padding: 9px 12px;
    font-family: 'DM Sans', sans-serif;
    font-size: .82rem;
    color: #0f172a;
    outline: none;
  }

  .search-box input::placeholder { color: #94a3b8; }

  .search-box button {
    border: none;
    background: #3b82f6;
    color: #fff;
    padding: 0 16px;
    height: 38px;
    font-size: .78rem;
    font-weight: 700;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    transition: background .15s ease;
    display: flex;
    align-items: center;
    gap: 5px;
  }

  .search-box button:hover { background: #2563eb; }

  .result-count {
    font-size: .76rem;
    font-weight: 600;
    color: #94a3b8;
    margin-left: auto;
  }

  /* ── Table ── */
  .rfq-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .85rem;
  }

  .rfq-table thead tr {
    background: #f8fafc;
    border-bottom: 1.5px solid #e8edf4;
  }

  .rfq-table thead th {
    padding: 11px 20px;
    font-size: .68rem;
    font-weight: 700;
    color: #64748b;
    letter-spacing: .5px;
    text-transform: uppercase;
    white-space: nowrap;
  }

  .rfq-table thead th.r { text-align: right; }

  .rfq-table tbody tr {
    border-bottom: 1px solid #f1f5f9;
    transition: background .1s ease;
    cursor: pointer;
  }

  .rfq-table tbody tr:last-child { border-bottom: none; }

  .rfq-table tbody tr:hover { background: #f0f7ff; }

  .rfq-table tbody td {
    padding: 14px 20px;
    color: #334155;
    font-weight: 500;
    vertical-align: middle;
  }

  .rfq-table tbody td.r { text-align: right; }

  /* RFQ ID badge */
  .rfq-id-badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 7px;
    background: #f1f5f9;
    color: #475569;
    font-size: .75rem;
    font-weight: 800;
    letter-spacing: .2px;
    white-space: nowrap;
  }

  /* Title link */
  .rfq-title-link {
    font-weight: 700;
    color: #0f172a;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: color .12s ease;
  }

  .rfq-title-link:hover {
    color: #3b82f6;
    text-decoration: none;
  }

  .rfq-title-link:hover .link-arrow { opacity: 1; transform: translateX(2px); }

  .link-arrow {
    font-size: .7rem;
    color: #3b82f6;
    opacity: 0;
    transition: opacity .12s ease, transform .15s ease;
  }

  /* Date */
  .date-cell {
    color: #64748b;
    font-weight: 600;
    font-size: .81rem;
  }

  /* Price */
  .price-val {
    font-weight: 800;
    color: #0f172a;
    font-variant-numeric: tabular-nums;
    letter-spacing: -.2px;
  }

  .price-val .curr {
    font-size: .72rem;
    color: #94a3b8;
    font-weight: 500;
    margin-right: 2px;
  }

  /* Empty state */
  .empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 56px 20px;
    gap: 10px;
    color: #cbd5e1;
  }

  .empty-state i { font-size: 2.2rem; }

  .empty-state p {
    font-size: .86rem;
    font-weight: 600;
    color: #94a3b8;
    margin: 0;
    text-align: center;
  }

  /* ── Table footer ── */
  .table-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 13px 20px;
    background: #f8fafc;
    border-top: 1.5px solid #e8edf4;
    flex-wrap: wrap;
    gap: 8px;
  }

  .tfoot-label {
    font-size: .72rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .4px;
  }

  @media (max-width: 600px) {
    .sinc-wrapper { padding: 18px 14px 40px; }
    .search-box { max-width: 100%; }
    .result-count { margin-left: 0; }
  }
</style>

<!-- TOP BAR -->
<div class="sinc-topbar">
  <div>
    <div class="sinc-page-title">
      <span class="title-icon"><i class="fas fa-layer-group"></i></span>
      Grouping
    </div>
    <div class="sinc-page-sub">All saved RFQ groups — click a title to view details</div>
  </div>

  <div class="nav-actions">
    <a href="{{ route('purnew.create') }}" class="sinc-nav-btn primary">
      <i class="fas fa-plus-circle"></i> Create New
    </a>
    <a href="{{ route('purnew.groups') }}" class="sinc-nav-btn active">
      <i class="fas fa-layer-group"></i> Grouping
    </a>
    <a href="{{ route('viewpurchasecase') }}" class="sinc-nav-btn outline">
      <i class="fas fa-briefcase"></i> Purchase Cases
    </a>
  </div>
</div>

<!-- MAIN CARD -->
<div class="sinc-card">

  <!-- Search bar -->
  <form method="get" action="{{ route('purnew.groups') }}">
    <div class="search-bar">
      <div class="search-box">
        <input type="text" name="term" value="{{ request('term') }}" placeholder="Search by title...">
        <button type="submit"><i class="fas fa-search"></i> Search</button>
      </div>
      @if(isset($rfqs))
        <span class="result-count">{{ $rfqs->count() }} result{{ $rfqs->count() !== 1 ? 's' : '' }}</span>
      @endif
    </div>
  </form>

  <!-- Table -->
  <div style="overflow-x:auto">
    <table class="rfq-table">
      <thead>
        <tr>
          <th style="width:90px">RFQ #</th>
          <th>Title</th>
          <th style="width:130px">Date</th>
          <th style="width:150px" class="r">Total (PKR)</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rfqs as $r)
        <tr onclick="window.location='{{ route('purnew.group.show', $r->rfq_id) }}'">
          <td>
            <span class="rfq-id-badge"># {{ $r->rfq_id }}</span>
          </td>
          <td>
            <a class="rfq-title-link" href="{{ route('purnew.group.show', $r->rfq_id) }}" onclick="event.stopPropagation()">
              {{ $r->pcs_title }}
              <i class="fas fa-chevron-right link-arrow"></i>
            </a>
          </td>
          <td>
            <span class="date-cell">
              <i class="fas fa-calendar-alt" style="font-size:.68rem;margin-right:5px;color:#cbd5e1"></i>
              {{ \Carbon\Carbon::parse($r->pcs_date)->format('d M Y') }}
            </span>
          </td>
          <td class="r">
            <span class="price-val">
              <span class="curr">Rs.</span>{{ number_format($r->total, 2) }}
            </span>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="4" style="padding:0;border:none">
            <div class="empty-state">
              <i class="fas fa-folder-open"></i>
              <p>No RFQ groups found.<br>Create a new one to get started.</p>
              <a href="{{ route('purnew.create') }}" class="sinc-nav-btn primary" style="margin-top:4px">
                <i class="fas fa-plus-circle"></i> Create New RFQ
              </a>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- Footer -->
  @if($rfqs->count() > 0)
  <div class="table-footer">
    <span class="tfoot-label">
      {{ $rfqs->count() }} RFQ group{{ $rfqs->count() !== 1 ? 's' : '' }} total
    </span>
    <span style="font-size:.76rem;font-weight:600;color:#94a3b8">
      Click any row to view details
    </span>
  </div>
  @endif

    </div>
  </div>
</div>
</div>
