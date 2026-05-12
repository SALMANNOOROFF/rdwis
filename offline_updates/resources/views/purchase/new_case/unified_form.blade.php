@extends('welcome')
@section('content')

@php
    $themes = [
        'material'    => ['icon' => 'fa-boxes', 'color' => '#3b82f6'],
        'consultancy' => ['icon' => 'fa-user-tie', 'color' => '#8b5cf6'],
        'services'    => ['icon' => 'fa-handshake', 'color' => '#0ea5e9'],
        'civil'       => ['icon' => 'fa-building', 'color' => '#f59e0b'],
        'training'    => ['icon' => 'fa-graduation-cap', 'color' => '#10b981'],
        'tada'        => ['icon' => 'fa-plane-departure', 'color' => '#6366f1'],
        'transport'   => ['icon' => 'fa-truck', 'color' => '#ef4444'],
        'books'       => ['icon' => 'fa-book', 'color' => '#d946ef'],
        'license'     => ['icon' => 'fa-key', 'color' => '#14b8a6'],
        'internet'    => ['icon' => 'fa-wifi', 'color' => '#3b82f6'],
        'publishing'  => ['icon' => 'fa-print', 'color' => '#8b5cf6'],
        'stationery'  => ['icon' => 'fa-pencil-alt', 'color' => '#64748b'],
    ];
    $theme = $themes[$type] ?? ['icon' => 'fa-file-alt', 'color' => '#64748b'];
@endphp

