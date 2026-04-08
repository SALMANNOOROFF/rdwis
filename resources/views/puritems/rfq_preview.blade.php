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
    margin-bottom: 28px;
  }

  .sinc-page-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--rd-text1);
    letter-spacing: -.3px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .sinc-page-title .title-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: var(--rd-accent-soft);
    color: var(--rd-accent);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
  }

  .sinc-page-sub {
    font-size: .78rem;
    color: var(--rd-text3);
    font-weight: 500;
    margin-top: 1px;
    margin-left: 48px;
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
    transition: all .18s ease;
  }

  .sinc-back-btn:hover {
    border-color: #3b82f6;
    color: #3b82f6;
    text-decoration: none;
  }

  /* ── Main card ── */
  .sinc-card {
    background: var(--rd-surface);
    border: 1.5px solid var(--rd-border);
    border-radius: 18px;
    overflow: hidden;
  }

  /* ── Save bar (replaces card-header) ── */
  .save-bar {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px 24px;
    border-bottom: 1.5px solid var(--rd-border2);
    background: var(--rd-surface);
    flex-wrap: wrap;  
  }

  .save-bar label {
    font-size: .82rem;
    font-weight: 700;
    color: var(--rd-text2);
    white-space: nowrap;
    margin: 0;
    letter-spacing: .2px;
  }

  .save-bar .rfq-input {
    flex: 1;
    min-width: 240px;
    max-width: 380px;
    height: 40px;
    border: 1.5px solid var(--rd-border);
    border-radius: 10px;
    padding: 0 14px;
    font-family: 'DM Sans', sans-serif;
    font-size: .88rem;
    font-weight: 600;
    color: var(--rd-text1);
    background: var(--rd-surface2);
    transition: border-color .18s ease, box-shadow .18s ease;
    outline: none;
  }

  .save-bar .rfq-input:focus {
    border-color: var(--rd-accent);
    box-shadow: 0 0 0 3px var(--rd-accent-soft);
    background: var(--rd-surface);
  }

  .save-bar .rfq-input::placeholder { color: #94a3b8; font-weight: 500; }

  .btn-save {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 0 20px;
    height: 40px;
    border-radius: 10px;
    background: var(--rd-accent);
    color: #fff;
    font-family: 'DM Sans', sans-serif;
    font-size: .85rem;
    font-weight: 700;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(59,130,246,.28);
    transition: transform .18s cubic-bezier(.34,1.56,.64,1), box-shadow .18s ease;
    white-space: nowrap;
  }

  .btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(59,130,246,.36);
    color: #fff;
  }

  /* ── Table ── */
  .rfq-table-wrap {
    overflow-x: auto;
  }

  .rfq-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .86rem;
  }

  .rfq-table thead tr {
    background: var(--rd-surface2);
    border-bottom: 1.5px solid var(--rd-border);
  }

  .rfq-table thead th {
    padding: 12px 18px;
    font-size: .72rem;
    font-weight: 700;
    color: var(--rd-text3);
    letter-spacing: .5px;
    text-transform: uppercase;
    white-space: nowrap;
  }

  .rfq-table thead th.text-right { text-align: right; }

  .rfq-table tbody tr {
    border-bottom: 1px solid var(--rd-border2);
    transition: background .12s ease;
  }

  .rfq-table tbody tr:hover { background: var(--rd-surface2); }

  .rfq-table tbody td {
    padding: 13px 18px;
    color: var(--rd-text1);
    font-weight: 500;
    vertical-align: middle;
  }

  .rfq-table tbody td.text-right { text-align: right; }

  /* Row number badge */
  .row-num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border-radius: 7px;
    background: var(--rd-surface2);
    color: var(--rd-text3);
    font-size: .75rem;
    font-weight: 700;
  }

  /* Item name */
  .item-name {
    font-weight: 600;
    color: var(--rd-text1);
  }

  /* Price */
  .price-cell {
    font-weight: 700;
    color: var(--rd-text1);
    font-variant-numeric: tabular-nums;
    letter-spacing: -.2px;
  }

  .price-prefix {
    font-size: .74rem;
    color: var(--rd-text3);
    font-weight: 500;
    margin-right: 2px;
  }

  /* ── Footer / Grand Total ── */
  .rfq-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 24px;
    background: var(--rd-surface2);
    border-top: 1.5px solid var(--rd-border);
    flex-wrap: wrap;
    gap: 8px;
  }

  .rfq-footer .item-count {
    font-size: .78rem;
    font-weight: 600;
    color: var(--rd-text3);
  }

  .grand-total-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .grand-total-label {
    font-size: .78rem;
    font-weight: 700;
    color: var(--rd-text2);
    text-transform: uppercase;
    letter-spacing: .4px;
  }

  .grand-total-value {
    font-size: 1.2rem;
    font-weight: 800;
    color: var(--rd-accent);
    letter-spacing: -.5px;
  }

  .grand-total-value .currency {
    font-size: .8rem;
    font-weight: 600;
    color: var(--rd-text3);
    margin-right: 3px;
  }

  @media (max-width: 768px) {
    .sinc-wrapper { padding: 16px 12px 32px; }
    .sinc-topbar { flex-direction: column; align-items: flex-start; gap: 12px; }
    .sinc-page-sub { margin-left: 0; }
    .sinc-card { border-radius: 12px; }
    .save-bar { padding: 14px 16px; flex-direction: column; align-items: stretch; }
    .save-bar .rfq-input { max-width: 100%; min-width: 0; }
    .btn-save { width: 100%; justify-content: center; }
    .rfq-table thead th { padding: 10px; font-size: .65rem; }
    .rfq-table tbody td { padding: 10px; font-size: .8rem; }
    .rfq-footer { flex-direction: column; align-items: center; text-align: center; padding: 20px; }
    .grand-total-wrap { margin-top: 10px; }
  }
