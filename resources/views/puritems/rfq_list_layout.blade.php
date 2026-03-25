
@extends('welcome')
@section('content')

<div class="content-wrapper p-0">
  <div class="container-fluid p-0">
    <div class="sinc-wrapper">
<style>
  @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

  :root { 
      --admin-dark: #0f172a; 
      --accent: #5b21b6; 
      --glass: rgba(255, 255, 255, 0.9);
      --border-color: #e2e8f0;
      --text-primary: #1e293b;
      --text-secondary: #64748b;
  }

  .sinc-wrapper {
    min-height: 100vh;
    background-color: #ffffff;
    font-family: 'Plus Jakarta Sans', sans-serif;
    padding: 24px 24px 50px;
    color: var(--text-primary);
  }

  /* ── Top bar ── */
  .sinc-topbar {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    margin-bottom: 2rem;
    padding: 12px 0;
    border-bottom: 1px solid var(--border-color);
  }

  .sinc-page-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
  }

  .sinc-page-sub {
    font-size: .95rem;
    color: var(--text-secondary);
  }

  .nav-actions {
    display: flex;
    gap: 12px;
  }

  .btn-action-primary {
    background-color: var(--accent);
    color: white;
    border-radius: 6px;
    font-weight: 600;
    padding: 8px 16px;
    border: none;
    text-decoration: none;
    transition: background-color 0.2s;
  }
  .btn-action-primary:hover { background-color: #4c1d95; color: white; }

  .btn-action-secondary {
    background-color: #fff;
    color: var(--accent);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    font-weight: 600;
    padding: 8px 16px;
    text-decoration: none;
    transition: background-color 0.2s;
  }
  .btn-action-secondary:hover { background-color: #f8fafc; color: var(--accent); }

  /* ── Table Redesign ── */
  .table-card { 
      background: white; 
      border: 1px solid var(--border-color);
      border-radius: 12px;
      box-shadow: 0 10px 25px -10px rgba(0,0,0,0.05);
      overflow: hidden;
  }

  .search-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 16px 20px;
      border-bottom: 1px solid var(--border-color);
  }

  .search-box {
      position: relative;
      width: 320px;
  }
  .search-box input {
      padding: 10px 16px 10px 36px;
      border-radius: 6px;
      border: 1px solid var(--border-color);
      font-size: 0.9rem;
      width: 100%;
      outline: none;
      transition: border-color 0.2s;
  }
  .search-box input:focus { border-color: var(--accent); }
  .search-box i {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: #94a3b8;
  }
  .search-box button {
      display: none;
  }

  .rfq-table { width: 100%; border-collapse: collapse; }
  .rfq-table thead th {
    background: #fff;
    color: #1e293b;
    font-weight: 700;
    font-size: 0.85rem;
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
    white-space: nowrap;
    position: sticky; top: 0; z-index: 10;
  }
  .rfq-table tbody td {
    padding: 16px 20px;
    font-size: 0.9rem;
    color: var(--text-secondary);
    border-bottom: 1px solid #f8fafc;
    vertical-align: middle;
  }
  .rfq-table tbody tr.main-row:hover td { 
    background-color: #f8fafc;
  }

  .rfq-id-badge {
    padding: 6px 12px;
    border-radius: 8px;
    font-weight: 700; 
    font-size: 0.75rem;
    background: #f1f5f9; 
    color: #475569; 
    border: 1px solid #e2e8f0;
  }

  .price-val { 
    font-weight: 700; 
    color: #15803d; /* Green */
  }

  /* ── Dropdown Styling ── */
  .dropdown-menu {
      border: 1px solid var(--border-color);
      box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
      border-radius: 12px;
      padding: 8px;
  }
  .dropdown-item {
      border-radius: 8px;
      padding: 8px 12px;
      font-size: 0.85rem;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 10px;
      cursor: pointer;
  }
  .dropdown-item:hover { background-color: #f1f5f9; color: var(--accent); }
  .dropdown-item.text-danger:hover { background-color: #fef2f2; color: #dc2626 !important; }
  
  .details-row { background: #f8fafc; }
  .details-box {
    padding: 24px;
    margin: 16px 24px;
    background: #fff;
    border: 1px solid var(--border-color);
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
  }

  /* ── Quotation Modal Details ── */
  .modal-fullscreen { width: 100vw !important; max-width: 100% !important; height: 100vh !important; margin: 0 !important; padding: 0 !important; }
  #quotationModal .modal-content {
      background: linear-gradient(to right, #f8fafc 250px, #ffffff 250px);
      position: relative; height: 100vh; border: 0; border-radius: 0;
  }
  #quotationModal .modal-content::after {
      content: ""; position: absolute; top: 0; bottom: 0; left: 250px;
      width: 1px; background-color: #cbd5e1; z-index: 20; pointer-events: none;
  }
  #quotationModal .modal-header, #quotationModal .modal-footer {
      z-index: 21; background: #fff !important; border-color: #e2e8f0;
  }
  .quote-table-container {
      height: calc(100vh - 150px);
      display: flex; flex-direction: column; background: #f8f9fa; overflow: auto;
  }
  #quotationTable {
      background: transparent !important;
      border-collapse: separate; border-spacing: 0; width: max-content; table-layout: fixed;
  }
  #quotationTable tr { height: 1px; }

  #quotationTable th.sticky-left, #quotationTable td.sticky-left {
      position: sticky; left: 0; z-index: 5;
      background-color: #f8fafc; border-right: none !important;
      height: inherit; width: 250px; min-width: 250px; padding: 0 !important;
  }
  .item-desc-content {
      padding: 8px 16px; display: flex; flex-direction: column; justify-content: center;
      height: 100%; background: transparent !important; font-size: 0.85rem;
  }

  #quotationTable td { height: inherit; padding: 4px 0 !important; border-bottom: 1px solid #f1f5f9; }
  #quotationTable thead th {
      background-color: transparent !important; border-bottom: 1px solid #cbd5e1 !important;
      padding: 8px !important; height: 60px; font-weight: 700; color: #1e293b;
  }
  #quotationTable thead th.sticky-left { z-index: 11; background-color: #f8fafc !important; padding-left: 0 !important; }
  
  #quotationTable tfoot { position: sticky; bottom: 0; z-index: 12; }
  #quotationTable tfoot th, #quotationTable tfoot td {
      background-color: #f8fafc !important; border-top: 2px solid #cbd5e1 !important;
      border-bottom: none !important; position: sticky; bottom: 0; font-weight: 700;
  }
  #quotationTable tfoot th.sticky-left { z-index: 13; }

  .vendor-col-header {
      background: transparent !important; padding: 8px 16px !important; min-width: 240px; border-bottom: none !important;
  }
  .vendor-select {
      border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 12px; font-weight: 600; font-size: 0.85rem; background: #fff; box-shadow: 0 1px 2px rgba(0,0,0,0.05);
  }
  .vendor-select:focus { border-color: var(--accent); box-shadow: 0 0 0 0.2rem rgba(91, 33, 182, 0.15); outline: none; }

  .price-input {
      border: 1px solid #cbd5e1 !important; border-radius: 4px !important; box-shadow: none !important;
      width: 95% !important; margin: auto; display: block;
      font-size: 0.85rem !important; padding: 4px 8px !important; font-weight: 500; text-align: left;
  }
  .price-input:focus {
      background-color: #fff !important; border-color: var(--accent) !important;
      box-shadow: 0 0 0 0.2rem rgba(91, 33, 182, 0.15) !important; outline: none;
  }
  .price-input.is-valid { background-color: #dcfce7 !important; border-color: #166534 !important; color: #166534; font-weight: 700; transition: background-color 0.5s ease; box-shadow: none !important; }

  /* ── Toast Styling ── */
  .toast-container-custom { position: fixed; bottom: 20px; right: 20px; z-index: 99999; }
  .custom-toast { 
      background: var(--admin-dark); color: white; padding: 12px 24px; border-radius: 12px; 
      box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2); display: flex; align-items: center; gap: 10px;
      margin-top: 10px; transform: translateX(400px); animation: slideInRight 0.3s ease-out forwards; font-size: 0.9rem;
  }
  @keyframes slideInRight {
      from { transform: translateX(100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
  }

  /* ── Confirm & Tracking Modals ── */
  .modal-content { border-radius: 12px; border: none; box-shadow: 0 25px 50px rgba(0,0,0,0.15); }
  .modal-header { border-bottom: 1px solid var(--border-color); background: #fff; }
  .modal-header .modal-title { font-weight: 700; font-size: 1.1rem; color: var(--admin-dark); }
  
  /* Compact Timeline View Styles for Tracking */
  .tracking-item {
      position: relative;
      margin-bottom: 15px;
      padding-left: 50px;
      transition: all 0.2s ease-in-out;
  }
  .tracking-item::before {
      content: '';
      position: absolute;
      top: 0;
      bottom: -15px;
      left: 24px;
      width: 2px;
      background: #e2e8f0;
      z-index: 0;
  }
  .tracking-item:last-child::before { display: none; }
  .tracking-marker {
      position: absolute;
      left: 17px;
      width: 16px;
      height: 16px;
      border-radius: 50%;
      margin-top: 4px;
      background: #fff;
      border: 3px solid #cbd5e1;
      z-index: 1;
  }
  .tracking-item:first-child .tracking-marker {
      border-color: var(--accent);
      box-shadow: 0 0 0 4px rgba(91, 33, 182, 0.15);
  }
  .tracking-content {
      background: #ffffff;
      padding: 10px 15px;
      border-radius: 12px;
      border: 1px solid #e2e8f0;
      box-shadow: 0 1px 3px rgba(0,0,0,0.02);
  }
  .tracking-item:first-child .tracking-content {
      border-left: 4px solid var(--accent);
      background: #f8fafc;
  }
</style>

<div class="toast-container-custom" id="toastContainer"></div>

<div class="sinc-topbar">
  <div>
    <h3 class="sinc-page-title">Procurement Groups</h3>
    <p class="sinc-page-sub mb-0">Manage RFQ groups and consolidate vendor quotations.</p>
  </div>

  <div class="nav-actions">
    <a href="{{ route('viewpurchasecase') }}" class="btn-action-secondary">
      <i class="fas fa-briefcase me-1"></i> View Cases
    </a>
    <a href="{{ route('purnew.create') }}" class="btn-action-primary">
      <i class="fas fa-plus me-1"></i> Create New RFQ
    </a>
  </div>
</div>

<form method="get" action="{{ route('purnew.groups') }}">
  <div class="search-bar-wrapper">
    <div class="search-box">
      <i class="fas fa-search"></i>
      <input type="text" name="term" value="{{ request('term') }}" placeholder="Search RFQs or vendor...">
    </div>
    <button type="submit" class="d-none">Search</button>
  </div>
</form>

<div class="table-card">
  <div class="table-responsive">
    <table class="rfq-table" id="demandTable">
      <thead>
        <tr>
          <th style="width:120px" class="ps-4">RFQ ID</th>
          <th>Project Title</th>
          <th style="width:160px">Date</th>
          <th style="width:180px" class="text-end">Total Estimate</th>
          <th style="width:80px" class="text-center pe-4">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rfqs as $r)
        <tr class="main-row">
          <td class="ps-4"><span class="rfq-id-badge">#{{ $r->rfq_id }}</span></td>
          <td>
            <div class="fw-bold text-dark d-flex flex-column justify-content-center">
              <div style="font-size:1rem;">{{ $r->pcs_title }}</div>
              <div class="small text-muted mt-1">{{ count($r->items ?? []) }} Items</div>
            </div>
          </td>
          <td>
            <div class="text-dark small fw-bold">
              {{ \Carbon\Carbon::parse($r->pcs_date)->format('d M Y') }}
            </div>
            <div class="text-muted" style="font-size: 0.75rem;">
              {{ \Carbon\Carbon::parse($r->pcs_date)->diffForHumans() }}
            </div>
          </td>
          <td class="text-end">
            <div class="price-val" id="total_{{ $r->rfq_id }}">Rs. {{ number_format($r->total, 0) }}</div>
            <div class="small text-muted mt-1">Avg: Rs. {{ number_format($r->total / max(1, count($r->items ?? [])), 0) }}</div>
          </td>
          <td class="text-end pe-4">
            <div class="d-inline-flex align-items-center gap-2">
                <button class="btn btn-light btn-sm rounded-3 border-0" type="button" onclick="toggleDetails({{ $r->rfq_id }})" title="Toggle Details">
                    <i class="fas fa-chevron-down text-muted" id="icon_{{ $r->rfq_id }}" style="transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);"></i>
                </button>
                <button class="btn btn-light btn-sm rounded-3 border-0" type="button" onclick="showTracking({{ $r->rfq_id }}, '{{ addslashes($r->pcs_title) }}', '{{ \Carbon\Carbon::parse($r->pcs_date)->format('d M Y') }}')" title="View History">
                    <i class="fas fa-history text-muted"></i>
                </button>
                <button class="btn btn-light btn-sm rounded-3 border-0" type="button" onclick="openQuotationModal({{ $r->rfq_id }}, '{{ addslashes($r->pcs_title) }}')" title="Edit Quotes">
                    <i class="fas fa-file-invoice-dollar text-primary"></i>
                </button>
                <button class="btn btn-light btn-sm rounded-3 border-0" type="button" onclick="openEditGroupModal({{ $r->rfq_id }}, '{{ addslashes($r->pcs_title) }}')" title="Edit Group Details">
                    <i class="fas fa-edit text-info"></i>
                </button>
                <button class="btn btn-light btn-sm rounded-3 border-0" type="button" onclick="deleteGroup({{ $r->rfq_id }}, '{{ addslashes($r->pcs_title) }}')" title="Delete RFQ">
                    <i class="fas fa-trash-alt text-danger"></i>
                </button>
            </div>
          </td>
        </tr>
        <tr id="details_row_{{ $r->rfq_id }}" class="details-row d-none">
          <td colspan="5" class="p-0 border-0">
             <div class="details-box" id="details_content_{{ $r->rfq_id }}">
                <div class="text-center text-muted py-5">
                  <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                  <div>Loading items...</div>
                </div>
             </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="5" class="text-center py-5 text-muted"><i class="fas fa-inbox fa-3x mb-3 text-light"></i><div class="h5 mb-1">No RFQ Groups Found</div><div class="small">Create your first group to get started!</div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<!-- Quotation Modal -->
<div class="modal fade" id="quotationModal" tabindex="-1">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header px-4 py-3 align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <h5 class="modal-title mb-1">Quote Management</h5>
                        <div class="text-muted" style="font-size:0.85rem">
                          Group: <span id="quote_group_name" class="fw-bold text-dark"></span> 
                          | <span class="rfq-id-badge" id="quote_group_id"></span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-4 border-start ps-4 ms-4">
                    <div class="d-flex align-items-center gap-2">
                        <label class="small text-muted mb-0 fw-bold">Tax Type:</label>
                        <select id="quote_tax_type" class="form-select form-select-sm shadow-none" style="width: 90px; border-color: #e2e8f0;" onchange="calculateTotals()">
                            <option value="gst">GST</option>
                            <option value="sst">SST</option>
                        </select>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label class="small text-muted mb-0 fw-bold">Tax %:</label>
                        <input type="number" id="quote_tax_perc" class="form-control form-control-sm shadow-none text-center" style="width: 60px; border-color: #e2e8f0;" value="18" oninput="calculateTotals()">
                    </div>
                </div>
                <div class="ms-auto d-flex align-items-center gap-3">
                    <button type="button" class="btn btn-primary rounded-3 px-3 fw-bold shadow-sm d-flex align-items-center" onclick="addNewVendorColumn()">
                        <i class="fas fa-user-plus me-2"></i> Add Vendor
                    </button>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close" onclick="closeQuotationModal()" style="font-size: 1.1rem; opacity: 0.6;"></button>
                </div>
            </div>
            
            <div class="modal-body p-0">
                <div class="quote-table-container">
                    <table class="table align-middle mb-0" id="quotationTable">
                        <thead>
                            <tr id="quote_header_row">
                                <th class="sticky-left">
                                  <div class="item-desc-content fw-bold text-dark">
                                    Item Description & Estimate
                                  </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="quote_body">
                            <tr><td colspan="10" class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm text-primary mb-2"></div><br>Loading quotation items...</td></tr>
                        </tbody>
                        <tfoot id="quote_footer">
                            <tr id="quote_total_row">
                                <th class="sticky-left">
                                  <div class="item-desc-content text-primary">TOTAL (INC. TAX)</div>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="modal-footer px-4 py-3 bg-white d-flex justify-content-between">
                <div class="text-muted small">
                    <i class="fas fa-check-circle text-success me-1"></i> Auto-save enabled
                </div>
                <div>
                    <button type="button" class="btn btn-light border fw-bold me-2 px-4" data-bs-dismiss="modal" onclick="closeQuotationModal()">Close</button>
                    <button type="button" class="btn-action-primary px-4" onclick="exportQuotes()"><i class="fas fa-download me-1"></i> Export Quotes</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Simple Confirm Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmTitle">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <i class="fas fa-exclamation-circle fa-3x text-danger mb-3 opacity-75"></i>
                <p class="mb-0 text-muted" id="confirmText" style="white-space: pre-line;"></p>
            </div>
            <div class="modal-footer bg-light border-0 d-flex justify-content-center">
                <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger px-4" id="confirmBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Minimal Tracking Modal -->
<div class="modal fade" id="trackingModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center gap-2">
                    <i class="fas fa-history text-muted"></i> Activity Log
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="mb-3">
                    <small class="text-muted fw-bold text-uppercase" id="tracking_group_name"></small>
                </div>
                <div style="position: relative; padding-left: 10px;">
                    <div class="tracking-item bg-white p-3 rounded shadow-sm position-relative">
                        <div class="tracking-item-icon border-white">
                            <i class="fas fa-file-alt" style="font-size: 0.8rem;"></i>
                        </div>
                        <h6 class="mb-1 fw-bold text-dark fs-6" style="margin-left:8px;">RFQ Generated</h6>
                        <div class="text-muted small d-flex gap-3" style="margin-left:8px;">
                            <span><i class="far fa-calendar-alt me-1"></i><span id="tracking_date"></span></span>
                            <span><i class="far fa-clock me-1"></i>Just now</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Group Modal -->
<div class="modal fade" id="editGroupModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-3" style="background-color: #0d6efd;">
                <h5 class="modal-title text-white fw-bold fs-6">Edit Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.8;"></button>
            </div>
            <div class="bg-white px-4 pt-4 pb-2">
                <div class="d-flex mb-4">
                    <button class="btn btn-primary w-50 fw-bold rounded-start-pill" style="border-start-end-radius:0; border-end-end-radius:0;">Edit Group</button>
                    <button class="btn btn-outline-primary w-50 fw-bold rounded-end-pill" style="border-start-start-radius:0; border-end-start-radius:0;" disabled>Edit Case</button>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted fw-bold small mb-1" style="font-size: 0.75rem;">GROUP NAME</label>
                    <input type="text" class="form-control form-control-sm shadow-none" id="edit_group_name" readonly style="background-color: #fafafa; border-color: #f1f5f9; font-weight: 600;">
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted fw-bold small mb-1" style="font-size: 0.75rem;">VENDOR SELECTION (FROM QUOTATIONS)</label>
                    <select class="form-select form-select-sm shadow-none" id="edit_group_vendor" style="border-color: #e2e8f0; font-size:0.85rem; padding: 6px 12px; font-weight: 600;">
                        <option value="0">Not Assigned</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted fw-bold small mb-1" style="font-size: 0.75rem;">PRIORITY LEVEL</label>
                    <select class="form-select form-select-sm shadow-none" style="border-color: #e2e8f0; font-size:0.85rem; padding: 6px 12px;">
                        <option>Normal</option>
                        <option>High</option>
                        <option>Urgent</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label text-muted fw-bold small mb-1" style="font-size: 0.75rem;">PAYMENT STATUS</label>
                    <select class="form-select form-select-sm shadow-none" style="border-color: #e2e8f0; font-size:0.85rem; padding: 6px 12px;">
                        <option>Pending</option>
                        <option>Paid</option>
                    </select>
                </div>
            </div>
            
            <div class="modal-footer border-0 pt-0 pb-4 px-4 bg-white">
                <button type="button" class="btn btn-primary w-100 fw-bold shadow-sm rounded-3 py-2" onclick="saveGroupEdit()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
const csrfToken = '{{ csrf_token() }}';
const firmsList = @json($firms);
let currentGroupItems = [];
let activeVendorColumns = [];

// ========== Enhanced UI Functions ==========
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    const icons = {
        success: 'fa-check-circle text-success',
        error: 'fa-exclamation-circle text-danger',
        warning: 'fa-exclamation-triangle text-warning'
    };
    
    toast.className = 'custom-toast';
    toast.innerHTML = `<i class="fas ${icons[type] || icons.success}"></i> <strong>${message}</strong>`;
    container.appendChild(toast);
    setTimeout(() => { 
        toast.style.animation = 'none';
        toast.style.transform = 'translateX(400px)';
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}

function showConfirm(title, text) {
    return new Promise((resolve) => {
        const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
        document.getElementById('confirmTitle').innerText = title;
        document.getElementById('confirmText').innerText = text;
        const confirmBtn = document.getElementById('confirmBtn');
        
        const handleConfirm = () => {
            modal.hide();
            confirmBtn.removeEventListener('click', handleConfirm);
            resolve({ isConfirmed: true });
        };
        
        confirmBtn.onclick = handleConfirm;
        modal.show();
        
        modal._element.addEventListener('hidden.bs.modal', () => {
            resolve({ isConfirmed: false });
        }, { once: true });
    });
}

// ========== Accordion & Group Actions ==========
function toggleDetails(rfqId) {
    const row = document.getElementById(`details_row_${rfqId}`);
    const icon = document.getElementById(`icon_${rfqId}`);
    const content = document.getElementById(`details_content_${rfqId}`);
    
    if (row.classList.contains('d-none')) {
        row.classList.remove('d-none');
        icon.style.transform = 'rotate(180deg) scale(1.1)';
        icon.style.color = '#007BFF';
        
        fetch(`{{ url('purnew/group') }}/${rfqId}/details`)
            .then(r => r.json())
            .then(data => {
                let html = `
                    <div class="items-card p-4 bg-white rounded-4 shadow-sm border mt-3 mb-3 mx-4" style="animation: slideDown 0.3s ease-out;">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-list-ul text-primary"></i>
                                <span class="fw-bold fs-6">Items in this group</span>
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 fw-bold fs-7 ms-2">
                                    ${data.length} items • Est. Total: PKR ${data.reduce((sum, item) => sum + parseFloat(item.est_price || 0), 0).toLocaleString()}
                                </span>
                            </div>
                            <button class="btn btn-sm btn-light border rounded-3 px-3 py-2" onclick="toggleDetails(${rfqId})" title="Close Details">
                                <i class="fas fa-times text-muted"></i>
                            </button>
                        </div>
                        
                        ${data.length === 0 ? `
                            <div class="text-center py-5">
                                <i class="fas fa-box-open fa-3x text-muted mb-3 opacity-25"></i>
                                <div class="text-muted">No items found in this group</div>
                            </div>
                        ` : `
                        <div class="table-responsive">
                            <table class="table table-borderless table-striped table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="bg-light text-muted small fw-bold" style="letter-spacing: 0.5px;">
                                    <tr>
                                        <th class="ps-3 py-3" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;">ITEM DESCRIPTION</th>
                                        <th class="py-3">EST. PRICE</th>
                                        <th class="py-3">ACCEPTED VENDOR</th>
                                        <th class="py-3" style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;">FINAL PRICE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.map(item => `
                                        <tr>
                                            <td class="ps-3 py-3 fw-bold text-dark">${item.title}</td>
                                            <td class="py-3 text-muted">PKR ${parseFloat(item.est_price || 0).toLocaleString()}</td>
                                            <td class="py-3">
                                                ${item.available_quotes && item.available_quotes.length > 0 ? `
                                                    <select class="form-select form-select-sm shadow-none border fw-bold" style="border-radius:6px; color:#1e293b; width: 100%; max-width: 200px; font-size:0.8rem;" onchange="acceptItemQuoteBackend(${item.rfq_item_id}, this.value, ${rfqId})">
                                                        <option value="0" ${!item.has_accepted_quote ? 'selected' : ''}>Pending Vendor</option>
                                                        ${item.available_quotes.map(q => `
                                                            <option value="${q.frm_id}" ${item.vendor_id == q.frm_id ? 'selected' : ''}>${q.vendor_name} (Rs. ${parseFloat(q.quoted_price).toLocaleString()})</option>
                                                        `).join('')}
                                                    </select>
                                                ` : `
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill"><i class="fas fa-clock me-1"></i> Pending Vendor</span>
                                                `}
                                            </td>
                                            <td class="py-3">
                                                ${item.has_accepted_quote ? `<span class="fw-bold text-success fs-6">PKR ${parseFloat(item.accepted_price).toLocaleString()}</span>` : `<span class="text-muted fst-italic">-</span>`}
                                            </td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                        `}
                    </div>
                `;
                content.innerHTML = html;
            })
            .catch(() => {
                content.innerHTML = '<div class="text-center text-danger py-8"><i class="fas fa-exclamation-triangle fa-2x mb-3"></i><div class="h5">Failed to load items</div></div>';
            });
    } else {
        row.classList.add('d-none');
        icon.style.transform = 'rotate(0deg) scale(1)';
        icon.style.color = '';
    }
}

function acceptItemQuoteBackend(rfqItemId, frmId, rfqId) {
    const fd = new FormData();
    fd.append('_token', csrfToken);
    fd.append('rfq_item_id', rfqItemId);
    fd.append('frm_id', frmId);
    
    fetch('{{ route("purnew.quotes.acceptItem") }}', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast('Vendor assignment updated', 'success');
                setTimeout(() => {
                    const row = document.getElementById(`details_row_${rfqId}`);
                    row.classList.add('d-none');
                    toggleDetails(rfqId);
                    // Reload to update total estimates
                    setTimeout(() => location.reload(), 1000); 
                }, 300);
            }
        });
}

let currentEditGroupId = null;
function openEditGroupModal(rfqId, rfqTitle) {
    currentEditGroupId = rfqId;
    document.getElementById('edit_group_name').value = rfqTitle;
    const vendorSelect = document.getElementById('edit_group_vendor');
    
    vendorSelect.innerHTML = '<option value="0">Loading...</option>';
    
    const egModal = new bootstrap.Modal(document.getElementById('editGroupModal'));
    egModal.show();
    
    fetch(`{{ url('purnew/quotes') }}/${rfqId}`)
        .then(r => r.json())
        .then(quotes => {
            vendorSelect.innerHTML = '<option value="0">Not Assigned</option>';
            if (quotes && quotes.length > 0) {
                // Extract unique vendors who have quoted
                const uniqueVendors = [...new Map(quotes.map(q => [q.frm_id, {id: q.frm_id, name: q.frm_name}])).values()];
                uniqueVendors.forEach(v => {
                    vendorSelect.innerHTML += `<option value="${v.id}">${v.name}</option>`;
                });
                
                // Try to find if one is already accepted
                const accepted = quotes.find(q => q.is_accepted);
                if (accepted) {
                    vendorSelect.value = accepted.frm_id;
                }
            }
        })
        .catch(() => {
            vendorSelect.innerHTML = '<option value="0">Error loading data</option>';
        });
}

function saveGroupEdit() {
    const frmId = document.getElementById('edit_group_vendor').value;
    if (!currentEditGroupId) return;
    
    // Bulk accept via existing endpoint
    const fd = new FormData();
    fd.append('_token', csrfToken);
    fd.append('rfq_id', currentEditGroupId);
    fd.append('frm_id', frmId);
    
    fetch('{{ route("purnew.quotes.accept") }}', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast('Group vendor updated successfully', 'success');
                const m = bootstrap.Modal.getInstance(document.getElementById('editGroupModal'));
                if (m) m.hide();
                setTimeout(() => location.reload(), 800);
            }
        });
}

