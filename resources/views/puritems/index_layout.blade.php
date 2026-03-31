@extends('welcome')
@section('content')

<style>
  @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap');

  /* ══════════════════════════════════════════
     RESET & BASE
  ══════════════════════════════════════════ */
  * { box-sizing: border-box; }

  .sinc-page {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 57px); /* subtract AdminLTE navbar */
    background: var(--rd-bg);
    font-family: 'DM Sans', sans-serif;
    overflow: hidden;
  }

  /* ── Top Nav Bar ── */
  .sinc-navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 24px;
    height: 52px;
    background: var(--rd-surface);
    border-bottom: 1.5px solid var(--rd-border);
    flex-shrink: 0;
    gap: 12px;
  }

  .sinc-nav-left {
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .sinc-nav-title {
    font-size: .95rem;
    font-weight: 800;
    color: var(--rd-text1);
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .sinc-nav-title .nt-icon {
    width: 30px; height: 30px;
    border-radius: 8px;
    background: var(--rd-accent-soft);
    color: var(--rd-accent);
    display: flex; align-items: center; justify-content: center;
    font-size: .82rem;
  }

  .sinc-nav-divider {
    width: 1px; height: 20px;
    background: var(--rd-border);
  }

  .sinc-nav-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 8px;
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
    box-shadow: 0 2px 8px rgba(59,130,246,.25);
  }

  .sinc-nav-btn.primary:hover { background: #2563eb; color: #fff; text-decoration: none; }

  .sinc-nav-btn.outline {
    background: var(--rd-surface);
    color: var(--rd-text2);
    border-color: var(--rd-border);
  }

  .sinc-nav-btn.outline:hover { border-color: #3b82f6; color: #3b82f6; text-decoration: none; }

  /* ══════════════════════════════════════════
     3-PANEL LAYOUT
  ══════════════════════════════════════════ */
  .sinc-panels {
    display: grid;
    grid-template-columns: 450px 1fr 420px;
    flex: 1;
    overflow: hidden;
    gap: 0;
  }

  /* ── Responsive Grid ── */
  @media (max-width: 1400px) {
    .sinc-panels { grid-template-columns: 380px 1fr 350px; }
  }

  @media (max-width: 1100px) {
    .sinc-panels { grid-template-columns: 300px 1fr 300px; }
  }

  @media (max-width: 1024px) {
    .sinc-panels { grid-template-columns: 1fr; overflow-y: auto; height: auto; }
    .sinc-page { height: auto; overflow-y: auto; }
    .panel { min-height: 400px; border-left: none !important; border-bottom: 2px solid var(--rd-border); }
    .panel-body { max-height: 500px; }
  }

  @media (max-width: 768px) {
    .sinc-navbar { flex-direction: column; height: auto; padding: 12px; gap: 8px; align-items: flex-start; }
    .sinc-nav-divider { display: none; }
    .panel { min-height: auto; }
    .panel-body { max-height: 400px; }
    .config-fields { grid-template-columns: 1fr; }
    .grand-total-bar { position: sticky; bottom: 0; z-index: 10; border-top: 2px solid rgba(255,255,255,.2); }
  }

  /* ── Panel shared ── */
  .panel {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: var(--rd-bg);
  }

  .panel + .panel {
    border-left: 1.5px solid var(--rd-border);
  }

  .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px 12px;
    background: var(--rd-surface);
    border-bottom: 1.5px solid var(--rd-border2);
    flex-shrink: 0;
  }

  .panel-head-title {
    font-size: .82rem;
    font-weight: 800;
    color: var(--rd-text1);
    display: flex;
    align-items: center;
    gap: 7px;
  }

  .panel-head-title i { color: #3b82f6; }

  .panel-body {
    flex: 1;
    overflow-y: auto;
    padding: 0;
  }

  /* scrollbar */
  .panel-body::-webkit-scrollbar { width: 4px; }
  .panel-body::-webkit-scrollbar-track { background: transparent; }
  .panel-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }

  /* ══════════════════════════════════════════
     LEFT PANEL — INVENTORY MASTER
  ══════════════════════════════════════════ */
  .search-wrap {
    padding: 12px 14px;
    border-bottom: 1px solid var(--rd-border2);
    background: var(--rd-surface);
  }

  .search-box {
    display: flex;
    align-items: center;
    gap: 0;
    border: 1.5px solid var(--rd-border);
    border-radius: 10px;
    overflow: hidden;
    background: var(--rd-surface2);
    transition: border-color .15s ease;
  }

  .search-box:focus-within { border-color: #3b82f6; background: var(--rd-surface); }

  .search-box input {
    flex: 1;
    border: none;
    background: transparent;
    padding: 9px 12px;
    font-family: 'DM Sans', sans-serif;
    font-size: .82rem;
    color: var(--rd-text1);
    outline: none;
  }

  .search-box input::placeholder { color: #94a3b8; }

  .search-box button {
    border: none;
    background: #3b82f6;
    color: #fff;
    padding: 0 14px;
    height: 38px;
    font-size: .78rem;
    font-weight: 700;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    transition: background .15s ease;
  }

  .search-box button:hover { background: #2563eb; }

  /* Item rows */
  .inv-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 11px 16px;
    border-bottom: 1px solid var(--rd-border2);
    background: var(--rd-surface);
    cursor: pointer;
    transition: background .12s ease;
    gap: 10px;
  }

  .inv-item:hover { background: var(--rd-surface2); }
  .inv-item.active { background: var(--rd-accent-soft); border-left: 3px solid var(--rd-accent); }

  .inv-item-info { flex: 1; min-width: 0; }

  .inv-item-name {
    font-size: .82rem;
    font-weight: 700;
    color: var(--rd-text1);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 1px;
  }

  .inv-item-sub {
    font-size: .7rem;
    color: var(--rd-text2);
    font-weight: 500;
  }

  .inv-item-right {
    display: flex;
    align-items: center;
    gap: 7px;
    flex-shrink: 0;
  }

  .stock-badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: .68rem;
    font-weight: 700;
    white-space: nowrap;
  }

  .stock-badge.in  { background: var(--rd-success-soft); color: var(--rd-success); }
  .stock-badge.out { background: var(--rd-danger-soft); color: var(--rd-danger); }

  .btn-quick-add {
    border: 1.5px solid var(--rd-accent);
    background: var(--rd-surface);
    color: var(--rd-accent);
    border-radius: 7px;
    font-size: .7rem;
    font-weight: 700;
    padding: 3px 9px;
    cursor: pointer;
    font-family: 'DM Sans', sans-serif;
    transition: all .15s ease;
    white-space: nowrap;
  }

  .btn-quick-add:hover { background: #3b82f6; color: #fff; }

  /* ══════════════════════════════════════════
     CENTER PANEL — ITEM CONFIG
  ══════════════════════════════════════════ */
  .center-panel-body {
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  /* Config card */
  .config-card {
    background: var(--rd-surface);
    border: 1.5px solid var(--rd-border);
    border-radius: 16px;
    overflow: hidden;
  }

  .config-card-inner {
    padding: 22px 22px 18px;
  }

  /* Selected item display */
  .selected-item-display {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px;
    background: var(--rd-surface2);
    border-radius: 12px;
    border: 1.5px solid var(--rd-border);
    margin-bottom: 18px;
    min-height: 54px;
    gap: 12px;
  }

  .selected-item-name {
    font-size: .92rem;
    font-weight: 700;
    color: var(--rd-text1);
  }

  .selected-item-placeholder {
    font-size: .84rem;
    font-weight: 500;
    color: #94a3b8;
  }

  .selected-stock-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;
    border-radius: 8px;
    font-size: .75rem;
    font-weight: 700;
    white-space: nowrap;
    flex-shrink: 0;
  }

  /* Field grid */
  .config-fields {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 14px;
    margin-bottom: 20px;
  }

  .cfg-field label {
    display: block;
    font-size: .7rem;
    font-weight: 700;
    color: var(--rd-text3);
    letter-spacing: .4px;
    text-transform: uppercase;
    margin-bottom: 6px;
  }

  .cfg-input {
    width: 100%;
    height: 42px;
    border: 1.5px solid var(--rd-border);
    border-radius: 10px;
    padding: 0 12px;
    font-family: 'DM Sans', sans-serif;
    font-size: .88rem;
    font-weight: 600;
    color: var(--rd-text1);
    background: var(--rd-surface2);
    outline: none;
    transition: border-color .15s ease, box-shadow .15s ease;
  }

  .cfg-input:focus {
    border-color: var(--rd-accent);
    box-shadow: 0 0 0 3px var(--rd-accent-soft);
    background: var(--rd-surface);
  }

  .cfg-input::placeholder { color: #94a3b8; font-weight: 400; }

  .cfg-input[readonly] {
    background: var(--rd-surface3);
    color: var(--rd-text3);
    cursor: not-allowed;
  }

  /* Confirm button */
  .btn-confirm {
    width: 100%;
    height: 48px;
    border-radius: 12px;
    background: #3b82f6;
    color: #fff;
    border: none;
    font-family: 'DM Sans', sans-serif;
    font-size: .92rem;
    font-weight: 800;
    letter-spacing: .2px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 4px 16px rgba(59,130,246,.3);
    transition: transform .18s cubic-bezier(.34,1.56,.64,1), box-shadow .18s ease;
  }

  .btn-confirm:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(59,130,246,.38);
  }

  .btn-confirm i { font-size: 1rem; }

  /* ══════════════════════════════════════════
     RIGHT PANEL — PURCHASE QUEUE
  ══════════════════════════════════════════ */
  .queue-list {
    flex: 1;
    overflow-y: auto;
    padding: 0;
  }

  .queue-item {
    padding: 13px 18px;
    border-bottom: 1px solid var(--rd-border2);
    background: var(--rd-surface);
    display: flex;
    flex-direction: column;
    gap: 4px;
    position: relative;
  }

  .queue-item:hover { background: #fafbfc; }

  .qi-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
  }

  .qi-name {
    font-size: .82rem;
    font-weight: 700;
    color: var(--rd-text1);
    flex: 1;
    min-width: 0;
    line-height: 1.3;
  }

  .btn-qi-remove {
    width: 20px; height: 20px;
    border-radius: 50%;
    border: none;
    background: #fee2e2;
    color: #dc2626;
    font-size: .7rem;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    transition: background .12s ease;
  }

  .btn-qi-remove:hover { background: #dc2626; color: #fff; }

  .qi-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
  }

  .qi-qty-label {
    font-size: .7rem;
    color: #94a3b8;
    font-weight: 500;
  }

  .qi-price-input {
    width: 100px;
    height: 30px;
    border: 1.5px solid var(--rd-border);
    border-radius: 7px;
    padding: 0 8px;
    font-family: 'DM Sans', sans-serif;
    font-size: .78rem;
    font-weight: 700;
    color: var(--rd-text1);
    text-align: right;
    background: var(--rd-surface2);
    outline: none;
    transition: border-color .12s ease;
  }

  .qi-price-input:focus { border-color: #3b82f6; background: var(--rd-surface); }

  .qi-line-total {
    font-size: .82rem;
    font-weight: 800;
    color: var(--rd-accent);
    white-space: nowrap;
    min-width: 70px;
    text-align: right;
  }

  /* Empty queue state */
  .queue-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    min-height: 180px;
    color: #cbd5e1;
    gap: 8px;
    padding: 32px;
    text-align: center;
  }

  .queue-empty i { font-size: 2rem; }
  .queue-empty p { font-size: .8rem; font-weight: 600; margin: 0; }

  /* Grand Total Bar (blue bar at bottom like SINC) */
  .grand-total-bar {
    background: #3b82f6;
    padding: 16px 20px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .gt-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .gt-label {
    font-size: .72rem;
    font-weight: 700;
    color: rgba(255,255,255,.7);
    letter-spacing: .4px;
    text-transform: uppercase;
  }

  .gt-value {
    font-size: 1.5rem;
    font-weight: 800;
    color: #fff;
    letter-spacing: -.5px;
    line-height: 1;
  }

  .gt-value .gt-currency {
    font-size: .85rem;
    font-weight: 600;
    opacity: .8;
    margin-right: 2px;
  }

  .btn-confirm-group {
    width: 100%;
    height: 40px;
    border-radius: 10px;
    background: rgba(255,255,255,.18);
    border: 1.5px solid rgba(255,255,255,.35);
    color: #fff;
    font-family: 'DM Sans', sans-serif;
    font-size: .85rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    transition: background .15s ease;
    backdrop-filter: blur(4px);
  }

  .btn-confirm-group:hover { background: rgba(255,255,255,.28); }

  /* ══════════════════════════════════════════
     MODAL
  ══════════════════════════════════════════ */
  .modal-content {
    background: var(--rd-surface) !important;
    border: 1.5px solid var(--rd-border) !important;
    border-radius: 18px !important;
    box-shadow: 0 16px 48px rgba(0,0,0,0.4) !important;
    font-family: 'DM Sans', sans-serif;
    overflow: hidden;
  }

  .modal-header {
    border-bottom: 1.5px solid var(--rd-border2) !important;
    padding: 18px 22px 14px !important;
  }

  .modal-title { font-weight: 800 !important; font-size: 1rem !important; color: var(--rd-text1) !important; }

  .modal-header .close { color: #94a3b8; opacity: 1; }

  .modal-body { padding: 18px 22px !important; }

  .modal-body .form-control {
    background: var(--rd-surface2);
    border: 1.5px solid var(--rd-border);
    color: var(--rd-text1);
    border-radius: 9px;
    padding: 8px 12px;
    height: auto;
  }

  .modal-body .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.1); }

  .modal-footer { border-top: 1.5px solid var(--rd-border2) !important; padding: 14px 22px !important; }


</style>

<div class="content-wrapper p-0">
  <div class="container-fluid p-0">
    <div class="sinc-page">

  <!-- ── TOP NAV BAR ── -->
  <div class="sinc-navbar">
    <div class="sinc-nav-left">
      <div class="sinc-nav-title">
        <span class="nt-icon"><i class="fas fa-boxes"></i></span>
        Purchase Case
      </div>
      <div class="sinc-nav-divider"></div>
      <a href="{{ route('purnew.create') }}" class="sinc-nav-btn primary">
        <i class="fas fa-plus-circle"></i> Create Case
      </a>
      <a href="{{ route('purnew.groups') }}" class="sinc-nav-btn outline">
        <i class="fas fa-layer-group"></i> Grouping
      </a>
    </div>
    <a href="{{ route('viewpurchasecase') }}" class="sinc-nav-btn outline">
      <i class="fas fa-briefcase"></i> Purchase Cases
    </a>
  </div>

  <!-- ── 3-PANEL LAYOUT ── -->
  <div class="sinc-panels">

    <!-- ══ LEFT: INVENTORY MASTER ══ -->
    <div class="panel" style="background: var(--rd-bg)">
      <div class="panel-head">
        <div class="panel-head-title">
          <i class="fas fa-database"></i> Inventory Master
        </div>
        <button class="sinc-nav-btn primary" style="padding:5px 11px;font-size:.72rem" data-toggle="modal" data-target="#modal-add-item">
          <i class="fas fa-plus"></i> Custom
        </button>
      </div>

      <!-- Search -->
      <div class="search-wrap">
        <form method="get" action="{{ route('purnew.create') }}">
          <div class="search-box">
            <input type="text" name="term" value="{{ $filters['term'] ?? '' }}" placeholder="Search items...">
            <button type="submit"><i class="fas fa-search"></i></button>
          </div>
        </form>
      </div>

      <!-- Item list -->
      <div class="panel-body">
        @foreach($items as $it)
          @php
            $lp  = $it->last_price ?? 0;
            $stk = (float)($it->stock_qty ?? 0);
          @endphp
          <div class="inv-item item-row"
               data-id="{{ $it->item_id }}"
               data-title="{{ $it->title }}"
               data-price="{{ $lp }}"
               data-stock="{{ $stk }}">
            <div class="inv-item-info">
              <div class="inv-item-name">{{ $it->title }}</div>
              <div class="inv-item-sub">{{ $it->category ?? 'Inventory' }}</div>
            </div>
            <div class="inv-item-right">
              <span class="stock-badge {{ $stk > 0 ? 'in' : 'out' }}">
                {{ $stk > 0 ? 'Stock: '.number_format($stk,0) : 'Out of Stock' }}
              </span>
              <button class="btn-quick-add quick-add"
                      data-id="{{ $it->item_id }}"
                      data-title="{{ $it->title }}"
                      data-price="{{ $lp }}"
                      data-stock="{{ $stk }}">
                Add
              </button>
            </div>
          </div>
        @endforeach
      </div>
    </div>

    <!-- ══ CENTER: ITEM CONFIG ══ -->
    <div class="panel">
      <div class="panel-head" style="background: var(--rd-surface)">
        <div class="panel-head-title">
          <i class="fas fa-sliders-h"></i> Item Configuration
        </div>
        <button class="sinc-nav-btn outline" style="padding:5px 11px;font-size:.72rem">
          <i class="fas fa-file-import"></i> Import BOM
        </button>
      </div>

      <div class="panel-body" style="background: var(--rd-bg)">
        <div class="center-panel-body">

          <div class="config-card">
            <div class="config-card-inner">

              <!-- Selected item display -->
              <div class="selected-item-display">
                <div id="cfg-selected-name" class="selected-item-placeholder">
                  <i class="fas fa-hand-pointer mr-1" style="color:var(--rd-text3)"></i>
                  Pick an item from the left panel
                </div>
                <span class="selected-stock-badge out" id="cfg-stock-badge" style="display:none"></span>
              </div>

              <!-- Fields -->
              <div class="config-fields">
                <div class="cfg-field">
                  <label>Unit</label>
                  <input type="text" class="cfg-input" id="cfg-unit" placeholder="num">
                </div>
                <div class="cfg-field">
                  <label>Price (PKR)</label>
                  <input type="number" step="0.01" class="cfg-input" id="cfg-price" placeholder="0.00">
                </div>
                <div class="cfg-field">
                  <label>Request Qty</label>
                  <input type="number" step="0.01" class="cfg-input" id="cfg-qty" placeholder="1">
                </div>
              </div>

              <!-- Confirm button -->
              <button class="btn-confirm" id="btn-add-config">
                <i class="fas fa-check-circle"></i>
                CONFIRM &amp; ADD TO LIST
                <i class="fas fa-chevron-right" style="font-size:.75rem;opacity:.7"></i>
              </button>

            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- ══ RIGHT: PURCHASE QUEUE ══ -->
    <div class="panel" style="background: var(--rd-bg)">
      <div class="panel-head">
        <div class="panel-head-title">
          <i class="fas fa-shopping-cart"></i> Purchase Queue
        </div>
        <span id="queue-count" style="font-size:.7rem;font-weight:700;color:#94a3b8">0 items</span>
      </div>

      <!-- Queue list -->
      <div class="queue-list panel-body" id="queue-list">
        <div class="queue-empty" id="queue-empty">
          <i class="fas fa-cart-plus"></i>
          <p>No items added yet.<br>Select an item and confirm.</p>
        </div>
      </div>

      <!-- Grand Total Bar -->
      <div class="grand-total-bar">
        <div class="gt-row">
          <span class="gt-label">Estimated Grand Total:</span>
        </div>
        <div class="gt-row">
          <span class="gt-value">
            <span class="gt-currency">Rs.</span><span id="grand-total">0.00</span>
          </span>
          <button class="btn-confirm-group" id="btn-preview" style="width:auto;padding:0 16px">
            <i class="fas fa-check"></i> Confirm Group
          </button>
        </div>
      </div>
    </div>

  </div><!-- /.sinc-panels -->
    </div><!-- /.sinc-page -->
  </div><!-- /.container-fluid -->
</div><!-- /.content-wrapper -->


<!-- ══ ADD ITEM MODAL ══ -->
<div class="modal fade" id="modal-add-item" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:400px">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-plus-circle mr-2" style="color:#3b82f6"></i>Add Custom Item</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form method="post" action="{{ route('purnew.item.create') }}" id="form-add-item">
        @csrf
        <div class="modal-body" style="display:flex;flex-direction:column;gap:10px">
          <input class="form-control" name="title" placeholder="Item Title" required>
          <div class="form-row">
            <div class="col">
              <input class="form-control" name="type" type="number" placeholder="Type">
            </div>
            <div class="col">
              <input class="form-control" name="serial" type="number" placeholder="Serial">
            </div>
          </div>
          <input class="form-control" name="subtype" placeholder="Subtype">
        </div>
        <div class="modal-footer" style="justify-content:flex-end;gap:8px">
          <button type="button" class="sinc-nav-btn outline" data-dismiss="modal">Cancel</button>
          <button type="submit" class="sinc-nav-btn primary"><i class="fas fa-save"></i> Save Item</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ══ CONFIRM GROUP MODAL ══ -->
<div class="modal fade" id="modal-confirm-group-name" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:450px">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-layer-group mr-2" style="color:#3b82f6"></i>Confirm Group Name</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <label class="small text-muted mb-2">Please provide a name for this RFQ / Grouping</label>
        <input type="text" id="modal-rfq-title" class="form-control" placeholder="e.g. IT Equipment for R&D..." required>
        <div id="modal-rfq-error" class="text-danger small mt-2" style="display:none">Please enter a valid name.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="sinc-nav-btn outline" data-dismiss="modal">Back to Queue</button>
        <button type="button" id="btn-final-submit" class="sinc-nav-btn primary">
          <i class="fas fa-check-circle mr-1"></i> Proceed to Preview
        </button>
      </div>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
(function () {
  const group = [];
  let selected = null;

  /* ── helpers ── */
  function fmt(n) {
    return parseFloat(n || 0).toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function renderQueue() {
    const list  = document.getElementById('queue-list');
    const empty = document.getElementById('queue-empty');
    const countEl = document.getElementById('queue-count');
    let total = 0;

    // clear dynamic items but keep empty state
    list.querySelectorAll('.queue-item').forEach(el => el.remove());

    if (group.length === 0) {
      empty.style.display = 'flex';
      countEl.textContent = '0 items';
    } else {
      empty.style.display = 'none';
      countEl.textContent = group.length + ' item' + (group.length !== 1 ? 's' : '');
      group.forEach(function (g, idx) {
        total += g.price || 0;
        const div = document.createElement('div');
        div.className = 'queue-item';
        div.innerHTML = `
          <div class="qi-top">
            <div class="qi-name">${g.title}</div>
            <button class="btn-qi-remove rm" data-idx="${idx}"><i class="fas fa-times" style="font-size:.6rem"></i></button>
          </div>
          <div class="qi-bottom">
            <span class="qi-qty-label">Qty: ${g.qty || 1}</span>
            <input type="number" step="0.01" class="qi-price-input price" data-idx="${idx}" value="${(g.price || 0).toFixed(2)}">
            <span class="qi-line-total">Rs. ${fmt(g.price)}</span>
          </div>
        `;
        list.insertBefore(div, empty);
      });
    }

    document.getElementById('grand-total').textContent = fmt(total);
  }

  /* ── Select item (click row) ── */
  function selectItem(id, title, price, stock) {
    selected = { item_id: id, title: title, unit: 'num', unitPrice: price, stock: stock };

    // update UI
    document.querySelectorAll('.inv-item').forEach(el => el.classList.remove('active'));
    const activeEl = document.querySelector('.item-row[data-id="' + id + '"]');
    if (activeEl) activeEl.classList.add('active');

    const nameEl  = document.getElementById('cfg-selected-name');
    const badgeEl = document.getElementById('cfg-stock-badge');

    nameEl.classList.remove('selected-item-placeholder');
    nameEl.innerHTML = '<span style="font-size:.82rem;font-weight:800;color:var(--rd-text1)">' + title + '</span>';

    badgeEl.style.display = 'inline-flex';
    if (parseFloat(stock) > 0) {
      badgeEl.className = 'selected-stock-badge in';
      badgeEl.textContent = 'Lab: ' + parseFloat(stock);
    } else {
      badgeEl.className = 'selected-stock-badge out';
      badgeEl.textContent = 'Out of Stock';
    }

    document.getElementById('cfg-unit').value  = 'num';
    document.getElementById('cfg-price').value = parseFloat(price).toFixed(2);
    document.getElementById('cfg-qty').value   = 1;
  }

  /* ── Item row click ── */
  $(document).on('click', '.item-row', function () {
    selectItem($(this).data('id'), $(this).data('title'), $(this).data('price'), $(this).data('stock'));
  });

  /* ── Quick Add ── */
  $(document).on('click', '.quick-add', function (e) {
    e.stopPropagation();
    selectItem($(this).data('id'), $(this).data('title'), $(this).data('price'), $(this).data('stock'));
  });

  /* ── Confirm & Add ── */
  document.getElementById('btn-add-config').addEventListener('click', function () {
    if (!selected) {
      alert('Please select an item first.');
      return;
    }
    const qty       = parseFloat(document.getElementById('cfg-qty').value) || 1;
    const unitPrice = parseFloat(document.getElementById('cfg-price').value) || 0;
    const lineTotal = qty * unitPrice;
    group.push({ item_id: selected.item_id, title: selected.title, qty: qty, price: lineTotal });
    renderQueue();
  });

  /* ── Inline price edit ── */
  $(document).on('input', '.price', function () {
    const idx = $(this).data('idx');
    group[idx].price = parseFloat($(this).val()) || 0;
    // update line total display
    $(this).siblings('.qi-line-total').text('Rs. ' + fmt(group[idx].price));
    // recalculate grand total only
    let total = 0;
    group.forEach(g => total += g.price || 0);
    document.getElementById('grand-total').textContent = fmt(total);
  });

  /* ── Remove item ── */
  $(document).on('click', '.rm', function (e) {
    e.preventDefault();
    group.splice($(this).data('idx'), 1);
    renderQueue();
  });

  /* ── Confirm Group / Show Name Modal ── */
  document.getElementById('btn-preview').addEventListener('click', function () {
    if (group.length === 0) { alert('Add at least one item.'); return; }
    $('#modal-confirm-group-name').modal('show');
  });

  /* ── Final Submit from Modal ── */
  document.getElementById('btn-final-submit').addEventListener('click', function () {
    const title = document.getElementById('modal-rfq-title').value.trim();
    if (!title) {
        document.getElementById('modal-rfq-error').style.display = 'block';
        return;
    }
    document.getElementById('modal-rfq-error').style.display = 'none';

    // Build form and submit
    const f = $('<form method="post" action="{{ route('purnew.rfq.preview') }}"></form>');
    f.append('<input type="hidden" name="_token" value="{{ csrf_token() }}">');
    f.append('<input type="hidden" name="title" value="' + title + '">');
    group.forEach(function (it, i) {
      f.append('<input type="hidden" name="items[' + i + '][item_id]" value="' + it.item_id + '">');
      f.append('<input type="hidden" name="items[' + i + '][price]" value="' + (it.price || 0) + '">');
    });
    $('body').append(f);
    f.submit();
    f.remove();
  });

  /* ── initial render ── */
  renderQueue();
})();
</script>
@endsection