</style>

<!-- TOP BAR -->
<div class="sinc-topbar">
  <div>
    <div class="sinc-page-title">
      <span class="title-icon"><i class="fas fa-file-invoice"></i></span>
      RFQ Preview
    </div>
    <div class="sinc-page-sub">Review items and save to create the RFQ</div>
  </div>
  <a href="{{ url()->previous() }}" class="sinc-back-btn">
    <i class="fas fa-arrow-left" style="font-size:.72rem"></i> Back
  </a>
</div>

<!-- MAIN CARD -->
<div class="sinc-card">

  <!-- Save Bar -->
  <form method="post" action="{{ route('purnew.rfq.submit') }}" id="rfq-submit-form">
    @csrf
    <div class="save-bar">
      <label for="rfq-title-input">Group Name</label>
      <input
        id="rfq-title-input"
        type="text"
        class="rfq-input"
        name="title"
        placeholder="Enter RFQ title"
        value="{{ request('title', 'Draft RFQ') }}"
      >
      <button type="submit" class="btn-save">
        <i class="fas fa-check"></i> Save &amp; Create RFQ
      </button>
    </div>
  </form>

  <!-- Table -->
  <div class="rd-table-responsive">
    <table class="rfq-table">
      <thead>
        <tr>
          <th style="width:60px">#</th>
          <th>Item</th>
          <th style="width:160px" class="text-right">Price (PKR)</th>
        </tr>
      </thead>
      <tbody>
        @foreach($rows as $i => $r)
        <tr>
          <td><span class="row-num">{{ $i + 1 }}</span></td>
          <td>
            <span class="item-name">{{ $r['item'] ? $r['item']->title : '—' }}</span>
          </td>
          <td class="text-right">
            <span class="price-cell">
              <span class="price-prefix">Rs.</span>{{ number_format($r['price'] ?? 0, 2) }}
            </span>
          </td>
        </tr>
        <!-- Hidden fields bound to the form above -->
        <input type="hidden" form="rfq-submit-form" name="items[{{ $i }}][item_id]" value="{{ $r['item'] ? $r['item']->item_id : '' }}">
        <input type="hidden" form="rfq-submit-form" name="items[{{ $i }}][price]"   value="{{ $r['price'] ?? 0 }}">
        @endforeach
      </tbody>
    </table>
  </div>

  <!-- Footer / Grand Total -->
  <div class="rfq-footer">
    <span class="item-count">{{ count($rows) }} item{{ count($rows) !== 1 ? 's' : '' }} in this RFQ</span>
    <div class="grand-total-wrap">
      <span class="grand-total-label">Grand Total</span>
      <span class="grand-total-value">
        <span class="currency">Rs.</span>{{ number_format($total, 2) }}
      </span>
    </div>
  </div>

</div><!-- /.sinc-card -->

    </div>
  </div>
</div>
@endsection