function deleteGroup(rfqId, title) {
    showConfirm('Delete RFQ Group', `Are you sure you want to permanently delete "${title}"?\n\n⚠️ This will remove all items, quotations and associated data.`)
        .then(res => {
            if (res.isConfirmed) {
                showToast('Deleting group...', 'warning');
                const fd = new FormData();
                fd.append('_token', csrfToken);
                fd.append('_method', 'DELETE');
                
                fetch(`{{ url('purnew/group') }}/${rfqId}`, {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast('✅ Group deleted successfully!', 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast('❌ Error: ' + (data.message || 'Failed to delete'), 'error');
                    }
                });
            }
        });
}

function showTracking(rfqId, title, dateStr) {
    document.getElementById('tracking_group_name').innerText = `#${rfqId} - ${title}`;
    document.getElementById('tracking_date').innerText = dateStr;
    new bootstrap.Modal(document.getElementById('trackingModal')).show();
}

// ========== Enhanced Quotation Modal ==========
let quotationModalInstance = null;

function closeQuotationModal() {
    if (quotationModalInstance) {
        quotationModalInstance.hide();
    } else {
        try {
            const m = bootstrap.Modal.getInstance(document.getElementById('quotationModal'));
            if (m) m.hide();
        } catch(e) {}
        if (typeof $ !== 'undefined') {
            $('#quotationModal').modal('hide');
        }
    }
}

