@extends('welcome')
@section('content')

<div class="content-wrapper p-0">
  <div class="container-fluid p-0">
    <div class="sinc-wrapper">
<style>
  @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap');

  .sinc-wrapper {
    min-height: 100vh;
    background: var(--rd-bg);
    font-family: 'DM Sans', sans-serif;
    padding: 32px 32px 56px;
  }

  /* ── Top bar ── */
  .sinc-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 15px;
  }

  .sinc-page-title {
    font-size: 1.2rem;
    font-weight: 800;
    color: var(--rd-text1);
    letter-spacing: -.3px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .sinc-page-title .title-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    background: var(--rd-accent-soft);
    color: var(--rd-accent);
    display: flex; align-items: center; justify-content: center;
    font-size: .9rem;
    flex-shrink: 0;
  }

  .sinc-page-sub {
    font-size: .76rem;
    color: var(--rd-text3);
    font-weight: 500;
    margin-top: 1px;
    margin-left: 46px;
  }

  .sinc-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border: 1.5px solid var(--rd-border);
    border-radius: 10px;
    background: var(--rd-surface);
    color: var(--rd-text2);
    font-size: .82rem;
    font-weight: 600;
    text-decoration: none;
    transition: all .15s ease;
    white-space: nowrap;
  }

  .sinc-back-btn:hover { border-color: #3b82f6; color: #3b82f6; text-decoration: none; }

  /* ── Info strip ── */
  .info-strip {
    background: var(--rd-surface);
    border: 1.5px solid var(--rd-border);
    border-radius: 16px;
    padding: 0;
    margin-bottom: 16px;
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    overflow: hidden;
  }

  .info-cell {
    padding: 18px 24px;
    position: relative;
  }

  .info-cell + .info-cell::before {
    content: '';
    position: absolute;
    left: 0; top: 20%; bottom: 20%;
    width: 1.5px;
    background: var(--rd-border2);
  }

  .info-label {
    font-size: .68rem;
    font-weight: 700;
    color: var(--rd-text3);
    letter-spacing: .5px;
    text-transform: uppercase;
    margin-bottom: 5px;
  }

  .info-value {
    font-size: .95rem;
    font-weight: 800;
    color: var(--rd-text1);
    line-height: 1.2;
  }

  .info-value.blue { color: var(--rd-accent); }

  .info-value.big {
    font-size: 1.25rem;
    color: var(--rd-accent);
    letter-spacing: -.4px;
  }

  .info-value .currency {
    font-size: .78rem;
    font-weight: 600;
    color: var(--rd-text3);
    margin-right: 2px;
  }

  /* ── Table card ── */
  .sinc-card {
    background: var(--rd-surface);
    border: 1.5px solid var(--rd-border);
    border-radius: 16px;
    overflow: hidden;
  }

  .sinc-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px 12px;
    border-bottom: 1.5px solid var(--rd-border2);
  }

  .sinc-card-head-title {
    font-size: .82rem;
    font-weight: 800;
    color: var(--rd-text1);
    display: flex;
    align-items: center;
    gap: 7px;
  }

  .sinc-card-head-title i { color: var(--rd-accent); }

  .item-count-badge {
    font-size: .72rem;
    font-weight: 700;
    color: var(--rd-text2);
    background: var(--rd-surface2);
    padding: 3px 10px;
    border-radius: 999px;
  }

  /* ── Table ── */
  .rfq-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .85rem;
  }

  .rfq-table thead tr {
    background: var(--rd-surface2);
    border-bottom: 1.5px solid var(--rd-border);
  }

  .rfq-table thead th {
    padding: 11px 20px;
    font-size: .68rem;
    font-weight: 700;
    color: var(--rd-text3);
    letter-spacing: .5px;
    text-transform: uppercase;
    white-space: nowrap;
  }

  .rfq-table thead th.r { text-align: right; }

  .rfq-table tbody tr {
    border-bottom: 1px solid var(--rd-border);
    transition: background .1s ease;
  }

  .rfq-table tbody tr:last-child { border-bottom: none; }
  .rfq-table tbody tr:hover { background: var(--rd-surface2); }

  .rfq-table tbody td {
    padding: 13px 20px;
    color: var(--rd-text1);
    font-weight: 500;
    vertical-align: middle;
  }

  .rfq-table tbody td.r { text-align: right; }

  .row-num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px; height: 24px;
    border-radius: 7px;
    background: var(--rd-surface2);
    color: var(--rd-text3);
    font-size: .72rem;
    font-weight: 700;
  }

  .item-name { font-weight: 700; color: var(--rd-text1); }

  .price-val {
    font-weight: 700;
    color: var(--rd-text1);
    font-variant-numeric: tabular-nums;
  }

  .price-val .curr {
    font-size: .72rem;
    color: var(--rd-text3);
    font-weight: 500;
    margin-right: 2px;
  }

  /* Empty state */
  .empty-row td {
    text-align: center;
    padding: 40px 20px !important;
    color: #94a3b8;
    font-size: .84rem;
    font-weight: 600;
  }

  /* ── Footer ── */
  .table-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    background: var(--rd-surface2);
    border-top: 1.5px solid var(--rd-border);
    flex-wrap: wrap;
    gap: 8px;
  }

  .tfoot-label {
    font-size: .72rem;
    font-weight: 700;
    color: var(--rd-text3);
    text-transform: uppercase;
    letter-spacing: .4px;
  }

  .tfoot-total {
    font-size: 1.15rem;
    font-weight: 800;
    color: var(--rd-accent);
    letter-spacing: -.4px;
  }

  .tfoot-total .curr {
    font-size: .78rem;
    font-weight: 600;
    color: var(--rd-text3);
    margin-right: 2px;
  }

  @media (max-width: 640px) {
    .sinc-wrapper { padding: 18px 14px 40px; }
    .info-strip { grid-template-columns: 1fr; }
    .info-cell + .info-cell::before { top: 0; bottom: auto; left: 20%; right: 20%; width: auto; height: 1.5px; }
  }
