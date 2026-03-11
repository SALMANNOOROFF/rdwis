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
    margin-bottom: 28px;
  }

  .sinc-page-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -.3px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .sinc-page-title .title-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: #eff6ff;
    color: #3b82f6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
  }

  .sinc-page-sub {
    font-size: .78rem;
    color: #94a3b8;
    font-weight: 500;
    margin-top: 1px;
    margin-left: 48px;
  }

  .sinc-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
    color: #64748b;
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
    background: #fff;
    border: 1.5px solid #e8edf4;
    border-radius: 18px;
    overflow: hidden;
  }

  /* ── Save bar (replaces card-header) ── */
  .save-bar {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px 24px;
    border-bottom: 1.5px solid #f1f5f9;
    background: #fff;
    flex-wrap: wrap;  
  }

  .save-bar label {
    font-size: .82rem;
    font-weight: 700;
    color: #64748b;
    white-space: nowrap;
    margin: 0;
    letter-spacing: .2px;
  }

  .save-bar .rfq-input {
    flex: 1;
    min-width: 240px;
    max-width: 380px;
    height: 40px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 0 14px;
    font-family: 'DM Sans', sans-serif;
    font-size: .88rem;
    font-weight: 600;
    color: #0f172a;
    background: #f8fafc;
    transition: border-color .18s ease, box-shadow .18s ease;
    outline: none;
  }

  .save-bar .rfq-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59,130,246,.1);
    background: #fff;
  }

  .save-bar .rfq-input::placeholder { color: #94a3b8; font-weight: 500; }

  .btn-save {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 0 20px;
    height: 40px;
    border-radius: 10px;
    background: #3b82f6;
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
    background: #f8fafc;
    border-bottom: 1.5px solid #e8edf4;
  }

  .rfq-table thead th {
    padding: 12px 18px;
    font-size: .72rem;
    font-weight: 700;
    color: #64748b;
    letter-spacing: .5px;
    text-transform: uppercase;
    white-space: nowrap;
  }

  .rfq-table thead th.text-right { text-align: right; }

  .rfq-table tbody tr {
    border-bottom: 1px solid #f1f5f9;
    transition: background .12s ease;
  }

  .rfq-table tbody tr:hover { background: #f8fafc; }

  .rfq-table tbody td {
    padding: 13px 18px;
    color: #334155;
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
    background: #f1f5f9;
    color: #64748b;
    font-size: .75rem;
    font-weight: 700;
  }

  /* Item name */
  .item-name {
    font-weight: 600;
    color: #0f172a;
  }

  /* Price */
  .price-cell {
    font-weight: 700;
    color: #0f172a;
    font-variant-numeric: tabular-nums;
    letter-spacing: -.2px;
  }

  .price-prefix {
    font-size: .74rem;
    color: #94a3b8;
    font-weight: 500;
    margin-right: 2px;
  }

  /* ── Footer / Grand Total ── */
  .rfq-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 24px;
    background: #f8fafc;
    border-top: 1.5px solid #e8edf4;
    flex-wrap: wrap;
    gap: 8px;
  }

  .rfq-footer .item-count {
    font-size: .78rem;
    font-weight: 600;
    color: #94a3b8;
  }

  .grand-total-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .grand-total-label {
    font-size: .78rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .4px;
  }

  .grand-total-value {
    font-size: 1.2rem;
    font-weight: 800;
    color: #3b82f6;
    letter-spacing: -.5px;
  }

  .grand-total-value .currency {
    font-size: .8rem;
    font-weight: 600;
    color: #94a3b8;
    margin-right: 3px;
  }

  @media (max-width: 600px) {
    .sinc-wrapper { padding: 18px 14px 40px; }
    .save-bar { flex-direction: column; align-items: stretch; }
    .save-bar .rfq-input { max-width: 100%; }
    .btn-save { width: 100%; justify-content: center; }
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
        value="Draft RFQ"
      >
      <button type="submit" class="btn-save">
        <i class="fas fa-check"></i> Save &amp; Create RFQ
      </button>
    </div>
  </form>

  <!-- Table -->
  <div class="rfq-table-wrap">
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