function openQuotationModal(rfqId, rfqTitle) {
    document.getElementById('quote_group_id').innerText = rfqId;
    document.getElementById('quote_group_name').innerText = rfqTitle;

    const headerRow = document.getElementById('quote_header_row');
    const quoteBody = document.getElementById('quote_body');
    const totalRow = document.getElementById('quote_total_row');

    // Reset table
    headerRow.innerHTML = '<th style="width: 250px; min-width: 250px;" class="bg-light sticky-left border-end"><div class="item-desc-content fw-bold text-muted small text-uppercase" style="letter-spacing:0.5px; padding: 12px 16px;">Item Description</div></th>';
    quoteBody.innerHTML = '<tr><td colspan="20" class="text-center p-8"><div class="spinner-border spinner-border-lg text-primary mx-auto mb-4" style="width:4rem;height:4rem;"></div><div class="h5 text-muted">Loading quotation data...</div></td></tr>';
    totalRow.innerHTML = '<th class="bg-light sticky-left border-end"><div class="item-desc-content fw-bold text-dark small" style="padding: 12px 16px;">TOTAL (INC. TAX)</div></th>';
    
    activeVendorColumns = [];
    currentGroupItems = [];

    if (!quotationModalInstance) {
        quotationModalInstance = new bootstrap.Modal(document.getElementById('quotationModal'), { backdrop: 'static' });
    }
    quotationModalInstance.show();

    Promise.all([
        fetch(`{{ url('purnew/quotes') }}/${rfqId}/items`).then(r => r.json()),
        fetch(`{{ url('purnew/quotes') }}/${rfqId}`).then(r => r.json())
    ]).then(([items, existingQuotes]) => {
        currentGroupItems = items || [];
        renderQuotationTable();
        showToast('✅ Table ready! Add vendors to start quoting.', 'success');

        if (existingQuotes?.length > 0) {
            const vendorGroups = existingQuotes.reduce((acc, row) => {
                if (!acc[row.frm_id]) acc[row.frm_id] = [];
                acc[row.frm_id].push(row);
                return acc;
            }, {});

            Object.keys(vendorGroups).forEach(frmId => {
                const colId = addNewVendorColumn(frmId);
                vendorGroups[frmId].forEach(row => {
                    const tr = document.querySelector(`#quote_body tr[data-item-id="${row.rfq_item_id}"]`);
                    if (tr) {
                        const input = tr.querySelector(`td[data-col-id="${colId}"] .price-input`);
                        if (input) {
                            input.value = row.quoted_price;
                            input.classList.add('is-valid');
                            input.dataset.lastSaved = row.quoted_price;
                            input.dataset.lastVendor = frmId;
                            input.disabled = false;
                        }
                    }
                });
            });
        } else {
            // Add first empty column
            setTimeout(() => addNewVendorColumn(), 300);
        }
        calculateTotals();
    }).catch(() => {
        showToast('Failed to load quotation data', 'error');
    });
}