</style>

<!-- TOP BAR -->
<div class="sinc-topbar">
  <div>
    <div class="sinc-page-title">
      <span class="title-icon"><i class="fas fa-file-invoice-dollar"></i></span>
      RFQ &nbsp;<span style="color:#94a3b8;font-weight:600">#{{ $rfq->rfq_id }}</span>
    </div>
    <div class="sinc-page-sub">Request for Quotation — item details and pricing</div>
  </div>
  <a href="{{ route('purnew.groups') }}" class="sinc-back-btn">
    <i class="fas fa-arrow-left" style="font-size:.72rem"></i> Back to Groups
  </a>
</div>

<!-- INFO STRIP -->
<div class="info-strip">
  <div class="info-cell">
    <div class="info-label">RFQ Title</div>
    <div class="info-value">{{ $rfq->pcs_title }}</div>
  </div>
  <div class="info-cell">
    <div class="info-label">Date</div>
    <div class="info-value blue">
      <i class="fas fa-calendar-alt" style="font-size:.8rem;margin-right:5px;opacity:.7"></i>
      {{ \Carbon\Carbon::parse($rfq->pcs_date)->format('d M Y') }}
    </div>
  </div>
  <div class="info-cell">
    <div class="info-label">Total Value</div>
    <div class="info-value big">
      <span class="currency">Rs.</span>{{ number_format($total, 2) }}
    </div>
  </div>
</div>

<!-- ITEMS TABLE CARD -->
<div class="sinc-card">
  <div class="sinc-card-head">
    <div class="sinc-card-head-title">
      <i class="fas fa-list-ul"></i> Items
    </div>
    <span class="item-count-badge">
      {{ $items->count() }} item{{ $items->count() !== 1 ? 's' : '' }}
    </span>
  </div>

  <div class="rd-table-responsive">
    <table class="rfq-table">
      <thead>
        <tr>
          <th style="width:56px">#</th>
          <th>Item Name</th>
          <th style="width:160px" class="r">Price (PKR)</th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $i => $r)
        <tr>
          <td><span class="row-num">{{ $i + 1 }}</span></td>
          <td><span class="item-name">{{ $r->title }}</span></td>
          <td class="r">
            <span class="price-val">
              <span class="curr">Rs.</span>{{ number_format($r->price ?? 0, 2) }}
            </span>
          </td>
        </tr>
        @empty
        <tr class="empty-row">
          <td colspan="3">
            <i class="fas fa-inbox" style="font-size:1.5rem;display:block;margin-bottom:8px;color:#e2e8f0"></i>
            No items found in this RFQ
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- Table footer -->
  <div class="table-footer">
    <span class="tfoot-label">Grand Total</span>
    <span class="tfoot-total">
      <span class="curr">Rs.</span>{{ number_format($total, 2) }}
    </span>
  </div>
</div>

    </div>
  </div>
</div>
@endsection