<div class="content-wrapper p-0">
  <div class="container-fluid p-0">
    <div class="sinc-wrapper" style="min-height:100vh;background:var(--rd-bg);font-family:'DM Sans',sans-serif;padding:12px 24px 32px">
      <style>
        .sinc-card { background:var(--rd-surface); border:1px solid var(--rd-border); border-radius:12px; box-shadow: 0 8px 32px rgba(0,0,0,0.3); overflow:hidden; }
        .sinc-card-head { display:flex; align-items:center; justify-content:space-between; padding:12px 20px; border-bottom:1px solid var(--rd-border2); background:rgba(255,255,255,0.02); }
        .sinc-card-title { font-size:0.9rem; font-weight:800; color:var(--rd-text1); display:flex; align-items:center; gap:8px; font-family: 'Rajdhani', sans-serif; letter-spacing: 0.5px; }
        
        .soft-form { padding:20px; }
        .soft-row { display:grid; grid-template-columns:1fr 1fr; gap:15px; }
        .soft-row-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:15px; }
        .soft-group { margin-bottom:12px; }

        @media (min-width: 992px) {
          .soft-split-grid { display: grid; grid-template-columns: 1fr 340px; gap: 25px; align-items: start; }
        }

        @media (max-width: 991.98px) {
          .soft-row, .soft-row-3, .soft-split-grid { grid-template-columns: 1fr; gap: 8px; }
          .sinc-wrapper { padding: 10px 10px 32px !important; }
        }
        
        .soft-label { font-size:0.68rem; font-weight:700; color:var(--rd-text3); margin-bottom:3px; display:block; text-transform:uppercase; letter-spacing:0.3px; }
        
        .soft-input, .soft-select, .soft-textarea {
          width:100%; border:1.5px solid var(--rd-border); border-radius:8px; background:var(--rd-surface2); padding:4px 10px; font-size:0.75rem; height: 32px; color:var(--rd-text1); font-family:'DM Sans',sans-serif; transition:all 0.2s ease;
        }
        .soft-select { cursor:pointer; appearance:none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 10px center; padding-right:28px; }
        
        .soft-input:focus, .soft-select:focus, .soft-textarea:focus { outline:none; border-color:var(--rd-accent); background:var(--rd-surface); box-shadow:0 0 0 3px var(--rd-accent-soft); }
        .soft-textarea { min-height:50px; height:auto; resize:vertical; padding-top: 8px; }
        
        .section-title { font-size:0.75rem; font-weight:800; color:var(--rd-text1); margin:4px 0 10px; display:flex; align-items:center; gap:6px; padding-bottom:4px; border-bottom:1px dashed var(--rd-border2); text-transform: uppercase; letter-spacing: 0.5px; }
        
        .sinc-topbar { display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; flex-wrap: wrap; gap: 10px; border-bottom: 1px solid var(--rd-border); padding-bottom: 12px; }
        .sinc-page-title { font-size:1rem; font-weight:800; color:var(--rd-text1); display:flex; align-items:center; gap:8px; }
        .title-icon { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:0.9rem; }
        
        .btn-submit { display:inline-flex; align-items:center; justify-content:center; gap:6px; padding:8px 20px; border-radius:8px; background:linear-gradient(135deg, var(--rd-accent), var(--rd-accent-dark)); color:#fff; font-size:0.8rem; font-weight:700; border:none; cursor:pointer; box-shadow:0 4px 12px rgba(0,0,0,0.3); transition:all 0.2s ease; width: 100%; max-width: fit-content; }
        .btn-submit:hover { transform:translateY(-1px); box-shadow:0 6px 16px rgba(0,0,0,0.4); }
        
        .badge-draft { background-color:rgba(255,193,7,0.1); color:#ffc107; padding:2px 10px; border-radius:4px; font-size:0.6rem; font-weight:800; border:1px solid rgba(255,193,7,0.2); letter-spacing:0.5px; }
        
        /* Interactive dynamic lists */
        .dyn-list { display:flex; flex-direction:column; gap:6px; }
        .dyn-row { display:grid; grid-template-columns:1fr 140px 32px; gap:8px; align-items:end; padding:8px; border:1px solid var(--rd-border); border-radius:8px; background:rgba(0,0,0,0.1); transition:all 0.2s; }
        @media (max-width: 575.98px) {
          .dyn-row { grid-template-columns: 1fr !important; position: relative; padding-bottom: 45px; }
          .btn-rm-row { position: absolute; bottom: 10px; right: 10px; width: calc(100% - 20px) !important; }
        }
        .dyn-row:focus-within { border-color:var(--rd-accent); background: rgba(0,0,0,0.2); }
        .btn-add-row { display:inline-flex; align-items:center; gap:5px; padding:5px 10px; border-radius:6px; background:rgba(255,255,255,0.05); color:var(--rd-text2); font-weight:700; font-size:0.65rem; border:1px solid var(--rd-border); cursor:pointer; transition:all 0.2s; margin-top:6px; }
        .btn-add-row:hover { background:var(--rd-accent-soft); color:var(--rd-accent); border-color:var(--rd-accent); }
        .btn-rm-row { width:32px; height:32px; border-radius:6px; border:1px solid var(--rd-border); background:rgba(220,53,69,0.05); color:var(--rd-danger); display:inline-flex; align-items:center; justify-content:center; cursor:pointer; transition:all 0.2s; }
        .btn-rm-row:hover { background:var(--rd-danger); color:#fff; }

        /* Actions Footer */
        .form-actions-footer { margin-top:24px; padding:16px; background:var(--rd-surface); border-radius: 12px; border: 1px solid var(--rd-border); display:flex; justify-content:space-between; align-items:center; }
        @media (max-width: 768px) { .form-actions-footer { flex-direction: column; gap: 20px; text-align: center; } }
        
        .side-box { background: var(--rd-surface); border: 1px solid var(--rd-border); border-radius: 12px; padding: 14px; margin-bottom: 12px; }
        .side-label { font-family: 'Rajdhani', sans-serif; font-size: 11px; font-weight: 700; color: var(--rd-accent); letter-spacing: 1px; text-transform: uppercase; margin-bottom: 8px; display: flex; align-items: center; gap: 6px; }
        
        .stats-card { background: rgba(0,0,0,0.2); border: 1px solid var(--rd-border2); border-radius: 10px; padding: 12px; text-align: center; }
        .stats-val { font-family: 'Rajdhani', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--rd-info); line-height: 1; }
        .stats-lbl { font-size: 9px; color: var(--rd-text3); text-transform: uppercase; margin-top: 4px; font-weight: 700; }

        /* Popup Styles (Missing) */
        .sinc-popup-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); backdrop-filter: blur(8px); display: none; align-items: center; justify-content: center; z-index: 9999; opacity: 0; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .sinc-popup-overlay.show { display: flex; opacity: 1; }
        .sinc-popup-box { background: var(--rd-surface); border: 1px solid var(--rd-border); border-radius: 20px; padding: 30px; width: 100%; max-width: 480px; transform: translateY(20px); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 20px 40px rgba(0,0,0,0.5); }
        .sinc-popup-overlay.show .sinc-popup-box { transform: translateY(0); }
        .sinc-popup-icon { width: 60px; height: 60px; border-radius: 15px; background: rgba(255,193,7,0.1); color: #ffc107; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 20px; }
        .sinc-popup-title { font-family: 'Rajdhani', sans-serif; font-size: 1.4rem; font-weight: 800; color: var(--rd-text1); text-align: center; margin-bottom: 12px; }
        .sinc-popup-text { font-size: 0.9rem; color: var(--rd-text2); text-align: center; margin-bottom: 25px; line-height: 1.6; }
        .sinc-popup-btn { background: var(--rd-accent); color: #fff; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 700; font-size: 0.95rem; width: 100%; cursor: pointer; transition: all 0.2s; }
        .sinc-popup-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.3); }

        .btn-action-main { height: 42px; border-radius: 10px; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s; font-size: 0.85rem; cursor: pointer; }
        .btn-draft { background: rgba(255,255,255,0.05); color: var(--rd-text1); border: 1.5px solid var(--rd-border2); padding: 0 20px; }
        .btn-draft:hover { background: rgba(255,255,255,0.1); border-color: <?= $theme['color'] ?>; color: <?= $theme['color'] ?>; }
        
        .btn-release { background: linear-gradient(135deg, <?= $theme['color'] ?>, <?= $theme['color'] ?>cc); color: #fff; border: none; padding: 0 28px; box-shadow: 0 4px 12px <?= $theme['color'] ?>40; }
        .btn-release:hover { transform: translateY(-2px); box-shadow: 0 6px 16px <?= $theme['color'] ?>60; opacity: 0.9; }
      </style>

      <!-- Toasts & Custom Popup -->
      <div id="toast-wrapper" class="toast-container"></div>

      <div class="sinc-popup-overlay" id="sincCustomAlert">
          <div class="sinc-popup-box">
              <div class="sinc-popup-icon"><i class="fas fa-exchange-alt"></i></div>
              <div class="sinc-popup-title">Shifted to Services</div>
              <div class="sinc-popup-text">Because you included Hardware Devices, this case has been automatically adapted into an <b>Outsourced Service</b> case. <br><br>Don't worry, your existing inputs are safe!</div>
              <button class="sinc-popup-btn" onclick="document.getElementById('sincCustomAlert').classList.remove('show')">Got it, thanks!</button>
          </div>
      </div>

      <div style="max-width: 1060px; margin: 0 auto;">
        <!-- Topbar -->
        <div class="sinc-topbar">
        <div class="d-flex align-items-center">
          <div class="mr-4 pr-4 border-right" style="border-color: var(--rd-border2) !important;">
             <span class="small text-muted font-weight-bold rajdhani d-block" style="letter-spacing: 1.5px; font-size: 10px;">CASE ID</span>
             <span class="rajdhani font-weight-bold" style="font-size: 1.4rem; color: var(--rd-accent); line-height: 1;">#{{ $nextId }}</span>
          </div>

          <div class="sinc-page-title d-flex align-items-center">
            <span class="title-icon theme-icon mr-3" style="background: <?= $theme['color'] ?>20; color: <?= $theme['color'] ?>;"><i class="fas <?= $theme['icon'] ?>"></i></span>
            <div class="d-flex flex-column">
                <span class="small text-muted font-weight-bold rajdhani" style="letter-spacing: 1px; font-size: 9px;">PROCUREMENT CATEGORY</span>
                <select id="typeSwitcher" class="form-control form-control-sm rajdhani font-weight-bold" 
                        style="background:transparent; border:none; color:var(--rd-text1); font-size:1.1rem; padding:0; height:auto; cursor:pointer;"
                        onchange="if(this.value) window.location.href='/purchase/new/'+this.value">
                    @foreach([
                        'material' => 'Material / Equipment Purchase',
                        'consultancy' => 'Consultancy Services',
                        'services' => 'Outsourced Services',
                        'civil' => 'Civil Works / Upfit',
                        'training' => 'Training Program',
                        'tada' => 'TA / DA Entry',
                        'transport' => 'Transport Expense',
                        'books' => 'Books & Magazines',
                        'license' => 'License & Software',
                        'internet' => 'Communication & Internet',
                        'publishing' => 'Publishing / Registration',
                        'stationery' => 'Stationery / Consumables'
                    ] as $val => $lbl)
                        <option value="{{ $val }}" {{ $type == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
          </div>
        </div>
        
        <div class="d-flex gap-2 align-items-center">
            <a href="{{ route('purchase.initiation.index') }}" class="btn-cancel px-3 py-1 mr-2" style="display:inline-flex;align-items:center;gap:6px;border:1.5px solid var(--rd-border2);border-radius:8px;background:var(--rd-surface);color:var(--rd-text2);font-size:0.75rem;font-weight:700;text-decoration:none;"> 
              <i class="fas fa-arrow-left"></i> BACK TO HUB
            </a>
            
            <div class="glass-card px-2 py-1 d-flex align-items-center" style="background: rgba(0,123,255,0.05); border: 1px solid rgba(0,123,255,0.1); border-radius: 8px;">
                <i class="fas fa-shield-alt text-primary mr-2" style="font-size: 10px;"></i>
                <span class="rajdhani text-white font-weight-bold" style="font-size: 10px; letter-spacing: 0.5px;">SECURE INITIATION MODE</span>
            </div>
        </div>
      </div>

      <div class="sinc-card">
        <div class="sinc-card-head">
          <div class="sinc-card-title"><i class="fas fa-edit" style="color:<?= $theme['color'] ?>"></i> INITIATE NEW CASE</div>
          <span class="badge-draft">DRAFT MODE</span>
        </div>
        
        <form class="soft-form" id="unifiedPurchaseForm" action="{{ route('purchase.store') }}" method="POST" onsubmit="return handleFormSubmit(event)">
          @csrf
          <input type="hidden" name="pcs_type" value="{{ $type }}">
          
          <div class="soft-split-grid">
            {{-- Left Side: Primary Details --}}
            <div>
              <div class="section-title"><i class="fas fa-info-circle"></i> General Information</div>
              <div class="soft-row-3">
                <div class="soft-group">
                  <label class="soft-label">Creation Date</label>
                  <input type="date" name="pcs_date" class="soft-input" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="soft-group">
                  <label class="soft-label">Minute Number</label>
                  <input type="number" name="pcs_minute" id="pcs_minute" class="soft-input" placeholder="e.g. 1" required>
                  <div id="minute-hint" style="font-size:0.6rem;color:#3b82f6;margin-top:2px;font-weight:600"></div>
                </div>
                <div class="soft-group">
                  <label class="soft-label">Project / Budget Head</label>
                  <select name="pcs_hed_id" id="pcs_hed_id" class="soft-select" required>
                    <option value="" selected disabled>Select Project Head...</option>
                    @foreach($heads as $head)
                      <option value="{{ $head->hed_id }}">{{ $head->hed_code }} - {{ $head->hed_name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="soft-group">
                <label class="soft-label">Case Title / Subject</label>
                <input type="text" name="pcs_title" class="soft-input" placeholder="Define a clear subject for this case" required>
              </div>

              <!-- TYPE SPECIFIC DYNAMIC FIELDS -->
              <div class="section-title mt-4"><i class="fas <?= $theme['icon'] ?>"></i> Details: <?= ucfirst($type) ?> Profile</div>
              
              @if(in_array($type, ['consultancy', 'services']))
                <div class="soft-row">
                  <div class="soft-group">
                    <label class="soft-label">Scope of Work</label>
                    <textarea name="remarks_JSON[scope]" class="soft-textarea" placeholder="Describe scope..."></textarea>
                  </div>
                  <div class="soft-group">
                    <label class="soft-label">Estimated Duration</label>
                    <input type="text" name="remarks_JSON[duration]" class="soft-input" placeholder="e.g. 6 Weeks">
                  </div>
                </div>
              @elseif($type == 'civil')
                <div class="soft-row">
                    <div class="soft-group">
                        <label class="soft-label">Location / Site</label>
                        <input type="text" name="remarks_JSON[location]" class="soft-input" placeholder="e.g. Building A, Floor 2">
                    </div>
                    <div class="soft-group">
                        <label class="soft-label">Work Type</label>
                        <select name="remarks_JSON[work_type]" class="soft-select">
                            <option value="renovation">Renovation</option>
                            <option value="new_upfit">New Upfit / Construction</option>
                            <option value="maintenance">Maintenance / Repair</option>
                        </select>
                    </div>
                </div>
              @elseif($type == 'tada')
                 <div class="soft-row-3">
                    <div class="soft-group">
                        <label class="soft-label">Employee / Person</label>
                        <input type="text" name="remarks_JSON[person]" class="soft-input" placeholder="Name/ID">
                    </div>
                    <div class="soft-group">
                        <label class="soft-label">Destination</label>
                        <input type="text" name="remarks_JSON[destination]" class="soft-input" placeholder="e.g. Islamabad">
                    </div>
                    <div class="soft-group">
                        <label class="soft-label">Days</label>
                        <input type="number" name="remarks_JSON[days]" class="soft-input" value="1">
                    </div>
                 </div>
              @endif

              {{-- Default Item List (for Material, etc.) --}}
              @if(!in_array($type, ['tada', 'transport']))
                <div class="soft-group mt-2">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="soft-label mb-0">Line Items</label>
                    <button type="button" class="btn-add-row" onclick="addItemRow()">
                        <i class="fas fa-plus"></i> ADD ITEM
                    </button>
                  </div>
                  <div id="items-list" class="dyn-list">
                    <div class="dyn-row" data-idx="0" style="grid-template-columns: 40px 1fr 80px 80px 32px;">
                      <div class="text-center" style="display:flex;align-items:center;justify-content:center;color:var(--rd-text3);font-size:11px;font-weight:700;">1</div>
                      <div>
                        <input type="text" name="items[0][desc]" class="soft-input item-desc" placeholder="Item description" required>
                      </div>
                      <div>
                        <input type="number" name="items[0][qty]" class="soft-input item-qty" placeholder="Qty" value="1" min="1" required>
                      </div>
                      <div>
                        <input type="text" name="items[0][unit]" class="soft-input" placeholder="Unit" value="num">
                      </div>
                      <button type="button" class="btn-rm-row" tabindex="-1" style="visibility:hidden"><i class="fas fa-times"></i></button>
                    </div>
                  </div>
                </div>

                {{-- Quotation Grid --}}
                <div class="soft-group mt-3">
                  <div class="section-title"><i class="fas fa-balance-scale"></i> Firm Quotations</div>
                  <div class="d-flex align-items-end gap-2 mb-2">
                    <div style="flex:1;">
                      <label class="soft-label">Select Firm to Add</label>
                      <select id="firmSelector" class="soft-select">
                        <option value="" disabled selected>-- Choose a Firm --</option>
                        @foreach($firms ?? [] as $f)
                          <option value="{{ $f->frm_id }}" data-name="{{ $f->frm_name }}">{{ $f->frm_name }}</option>
                        @endforeach
                      </select>
                    </div>
                    <button type="button" class="btn-add-row" onclick="addFirmColumn()" style="height:32px;">
                      <i class="fas fa-plus"></i> ADD FIRM
                    </button>
                  </div>
                  <div style="overflow-x:auto; border:1px solid var(--rd-border); border-radius:8px;">
                    <table id="quotationTable" style="width:100%; border-collapse:collapse; font-size:12px;">
                      <thead>
                        <tr style="background:var(--rd-surface2); border-bottom:1px solid var(--rd-border);">
                          <th style="padding:8px 10px; color:var(--rd-text3); font-size:9px; text-transform:uppercase; letter-spacing:0.5px; font-weight:700; min-width:40px;">S.No</th>
                          <th style="padding:8px 10px; color:var(--rd-text3); font-size:9px; text-transform:uppercase; letter-spacing:0.5px; font-weight:700; min-width:180px;">Item</th>
                          <th style="padding:8px 10px; color:var(--rd-text3); font-size:9px; text-transform:uppercase; letter-spacing:0.5px; font-weight:700; text-align:center; min-width:60px;">Qty</th>
                          {{-- Firm columns will be dynamically added here --}}
                        </tr>
                      </thead>
                      <tbody id="quotationBody">
                        {{-- Rows populated by JS --}}
                      </tbody>
                      <tfoot>
                        <tr style="border-top:2px solid var(--rd-border); background:rgba(255,255,255,0.02);">
                          <td colspan="3" style="padding:8px 10px; font-weight:800; color:var(--rd-text1); text-align:right; font-family:'Rajdhani',sans-serif; font-size:13px;">TOTAL →</td>
                          {{-- Firm total cells added dynamically --}}
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                  <p class="text-muted mt-1 mb-0" style="font-size:10px;"><i class="fas fa-info-circle mr-1"></i> The lowest-priced firm will be auto-selected as the winning quote.</p>
                </div>
              @endif
            </div>

            {{-- Right Side: Sidebar --}}
            <div>
              <div class="side-box">
                <div class="side-label"><i class="fas fa-align-left"></i> Justification</div>
                <textarea name="remarks_JSON[justification]" class="soft-textarea" style="min-height: 140px;" placeholder="Why is this expense required? Provide detailed reasoning for approval..."></textarea>
              </div>

              <div class="side-box">
                <div class="side-label"><i class="fas fa-calculator"></i> Financial Summary</div>
                <div class="stats-card">
                    <div class="stats-val" id="live-total-display">PKR 0</div>
                    <div class="stats-lbl">Estimated Total Amount</div>
                </div>
                <p class="text-muted mt-2 mb-0" style="font-size: 0.6rem; text-align: center;">Total is calculated based on line items entered.</p>
              </div>

              @if(in_array($type, ['consultancy', 'services']))
                <div class="side-box" style="border-style: dashed; background: rgba(67,97,238,0.02); border-color: rgba(67,97,238,0.3);">
                    <div class="side-label" style="color: #4361ee;"><i class="fas fa-server"></i> Hardware Context</div>
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
                        <input type="checkbox" id="add_hardware_chk" name="remarks_JSON[includes_hardware]" value="1" style="width:15px;height:15px;cursor:pointer;">
                        <label for="add_hardware_chk" class="soft-label" style="margin:0; text-transform:none; font-size:0.75rem; color:var(--rd-text1); cursor:pointer;">
                            Includes Hardware Components
                        </label>
                    </div>
                    <div id="hardware_sub_options" style="display:none; padding-left:22px; border-left: 2px solid rgba(67,97,238,0.2);">
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <label style="font-size:0.75rem; display:flex; align-items:center; gap:8px; cursor:pointer; color: var(--rd-text2);"><input type="checkbox" name="remarks_JSON[hw_servers]" value="1"> Server Infrastructure</label>
                            <label style="font-size:0.75rem; display:flex; align-items:center; gap:8px; cursor:pointer; color: var(--rd-text2);"><input type="checkbox" name="remarks_JSON[hw_networking]" value="1"> Networking Equipment</label>
                        </div>
                    </div>
                </div>
              @endif
            </div>
          </div>
          
          <!-- FORM ACTIONS -->
          <div class="form-actions-footer">
            <div class="text-left">
                <p class="mb-0 text-muted" style="font-size: 0.75rem;"><i class="fas fa-info-circle mr-1"></i> You can save as <strong>Draft</strong> or</p>
                <p class="mb-0 text-muted" style="font-size: 0.75rem;"><strong>Release</strong> if ready.</p>
            </div>
            <div class="d-flex gap-2">
                <input type="hidden" name="release_directly" id="release_directly_flag" value="0">
                <input type="hidden" name="initiation_remarks" id="initiation_remarks_payload" value="">

                <button type="submit" id="draftSubmitBtn" class="btn-action-main btn-draft">
                   <i class="fas fa-save"></i> DRAFT
                </button>

                <button type="button" id="releaseSubmitBtn" class="btn-action-main btn-release" onclick="confirmAndRelease()">
                   <i class="fas fa-paper-plane"></i> SAVE & FORWARD
                </button>
            </div>
          </div>
        </form>
      </div>

    {{-- Confirmation Modal for Direct Release --}}
    <div class="sinc-popup-overlay" id="releaseDirectModal">
        <div class="sinc-popup-box" style="max-width: 450px;">
            <div class="sinc-popup-icon" style="background: rgba(0, 123, 255, 0.1); color: #007bff;"><i class="fas fa-paper-plane"></i></div>
            <div class="sinc-popup-title" style="font-size: 1.2rem;">Forward to Director Procurement</div>
            <div class="sinc-popup-text text-left mb-0">
                You are forwarding this case to **Director Procurement** (Technical Scrutiny Unit) for formal review.
                <br><br>
                <div class="d-flex align-items-center mb-2">
                    <label class="small font-weight-bold text-muted text-uppercase mb-0">Initiation Remarks (Optional)</label>
                    <span class="badge badge-dark ml-2" style="font-size: 8px; opacity: 0.5;">OPTIONAL</span>
                </div>
                <textarea id="directRemarksInput" class="soft-input w-100 mb-3 p-3" rows="3" placeholder="Enter context or specific justification (optional)..." style="background: rgba(0,0,0,0.2); border: 1px solid var(--rd-border); border-radius:12px; color:white; font-size: 0.9rem;"></textarea>
                
                <div class="alert alert-info border-0 p-3 mb-4" style="background: rgba(0, 123, 255, 0.05); border-radius: 12px; border: 1px solid rgba(0,123,255,0.1) !important;">
                    <i class="fas fa-shield-alt mr-2 text-primary"></i>
                    <span class="small text-muted">Authority: <strong>Director Procurement</strong> will review items and specifications.</span>
                </div>
            </div>
            <div class="d-flex gap-2 justify-content-center">
                <button class="btn btn-dark px-4 py-2 mr-2" onclick="document.getElementById('releaseDirectModal').classList.remove('show')" style="border-radius:12px; border: 1px solid var(--rd-border);">CANCEL</button>
                <button class="sinc-popup-btn px-4 py-2" id="finalReleaseBtn" onclick="executeDirectRelease()" style="border-radius:12px; width: auto !important; background: var(--rd-accent);">PROCEED & FORWARD</button>
            </div>
        </div>
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
      const t = document.createElement('div');
      t.className = `smart-toast ${type}`;
      t.innerHTML = `<i class="fas ${type==='success'?'fa-check-circle':'fa-exclamation-circle'}"></i> ${msg}`;
      w.appendChild(t);
      setTimeout(() => {
          t.style.animation = 'slideDown 0.3s cubic-bezier(0.175,0.885,0.32,1.275) forwards';
          setTimeout(() => t.remove(), 300);
      }, 3500);
  }

  // ----- Transport Recurrent Switcher -----
  function switchTransportMode(mode) {
      document.getElementById('transport_mode').value = mode;
      const btnNon = document.getElementById('btn-transport-nonrecurrent');
      const btnRec = document.getElementById('btn-transport-recurrent');
      const themeColor = '<?= $theme['color'] ?? '#10b981' ?>';
      if(mode === 'nonrecurrent') {
          $('#transport-recurrent').hide(); $('#transport-nonrecurrent').fadeIn(200);
          btnNon.style.background = '#fff'; btnNon.style.color = themeColor; btnNon.style.boxShadow = '0 2px 6px rgba(0,0,0,0.05)'; btnNon.style.fontWeight = '700';
          btnRec.style.background = 'transparent'; btnRec.style.color = '#64748b'; btnRec.style.boxShadow = 'none'; btnRec.style.fontWeight = '600';
      } else {
          $('#transport-nonrecurrent').hide(); $('#transport-recurrent').fadeIn(200);
          btnRec.style.background = '#fff'; btnRec.style.color = themeColor; btnRec.style.boxShadow = '0 2px 6px rgba(0,0,0,0.05)'; btnRec.style.fontWeight = '700';
          btnNon.style.background = 'transparent'; btnNon.style.color = '#64748b'; btnNon.style.boxShadow = 'none'; btnNon.style.fontWeight = '600';
      }
  }

  // ----- Training Type Switcher -----
  function switchTrnMode(mode) {
      document.getElementById('training_type_sel').value = mode;
      const themeColor = '<?= $theme['color'] ?? '#10b981' ?>';
      const btns = { 'external': document.getElementById('btn-trn-external'), 'online': document.getElementById('btn-trn-online'), 'inhouse': document.getElementById('btn-trn-inhouse') };
      Object.keys(btns).forEach(key => { if(!btns[key]) return; btns[key].style.background='transparent'; btns[key].style.color='#64748b'; btns[key].style.boxShadow='none'; btns[key].style.fontWeight='600'; });
      if(btns[mode]) { btns[mode].style.background='#fff'; btns[mode].style.color=themeColor; btns[mode].style.boxShadow='0 2px 6px rgba(0,0,0,0.05)'; btns[mode].style.fontWeight='700'; }
      $('#trn_external, #trn_online, #trn_inhouse').hide(); $('.trn_ext_req, .trn_onl_req, .trn_inh_req').prop('required', false);
      if(mode==='external') { $('#trn_external').fadeIn(200); $('.trn_ext_req').prop('required',true); }
      else if(mode==='online') { $('#trn_online').fadeIn(200); $('.trn_onl_req').prop('required',true); }
      else if(mode==='inhouse') { $('#trn_inhouse').fadeIn(200); $('.trn_inh_req').prop('required',true); }
  }
  <?php if($type == 'training'): ?>
  $(document).ready(function() { switchTrnMode('external'); });
  <?php endif; ?>

  // ----- Direct Release Logic -----
  function confirmAndRelease() { document.getElementById('releaseDirectModal').classList.add('show'); }
  function executeDirectRelease() {
      const remarks = document.getElementById('directRemarksInput').value;
      document.getElementById('release_directly_flag').value = '1';
      document.getElementById('initiation_remarks_payload').value = (remarks && remarks.trim().length > 0) ? remarks : "No Comments";
      const btn = document.getElementById('finalReleaseBtn');
      btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Processing...`; btn.disabled = true;
      document.getElementById('unifiedPurchaseForm').submit();
  }

  function handleFormSubmit(e) {
      fireToast('Synchronizing with ledger... Please wait', 'success');
      const isDraft = document.getElementById('release_directly_flag').value === '0';
      const btnId = isDraft ? 'draftSubmitBtn' : 'finalReleaseBtn';
      const btn = document.getElementById(btnId);
      if (btn) { btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Processing...`; btn.style.pointerEvents = 'none'; btn.style.opacity = '0.8'; }
      return true;
  }

  // ----- Dynamic Minute Number AJAX -----
  $('#pcs_hed_id').on('change', function() {
      const headId = $(this).val(); const $input = $('#pcs_minute'); const $hint = $('#minute-hint');
      if(headId) {
          $hint.html('<i class="fas fa-spinner fa-spin"></i> Fetching next available minute...');
          $.ajax({ url: '/get-next-minute/' + headId, type: "GET",
              success: function(data) { $input.val(data.next_minute); $hint.html(`Last minute: <strong style="color:var(--rd-text1)">${data.last_minute}</strong> &nbsp;|&nbsp; Suggested: <strong style="color:var(--rd-success)">${data.next_minute}</strong>`); fireToast(`Auto-filled minute: ${data.next_minute}`, 'success'); },
              error: function() { $hint.html('<span style="color:#ef4444">Could not auto-fetch minute number.</span>'); }
          });
      }
  });

  // ----- Hardware Shift -----
  $('#add_hardware_chk').on('change', function() {
      if(this.checked) { document.getElementById('sincCustomAlert').classList.add('show'); $('#hardware_sub_options').slideDown(200); if($('#actual_type_override').length===0) { $('#unifiedPurchaseForm').append('<input type="hidden" name="remarks_JSON[actual_type]" id="actual_type_override" value="services">'); } else { $('#actual_type_override').val('services'); } }
      else { $('#hardware_sub_options').slideUp(200); $('#actual_type_override').val('consultancy'); }
  });

  // =====================================================
  //  ITEMS + QUOTATION GRID MANAGEMENT
  // =====================================================
  let itemCounter = 1; // already have index 0
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

      const row = document.createElement('div');
      row.className = 'dyn-row';
      row.setAttribute('data-idx', newIdx);
      row.style.gridTemplateColumns = '40px 1fr 80px 80px 32px';

      row.innerHTML = `
          <div class="text-center" style="display:flex;align-items:center;justify-content:center;color:var(--rd-text3);font-size:11px;font-weight:700;">${serial}</div>
          <div><input type="text" name="items[${newIdx}][desc]" class="soft-input item-desc" placeholder="Item description" required></div>
          <div><input type="number" name="items[${newIdx}][qty]" class="soft-input item-qty" placeholder="Qty" value="1" min="1" required></div>
          <div><input type="text" name="items[${newIdx}][unit]" class="soft-input" placeholder="Unit" value="num"></div>
          <button type="button" class="btn-rm-row" onclick="removeItemRow(this)"><i class="fas fa-times"></i></button>
      `;
      list.appendChild(row);
      row.querySelector('.item-desc').focus();
      updateItemSerials();
      rebuildQuotationBody();
  }

  function removeItemRow(btn) {
      const row = btn.closest('.dyn-row');
      const list = document.getElementById('items-list');
      if (list.querySelectorAll('.dyn-row').length <= 1) return;
      row.style.opacity = '0';
      setTimeout(() => { row.remove(); updateItemSerials(); rebuildQuotationBody(); }, 200);
  }

  function updateItemSerials() {
      const rows = document.querySelectorAll('#items-list .dyn-row');
      rows.forEach((r, i) => {
          const numCell = r.querySelector('div:first-child');
          if (numCell) numCell.textContent = i + 1;
          // Update remove button visibility
          const btn = r.querySelector('.btn-rm-row');
          if (btn) btn.style.visibility = rows.length <= 1 ? 'hidden' : 'visible';
      });
  }

  // ---- Firm / Quotation Grid ----
  function addFirmColumn() {
      const sel = document.getElementById('firmSelector');
      const firmId = sel.value;
      const firmName = sel.options[sel.selectedIndex]?.getAttribute('data-name');
      if (!firmId || !firmName) { fireToast('Please select a firm first', 'error'); return; }
      if (firms.find(f => f.id == firmId)) { fireToast('This firm is already added', 'error'); return; }
      firms.push({ id: firmId, name: firmName });
      sel.value = '';
      rebuildQuotationGrid();
      fireToast(`${firmName} added`, 'success');
  }

  function removeFirm(firmId) {
      firms = firms.filter(f => f.id != firmId);
      rebuildQuotationGrid();
  }

  function rebuildQuotationGrid() {
      // Rebuild header
      const headerRow = document.querySelector('#quotationTable thead tr');
      // Remove old firm columns (keep first 3: S.No, Item, Qty)
      while (headerRow.children.length > 3) headerRow.removeChild(headerRow.lastChild);
      firms.forEach(f => {
          const th = document.createElement('th');
          th.style.cssText = 'padding:6px 8px; color:var(--rd-accent); font-size:9px; text-transform:uppercase; letter-spacing:0.5px; font-weight:700; text-align:center; min-width:120px; white-space:nowrap;';
          th.innerHTML = `${f.name} <button type="button" onclick="removeFirm('${f.id}')" style="background:none;border:none;color:var(--rd-danger);font-size:10px;cursor:pointer;margin-left:4px;"><i class="fas fa-times-circle"></i></button>`;
          headerRow.appendChild(th);
      });
      // Rebuild body
      rebuildQuotationBody();
      // Rebuild footer
      const footerRow = document.querySelector('#quotationTable tfoot tr');
      while (footerRow.children.length > 1) footerRow.removeChild(footerRow.lastChild);
      firms.forEach(f => {
          const td = document.createElement('td');
          td.style.cssText = 'padding:8px 10px; text-align:center; font-family:"Rajdhani",sans-serif; font-size:14px; font-weight:800; color:var(--rd-info);';
          td.id = `firm-total-${f.id}`;
          td.textContent = '0';
          footerRow.appendChild(td);
      });
      calculateQuotationTotals();
  }

  function rebuildQuotationBody() {
      const body = document.getElementById('quotationBody');
      body.innerHTML = '';
      const items = getItems();
      items.forEach((item, i) => {
          const tr = document.createElement('tr');
          tr.style.borderBottom = '1px solid var(--rd-border)';
          let html = `<td style="padding:6px 10px; color:var(--rd-text3); font-size:11px; text-align:center;">${i+1}</td>`;
          html += `<td style="padding:6px 10px; color:var(--rd-text1); font-size:11px;">${item.desc || '<span class="text-muted">—</span>'}</td>`;
          html += `<td style="padding:6px 10px; color:var(--rd-warning); font-size:11px; text-align:center; font-weight:600;">${item.qty}</td>`;
          firms.forEach(f => {
              html += `<td style="padding:4px 6px; text-align:center;">
                  <input type="number" name="quotations[${f.id}][${item.idx}]" class="soft-input firm-price-input" data-firm="${f.id}" 
                         style="width:100%; text-align:center; font-size:11px; padding:3px 6px; height:28px;" placeholder="0" min="0" oninput="calculateQuotationTotals()">
              </td>`;
          });
          tr.innerHTML = html;
          body.appendChild(tr);
      });
      calculateQuotationTotals();
  }

  function calculateQuotationTotals() {
      let lowestTotal = Infinity;
      firms.forEach(f => {
          let total = 0;
          document.querySelectorAll(`input[data-firm="${f.id}"]`).forEach(inp => {
              total += parseFloat(inp.value) || 0;
          });
          const el = document.getElementById(`firm-total-${f.id}`);
          if (el) el.textContent = total.toLocaleString();
          if (total > 0 && total < lowestTotal) lowestTotal = total;
      });
      // Update the financial summary
      const displayTotal = lowestTotal === Infinity ? 0 : lowestTotal;
      const liveDisplay = document.getElementById('live-total-display');
      if (liveDisplay) liveDisplay.textContent = 'PKR ' + displayTotal.toLocaleString();
  }

  // Sync quotation grid when item desc/qty changes
  document.addEventListener('input', function(e) {
      if (e.target.classList.contains('item-desc') || e.target.classList.contains('item-qty')) {
          rebuildQuotationBody();
      }
  });

  // ----- Financial Calculation (fallback for non-quotation) -----
  function calculateTotal() {
      let total = 0;
      $('.amount-input').each(function() { total += parseFloat($(this).val()) || 0; });
      $('#live-total-display').text('PKR ' + total.toLocaleString());
  }

  // ----- Legacy Dynamic List (for milestone-based forms) -----
  function updateRmButtons(listId) {
      const list = document.getElementById(listId); if(!list) return;
      const rows = list.querySelectorAll('.dyn-row');
      rows.forEach((r, idx) => { const btn = r.querySelector('.btn-rm-row');
          if(rows.length === 1) { btn.style.visibility = 'hidden'; }
          else { btn.style.visibility = 'visible'; btn.onclick = function() { r.style.opacity='0'; setTimeout(()=>{ r.remove(); updateRmButtons(listId); calculateTotal(); }, 200); }; }
      });
  }
  function addDynRow(listId, labels, keys, typeOverride='text') {
      const list = document.getElementById(listId); if(!list) return;
      const rows = Array.from(list.querySelectorAll('.dyn-row'));
      const newIdx = rows.length > 0 ? Math.max(...rows.map(r => parseInt(r.getAttribute('data-idx')))) + 1 : 0;
      const row = document.createElement('div'); row.className = 'dyn-row'; row.setAttribute('data-idx', newIdx);
      row.innerHTML = `<div><input type="text" name="remarks_JSON[items][${newIdx}][${keys[0]}]" class="soft-input" placeholder="Description"></div>
          <div><input type="${typeOverride}" name="remarks_JSON[items][${newIdx}][${keys[1]}]" class="soft-input amount-input" placeholder="0" oninput="calculateTotal()"></div>
          <button type="button" class="btn-rm-row"><i class="fas fa-times"></i></button>`;
      list.appendChild(row); updateRmButtons(listId); row.querySelector('input').focus();
  }

  // Init
  document.addEventListener('DOMContentLoaded', () => {
      updateRmButtons('dyn-list-milestone');
      updateRmButtons('dyn-list-misc');
      updateItemSerials();
  });
</script>

@endsection