function renderQuotationTable() {
    const quoteBody = document.getElementById('quote_body');
    quoteBody.innerHTML = '';
    
    currentGroupItems.forEach(item => {
        const row = document.createElement('tr');
        row.setAttribute('data-item-id', item.rfq_item_id);
        row.className = 'border-bottom border-light hover-row';
        row.innerHTML = `
            <td class="sticky-left border-end p-0" style="vertical-align: middle; background-color: #f8fafc;">
                <div class="d-flex justify-content-between align-items-center w-100 h-100" style="padding: 8px 16px;">
                    <span class="fw-bold text-dark" style="font-size: 0.85rem;">${item.title || item.item_name}</span>
                    <span class="text-muted" style="font-size: 0.75rem;">Qty: ${item.qty || '-'}</span>
                </div>
            </td>
        `;
        
        // Add cells for existing vendor columns
        activeVendorColumns.forEach(colId => {
            row.appendChild(createVendorInputCell(item.rfq_item_id, colId));
        });
        
        quoteBody.appendChild(row);
    });
}

function createVendorInputCell(rfqItemId, colId) {
    const td = document.createElement('td');
    td.setAttribute('data-col-id', colId);
    td.className = 'bg-white border-end align-middle position-relative';
    td.style.minWidth = '140px';
    td.style.padding = '6px 12px';

    const input = document.createElement('input');
    input.type = 'number';
    input.className = 'form-control form-control-sm price-input w-100 shadow-none';
    input.placeholder = 'Enter Price';
    input.style.fontSize = '0.8rem';
    input.style.padding = '4px 8px';
    input.style.borderRadius = '4px';
    input.style.fontStyle = 'italic';
    input.step = '0.01';
    input.min = '0';
    
    const vendorSelect = document.getElementById(`vendor_select_${colId}`);
    if (!vendorSelect || !vendorSelect.value) input.disabled = true;

    // Auto-save on blur/change
    const saveQuote = debounce(() => {
        const frmId = document.getElementById(`vendor_select_${colId}`)?.value;
        const price = input.value;
        const rfqId = document.getElementById('quote_group_id').innerText;
        
        if (!frmId || !price || (input.dataset.lastSaved === price && input.dataset.lastVendor === frmId)) return;

        const fd = new FormData();
        fd.append('_token', csrfToken);
        fd.append('rfq_id', rfqId);
        fd.append('rfq_item_id', rfqItemId);
        fd.append('frm_id', frmId);
        fd.append('quoted_price', price);

        fetch('{{ route("purnew.quotes.save") }}', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    input.classList.add('is-valid');
                    input.dataset.lastSaved = price;
                    input.dataset.lastVendor = frmId;
                    showToast(`✅ Saved: Rs. ${parseFloat(price).toLocaleString()}`, 'success');
                    setTimeout(() => input.classList.remove('is-valid'), 2000);
                }
            }).catch(() => {
                showToast('Failed to save quote', 'error');
                input.value = input.dataset.lastSaved || '';
            });
    }, 800);

    input.onblur = saveQuote;
    input.onchange = saveQuote;
    input.oninput = calculateTotals;
    
    td.appendChild(input);
    return td;
}

