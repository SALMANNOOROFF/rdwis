@extends('welcome')
@section('content')

@php
    $type = in_array($type, ['Ps', 'Pt', 'Rb'], true) ? $type : 'Ps';
    $themes = [
        'Ps' => ['icon' => 'fa-boxes', 'color' => '#2563eb', 'bg' => '#eff6ff', 'label' => 'Major Purchase (With Quotes)'],
        'Pt' => ['icon' => 'fa-receipt', 'color' => '#059669', 'bg' => '#ecfdf5', 'label' => 'Incidental Exp. (Without Quotes)'],
        'Rb' => ['icon' => 'fa-plane-departure', 'color' => '#d97706', 'bg' => '#fffbeb', 'label' => 'TA/DA Claim'],
    ];
    $theme = $themes[$type] ?? $themes['Ps'];
@endphp

<div class="content-wrapper p-0">
  <div class="container-fluid p-0">
    <div class="sinc-wrapper">
      <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Rajdhani:wght@600;700;800&display=swap');

        .sinc-wrapper {
          min-height: calc(100vh - 60px);
          background: #f8fafc;
          font-family: 'Inter', sans-serif;
          padding: 24px 20px 48px;
        }

        .sinc-card {
          background: #ffffff;
          border: 1.5px solid #e2e8f0;
          border-radius: 14px;
          box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
          overflow: hidden;
        }

        .sinc-card-head {
          display: flex;
          align-items: center;
          justify-content: space-between;
          padding: 16px 24px;
          border-bottom: 1.5px solid #e2e8f0;
          background: #ffffff;
        }

        .sinc-card-title {
          font-size: 1rem;
          font-weight: 800;
          color: #0f172a;
          display: flex;
          align-items: center;
          gap: 9px;
          font-family: 'Rajdhani', sans-serif;
          letter-spacing: 0.5px;
        }
        
        .soft-form { padding: 24px; }
        .soft-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .soft-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
        .soft-group { margin-bottom: 14px; }

        @media (min-width: 992px) {
          .soft-split-grid { display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start; }
        }

        @media (max-width: 991.98px) {
          .soft-row, .soft-row-3, .soft-split-grid { grid-template-columns: 1fr; gap: 12px; }
          .sinc-wrapper { padding: 12px 10px 36px !important; }
        }
        
        .soft-label {
          font-size: 0.72rem;
          font-weight: 700;
          color: #475569;
          margin-bottom: 5px;
          display: block;
          text-transform: uppercase;
          letter-spacing: 0.4px;
        }
        
        .soft-input, .soft-select, .soft-textarea {
          width: 100%;
          border: 1.5px solid #cbd5e1;
          border-radius: 8px;
          background: #ffffff;
          padding: 6px 12px;
          font-size: 0.82rem;
          height: 36px;
          color: #0f172a;
          font-family: 'Inter', sans-serif;
          transition: all 0.2s ease;
        }
        .soft-select {
          cursor: pointer;
          appearance: none;
          background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
          background-repeat: no-repeat;
          background-position: right 12px center;
          padding-right: 32px;
        }
        
        .soft-input:focus, .soft-select:focus, .soft-textarea:focus {
          outline: none;
          border-color: <?= $theme['color'] ?>;
          background: #ffffff;
          box-shadow: 0 0 0 3px <?= $theme['color'] ?>20;
        }
        .soft-textarea {
          min-height: 70px;
          height: auto;
          resize: vertical;
          padding-top: 8px;
        }
        
        .section-title {
          font-size: 0.82rem;
          font-weight: 800;
          color: #0f172a;
          margin: 8px 0 14px;
          display: flex;
          align-items: center;
          gap: 8px;
          padding-bottom: 6px;
          border-bottom: 1.5px dashed #e2e8f0;
          text-transform: uppercase;
          letter-spacing: 0.5px;
          font-family: 'Rajdhani', sans-serif;
        }
        
        .sinc-topbar {
          display: flex;
          align-items: center;
          justify-content: space-between;
          margin-bottom: 20px;
          flex-wrap: wrap;
          gap: 12px;
          border-bottom: 1.5px solid #e2e8f0;
          padding-bottom: 14px;
        }
        .sinc-page-title {
          font-size: 1rem;
          font-weight: 800;
          color: #0f172a;
          display: flex;
          align-items: center;
          gap: 10px;
        }
        .title-icon {
          width: 36px;
          height: 36px;
          border-radius: 10px;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 1rem;
        }
        
        .badge-draft {
          background-color: #fefce8;
          color: #a16207;
          padding: 4px 12px;
          border-radius: 6px;
          font-size: 0.7rem;
          font-weight: 800;
          border: 1px solid #fef08a;
          letter-spacing: 0.5px;
          font-family: 'Rajdhani', sans-serif;
        }
        
        /* Interactive dynamic lists */
        .dyn-list { display: flex; flex-direction: column; gap: 8px; }
        .dyn-row {
          display: grid;
          grid-template-columns: 1fr 140px 32px;
          gap: 8px;
          align-items: end;
          padding: 8px 12px;
          border: 1.5px solid #e2e8f0;
          border-radius: 8px;
          background: #f8fafc;
          transition: all 0.2s;
        }
        @media (max-width: 575.98px) {
          .dyn-row { grid-template-columns: 1fr !important; position: relative; padding-bottom: 45px; }
          .btn-rm-row { position: absolute; bottom: 10px; right: 10px; width: calc(100% - 20px) !important; }
        }
        .dyn-row:focus-within { border-color: <?= $theme['color'] ?>; background: #ffffff; }
        
        .btn-add-row {
          display: inline-flex;
          align-items: center;
          gap: 6px;
          padding: 6px 14px;
          border-radius: 6px;
          background: #f1f5f9;
          color: #334155;
          font-weight: 700;
          font-size: 0.72rem;
          border: 1px solid #cbd5e1;
          cursor: pointer;
          transition: all 0.2s;
          font-family: 'Rajdhani', sans-serif;
          letter-spacing: 0.5px;
        }
        .btn-add-row:hover {
          background: #e2e8f0;
          color: #0f172a;
          border-color: #94a3b8;
        }
        .btn-rm-row {
          width: 32px;
          height: 32px;
          border-radius: 6px;
          border: 1px solid #fecaca;
          background: #fff5f5;
          color: #ef4444;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
          transition: all 0.2s;
        }
        .btn-rm-row:hover { background: #ef4444; color: #ffffff; }

        .no-quote-chip {
          display: inline-flex;
          align-items: center;
          gap: 6px;
          background: #ffffff;
          border: 1.5px solid #cbd5e1;
          border-radius: 20px;
          padding: 4px 10px;
          font-size: 11.5px;
          font-weight: 600;
          color: #1e293b;
          box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
          transition: all 0.2s ease;
        }
        .no-quote-chip:hover {
          border-color: #94a3b8;
          background: #f8fafc;
        }
        .btn-remove-chip {
          background: none;
          border: none;
          color: #ef4444;
          cursor: pointer;
          padding: 0 4px;
          font-size: 14px;
          line-height: 1;
          font-weight: bold;
          border-radius: 50%;
        }
        .btn-remove-chip:hover {
          color: #b91c1c;
          background: #fee2e2;
        }

        /* Actions Footer */
        .form-actions-footer {
          margin-top: 24px;
          padding: 18px 24px;
          background: #ffffff;
          border-radius: 12px;
          border: 1.5px solid #e2e8f0;
          display: flex;
          justify-content: space-between;
          align-items: center;
          box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
        }
        @media (max-width: 768px) { .form-actions-footer { flex-direction: column; gap: 16px; text-align: center; } }
        
        .side-box {
          background: #ffffff;
          border: 1.5px solid #e2e8f0;
          border-radius: 12px;
          padding: 16px;
          margin-bottom: 16px;
        }
        .side-label {
          font-family: 'Rajdhani', sans-serif;
          font-size: 11.5px;
          font-weight: 700;
          color: #1e293b;
          letter-spacing: 0.8px;
          text-transform: uppercase;
          margin-bottom: 8px;
          display: flex;
          align-items: center;
          gap: 6px;
        }
        
        .stats-card {
          background: #f8fafc;
          border: 1.5px solid #e2e8f0;
          border-radius: 10px;
          padding: 16px;
          text-align: center;
        }
        .stats-val {
          font-family: 'Rajdhani', sans-serif;
          font-size: 1.7rem;
          font-weight: 800;
          color: #0f172a;
          line-height: 1;
        }
        .stats-lbl {
          font-size: 10px;
          color: #64748b;
          text-transform: uppercase;
          margin-top: 5px;
          font-weight: 700;
          letter-spacing: 0.5px;
        }

        .btn-action-main {
          height: 42px;
          border-radius: 8px;
          font-weight: 700;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          gap: 8px;
          transition: all 0.2s ease;
          font-size: 0.85rem;
          cursor: pointer;
          font-family: 'Rajdhani', sans-serif;
          letter-spacing: 0.5px;
        }

        .btn-back-link {
          display: inline-flex;
          align-items: center;
          gap: 6px;
          border: 1.5px solid #cbd5e1;
          border-radius: 8px;
          background: #ffffff;
          color: #475569;
          font-size: 0.78rem;
          font-weight: 700;
          text-decoration: none !important;
          padding: 6px 14px;
          transition: all 0.2s;
        }
        .btn-back-link:hover {
          background: #f1f5f9;
          color: #0f172a;
          border-color: #94a3b8;
        }
      </style>

      <!-- Toasts Container -->
      <div id="toast-wrapper" class="toast-container"></div>

      <div style="max-width: 1080px; margin: 0 auto;">
        <!-- Topbar -->
        <div class="sinc-topbar">
          <div class="d-flex align-items-center">
            <div class="mr-4 pr-4 border-right" style="border-color: #e2e8f0 !important;">
               <span class="small text-muted font-weight-bold rajdhani d-block" style="letter-spacing: 1.2px; font-size: 10px;">CASE ID</span>
               <span class="rajdhani font-weight-bold" style="font-size: 1.4rem; color: <?= $theme['color'] ?>; line-height: 1;">#{{ $nextId }}</span>
            </div>

            <div class="sinc-page-title d-flex align-items-center">
              <span class="title-icon theme-icon mr-3" style="background: <?= $theme['bg'] ?>; color: <?= $theme['color'] ?>; border: 1px solid <?= $theme['color'] ?>30;">
                <i class="fas <?= $theme['icon'] ?>"></i>
              </span>
              <div class="d-flex flex-column">
                  <span class="small text-muted font-weight-bold rajdhani" style="letter-spacing: 1px; font-size: 10px;">PURCHASE CASE TYPE</span>
                  <select id="typeSwitcher" class="form-control form-control-sm rajdhani font-weight-bold" 
                          style="background:transparent; border:none; color:#0f172a; font-size:1.15rem; padding:0; height:auto; cursor:pointer;"
                          onchange="if(this.value) window.location.href='/purchase/new/'+this.value">
                      @foreach([
                          'Ps' => 'Major Purchase (With Quotes)',
                          'Pt' => 'Incidental Exp. (Without Quotes)',
                          'Rb' => 'TA/DA',
                      ] as $val => $lbl)
                          <option value="{{ $val }}" {{ $type == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                      @endforeach
                  </select>
              </div>
            </div>
          </div>
          
          <div class="d-flex gap-2 align-items-center">
              <a href="{{ route('purchase.initiation.index') }}" class="btn-back-link mr-2"> 
                <i class="fas fa-arrow-left"></i> BACK TO HUB
              </a>
              
              <div class="px-3 py-1 d-flex align-items-center" style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 8px;">
                  <i class="fas fa-shield-alt text-primary mr-2" style="font-size: 11px;"></i>
                  <span class="rajdhani font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px; color: #334155;">SECURE DRAFT MODE</span>
              </div>
          </div>
        </div>

        <div class="sinc-card">
          <div class="sinc-card-head">
            <div class="sinc-card-title">
              <i class="fas fa-edit" style="color: <?= $theme['color'] ?>"></i> INITIATE NEW CASE
            </div>
            <span class="badge-draft">DRAFT MODE</span>
          </div>
          
          <form class="soft-form" id="unifiedPurchaseForm" action="{{ route('purchase.store') }}" method="POST" enctype="multipart/form-data" onsubmit="return handleFormSubmit(event)">
            @csrf
            <input type="hidden" name="pcs_type" value="{{ $type }}">
            
            <div class="soft-split-grid">
              {{-- Left Side: Primary Details --}}
              <div>
                <div class="section-title"><i class="fas fa-info-circle" style="color: <?= $theme['color'] ?>"></i> General Information</div>
                <div class="soft-row">
                  <div class="soft-group">
                    <label class="soft-label">Creation Date</label>
                    <input type="date" name="pcs_date" class="soft-input" value="{{ date('Y-m-d') }}" required>
                  </div>
                  <div class="soft-group">
                    <label class="soft-label">Minute Number</label>
                    <input type="number" name="pcs_minute" id="pcs_minute" class="soft-input" placeholder="e.g. 1" required>
                    <div id="minute-hint" style="font-size:0.65rem; color:#2563eb; margin-top:3px; font-weight:600"></div>
                  </div>
                </div>

                <div class="soft-row">
                  <div class="soft-group">
                    <label class="soft-label">Project / Budget Head <span class="text-danger">*</span></label>
                    <select name="pcs_hed_id" id="pcs_hed_id" class="soft-select" required onchange="handleProjectHeadChange(this.value)">
                      <option value="" selected disabled>Select Project Head...</option>
                      @foreach($heads as $head)
                        <option value="{{ $head->hed_id }}">{{ $head->hed_code }} - {{ $head->hed_name }}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="soft-group">
                    <label class="soft-label">Case Title / Subject <span class="text-danger">*</span></label>
                    <input type="text" name="pcs_title" class="soft-input" placeholder="Define a clear subject for this case" required>
                  </div>
                </div>

                @if($type === 'Pt')
                <div class="soft-group mt-1">
                  <label class="soft-label">Vendor / Firm Name <span class="text-danger">*</span></label>
                  <select name="pcs_frm_id" id="pcs_frm_id" class="soft-select" required>
                    <option value="" selected disabled>-- Select Vendor / Firm --</option>
                    @foreach($firms ?? [] as $firm)
                      <option value="{{ $firm->frm_id }}">{{ $firm->frm_name }}</option>
                    @endforeach
                  </select>
                  <div style="font-size: 0.72rem; color: #64748b; margin-top: 4px;">
                    <i class="fas fa-info-circle mr-1 text-primary"></i> Since this is an Incidental Expenditure without competitive quotations, select the single vendor/firm being paid.
                  </div>
                </div>
                @endif

                <!-- TYPE SPECIFIC DYNAMIC FIELDS -->
                <div class="section-title mt-4"><i class="fas <?= $theme['icon'] ?>" style="color: <?= $theme['color'] ?>"></i> Details: <?= $theme['label'] ?></div>

                {{-- BUDGET SUBHEAD SECTION (POSITIONED DIRECTLY ABOVE LINE ITEMS) --}}
                @if($type === 'Ps')
                  <div class="p-3 mb-3 rounded" style="background: #f8fafc; border: 1.5px solid #e2e8f0;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                      <div class="d-flex align-items-center">
                        <span class="mr-2 text-primary" style="font-size:16px;"><i class="fas fa-layer-group"></i></span>
                        <div>
                          <strong style="font-family:'Rajdhani',sans-serif; font-size:13px; font-weight:700; color:#1e293b; text-transform:uppercase; letter-spacing:0.5px;">Budget Subhead:</strong>
                          <span class="badge badge-primary ml-2 px-2 py-1" style="font-size:11px; font-weight:700; letter-spacing:0.5px;"><i class="fas fa-microchip mr-1"></i>Equipment</span>
                        </div>
                      </div>
                      <span class="text-muted" style="font-size:11px;">
                        <i class="fas fa-info-circle mr-1 text-primary"></i> Standard for Major Purchase. In-process commitments will automatically book under <strong>Equipment</strong>.
                      </span>
                    </div>
                    <input type="hidden" name="subhead" id="case_subhead" value="Equipment">
                  </div>
                @else
                  <div class="p-3 mb-3 rounded" style="background: #f8fafc; border: 1.5px solid #e2e8f0;">
                    <div class="row align-items-center">
                      <div class="col-md-6">
                        <label class="soft-label mb-1" style="font-weight:700; color:#1e293b;">
                          <i class="fas fa-layer-group text-primary mr-1"></i> Budget Subhead Allocation <span class="text-danger">*</span>
                        </label>
                        <select name="subhead" id="case_subhead" class="soft-select" required onchange="handleSubheadChange(this.value)">
                          <option value="Misc" selected>Misc (Default for {{ $type === 'Pt' ? 'Incidental' : 'TA/DA' }})</option>
                          <option value="Equipment">Equipment</option>
                        </select>
                      </div>
                      <div class="col-md-6">
                        <div style="font-size:11px; color:#64748b; line-height:1.4;">
                          <i class="fas fa-info-circle text-primary mr-1"></i>
                          In-process budget will deduct under <strong id="subheadPreview" class="text-dark">Misc</strong>.
                          You can switch to <strong>Equipment</strong> or any project-allocated subhead.
                        </div>
                      </div>
                    </div>
                  </div>
                @endif

                {{-- Line Items List (Common for all types) --}}
                <div class="soft-group mt-2">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="soft-label mb-0">Line Items</label>
                    <button type="button" class="btn-add-row" onclick="addItemRow()">
                        <i class="fas fa-plus"></i> ADD ITEM
                    </button>
                  </div>
                  <div id="items-list" class="dyn-list">
                    @if($type === 'Ps')
                    <div class="dyn-row dyn-row-card p-3 mb-2 rounded" data-idx="0" style="background:#ffffff; border:1.5px solid #e2e8f0; display:block;">
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="font-weight-bold rajdhani text-dark" style="font-size:13px;"><i class="fas fa-box-open text-primary mr-1"></i> Item #<span class="row-serial">1</span></span>
                        <button type="button" class="btn-rm-row" tabindex="-1" style="visibility:hidden" onclick="removeItemRow(this)"><i class="fas fa-times"></i></button>
                      </div>
                      <div class="row">
                        <div class="col-md-7">
                          <label class="soft-label mb-1">Item Description <span class="text-danger">*</span></label>
                          <input type="text" name="items[0][desc]" class="soft-input item-desc" placeholder="Item description / technical specifications" required oninput="rebuildQuotationBody()">
                        </div>
                        <div class="col-md-2">
                          <label class="soft-label mb-1">Quantity <span class="text-danger">*</span></label>
                          <input type="number" name="items[0][qty]" class="soft-input item-qty" value="1" min="1" required oninput="rebuildQuotationBody()">
                        </div>
                        <div class="col-md-3">
                          <label class="soft-label mb-1">Denomination (Unit)</label>
                          <select name="items[0][unit]" class="soft-select">
                            <option value="num" selected>num (Numbers)</option>
                            <option value="set">set (Sets)</option>
                            <option value="job">job (Job / Work)</option>
                            <option value="svc">svc (Service)</option>
                            <option value="day">day (Days)</option>
                            <option value="kg">kg (Kilograms)</option>
                            <option value="ltr">ltr (Liters)</option>
                            <option value="m">m (Meters)</option>
                            <option value="ft">ft (Feet)</option>
                            <option value="box">box (Boxes)</option>
                            <option value="pkt">pkt (Packets)</option>
                          </select>
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-md-4">
                          <label class="soft-label mb-1">Type</label>
                          <select name="items[0][type]" class="soft-select item-type-select" onchange="handleItemTypeChange(this, 0)">
                            <option value="7" selected>Permanent (7)</option>
                            <option value="2">Consumable (2)</option>
                            <option value="3">Service (3)</option>
                          </select>
                        </div>
                        <div class="col-md-4">
                          <label class="soft-label mb-1">Subtype</label>
                          <select name="items[0][subtype]" class="soft-select item-subtype-select">
                            <option value="Parts" selected>Parts</option>
                            <option value="Tools / Test Equipment">Tools / Test Equipment</option>
                            <option value="Machinery / Equipment">Machinery / Equipment</option>
                            <option value="Fabrication Machinery">Fabrication Machinery</option>
                            <option value="Test / Measuring Equipment">Test / Measuring Equipment</option>
                            <option value="IT Equipment">IT Equipment</option>
                            <option value="Software">Software</option>
                            <option value="Furniture">Furniture</option>
                            <option value="Appliance">Appliance</option>
                            <option value="Chemicals">Chemicals</option>
                            <option value="Raw Material">Raw Material</option>
                            <option value="Stationary">Stationary</option>
                            <option value="Cleaning Material">Cleaning Material</option>
                            <option value="POL">POL</option>
                            <option value="Equipment Installation">Equipment Installation</option>
                            <option value="Equipment Repairs & Maintenance">Equipment Repairs & Maintenance</option>
                            <option value="Other">Other</option>
                          </select>
                        </div>
                        <div class="col-md-4">
                          <label class="soft-label mb-1">Inv / Asset (Inv/Asst)</label>
                          <select name="items[0][inv_asst]" class="soft-select item-invasst-select">
                            <option value="5" selected>Inventory (5)</option>
                            <option value="6">Asset (6)</option>
                          </select>
                        </div>
                      </div>
                    </div>
                    @elseif($type === 'Rb')
                    <div class="dyn-row dyn-row-tada p-3 mb-2 rounded" data-idx="0" style="background: #f8fafc; border: 1.5px solid #e2e8f0; display: block;">
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <div style="font-size:13px;font-weight:700;color:#0f172a;font-family:'Rajdhani',sans-serif;">
                          <i class="fas fa-user-check text-warning mr-1"></i> Line #<span class="row-serial">1</span>: TA/DA Employee Selection
                        </div>
                        <button type="button" class="btn-rm-row" tabindex="-1" style="visibility:hidden" onclick="removeItemRow(this)"><i class="fas fa-times"></i></button>
                      </div>
                      <div class="row align-items-end mb-2">
                        <div class="col-md-8">
                          <label class="soft-label mb-1">Select Employee (Division Contracts)</label>
                          <select name="items[0][emp_id]" id="emp_select_0" class="soft-select emp-selector tada-emp-select" onchange="clearEmployeePreview(0)">
                            <option value="" disabled selected>-- Choose Active Contracted Employee --</option>
                            @forelse($employees ?? [] as $emp)
                              <option value="{{ $emp->emp_id }}" data-name="{{ $emp->emp_name }}" data-rank="{{ $emp->emp_rank }}">
                                {{ $emp->emp_name }} ({{ $emp->emp_id }}) [{{ $emp->emp_rank ?: 'Staff' }}]
                              </option>
                            @empty
                              <option value="" disabled>-- No active contracted employees in this division --</option>
                            @endforelse
                          </select>
                        </div>
                        <div class="col-md-4">
                          <button type="button" class="btn-add-row w-100 justify-content-center" style="background:#fef3c7; color:#92400e; border-color:#fde68a; height:36px;" onclick="fetchEmployeeDetails(0)">
                            <i class="fas fa-magic"></i> Add Emp Details
                          </button>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-7">
                          <label class="soft-label mb-1" style="font-size:10px;">Item Description (Auto-filled)</label>
                          <textarea name="items[0][desc]" id="item_desc_0" class="soft-input item-desc" rows="2" placeholder="Select employee & click 'Add Emp Details' above..." required style="min-height:54px; font-size:12px;"></textarea>
                        </div>
                        <div class="col-md-2">
                          <label class="soft-label mb-1" style="font-size:10px;">Qty</label>
                          <input type="number" name="items[0][qty]" id="item_qty_0" class="soft-input item-qty" value="1" min="1" readonly oninput="calculateDirectItemTotals()">
                          <input type="hidden" name="items[0][unit]" value="num">
                        </div>
                        <div class="col-md-3">
                          <label class="soft-label mb-1" style="font-size:10px;">Price (Auto-calc)</label>
                          <input type="number" step="any" name="items[0][price]" id="item_price_0" class="soft-input item-price font-weight-bold text-success" placeholder="PKR 0" oninput="calculateDirectItemTotals()" required>
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-md-4">
                          <label class="soft-label mb-1">Type</label>
                          <select name="items[0][type]" class="soft-select">
                            <option value="3" selected>Service (3)</option>
                          </select>
                        </div>
                        <div class="col-md-4">
                          <label class="soft-label mb-1">Subtype</label>
                          <select name="items[0][subtype]" class="soft-select">
                            <option value="Travelling/Boarding/Lodging" selected>Travelling/Boarding/Lodging</option>
                            <option value="Travel">Travel</option>
                            <option value="Meals/Refreshments">Meals/Refreshments</option>
                          </select>
                        </div>
                        <div class="col-md-4">
                          <label class="soft-label mb-1">Subhead</label>
                          <select name="items[0][subhead]" class="soft-select item-subhead-select">
                            <option value="Misc" selected>Misc</option>
                            <option value="Equipment">Equipment</option>
                          </select>
                        </div>
                      </div>
                    </div>
                    @else
                    <div class="dyn-row dyn-row-card p-3 mb-2 rounded" data-idx="0" style="background:#ffffff; border:1.5px solid #e2e8f0; display:block;">
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="font-weight-bold rajdhani text-dark" style="font-size:13px;"><i class="fas fa-receipt text-success mr-1"></i> Incidental Line #<span class="row-serial">1</span></span>
                        <button type="button" class="btn-rm-row" tabindex="-1" style="visibility:hidden" onclick="removeItemRow(this)"><i class="fas fa-times"></i></button>
                      </div>
                      <div class="row">
                        <div class="col-md-5">
                          <label class="soft-label mb-1">Item Description <span class="text-danger">*</span></label>
                          <input type="text" name="items[0][desc]" class="soft-input item-desc" placeholder="Item description / expense details" required>
                        </div>
                        <div class="col-md-2">
                          <label class="soft-label mb-1">Qty <span class="text-danger">*</span></label>
                          <input type="number" name="items[0][qty]" class="soft-input item-qty" placeholder="Qty" value="1" min="1" oninput="calculateDirectItemTotals()" required>
                        </div>
                        <div class="col-md-2">
                          <label class="soft-label mb-1">Unit</label>
                          <select name="items[0][unit]" class="soft-select">
                            <option value="num" selected>num</option>
                            <option value="set">set</option>
                            <option value="job">job</option>
                            <option value="svc">svc</option>
                            <option value="day">day</option>
                            <option value="kg">kg</option>
                            <option value="ltr">ltr</option>
                            <option value="m">m</option>
                            <option value="box">box</option>
                            <option value="pkt">pkt</option>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <label class="soft-label mb-1">Unit Price (PKR) <span class="text-danger">*</span></label>
                          <input type="number" step="any" name="items[0][price]" class="soft-input item-price" placeholder="Price (PKR)" oninput="calculateDirectItemTotals()" required>
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-md-4">
                          <label class="soft-label mb-1">Type</label>
                          <select name="items[0][type]" class="soft-select item-type-select">
                            <option value="2" selected>Consumable (2)</option>
                            <option value="7">Permanent (7)</option>
                            <option value="3">Service (3)</option>
                          </select>
                        </div>
                        <div class="col-md-4">
                          <label class="soft-label mb-1">Subtype</label>
                          <select name="items[0][subtype]" class="soft-select">
                            <option value="Parts" selected>Parts</option>
                            <option value="Tools / Test Equipment">Tools / Test Equipment</option>
                            <option value="Stationary">Stationary</option>
                            <option value="Chemicals">Chemicals</option>
                            <option value="Cleaning Material">Cleaning Material</option>
                            <option value="Raw Material">Raw Material</option>
                            <option value="Equipment Repairs & Maintenance">Equipment Repairs & Maintenance</option>
                            <option value="Other">Other</option>
                          </select>
                        </div>
                        <div class="col-md-4">
                          <label class="soft-label mb-1">Subhead</label>
                          <select name="items[0][subhead]" class="soft-select item-subhead-select">
                            <option value="Misc" selected>Misc</option>
                            <option value="Equipment">Equipment</option>
                          </select>
                        </div>
                      </div>
                    </div>
                    @endif
                  </div>
                </div>
              </div>

              {{-- Right Side: Sidebar --}}
              <div>
                <div class="side-box">
                  <div class="side-label">
                    <i class="fas fa-file-contract" style="color: <?= $theme['color'] ?>"></i> Terms & Conditions / Remarks
                  </div>
                  <textarea name="pcs_remarks" class="soft-textarea" style="min-height: 110px; font-size: 12.5px; line-height: 1.5;" placeholder="Enter payment terms, delivery conditions, or case remarks (e.g. 100% payment upon delivery)..."></textarea>
                  <div style="font-size: 11px; color: #64748b; margin-top: 6px; line-height: 1.4;">
                    <i class="fas fa-info-circle mr-1 text-primary"></i> These remarks will appear on the comparative statement & approval note.
                  </div>
                </div>

                <div class="side-box">
                  <div class="side-label">
                    <i class="fas fa-calculator" style="color: <?= $theme['color'] ?>"></i> Financial Summary
                  </div>
                  <div class="stats-card">
                      <div class="stats-val" id="live-total-display">PKR 0</div>
                      <div class="stats-lbl">Estimated Total Amount</div>
                  </div>
                  <p class="text-muted mt-2 mb-0" style="font-size: 11px; text-align: center;">Total is calculated based on entered line items.</p>
                </div>
              </div> {{-- End Right Side Sidebar --}}
            </div> {{-- End .soft-split-grid --}}

            {{-- Quotation Grid Strictly for Ps Only (Full Width Below Split Grid) --}}
            @if($type === 'Ps')
              <div class="soft-group mt-4 mb-4" style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 20px;">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                  <div class="section-title mb-0" style="border-bottom: none; margin: 0; padding: 0;">
                    <i class="fas fa-balance-scale" style="color: #2563eb;"></i> Firm Quotations & Comparative Evaluation
                  </div>
                  <div class="d-flex align-items-center flex-wrap gap-3">
                      <div class="d-flex align-items-center mr-1">
                          <label class="soft-label mb-0 mr-2" style="font-size: 11px; white-space:nowrap;">QUOTE TYPE:</label>
                          <select name="pcs_quotetype" id="newCaseQuoteType" class="soft-select" style="min-width:145px; height:32px; font-size:11px; padding:2px 8px;" onchange="handleQuoteTypeChange()">
                              <option value="1">1 - Without Tax (Net Quote)</option>
                              <option value="2" selected>2 - With Tax (GST/SST)</option>
                          </select>
                      </div>
                      <div class="d-flex align-items-center" id="taxTypeContainer">
                          <label class="soft-label mb-0 mr-2" style="font-size: 11px;">TAX TYPE:</label>
                          <select name="tax_type" id="newCaseTaxType" class="soft-select" style="width:80px; height:32px; font-size:11px; padding:2px 8px;" onchange="calculateQuotationTotals()">
                              <option value="GST">GST</option>
                              <option value="SST">SST</option>
                          </select>
                      </div>
                      <div class="d-flex align-items-center ml-1" id="taxPercentContainer">
                          <label class="soft-label mb-0 mr-2" style="font-size: 11px;">TAX %:</label>
                          <input type="number" name="tax_percent" id="newCaseTaxPercent" class="soft-input" value="18" min="0" max="100" style="width:64px; height:32px; font-size:11px; padding:2px 8px; text-align:center;" oninput="calculateQuotationTotals()">
                      </div>
                  </div>
                </div>

                <div class="d-flex align-items-end gap-2 mb-3">
                  <div style="flex:1;">
                    <label class="soft-label">Select Firm to Add to Comparison</label>
                    <div class="d-flex gap-2">
                      <input type="text" id="firmSearchFilter" class="soft-input" placeholder="🔍 Filter firm..." style="height:36px; max-width:210px; font-size:12px;" oninput="filterComparisonFirms(this.value)">
                      <select id="firmSelector" class="soft-select" style="height:36px;">
                        <option value="" disabled selected>-- Choose a Firm --</option>
                        @foreach($firms ?? [] as $f)
                          @if($f->frm_id > 0 && !str_contains($f->frm_name, '< Select'))
                            <option value="{{ $f->frm_id }}" data-name="{{ $f->frm_name }}">{{ $f->frm_name }}</option>
                          @endif
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <button type="button" class="btn-add-row" onclick="addFirmColumn()" style="height:36px; padding: 0 16px; white-space:nowrap;">
                    <i class="fas fa-plus"></i> ADD FIRM
                  </button>
                </div>

                <div style="overflow-x:auto; border:1.5px solid #e2e8f0; border-radius:8px;">
                  <table id="quotationTable" style="width:100%; border-collapse:collapse; font-size:12px;">
                    <thead>
                      <tr style="background:#f8fafc; border-bottom:1.5px solid #e2e8f0;">
                        <th style="padding:10px 12px; color:#475569; font-size:10px; text-transform:uppercase; letter-spacing:0.5px; font-weight:700; min-width:44px; text-align:center;">S.No</th>
                        <th style="padding:10px 12px; color:#475569; font-size:10px; text-transform:uppercase; letter-spacing:0.5px; font-weight:700; min-width:200px;">Item Description</th>
                        <th style="padding:10px 12px; color:#475569; font-size:10px; text-transform:uppercase; letter-spacing:0.5px; font-weight:700; text-align:center; min-width:70px;">Qty</th>
                        {{-- Firm columns will be dynamically added here --}}
                      </tr>
                    </thead>
                    <tbody id="quotationBody">
                      {{-- Rows populated by JS --}}
                    </tbody>
                    <tfoot id="quotationFoot">
                      <tr style="border-top:2px solid #e2e8f0; background: #ffffff;">
                        <td colspan="3" style="padding:8px 12px; font-size:11px; font-weight:700; color:#475569; text-align:right;">SUB TOTAL</td>
                      </tr>
                      <tr style="background: #ffffff;" id="quotationTaxRow">
                        <td colspan="3" style="padding:8px 12px; font-size:11px; font-weight:700; color:#475569; text-align:right;" id="taxRowLabel">TAX (18%)</td>
                      </tr>
                      <tr style="border-top:1.5px solid #e2e8f0; background:#f8fafc;">
                        <td colspan="3" style="padding:10px 12px; font-weight:800; color:#0f172a; text-align:right; font-family:'Rajdhani',sans-serif; font-size:14px;">TOTAL (PKR)</td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
                <p class="text-muted mt-2 mb-3" style="font-size:11px;"><i class="fas fa-info-circle mr-1 text-primary"></i> The lowest-priced firm will be auto-selected as the winning quote in the Comparative Statement.</p>

                {{-- Access Parity: Quotations Contacted but Not Received (pur.noquotes) --}}
                <div class="pt-3 border-top" style="border-top: 1.5px dashed #cbd5e1 !important;">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <div style="font-family:'Rajdhani',sans-serif; font-size:12px; font-weight:700; color:#334155; text-transform:uppercase; letter-spacing:0.5px;">
                      <i class="fas fa-file-excel text-danger mr-1"></i> Quotations Not Received (Contacted Firms with No Response)
                    </div>
                    <span class="badge badge-light border text-muted" style="font-size:10px;">Saved to pur.noquotes</span>
                  </div>

                  <div class="row align-items-start">
                    <div class="col-md-9">
                      <div class="d-flex align-items-end gap-2 mb-2">
                        <div style="flex:1;">
                          <label class="soft-label mb-1">Search & Select Invited Firm That Did Not Submit Quote</label>
                          <div class="d-flex gap-2">
                            <input type="text" id="noQuoteSearch" class="soft-input" placeholder="🔍 Quick search firm..." style="height:36px; max-width:210px; font-size:12px;" oninput="filterNoQuoteFirms(this.value)">
                            <select id="noQuoteFirmSelector" class="soft-select" style="height:36px; font-size:12px;">
                              <option value="" disabled selected>-- Choose Firm --</option>
                              @foreach($firms ?? [] as $f)
                                @if($f->frm_id > 0 && !str_contains($f->frm_name, '< Select'))
                                  <option value="{{ $f->frm_id }}" data-name="{{ $f->frm_name }}">{{ $f->frm_name }}</option>
                                @endif
                              @endforeach
                            </select>
                          </div>
                        </div>
                        <button type="button" class="btn-add-row" onclick="addNoQuoteFirm()" style="height:36px; padding: 0 16px; background:#dc2626; border-color:#dc2626; color:#ffffff; white-space:nowrap;">
                          <i class="fas fa-plus"></i> ADD NO-QUOTE
                        </button>
                      </div>

                      <!-- Selected Chips Container -->
                      <div id="noQuotesContainer" class="p-2 border rounded bg-light d-flex flex-wrap gap-2 align-items-center" style="min-height: 48px; border-color: #cbd5e1 !important;">
                        <span id="noQuotesEmpty" class="text-muted small font-italic px-2">
                          <i class="fas fa-info-circle mr-1 text-primary"></i> No unreceived firms added yet. Search or select a firm above and click <strong>ADD NO-QUOTE</strong>.
                        </span>
                      </div>
                      <div style="font-size: 10.5px; color: #64748b; margin-top: 4px;">
                        <i class="fas fa-info-circle mr-1 text-primary"></i> Added firms appear above as tags and will be saved directly into <code>pur.noquotes</code>.
                      </div>
                    </div>

                    <div class="col-md-3">
                      <div class="p-3 bg-light rounded text-center border" style="border-color: #cbd5e1 !important;">
                        <span class="text-muted d-block font-weight-bold" style="font-size:10px; text-transform:uppercase; letter-spacing:0.5px;">No-Quote Firms</span>
                        <strong id="noQuotesCount" class="text-danger font-weight-bold" style="font-size:20px; font-family:'Rajdhani',sans-serif;">0 Selected</strong>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
            @endif
            
            <!-- FORM ACTIONS -->
            <div class="form-actions-footer">
              <div class="text-left">
                  <p class="mb-0 text-muted" style="font-size: 0.8rem;"><i class="fas fa-info-circle mr-1 text-primary"></i> Case will be saved as <strong>Draft</strong> in your Unit.</p>
                  <p class="mb-0 text-muted" style="font-size: 0.8rem;">You can review, edit, and forward it from the <strong>PC Initiation Hub</strong>.</p>
              </div>
              <div class="d-flex gap-2">
                  <input type="hidden" name="release_directly" id="release_directly_flag" value="0">
                  <input type="hidden" name="initiation_remarks" id="initiation_remarks_payload" value="">

                  <button type="submit" id="draftSubmitBtn" class="btn-action-main" style="background: <?= $theme['color'] ?>; color: #ffffff; border: none; padding: 10px 24px; box-shadow: 0 4px 14px <?= $theme['color'] ?>30;">
                     <i class="fas fa-save mr-1"></i> SAVE AS DRAFT
                  </button>
              </div>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>
</div>

<script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
<script>
  // ----- Notification Toasts -----
  function fireToast(msg, type='success') {
      const w = document.getElementById('toast-wrapper');
      if (!w) return;
      const t = document.createElement('div');
      t.className = `smart-toast ${type}`;
      t.innerHTML = `<i class="fas ${type==='success'?'fa-check-circle':'fa-exclamation-circle'}"></i> ${msg}`;
      w.appendChild(t);
      setTimeout(() => {
          t.style.animation = 'slideDown 0.3s cubic-bezier(0.175,0.885,0.32,1.275) forwards';
          setTimeout(() => t.remove(), 300);
      }, 3500);
  }

  function handleFormSubmit(e) {
      fireToast('Saving draft... Please wait', 'success');
      const btn = document.getElementById('draftSubmitBtn');
      if (btn) {
          btn.innerHTML = `<i class="fas fa-spinner fa-spin mr-1"></i> Saving Draft...`;
          btn.style.pointerEvents = 'none';
          btn.style.opacity = '0.8';
      }
      return true;
  }

  // ----- Dynamic Minute Number & Subhead AJAX -----
  const subheadsByHead = @json($subheadsByHead ?? []);

  function updateSubheadDropdown(headId) {
      const subheadSelect = document.getElementById('case_subhead');
      if (!subheadSelect) return;
      
      const currentVal = subheadSelect.value;
      let available = (subheadsByHead[headId] || []).slice();
      let options = ['Misc', 'Equipment'];
      available.forEach(s => {
          if (s && !options.includes(s)) options.push(s);
      });

      subheadSelect.innerHTML = '';
      options.forEach(opt => {
          const optionEl = document.createElement('option');
          optionEl.value = opt;
          optionEl.textContent = opt + (opt === 'Misc' ? ' (Default)' : '');
          if (opt === currentVal || (!currentVal && opt === 'Misc')) {
              optionEl.selected = true;
          }
          subheadSelect.appendChild(optionEl);
      });

      syncItemSubheads(options, subheadSelect.value);
  }

  function syncItemSubheads(options, selectedVal) {
      document.querySelectorAll('.item-subhead-select').forEach(sel => {
          const current = sel.value;
          sel.innerHTML = '';
          options.forEach(opt => {
              const el = document.createElement('option');
              el.value = opt;
              el.textContent = opt;
              if (opt === current || (!current && opt === selectedVal)) el.selected = true;
              sel.appendChild(el);
          });
      });
  }

  $(document).on('change', '#case_subhead', function() {
      const val = $(this).val();
      document.querySelectorAll('.item-subhead-select').forEach(sel => {
          sel.value = val;
      });
  });

  $('#pcs_hed_id').on('change', function() {
      const headId = $(this).val();
      const $input = $('#pcs_minute');
      const $hint = $('#minute-hint');
      if(headId) {
          updateSubheadDropdown(headId);
          $hint.html('<i class="fas fa-spinner fa-spin"></i> Fetching next available minute...');
          $.ajax({
              url: '/get-next-minute/' + headId,
              type: "GET",
              success: function(data) {
                  $input.val(data.next_minute);
                  $hint.html(`Last minute: <strong style="color:#0f172a">${data.last_minute}</strong> &nbsp;|&nbsp; Suggested: <strong style="color:#059669">${data.next_minute}</strong>`);
                  fireToast(`Auto-filled minute: ${data.next_minute}`, 'success');
              },
              error: function() {
                  $hint.html('<span style="color:#ef4444">Could not auto-fetch minute number.</span>');
              }
          });
      }
  });

  // =====================================================
  //  ITEMS + ACCESS TYPE & SUBTYPE MAPPING
  // =====================================================
  const subtypeOptionsByType = {
      7: ['Parts', 'Tools / Test Equipment', 'Machinery / Equipment', 'IT Equipment', 'Software', 'Furniture', 'Appliance', 'Other'],
      2: ['Parts', 'Chemicals', 'Raw Material', 'Stationary', 'Cleaning Material', 'POL', 'Other'],
      3: ['Equipment Installation', 'Equipment Repairs & Maintenance', 'Travelling/Boarding/Lodging', 'Travel', 'Meals/Refreshments', 'Consultancy', 'Other']
  };

  function handleItemTypeChange(selectEl, idx) {
      const val = parseInt(selectEl.value);
      const card = selectEl.closest('.dyn-row');
      if (!card) return;

      const subtypeSelect = card.querySelector('.item-subtype-select');
      const invasstSelect = card.querySelector('.item-invasst-select');

      if (subtypeSelect) {
          const list = subtypeOptionsByType[val] || subtypeOptionsByType[7];
          const currSub = subtypeSelect.value;
          subtypeSelect.innerHTML = '';
          list.forEach(item => {
              const opt = document.createElement('option');
              opt.value = item;
              opt.textContent = item;
              if (item === currSub || (!currSub && list.indexOf(item) === 0)) opt.selected = true;
              subtypeSelect.appendChild(opt);
          });
      }

      if (invasstSelect) {
          if (val === 3) {
              invasstSelect.disabled = true;
              invasstSelect.value = '';
          } else {
              invasstSelect.disabled = false;
              if (!invasstSelect.value) {
                  invasstSelect.value = (val === 2 ? '5' : '6');
              }
          }
      }
  }

  // =====================================================
  //  ITEMS MANAGEMENT
  // =====================================================
  let itemCounter = 1;
  let firms = []; // { id, name }

  function getItems() {
      const rows = document.querySelectorAll('#items-list .dyn-row');
      const items = [];
      rows.forEach((r, i) => {
          const desc = r.querySelector('.item-desc')?.value || '';
          const qty = r.querySelector('.item-qty')?.value || '1';
          items.push({ idx: r.getAttribute('data-idx'), desc, qty, serial: i+1 });
      });
      return items;
  }

  function addItemRow() {
      const list = document.getElementById('items-list');
      const rows = list.querySelectorAll('.dyn-row');
      const newIdx = itemCounter++;
      const serial = rows.length + 1;
      const type = "{{ $type }}";

      const row = document.createElement('div');
      row.setAttribute('data-idx', newIdx);
      
      if (type === 'Ps') {
          row.className = 'dyn-row dyn-row-card p-3 mb-2 rounded';
          row.style.background = '#ffffff';
          row.style.border = '1.5px solid #e2e8f0';
          row.style.display = 'block';

          row.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="font-weight-bold rajdhani text-dark" style="font-size:13px;"><i class="fas fa-box-open text-primary mr-1"></i> Item #<span class="row-serial">${serial}</span></span>
              <button type="button" class="btn-rm-row" onclick="removeItemRow(this)"><i class="fas fa-times"></i></button>
            </div>
            <div class="row">
              <div class="col-md-7">
                <label class="soft-label mb-1">Item Description <span class="text-danger">*</span></label>
                <input type="text" name="items[${newIdx}][desc]" class="soft-input item-desc" placeholder="Item description / technical specifications" required oninput="rebuildQuotationBody()">
              </div>
              <div class="col-md-2">
                <label class="soft-label mb-1">Quantity <span class="text-danger">*</span></label>
                <input type="number" name="items[${newIdx}][qty]" class="soft-input item-qty" value="1" min="1" required oninput="rebuildQuotationBody()">
              </div>
              <div class="col-md-3">
                <label class="soft-label mb-1">Denomination (Unit)</label>
                <select name="items[${newIdx}][unit]" class="soft-select">
                  <option value="num" selected>num (Numbers)</option>
                  <option value="set">set (Sets)</option>
                  <option value="job">job (Job/Work)</option>
                  <option value="svc">svc (Service)</option>
                  <option value="day">day (Days)</option>
                  <option value="kg">kg (Kilogram)</option>
                  <option value="ltr">ltr (Liters)</option>
                  <option value="m">m (Meters)</option>
                  <option value="box">box (Box)</option>
                  <option value="pkt">pkt (Packets)</option>
                </select>
              </div>
            </div>
            <div class="row mt-2">
              <div class="col-md-4">
                <label class="soft-label mb-1">Type</label>
                <select name="items[${newIdx}][type]" class="soft-select item-type-select" onchange="handleItemTypeChange(this, ${newIdx})">
                  <option value="7" selected>Permanent (7)</option>
                  <option value="2">Consumable (2)</option>
                  <option value="3">Service (3)</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="soft-label mb-1">Subtype</label>
                <select name="items[${newIdx}][subtype]" class="soft-select item-subtype-select">
                  <option value="Parts" selected>Parts</option>
                  <option value="Tools / Test Equipment">Tools / Test Equipment</option>
                  <option value="Machinery / Equipment">Machinery / Equipment</option>
                  <option value="IT Equipment">IT Equipment</option>
                  <option value="Software">Software</option>
                  <option value="Furniture">Furniture</option>
                  <option value="Appliance">Appliance</option>
                  <option value="Chemicals">Chemicals</option>
                  <option value="Raw Material">Raw Material</option>
                  <option value="Stationary">Stationary</option>
                  <option value="Cleaning Material">Cleaning Material</option>
                  <option value="POL">POL</option>
                  <option value="Equipment Installation">Equipment Installation</option>
                  <option value="Equipment Repairs & Maintenance">Equipment Repairs & Maintenance</option>
                  <option value="Other">Other</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="soft-label mb-1">Inv / Asset (Inv/Asst)</label>
                <select name="items[${newIdx}][inv_asst]" class="soft-select item-invasst-select">
                  <option value="5">Inventory (5)</option>
                  <option value="6" selected>Asset (6)</option>
                </select>
              </div>
            </div>
            <input type="hidden" name="items[${newIdx}][subhead]" value="Equipment">
          `;
      } else if (type === 'Rb') {
          row.className = 'dyn-row dyn-row-tada p-3 mb-2 rounded';
          row.style.background = '#f8fafc';
          row.style.border = '1.5px solid #e2e8f0';
          row.style.display = 'block';

          const empOpts = document.getElementById('emp_select_0') ? document.getElementById('emp_select_0').innerHTML : '';
          const currentSubhead = document.getElementById('case_subhead')?.value || 'Misc';

          row.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div style="font-size:13px;font-weight:700;color:#0f172a;font-family:'Rajdhani',sans-serif;">
                <i class="fas fa-user-check text-warning mr-1"></i> Line #<span class="row-serial">${serial}</span>: TA/DA Employee Selection
              </div>
              <button type="button" class="btn-rm-row" onclick="removeItemRow(this)"><i class="fas fa-times"></i></button>
            </div>
            <div class="row align-items-end mb-2">
              <div class="col-md-8">
                <label class="soft-label mb-1">Select Employee (Division Contracts)</label>
                <select name="items[${newIdx}][emp_id]" id="emp_select_${newIdx}" class="soft-select emp-selector">
                  ${empOpts}
                </select>
              </div>
              <div class="col-md-4">
                <button type="button" class="btn-add-row w-100 justify-content-center" style="background:#fef3c7; color:#92400e; border-color:#fde68a; height:36px;" onclick="fetchTadaEmpDetails(${newIdx})">
                  <i class="fas fa-magic"></i> Add Emp Details
                </button>
              </div>
            </div>
            <div class="row">
              <div class="col-md-7">
                <label class="soft-label mb-1" style="font-size:10px;">Item Description (Auto-filled)</label>
                <textarea name="items[${newIdx}][desc]" id="item_desc_${newIdx}" class="soft-input item-desc" rows="2" placeholder="Select employee & click 'Add Emp Details' above..." required style="min-height:54px; font-size:12px;"></textarea>
              </div>
              <div class="col-md-2">
                <label class="soft-label mb-1" style="font-size:10px;">Qty</label>
                <input type="number" name="items[${newIdx}][qty]" id="item_qty_${newIdx}" class="soft-input item-qty" value="1" min="1" readonly oninput="calculateDirectItemTotals()">
                <input type="hidden" name="items[${newIdx}][unit]" value="num">
              </div>
              <div class="col-md-3">
                <label class="soft-label mb-1" style="font-size:10px;">Price (Auto-calc)</label>
                <input type="number" step="any" name="items[${newIdx}][price]" id="item_price_${newIdx}" class="soft-input item-price font-weight-bold text-success" placeholder="PKR 0" oninput="calculateDirectItemTotals()" required>
              </div>
            </div>
            <div class="row mt-2">
              <div class="col-md-4">
                <label class="soft-label mb-1">Type</label>
                <select name="items[${newIdx}][type]" class="soft-select">
                  <option value="3" selected>Service (3)</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="soft-label mb-1">Subtype</label>
                <select name="items[${newIdx}][subtype]" class="soft-select">
                  <option value="Travelling/Boarding/Lodging" selected>Travelling/Boarding/Lodging</option>
                  <option value="Travel">Travel</option>
                  <option value="Meals/Refreshments">Meals/Refreshments</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="soft-label mb-1">Subhead</label>
                <select name="items[${newIdx}][subhead]" class="soft-select item-subhead-select">
                  <option value="Misc" ${currentSubhead === 'Misc' ? 'selected' : ''}>Misc</option>
                  <option value="Equipment" ${currentSubhead === 'Equipment' ? 'selected' : ''}>Equipment</option>
                </select>
              </div>
            </div>
          `;
      } else {
          // Pt: Incidental
          row.className = 'dyn-row dyn-row-card p-3 mb-2 rounded';
          row.style.background = '#ffffff';
          row.style.border = '1.5px solid #e2e8f0';
          row.style.display = 'block';

          const currentSubhead = document.getElementById('case_subhead')?.value || 'Misc';

          row.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="font-weight-bold rajdhani text-dark" style="font-size:13px;"><i class="fas fa-receipt text-success mr-1"></i> Incidental Line #<span class="row-serial">${serial}</span></span>
              <button type="button" class="btn-rm-row" onclick="removeItemRow(this)"><i class="fas fa-times"></i></button>
            </div>
            <div class="row">
              <div class="col-md-5">
                <label class="soft-label mb-1">Item Description <span class="text-danger">*</span></label>
                <input type="text" name="items[${newIdx}][desc]" class="soft-input item-desc" placeholder="Item description / expense details" required>
              </div>
              <div class="col-md-2">
                <label class="soft-label mb-1">Qty <span class="text-danger">*</span></label>
                <input type="number" name="items[${newIdx}][qty]" class="soft-input item-qty" placeholder="Qty" value="1" min="1" oninput="calculateDirectItemTotals()" required>
              </div>
              <div class="col-md-2">
                <label class="soft-label mb-1">Unit</label>
                <select name="items[${newIdx}][unit]" class="soft-select">
                  <option value="num" selected>num</option>
                  <option value="set">set</option>
                  <option value="job">job</option>
                  <option value="svc">svc</option>
                  <option value="day">day</option>
                  <option value="kg">kg</option>
                  <option value="ltr">ltr</option>
                  <option value="m">m</option>
                  <option value="box">box</option>
                  <option value="pkt">pkt</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="soft-label mb-1">Unit Price (PKR) <span class="text-danger">*</span></label>
                <input type="number" step="any" name="items[${newIdx}][price]" class="soft-input item-price" placeholder="Price (PKR)" oninput="calculateDirectItemTotals()" required>
              </div>
            </div>
            <div class="row mt-2">
              <div class="col-md-4">
                <label class="soft-label mb-1">Type</label>
                <select name="items[${newIdx}][type]" class="soft-select item-type-select" onchange="handleItemTypeChange(this, ${newIdx})">
                  <option value="2" selected>Consumable (2)</option>
                  <option value="7">Permanent (7)</option>
                  <option value="3">Service (3)</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="soft-label mb-1">Subtype</label>
                <select name="items[${newIdx}][subtype]" class="soft-select item-subtype-select">
                  <option value="Parts" selected>Parts</option>
                  <option value="Tools / Test Equipment">Tools / Test Equipment</option>
                  <option value="Stationary">Stationary</option>
                  <option value="Chemicals">Chemicals</option>
                  <option value="Cleaning Material">Cleaning Material</option>
                  <option value="Raw Material">Raw Material</option>
                  <option value="Equipment Repairs & Maintenance">Equipment Repairs & Maintenance</option>
                  <option value="Other">Other</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="soft-label mb-1">Subhead</label>
                <select name="items[${newIdx}][subhead]" class="soft-select item-subhead-select">
                  <option value="Misc" ${currentSubhead === 'Misc' ? 'selected' : ''}>Misc</option>
                  <option value="Equipment" ${currentSubhead === 'Equipment' ? 'selected' : ''}>Equipment</option>
                </select>
              </div>
            </div>
          `;
      }

      list.appendChild(row);
      updateItemSerials();
      if (type === 'Ps') {
          rebuildQuotationBody();
      } else {
          calculateDirectItemTotals();
      }
  }

  function removeItemRow(btn) {
      const row = btn.closest('.dyn-row');
      const list = document.getElementById('items-list');
      if (list.querySelectorAll('.dyn-row').length <= 1) return;
      row.style.opacity = '0';
      setTimeout(() => { 
          row.remove(); 
          updateItemSerials(); 
          if ("{{ $type }}" === 'Ps') {
              rebuildQuotationBody(); 
          } else {
              calculateDirectItemTotals();
          }
      }, 200);
  }

  function calculateDirectItemTotals() {
      let total = 0;
      document.querySelectorAll('#items-list .dyn-row').forEach(row => {
          const qty = parseFloat(row.querySelector('.item-qty')?.value || 0);
          const price = parseFloat(row.querySelector('.item-price')?.value || 0);
          total += (qty * price);
      });
      const disp = document.getElementById('live-total-display');
      if (disp) disp.textContent = 'PKR ' + total.toLocaleString(undefined, {maximumFractionDigits: 2});
  }

  function updateItemSerials() {
      const rows = document.querySelectorAll('#items-list .dyn-row');
      rows.forEach((r, i) => {
          const serialEl = r.querySelector('.row-serial');
          if (serialEl) serialEl.textContent = i + 1;
          const btn = r.querySelector('.btn-rm-row');
          if (btn) btn.style.visibility = rows.length <= 1 ? 'hidden' : 'visible';
      });
  }

  @if($type === 'Rb')
  // ---- TA/DA AJAX Helper ----
  function fetchTadaEmpDetails(idx) {
      const empSelect = document.getElementById(`emp_select_${idx}`);
      const empId = empSelect ? empSelect.value : '';
      if (!empId) {
          if (typeof Swal !== 'undefined') {
              Swal.fire('Employee Required', 'Please select an employee first', 'warning');
          } else {
              alert('Please select an employee first');
          }
          return;
      }

      const url = "{{ url('/purchase/tada/employee-details') }}/" + encodeURIComponent(empId);
      fetch(url, {
          headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
          }
      })
      .then(async res => {
          const data = await res.json();
          if (!res.ok || !data.success) {
              const msg = data.error || data.message || 'Failed to fetch employee details';
              if (typeof Swal !== 'undefined') {
                  Swal.fire('TA/DA Validation Error', msg, 'error');
              } else {
                  alert(msg);
              }
              return;
          }

          // Auto-fill description and price
          const descEl = document.getElementById(`item_desc_${idx}`);
          const priceEl = document.getElementById(`item_price_${idx}`);
          const qtyEl = document.getElementById(`item_qty_${idx}`);

          if (descEl) descEl.value = data.description;
          if (priceEl) priceEl.value = data.tada_amount;
          if (qtyEl) qtyEl.value = 1;

          calculateDirectItemTotals();
          
          if (typeof Swal !== 'undefined') {
              Swal.fire({
                  title: 'Employee Details Added',
                  text: `${data.emp_name} (Grade: ${data.grade}) -> TA/DA PKR ${data.tada_amount}`,
                  icon: 'success',
                  timer: 1800,
                  showConfirmButton: false
              });
          }
      })
      .catch(err => {
          console.error(err);
          alert('Unable to retrieve employee TA/DA details.');
      });
  }
  @endif

  // =====================================================
  //  FIRM / QUOTATION GRID & NO-QUOTES MANAGEMENT
  // =====================================================
  function handleQuoteTypeChange() {
      const qtype = document.getElementById('newCaseQuoteType')?.value;
      const taxTypeContainer = document.getElementById('taxTypeContainer');
      const taxPercentContainer = document.getElementById('taxPercentContainer');
      const taxPercentInput = document.getElementById('newCaseTaxPercent');
      const taxRow = document.getElementById('quotationTaxRow');

      if (qtype === '1') {
          // 1 - Without Tax (Net Quote)
          if (taxPercentInput) taxPercentInput.value = 0;
          if (taxTypeContainer) taxTypeContainer.style.display = 'none';
          if (taxPercentContainer) taxPercentContainer.style.display = 'none';
          if (taxRow) taxRow.style.display = 'none';
      } else {
          // 2 - With Tax (GST/SST)
          if (taxTypeContainer) taxTypeContainer.style.display = 'flex';
          if (taxPercentContainer) taxPercentContainer.style.display = 'flex';
          if (taxRow) taxRow.style.display = 'table-row';
          const taxType = document.getElementById('newCaseTaxType')?.value || 'GST';
          if (taxPercentInput && parseFloat(taxPercentInput.value) === 0) {
              taxPercentInput.value = (taxType === 'SST' ? 13 : 18);
          }
      }
      calculateQuotationTotals();
  }

  function addFirmColumn() {
      const sel = document.getElementById('firmSelector');
      const firmId = sel.value;
      const firmName = sel.options[sel.selectedIndex]?.getAttribute('data-name');
      if (!firmId || !firmName) { fireToast('Please select a firm first', 'error'); return; }
      if (firms.find(f => f.id == firmId)) { fireToast('This firm is already added', 'error'); return; }
      firms.push({ id: firmId, name: firmName });
      sel.value = '';

      // If this firm was selected under No-Quotes, unselect and disable it
      const nqSelect = document.getElementById('notReceivedFirmsSelect');
      if (nqSelect) {
          const opt = nqSelect.querySelector(`option[value="${firmId}"]`);
          if (opt) {
              opt.selected = false;
              opt.disabled = true;
          }
          const count = ($(nqSelect).val() || []).length;
          $('#noQuotesCount').text(count + (count === 1 ? ' Firm' : ' Firms'));
      }

      rebuildQuotationGrid();
      fireToast(`${firmName} added`, 'success');
  }

  function removeFirm(firmId) {
      firms = firms.filter(f => f.id != firmId);
      const nqSelect = document.getElementById('notReceivedFirmsSelect');
      if (nqSelect) {
          const opt = nqSelect.querySelector(`option[value="${firmId}"]`);
          if (opt) opt.disabled = false;
      }
      rebuildQuotationGrid();
  }

  function rebuildQuotationGrid() {
      const headerRow = document.querySelector('#quotationTable thead tr');
      if (!headerRow) return;
      while (headerRow.children.length > 3) headerRow.removeChild(headerRow.lastChild);
      firms.forEach(f => {
          const th = document.createElement('th');
          th.style.cssText = 'padding:8px 10px; color:#2563eb; font-size:10px; text-transform:uppercase; letter-spacing:0.5px; font-weight:700; text-align:center; min-width:140px; white-space:nowrap; background:#eff6ff;';
          th.innerHTML = `
              <div class="d-flex align-items-center justify-content-center gap-1">
                  <span>${f.name}</span>
                  <button type="button" onclick="removeFirm('${f.id}')" style="background:none;border:none;color:#ef4444;font-size:11px;cursor:pointer;" title="Remove Firm"><i class="fas fa-times-circle"></i></button>
              </div>
              <div class="mt-1">
                  <label for="qfile_${f.id}" class="badge badge-light p-1" style="cursor:pointer; font-size:9px; border: 1px solid #cbd5e1; font-weight:600;" id="qfile_lbl_${f.id}" title="Attach Quote Scan">
                      <i class="fas fa-paperclip mr-1"></i> <span id="qfile_txt_${f.id}">Attach Quote</span>
                  </label>
                  <input type="file" id="qfile_${f.id}" name="quote_files[${f.id}]" style="display:none;" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" onchange="handleQuoteFileSelect(this, '${f.id}')">
              </div>
          `;
          headerRow.appendChild(th);
      });

      rebuildQuotationBody();

      const foot = document.getElementById('quotationFoot');
      if (foot) {
          const rows = foot.querySelectorAll('tr');
          rows.forEach(r => {
              while (r.children.length > 1) r.removeChild(r.lastChild);
          });

          firms.forEach(f => {
              // Subtotal cell
              const tdSub = document.createElement('td');
              tdSub.style.cssText = 'padding:8px 10px; text-align:center; font-size:12px; color:#334155; font-weight:600;';
              tdSub.id = `firm-subtotal-${f.id}`;
              tdSub.textContent = '0';
              rows[0].appendChild(tdSub);

              // Tax cell
              const tdTax = document.createElement('td');
              tdTax.style.cssText = 'padding:8px 10px; text-align:center; font-size:11px; color:#64748b;';
              tdTax.id = `firm-tax-${f.id}`;
              tdTax.textContent = '0';
              rows[1].appendChild(tdTax);

              // Total cell
              const tdTot = document.createElement('td');
              tdTot.style.cssText = 'padding:10px 12px; text-align:center; font-family:"Rajdhani",sans-serif; font-size:15px; font-weight:800; color:#0f172a;';
              tdTot.id = `firm-total-${f.id}`;
              tdTot.textContent = '0';
              rows[2].appendChild(tdTot);
          });
      }
      calculateQuotationTotals();
  }

  function handleQuoteFileSelect(input, firmId) {
      const lbl = document.getElementById(`qfile_txt_${firmId}`);
      const badge = document.getElementById(`qfile_lbl_${firmId}`);
      if (input.files && input.files[0]) {
          const name = input.files[0].name;
          const shortName = name.length > 12 ? name.substring(0, 10) + '..' : name;
          if (lbl) lbl.textContent = shortName;
          if (badge) {
              badge.className = 'badge badge-success p-1';
              badge.title = name;
          }
          fireToast(`Document attached for firm`, 'success');
      } else {
          if (lbl) lbl.textContent = 'Attach Quote';
          if (badge) {
              badge.className = 'badge badge-light p-1';
          }
      }
  }

  function rebuildQuotationBody() {
      const body = document.getElementById('quotationBody');
      if (!body) return;
      body.innerHTML = '';
      const items = getItems();
      items.forEach((item, i) => {
          const tr = document.createElement('tr');
          tr.style.borderBottom = '1px solid #e2e8f0';
          let html = `<td style="padding:8px 10px; color:#64748b; font-size:11px; text-align:center;">${i+1}</td>`;
          html += `<td style="padding:8px 10px; color:#0f172a; font-size:12px;">${item.desc || '<span class="text-muted">—</span>'}</td>`;
          html += `<td style="padding:8px 10px; color:#2563eb; font-size:12px; text-align:center; font-weight:700;">${item.qty}</td>`;
          firms.forEach(f => {
              html += `<td style="padding:6px 8px; text-align:center;">
                  <input type="number" name="quotations[${f.id}][${item.idx}]" class="soft-input firm-price-input" data-firm="${f.id}" 
                         style="width:100%; text-align:center; font-size:12px; padding:4px 8px; height:32px;" placeholder="0" min="0" oninput="calculateQuotationTotals()">
              </td>`;
          });
          tr.innerHTML = html;
          body.appendChild(tr);
      });
      calculateQuotationTotals();
  }

  function calculateQuotationTotals() {
      const qtype = document.getElementById('newCaseQuoteType')?.value;
      const taxType = document.getElementById('newCaseTaxType')?.value || 'GST';
      const taxPercent = (qtype === '1') ? 0 : parseFloat(document.getElementById('newCaseTaxPercent')?.value || 0);

      const taxLabel = document.getElementById('taxRowLabel');
      if (taxLabel) {
          taxLabel.textContent = (qtype === '1') ? 'TAX (0% - Net/Exempt)' : `TAX (${taxType} ${taxPercent}%)`;
      }

      const items = getItems();
      let lowestTotal = Infinity;
      let winningFirmId = null;

      firms.forEach(f => {
          let subtotal = 0;
          items.forEach((item) => {
              const inp = document.querySelector(`input[name="quotations[${f.id}][${item.idx}]"]`);
              const unitPrice = parseFloat(inp?.value) || 0;
              const qty = parseFloat(item.qty) || 1;
              subtotal += (unitPrice * qty);
          });

          const tax = subtotal * (taxPercent / 100);
          const total = subtotal + tax;

          const elSub = document.getElementById(`firm-subtotal-${f.id}`);
          if (elSub) elSub.textContent = subtotal.toLocaleString(undefined, {maximumFractionDigits: 2});

          const elTax = document.getElementById(`firm-tax-${f.id}`);
          if (elTax) elTax.textContent = tax.toLocaleString(undefined, {maximumFractionDigits: 2});

          const elTot = document.getElementById(`firm-total-${f.id}`);
          if (elTot) elTot.textContent = total.toLocaleString(undefined, {maximumFractionDigits: 2});

          if (total > 0 && total < lowestTotal) {
              lowestTotal = total;
              winningFirmId = f.id;
          }
      });

      // Highlight winner
      firms.forEach(f => {
          const elTot = document.getElementById(`firm-total-${f.id}`);
          if (elTot) {
              if (winningFirmId && f.id == winningFirmId) {
                  elTot.style.color = '#059669';
                  elTot.style.background = '#ecfdf5';
              } else {
                  elTot.style.color = '#0f172a';
                  elTot.style.background = 'transparent';
              }
          }
      });

      const displayTotal = lowestTotal === Infinity ? 0 : lowestTotal;
      const liveDisplay = document.getElementById('live-total-display');
      if (liveDisplay) liveDisplay.textContent = 'PKR ' + displayTotal.toLocaleString(undefined, {maximumFractionDigits: 2});
  }

  // Sync quotation grid when item desc/qty changes
  document.addEventListener('input', function(e) {
      if (e.target.classList.contains('item-desc') || e.target.classList.contains('item-qty')) {
          rebuildQuotationBody();
      }
  });

  // Helper: HTML Escaping
  function escapeHtml(text) {
      if (!text) return '';
      return String(text)
          .replace(/&/g, "&amp;")
          .replace(/</g, "&lt;")
          .replace(/>/g, "&gt;")
          .replace(/"/g, "&quot;")
          .replace(/'/g, "&#039;");
  }

  // Filter Comparison Firms
  function filterComparisonFirms(q) {
      const select = document.getElementById('firmSelector');
      if (!select) return;
      const term = (q || '').toLowerCase().trim();
      let firstMatch = null;
      for (let i = 0; i < select.options.length; i++) {
          const opt = select.options[i];
          if (!opt.value) continue;
          const text = (opt.getAttribute('data-name') || opt.text).toLowerCase();
          const matches = !term || text.includes(term);
          opt.style.display = matches ? '' : 'none';
          if (matches && !firstMatch) firstMatch = opt;
      }
      if (firstMatch && term) {
          select.value = firstMatch.value;
      }
  }

  // Filter No-Quote Firms
  function filterNoQuoteFirms(q) {
      const select = document.getElementById('noQuoteFirmSelector');
      if (!select) return;
      const term = (q || '').toLowerCase().trim();
      let firstMatch = null;
      for (let i = 0; i < select.options.length; i++) {
          const opt = select.options[i];
          if (!opt.value) continue;
          const text = (opt.getAttribute('data-name') || opt.text).toLowerCase();
          const matches = !term || text.includes(term);
          opt.style.display = matches ? '' : 'none';
          if (matches && !firstMatch) firstMatch = opt;
      }
      if (firstMatch && term) {
          select.value = firstMatch.value;
      }
  }

  // Selected No-Quote Firms Set
  const selectedNoQuoteFirms = new Set();

  function addNoQuoteFirm() {
      const select = document.getElementById('noQuoteFirmSelector');
      if (!select) return;
      const firmId = select.value;
      if (!firmId) {
          fireToast('Please select a firm from the dropdown first.', 'warning');
          return;
      }

      if (selectedNoQuoteFirms.has(firmId)) {
          fireToast('This firm is already added to No-Quotes.', 'warning');
          return;
      }

      // Check if firm is already in participating quotes table
      if (window.firms && window.firms.some(f => f.id == firmId)) {
          fireToast('This firm has already submitted a quotation in the table above!', 'warning');
          return;
      }

      selectedNoQuoteFirms.add(firmId);
      updateNoQuotesUI();

      // Reset selection
      select.value = '';
      const sInput = document.getElementById('noQuoteSearch');
      if (sInput) sInput.value = '';
      filterNoQuoteFirms('');
  }

  function removeNoQuoteFirm(firmId) {
      selectedNoQuoteFirms.delete(String(firmId));
      selectedNoQuoteFirms.delete(Number(firmId));
      updateNoQuotesUI();
  }

  function updateNoQuotesUI() {
      const container = document.getElementById('noQuotesContainer');
      const countEl = document.getElementById('noQuotesCount');
      const select = document.getElementById('noQuoteFirmSelector');
      if (!container) return;

      container.innerHTML = '';

      if (selectedNoQuoteFirms.size === 0) {
          container.innerHTML = `<span id="noQuotesEmpty" class="text-muted small font-italic px-2"><i class="fas fa-info-circle mr-1 text-primary"></i> No unreceived firms added yet. Search or select a firm above and click <strong>ADD NO-QUOTE</strong>.</span>`;
          if (countEl) countEl.textContent = '0 Selected';
          return;
      }

      selectedNoQuoteFirms.forEach(firmId => {
          let firmName = 'Firm #' + firmId;
          if (select) {
              for (let opt of select.options) {
                  if (opt.value == firmId) {
                      firmName = opt.getAttribute('data-name') || opt.text;
                      break;
                  }
              }
          }

          const chip = document.createElement('div');
          chip.className = 'no-quote-chip';
          chip.innerHTML = `
              <i class="fas fa-times-circle text-danger mr-1"></i>
              <span>${escapeHtml(firmName)}</span>
              <button type="button" class="btn-remove-chip" onclick="removeNoQuoteFirm('${firmId}')" title="Remove">&times;</button>
              <input type="hidden" name="not_received_firms[]" value="${firmId}">
          `;
          container.appendChild(chip);
      });

      if (countEl) {
          countEl.textContent = `${selectedNoQuoteFirms.size} Selected`;
      }
  }

  // Keyboard shortcut: Press enter in search inputs to trigger add
  document.getElementById('noQuoteSearch')?.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
          e.preventDefault();
          addNoQuoteFirm();
      }
  });

  document.getElementById('firmSearchFilter')?.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
          e.preventDefault();
          addFirmColumn();
      }
  });

  // Project Head & Subhead Management
  const subheadsByHead = @json($subheadsByHead ?? []);

  function handleProjectHeadChange(hedId) {
      const subheadSelect = document.getElementById('case_subhead');
      if (!subheadSelect || subheadSelect.tagName !== 'SELECT') return;

      subheadSelect.innerHTML = `
          <option value="Misc" selected>Misc (Default for {{ $type === 'Pt' ? 'Incidental' : 'TA/DA' }})</option>
          <option value="Equipment">Equipment</option>
      `;

      if (hedId && subheadsByHead[hedId]) {
          subheadsByHead[hedId].forEach(sh => {
              const name = (sh.sbh_name || '').trim();
              if (name && name !== 'Misc' && name !== 'Equipment' && name !== 'General') {
                  const opt = document.createElement('option');
                  opt.value = name;
                  opt.textContent = name;
                  subheadSelect.appendChild(opt);
              }
          });
      }
      handleSubheadChange(subheadSelect.value);
  }

  function handleSubheadChange(val) {
      const preview = document.getElementById('subheadPreview');
      if (preview) preview.textContent = val;

      document.querySelectorAll('.item-subhead-select').forEach(el => {
          el.value = val;
      });
  }

  // Init
  document.addEventListener('DOMContentLoaded', () => {
      updateItemSerials();
      if ("{{ $type }}" === 'Ps') {
          handleQuoteTypeChange();
      }
  });
</script>

@endsection