// Utility: Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function addNewVendorColumn(initialFrmId = null) {
    const colId = Date.now() + Math.random().toString(36).substr(2, 9);
    activeVendorColumns.push(colId);

    // Add header
    const headerRow = document.getElementById('quote_header_row');
    const th = document.createElement('th');
    th.className = 'vendor-col-header position-relative';
    th.id = `header_col_${colId}`;
    th.innerHTML = `
        <div class="d-flex align-items-center gap-2" style="padding: 4px 6px;">
            <select class="form-select form-select-sm shadow-none w-100" id="vendor_select_${colId}" style="border-radius: 6px; font-weight: 600; font-size: 0.8rem; background-color: #fff;">
                <option value="">Select Vendor</option>
            </select>
            <button class="btn btn-sm p-0 m-0 border-0 bg-transparent text-danger" onclick="removeVendorColumn('${colId}')" title="Remove Vendor">
                <i class="fas fa-trash-alt" style="opacity: 0.6; font-size: 0.9rem;"></i>
            </button>
        </div>
    `;
    headerRow.appendChild(th);

    // Setup vendor select
    const select = th.querySelector('select');
    updateVendorOptions(select);
    if (initialFrmId) select.value = initialFrmId;

    // Add cells to all rows
    document.querySelectorAll('#quote_body tr').forEach(row => {
        row.appendChild(createVendorInputCell(row.dataset.itemId, colId));
    });

    // Add total cell
    const totalTd = document.createElement('td');
    totalTd.setAttribute('data-col-id', colId);
    totalTd.className = 'p-2 border-end text-start bg-white';
    totalTd.style.minWidth = '140px';
    totalTd.innerHTML = `
        <div class="px-2 py-1">
            <div class="text-muted" style="font-size:0.65rem">Sub: PKR 0.00</div>
            <div class="text-primary fw-bold" style="font-size:0.85rem">PKR 0.00</div>
        </div>
    `;
    document.getElementById('quote_total_row').appendChild(totalTd);

    // Vendor change handler
    select.onchange = () => {
        const hasVendor = select.value !== "";
        document.querySelectorAll(`td[data-col-id="${colId}"] .price-input`).forEach(input => {
            input.disabled = !hasVendor;
        });
        
        // Update other selects
        document.querySelectorAll('[id^="vendor_select_"]').forEach(s => { 
            if(s !== select) updateVendorOptions(s); 
        });
        
        calculateTotals();
        showToast(`✅ ${select.options[select.selectedIndex].text} added`, 'success');
    };

    calculateTotals();
    return colId;
}

function updateVendorOptions(selectEl) {
    const currentVal = selectEl.value;
    const selectedIds = Array.from(document.querySelectorAll('[id^="vendor_select_"]'))
                             .filter(s => s !== selectEl)
                             .map(s => s.value)
                             .filter(Boolean);
    
    selectEl.innerHTML = '<option value="">👥 Select Vendor</option>';
    firmsList.forEach(f => {
        if (!selectedIds.includes(f.frm_id.toString()) || f.frm_id.toString() === currentVal) {
            const opt = new Option(`${f.frm_name}`, f.frm_id);
            if (f.frm_id.toString() === currentVal) opt.selected = true;
            selectEl.add(opt);
        }
    });
}

function removeVendorColumn(colId) {
    const select = document.getElementById(`vendor_select_${colId}`);
    const frmId = select?.value;
    
    if (frmId) {
        showConfirm('Remove Vendor Column', `Delete all quotes for "${select.options[select.selectedIndex].text}"?\n\nThis will permanently remove all prices entered for this vendor.`)
            .then(res => {
                if (res.isConfirmed) {
                    const fd = new FormData();
                    fd.append('_token', csrfToken);
                    fd.append('rfq_id', document.getElementById('quote_group_id').innerText);
                    fd.append('frm_id', frmId);
                    
                    fetch('{{ route("purnew.quotes.deleteColumn") }}', { method: 'POST', body: fd })
                        .then(() => {
                            showToast('✅ Vendor column removed', 'success');
                            location.reload();
                        });
                }
            });
    } else {
        // No data, just remove UI
        document.getElementById(`header_col_${colId}`)?.remove();
        document.querySelectorAll(`[data-col-id="${colId}"]`).forEach(el => el.remove());
        activeVendorColumns = activeVendorColumns.filter(id => id !== colId);
        calculateTotals();
        showToast('🗑️ Empty column removed', 'success');
    }
}

function calculateTotals() {
    const taxPerc = parseFloat(document.getElementById('quote_tax_perc').value) || 0;
    
    activeVendorColumns.forEach(colId => {
        let subtotal = 0;
        let validQuotes = 0;
        
        document.querySelectorAll(`td[data-col-id="${colId}"] .price-input`).forEach(input => {
            const price = parseFloat(input.value) || 0;
            subtotal += price;
            if (price > 0) validQuotes++;
        });
        
        const total = subtotal + (subtotal * (taxPerc / 100));
        const cell = document.querySelector(`#quote_total_row td[data-col-id="${colId}"]`);
        
        if (cell) {
            cell.innerHTML = `
                <div class="text-start ms-3">
                    <div class="small text-muted mb-2" style="font-size:0.85rem">
                        Subtotal: <strong>PKR ${subtotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>
                        ${validQuotes > 0 ? `• ${validQuotes} items` : ''}
                    </div>
                    <div class="h4 text-primary mb-0 fw-bold" style="font-size:1.4rem !important;">
                        PKR ${total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                    </div>
                    ${taxPerc > 0 ? `<div class="small text-success">+${taxPerc}% tax</div>` : ''}
                </div>
            `;
        }
    });
}

function exportQuotes() {
    showToast('📥 Export feature coming soon!', 'warning');
}

// Enhanced hover effects
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.hover-row, .hover-lift').forEach(el => {
        el.addEventListener('mouseenter', () => el.style.transform = 'translateY(-2px)');
        el.addEventListener('mouseleave', () => el.style.transform = '');
    });
});
</script>
@endsection