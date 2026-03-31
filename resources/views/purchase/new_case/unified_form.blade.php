@extends('welcome')
@section('content')

<div class="content-wrapper p-0">
  <div class="container-fluid p-0">
    <div class="sinc-wrapper" style="min-height:100vh;background:var(--rd-bg);font-family:'DM Sans',sans-serif;padding:24px 24px 32px">
      <style>
        .sinc-card { background:var(--rd-surface); border:1px solid var(--rd-border); border-radius:14px; box-shadow: 0 4px 16px rgba(0,0,0,0.2); overflow:hidden; }
        .sinc-card-head { display:flex; align-items:center; justify-content:space-between; padding:14px 20px 12px; border-bottom:1px solid var(--rd-border2); background:var(--rd-surface); }
        .sinc-card-title { font-size:0.95rem; font-weight:800; color:var(--rd-text1); display:flex; align-items:center; gap:8px; }
        
        .soft-form { padding:16px 20px; }
        .soft-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
        .soft-row-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; }
        .soft-group { margin-bottom:14px; }
        
        .soft-label { font-size:0.72rem; font-weight:700; color:var(--rd-text3); margin-bottom:4px; display:block; text-transform:uppercase; letter-spacing:0.3px; }
        
        .soft-input, .soft-select, .soft-textarea {
          width:100%; border:1.5px solid var(--rd-border); border-radius:10px; background:var(--rd-surface2); padding:8px 12px; font-size:0.85rem; height: 38px; color:var(--rd-text1); font-family:'DM Sans',sans-serif; transition:all 0.2s ease;
        }
        .soft-select { cursor:pointer; appearance:none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 12px center; padding-right:32px; }
        
        .soft-input:focus, .soft-select:focus, .soft-textarea:focus { outline:none; border-color:var(--rd-accent); background:var(--rd-surface); box-shadow:0 0 0 3px var(--rd-accent-soft); }
        .soft-textarea { min-height:75px; height:auto; resize:vertical; }
        
        .section-title { font-size:0.85rem; font-weight:800; color:var(--rd-text1); margin:6px 0 12px; display:flex; align-items:center; gap:8px; padding-bottom:6px; border-bottom:1px dashed var(--rd-border2); }
        
        .sinc-topbar { display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; }
        .sinc-page-title { font-size:1.15rem; font-weight:800; color:var(--rd-text1); display:flex; align-items:center; gap:10px; }
        .title-icon { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1rem; }
        
        .btn-submit { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:10px 24px; border-radius:10px; background:linear-gradient(135deg, var(--rd-accent), var(--rd-accent-dark)); color:#fff; font-size:0.85rem; font-weight:700; border:none; cursor:pointer; box-shadow:0 4px 12px rgba(0,0,0,0.3); transition:all 0.2s ease; }
        .btn-submit:hover { transform:translateY(-1px); box-shadow:0 6px 16px rgba(0,0,0,0.4); }
        
        .badge-draft { background-color:#fffbeb; color:#d97706; padding:4px 12px; border-radius:50px; font-size:0.7rem; font-weight:800; border:1px solid #fcd34d; letter-spacing:0.3px; }
        
        /* Interactive dynamic lists */
        .dyn-list { display:flex; flex-direction:column; gap:10px; }
        .dyn-row { display:grid; grid-template-columns:1fr 1fr 38px; gap:12px; align-items:end; padding:12px; border:1.5px solid var(--rd-border); border-radius:12px; background:var(--rd-surface); transition:all 0.2s; }
        .dyn-row:focus-within { border-color:var(--rd-accent); box-shadow:0 2px 8px rgba(0,0,0,0.1); }
        .btn-add-row { display:inline-flex; align-items:center; gap:6px; padding:8px 14px; border-radius:8px; background:var(--rd-accent-soft); color:var(--rd-accent); font-weight:700; font-size:0.75rem; border:none; cursor:pointer; transition:background 0.2s; margin-top:6px; }
        .btn-add-row:hover { background:var(--rd-accent-soft); opacity: 0.8; }
        .btn-rm-row { width:38px; height:38px; border-radius:10px; border:1.5px solid var(--rd-border2); background:var(--rd-surface); color:var(--rd-text3); display:inline-flex; align-items:center; justify-content:center; cursor:pointer; transition:all 0.2s; }
        .btn-rm-row:hover { border-color:var(--rd-danger); color:var(--rd-danger); background:var(--rd-danger-soft); }

        /* Color Themes depending on Type */
        <?php 
          $theme = match($type) {
              'consultancy', 'services', 'civil' => ['bg' => 'var(--rd-accent-soft)', 'color' => 'var(--rd-accent)', 'icon' => 'fa-layer-group'],
              'training', 'tada' => ['bg' => 'var(--rd-warning-soft)', 'color' => 'var(--rd-warning)', 'icon' => 'fa-plane-departure'],
              'transport', 'books', 'license', 'internet', 'publishing', 'stationery' => ['bg' => 'var(--rd-success-soft)', 'color' => 'var(--rd-success)', 'icon' => 'fa-box-open'],
              default => ['bg' => 'var(--rd-accent-soft)', 'color' => 'var(--rd-accent)', 'icon' => 'fa-file-invoice']
          };
          
          $titles = [
              'consultancy' => 'Consultancy Case', 'services' => 'Outsourced Services', 'civil' => 'Civil Works / Upfit',
              'training' => 'Training Program', 'tada' => 'TA / DA Entry',
              'transport' => 'Transport Expense', 'books' => 'Books & Magazines', 'license' => 'License Fees',
              'internet' => 'Communication & Internet', 'publishing' => 'Publishing / Reg. Fees', 'stationery' => 'Stationery'
          ];
          $pageTitle = $titles[$type] ?? 'New Purchase Case';
        ?>
        .theme-icon { background: <?= $theme['bg'] ?>; color: <?= $theme['color'] ?>; }

        /* Toast Notifications */
        .toast-container { position:fixed; bottom:24px; right:24px; z-index:9999; display:flex; flex-direction:column; gap:10px; }
        .smart-toast { padding:14px 20px; border-radius:12px; font-weight:600; font-size:0.9rem; color:#fff; display:flex; align-items:center; gap:10px; box-shadow:0 10px 25px rgba(0,0,0,0.15); animation:slideUp 0.3s cubic-bezier(0.175,0.885,0.32,1.275) forwards; transform:translateY(100%); opacity:0; }
        .smart-toast.success { background:#10b981; }
        .smart-toast.error { background:#ef4444; }
        @keyframes slideUp { to { transform:translateY(0); opacity:1; } }
        @keyframes slideDown { to { transform:translateY(100%); opacity:0; } }

        /* Beautiful Centered Popup */
        .sinc-popup-overlay { position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.6); backdrop-filter:blur(6px); -webkit-backdrop-filter:blur(6px); display:flex; align-items:center; justify-content:center; z-index:10000; opacity:0; visibility:hidden; transition:all 0.3s ease; }
        .sinc-popup-overlay.show { opacity:1; visibility:visible; }
        .sinc-popup-box { background:var(--rd-surface3); width:100%; max-width:380px; border-radius:24px; padding:32px 28px; text-align:center; box-shadow:0 24px 48px rgba(0,0,0,0.4); transform:translateY(20px) scale(0.95); opacity:0; transition:all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .sinc-popup-overlay.show .sinc-popup-box { transform:translateY(0) scale(1); opacity:1; }
        .sinc-popup-icon { width:64px; height:64px; border-radius:50%; background:var(--rd-accent-soft); color:var(--rd-accent); font-size:1.8rem; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; box-shadow:0 8px 16px rgba(0,0,0,0.2); }
        .sinc-popup-title { font-size:1.3rem; font-weight:800; color:var(--rd-text1); margin-bottom:8px; line-height:1.2; }
        .sinc-popup-text { font-size:0.9rem; color:var(--rd-text2); margin-bottom:28px; line-height:1.5; padding:0 10px; }
        .sinc-popup-btn { background:linear-gradient(135deg, var(--rd-accent), var(--rd-accent-dark)); color:#fff; border:none; width:100%; padding:14px; border-radius:14px; font-weight:700; font-size:0.95rem; font-family:'DM Sans', sans-serif; cursor:pointer; transition:all 0.2s; box-shadow:0 6px 16px rgba(0,0,0,0.3); }
        .sinc-popup-btn:hover { background:linear-gradient(135deg, var(--rd-accent-dark), var(--rd-accent)); transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,0,0,0.4); }
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

      <!-- Topbar -->
      <div class="sinc-topbar">
        <div>
          <div class="sinc-page-title">
            <span class="title-icon theme-icon"><i class="fas <?= $theme['icon'] ?>"></i></span>
            <?= $pageTitle ?>
          </div>
          <div style="font-size:0.75rem;color:#64748b;font-weight:500;margin-top:2px">Unified form processor for specialized procurement cases. Fill the required fields below.</div>
        </div>
        <a href="{{ route('purchase.select') }}" class="btn-cancel" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border:1.5px solid var(--rd-border2);border-radius:10px;background:var(--rd-surface);color:var(--rd-text2);font-size:0.85rem;font-weight:700;text-decoration:none;box-shadow:0 2px 4px rgba(0,0,0,0.1)"> 
          <i class="fas fa-arrow-left" style="font-size:0.75rem"></i> Back to Selection
        </a>
      </div>

      <div class="sinc-card">
        <div class="sinc-card-head">
          <div class="sinc-card-title"><i class="fas fa-clipboard-check" style="color:<?= $theme['color'] ?>"></i> General Information</div>
          <span class="badge-draft">DRAFT</span>
        </div>
        
        <form class="soft-form" id="unifiedPurchaseForm" action="{{ route('purchase.store') }}" method="POST" onsubmit="return handleFormSubmit(event)">
          @csrf
          <input type="hidden" name="pcs_type" value="pt"> <!-- Legacy fallback -->
          
          <!-- BASE FIELDS REQUIRED FOR ALL -->
          <div class="soft-row-3">
            <div class="soft-group">
              <label class="soft-label">Case ID (System Generated)</label>
              <input type="text" class="soft-input" value="{{ $nextId }}" readonly style="background:var(--rd-surface3);cursor:not-allowed;font-family:'DM Mono',monospace;font-weight:700">
            </div>
            <div class="soft-group">
              <label class="soft-label">Creation Date</label>
              <input type="date" name="pcs_date" class="soft-input" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="soft-group">
              <label class="soft-label">Minute Number</label>
              <input type="number" name="pcs_minute" id="pcs_minute" class="soft-input" placeholder="e.g. 1" required>
              <div id="minute-hint" style="font-size:0.75rem;color:#3b82f6;margin-top:6px;font-weight:600"></div>
            </div>
          </div>

          <div class="soft-row">
            <div class="soft-group">
              <label class="soft-label">Case Title / Subject</label>
              <input type="text" name="pcs_title" class="soft-input" placeholder="Define a clear subject for this case" required>
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

          <!-- TYPE SPECIFIC DYNAMIC FIELDS -->
          <div class="section-title mt-4"><i class="fas fa-sliders-h" style="color:<?= $theme['color'] ?>"></i> Specific Details (<?= ucfirst($type) ?>)</div>
          
          @if(in_array($type, ['consultancy', 'services']))
            <div class="soft-row">
              <div class="soft-group">
                <label class="soft-label">Scope of Work</label>
                <textarea name="remarks_JSON[scope]" class="soft-textarea" placeholder="Describe the comprehensive scope..."></textarea>
              </div>
              <div class="soft-group">
                <label class="soft-label">Estimated Duration</label>
                <input type="text" name="remarks_JSON[duration]" class="soft-input" placeholder="e.g. 6 Weeks">
              </div>
            </div>
            <div class="soft-group">
              <label class="soft-label">Milestones & Payments</label>
              <div id="dyn-list-milestone" class="dyn-list">
                <div class="dyn-row" data-idx="0">
                  <div>
                    <label class="soft-label" style="font-size:0.7rem">Milestone Desc</label>
                    <input type="text" name="remarks_JSON[milestones][0][desc]" class="soft-input" placeholder="e.g. Requirement Gathering">
                  </div>
                  <div>
                    <label class="soft-label" style="font-size:0.7rem">Payment %</label>
                    <input type="text" name="remarks_JSON[milestones][0][pay]" class="soft-input" placeholder="e.g. 15% Advance">
                  </div>
                  <button type="button" class="btn-rm-row" tabindex="-1" style="visibility:hidden"><i class="fas fa-times"></i></button>
                </div>
              </div>
              <button type="button" class="btn-add-row" onclick="addDynRow('dyn-list-milestone', ['Milestone Desc', 'Payment %'], ['desc', 'pay'])">
                <i class="fas fa-plus"></i> Add Milestone
              </button>
            </div>
            
            <!-- HARDWARE INCORPORATION SECTION -->
            <div class="soft-group" style="padding: 16px; border: 1.5px dashed #4361ee; border-radius: 12px; background: rgba(67,97,238,0.03);">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
                    <input type="checkbox" id="add_hardware_chk" name="remarks_JSON[includes_hardware]" value="1" style="width:16px;height:16px;cursor:pointer;">
                    <label for="add_hardware_chk" class="soft-label" style="margin:0; text-transform:none; font-size:0.85rem; color:#1e293b; cursor:pointer;">
                        Include Hardware Devices
                    </label>
                </div>
                
                <div id="hardware_sub_options" style="display:none; padding-left:24px;">
                    <label class="soft-label" style="text-transform:none; margin-bottom:8px;">Select Required Hardware:</label>
                    <div style="display:flex; gap:16px; flex-wrap:wrap;">
                        <label style="font-size:0.8rem; display:flex; align-items:center; gap:6px; cursor:pointer;"><input type="checkbox" name="remarks_JSON[hw_servers]" value="1"> Servers / Storage</label>
                        <label style="font-size:0.8rem; display:flex; align-items:center; gap:6px; cursor:pointer;"><input type="checkbox" name="remarks_JSON[hw_networking]" value="1"> Networking Gear</label>
                        <label style="font-size:0.8rem; display:flex; align-items:center; gap:6px; cursor:pointer;"><input type="checkbox" name="remarks_JSON[hw_workstations]" value="1"> Workstations / Laptops</label>
                    </div>
                    <div style="margin-top: 14px;">
                        <label class="soft-label" style="text-transform:none; margin-bottom:6px;">Hardware Comments / Specifications (Optional):</label>
                        <input type="text" name="remarks_JSON[hw_comments]" class="soft-input" placeholder="Enter any specific requirements or comments...">
                    </div>
                </div>
            </div>
            
          @elseif($type == 'civil')
            <div class="soft-row">
              <div class="soft-group">
                <label class="soft-label">Site / Location</label>
                <input type="text" name="remarks_JSON[location]" class="soft-input" placeholder="e.g. Lab 4B Main Campus">
              </div>
              <div class="soft-group">
                <label class="soft-label">Target Completion</label>
                <input type="date" name="remarks_JSON[completion_date]" class="soft-input">
              </div>
            </div>
            <div class="soft-group">
              <label class="soft-label">Technical Scope / Requirements</label>
              <textarea name="remarks_JSON[scope]" class="soft-textarea" placeholder="Detail the civil works required..."></textarea>
            </div>
            
          @elseif($type == 'tada')
            <div class="soft-row-3">
              <div class="soft-group">
                <label class="soft-label">Traveler Name</label>
                <input type="text" name="remarks_JSON[traveler]" class="soft-input" placeholder="Name of individual">
              </div>
              <div class="soft-group">
                <label class="soft-label">Designation</label>
                <input type="text" name="remarks_JSON[designation]" class="soft-input" placeholder="e.g. Senior Researcher">
              </div>
              <div class="soft-group">
                <label class="soft-label">Destination</label>
                <input type="text" name="remarks_JSON[destination]" class="soft-input" placeholder="City / Country">
              </div>
            </div>
            <div class="soft-row">
              <div class="soft-group">
                <label class="soft-label">Travel Dates</label>
                <div style="display:flex;gap:10px">
                  <input type="date" name="remarks_JSON[date_from]" class="soft-input">
                  <input type="date" name="remarks_JSON[date_to]" class="soft-input">
                </div>
              </div>
              <div class="soft-group">
                <label class="soft-label">Purpose of Visit</label>
                <textarea name="remarks_JSON[purpose]" class="soft-input" style="min-height:50px" placeholder="Meeting, Inspection, etc."></textarea>
              </div>
            </div>
            
          @elseif($type == 'training')
            <!-- Training Type Switcher -->
            <div class="text-center mb-4" style="margin-bottom:20px;text-align:center;">
                <div style="background:#e8edf4; padding:4px; border-radius:12px; display:inline-flex;">
                    <button type="button" id="btn-trn-external" onclick="switchTrnMode('external')" style="padding:10px 24px; border-radius:10px; border:none; background:#fff; font-weight:700; color:<?= $theme['color'] ?>; box-shadow:0 2px 6px rgba(0,0,0,0.05); cursor:pointer; transition:all 0.2s;">External Training</button>
                    <button type="button" id="btn-trn-online" onclick="switchTrnMode('online')" style="padding:10px 24px; border-radius:10px; border:none; background:transparent; font-weight:600; color:#64748b; cursor:pointer; transition:all 0.2s;">Online Course</button>
                    <button type="button" id="btn-trn-inhouse" onclick="switchTrnMode('inhouse')" style="padding:10px 24px; border-radius:10px; border:none; background:transparent; font-weight:600; color:#64748b; cursor:pointer; transition:all 0.2s;">In-house Training</button>
                </div>
                <!-- Hidden input substituting the required dropdown for form submission -->
                <input type="hidden" name="remarks_JSON[training_type]" id="training_type_sel" value="external">
            </div>

            <div class="section-title" style="margin-top:0"><i class="fas fa-id-card text-muted"></i> Basic Information</div>
            <div class="soft-row-3">
              <div class="soft-group">
                <label class="soft-label">Employee Name <span class="text-danger">*</span></label>
                <input type="text" name="remarks_JSON[emp_name]" class="soft-input" required>
              </div>
              <div class="soft-group">
                <label class="soft-label">Employee ID <span class="text-danger">*</span></label>
                <input type="text" name="remarks_JSON[emp_id]" class="soft-input" required>
              </div>
              <div class="soft-group">
                <label class="soft-label">Department / Division <span class="text-danger">*</span></label>
                <input type="text" name="remarks_JSON[department]" class="soft-input" required>
              </div>
            </div>

            <div class="section-title"><i class="fas fa-info-circle text-muted"></i> Training Details</div>
            <div class="soft-group">
              <label class="soft-label">Training / Course Title <span class="text-danger">*</span></label>
              <input type="text" name="remarks_JSON[course_title]" class="soft-input" required>
            </div>
            <div class="soft-row-3">
              <div class="soft-group">
                <label class="soft-label">Start Date <span class="text-danger">*</span></label>
                <input type="date" name="remarks_JSON[start_date]" class="soft-input" required>
              </div>
              <div class="soft-group">
                <label class="soft-label">End Date <span class="text-danger">*</span></label>
                <input type="date" name="remarks_JSON[end_date]" class="soft-input" required>
              </div>
              <div class="soft-group">
                <label class="soft-label">Duration <span class="text-danger">*</span></label>
                <input type="text" name="remarks_JSON[duration]" class="soft-input" placeholder="e.g. 5 Days" required>
              </div>
            </div>

            <!-- CONDITIONAL: EXTERNAL -->
            <div id="trn_external" style="display:none; margin-top:20px; padding:20px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px;">
              <div class="section-title" style="margin-top:0"><i class="fas fa-external-link-alt text-muted"></i> External Training Specs</div>
              <div class="soft-row-3" style="margin-bottom:0">
                <div class="soft-group mb-0">
                  <label class="soft-label">Institute / Organization Name <span class="text-danger">*</span></label>
                  <input type="text" name="remarks_JSON[ext_institute]" class="soft-input trn_ext_req">
                </div>
                <div class="soft-group mb-0">
                  <label class="soft-label">Training Location <span class="text-danger">*</span></label>
                  <input type="text" name="remarks_JSON[ext_location]" class="soft-input trn_ext_req">
                </div>
                <div class="soft-group mb-0">
                  <label class="soft-label">Course Fee <span class="text-danger">*</span></label>
                  <input type="number" name="remarks_JSON[ext_fee]" class="soft-input trn_ext_req" placeholder="e.g. 50000">
                </div>
              </div>
            </div>

            <!-- CONDITIONAL: ONLINE -->
            <div id="trn_online" style="display:none; margin-top:20px; padding:20px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px;">
              <div class="section-title" style="margin-top:0"><i class="fas fa-globe text-muted"></i> Online Course Specs</div>
              <div class="soft-row-3" style="margin-bottom:0">
                <div class="soft-group mb-0">
                  <label class="soft-label">Platform Name <span class="text-danger">*</span></label>
                  <input type="text" name="remarks_JSON[onl_platform]" class="soft-input trn_onl_req" placeholder="e.g. Coursera, Udemy">
                </div>
                <div class="soft-group mb-0">
                  <label class="soft-label">Course Link <span class="text-danger">*</span></label>
                  <input type="url" name="remarks_JSON[onl_link]" class="soft-input trn_onl_req" placeholder="https://...">
                </div>
                <div class="soft-group mb-0">
                  <label class="soft-label">Instructor Name <span class="text-danger">*</span></label>
                  <input type="text" name="remarks_JSON[onl_instructor]" class="soft-input trn_onl_req">
                </div>
              </div>
            </div>

            <!-- CONDITIONAL: IN-HOUSE -->
            <div id="trn_inhouse" style="display:none; margin-top:20px; padding:20px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px;">
              <div class="section-title" style="margin-top:0"><i class="fas fa-building text-muted"></i> In-house Training Specs</div>
              <div class="soft-row-3" style="margin-bottom:0">
                <div class="soft-group mb-0">
                  <label class="soft-label">Trainer Name <span class="text-danger">*</span></label>
                  <input type="text" name="remarks_JSON[inh_trainer_name]" class="soft-input trn_inh_req">
                </div>
                <div class="soft-group mb-0">
                  <label class="soft-label">Trainer Organization <span class="text-danger">*</span></label>
                  <input type="text" name="remarks_JSON[inh_trainer_org]" class="soft-input trn_inh_req">
                </div>
                <div class="soft-group mb-0">
                  <label class="soft-label">No. of Participants <span class="text-danger">*</span></label>
                  <input type="number" name="remarks_JSON[inh_participants]" class="soft-input trn_inh_req" placeholder="e.g. 15">
                </div>
              </div>
            </div>
            
          @elseif($type == 'transport')
            <!-- Transport Recurrent Switcher -->
            <div class="text-center mb-4" style="margin-bottom:20px;text-align:center;">
                <div style="background:#e8edf4; padding:4px; border-radius:12px; display:inline-flex;">
                    <button type="button" id="btn-transport-nonrecurrent" onclick="switchTransportMode('nonrecurrent')" style="padding:10px 24px; border-radius:10px; border:none; background:#fff; font-weight:700; color:<?= $theme['color'] ?>; box-shadow:0 2px 6px rgba(0,0,0,0.05); cursor:pointer; transition:all 0.2s;">Non-Recurrent</button>
                    <button type="button" id="btn-transport-recurrent" onclick="switchTransportMode('recurrent')" style="padding:10px 24px; border-radius:10px; border:none; background:transparent; font-weight:600; color:#64748b; cursor:pointer; transition:all 0.2s;">Recurrent</button>
                </div>
                <input type="hidden" name="remarks_JSON[transport_mode]" id="transport_mode" value="nonrecurrent">
            </div>

            <!-- NON RECURRENT FORM -->
            <div id="transport-nonrecurrent">
                <div class="soft-row-3">
                  <div class="soft-group">
                    <label class="soft-label">Type of Vehicle</label>
                    <input type="text" name="remarks_JSON[nr_vehicle]" class="soft-input" placeholder="e.g. Sedan, Hiace...">
                  </div>
                  <div class="soft-group">
                    <label class="soft-label">Model</label>
                    <input type="text" name="remarks_JSON[nr_model]" class="soft-input" placeholder="e.g. 2023+">
                  </div>
                  <div class="soft-group">
                    <label class="soft-label">No. of Days</label>
                    <input type="number" name="remarks_JSON[nr_days]" class="soft-input" placeholder="e.g. 3">
                  </div>
                </div>
                
                <div class="soft-row-3">
                  <div class="soft-group">
                    <label class="soft-label">Time (From & To)</label>
                    <div style="display:flex;gap:10px">
                      <input type="time" name="remarks_JSON[nr_time_from]" class="soft-input">
                      <input type="time" name="remarks_JSON[nr_time_to]" class="soft-input">
                    </div>
                  </div>
                  <div class="soft-group">
                    <label class="soft-label">Est. Rate Amount</label>
                    <input type="number" name="remarks_JSON[nr_rate]" class="soft-input" placeholder="e.g. 5000">
                  </div>
                  <div class="soft-group">
                    <label class="soft-label">Rate Type</label>
                    <select name="remarks_JSON[nr_rate_type]" class="soft-select">
                        <option value="per_hour">Per Hour</option>
                        <option value="per_day" selected>Per Day</option>
                        <option value="per_week">Per Week</option>
                        <option value="per_month">Per Month</option>
                    </select>
                  </div>
                </div>
                
                <div class="soft-row">
                  <div class="soft-group">
                    <label class="soft-label">Details / Requirements</label>
                    <textarea name="remarks_JSON[nr_details]" class="soft-textarea" style="min-height:55px;" placeholder="Specific details..."></textarea>
                  </div>
                  <div class="soft-group">
                    <label class="soft-label">Remarks</label>
                    <textarea name="remarks_JSON[nr_remarks]" class="soft-textarea" style="min-height:55px;"></textarea>
                  </div>
                </div>
            </div>

            <!-- RECURRENT FORM -->
            <div id="transport-recurrent" style="display:none;">
                <div class="soft-row-3">
                  <div class="soft-group">
                    <label class="soft-label">Type of Vehicle</label>
                    <input type="text" name="remarks_JSON[r_vehicle]" class="soft-input" placeholder="e.g. Bus, Coaster">
                  </div>
                  <div class="soft-group">
                    <label class="soft-label">Model Details</label>
                    <input type="text" name="remarks_JSON[r_model]" class="soft-input" placeholder="e.g. 2022+ A/C">
                  </div>
                  <div class="soft-group">
                    <label class="soft-label">Driver Requirement</label>
                    <select name="remarks_JSON[r_driver]" class="soft-select">
                        <option value="with_driver">With Driver</option>
                        <option value="without_driver">Without Driver</option>
                    </select>
                  </div>
                </div>
                
                <div class="soft-row-3">
                  <div class="soft-group">
                    <label class="soft-label">Pick & Drop Required?</label>
                    <select name="remarks_JSON[r_pickdrop]" class="soft-select">
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                  </div>
                  <div class="soft-group">
                    <label class="soft-label">Permanent Assigned?</label>
                    <select name="remarks_JSON[r_permanent]" class="soft-select">
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                  </div>
                  <div class="soft-group">
                    <label class="soft-label">Working Days</label>
                    <select name="remarks_JSON[r_frequency]" class="soft-select">
                        <option value="5_days">5 Days a Week</option>
                        <option value="6_days">6 Days a Week</option>
                        <option value="7_days">7 Days a Week (Full)</option>
                        <option value="custom">Custom (Specify in remarks)</option>
                    </select>
                  </div>
                </div>

                <div class="soft-row-3">
                  <div class="soft-group">
                    <label class="soft-label">Est. Rate</label>
                    <input type="number" name="remarks_JSON[r_rate]" class="soft-input" placeholder="e.g. 150000">
                  </div>
                  <div class="soft-group">
                    <label class="soft-label">Rate Type</label>
                    <select name="remarks_JSON[r_rate_type]" class="soft-select">
                        <option value="per_month" selected>Per Month</option>
                        <option value="per_week">Per Week</option>
                        <option value="per_day">Per Day</option>
                    </select>
                  </div>
                  <div class="soft-group">
                    <label class="soft-label">Remarks / Details</label>
                    <textarea name="remarks_JSON[r_remarks]" class="soft-input" style="height:38px;resize:none;" placeholder="Specific detail / requirement"></textarea>
                  </div>
                </div>
            </div>
            
          @else <!-- Misc categories (books, license, internet, publishing, stationery) -->
            <div class="soft-group">
              <label class="soft-label">Items / Breakdown</label>
              <div id="dyn-list-misc" class="dyn-list">
                <div class="dyn-row" data-idx="0">
                  <div>
                    <label class="soft-label" style="font-size:0.7rem">Description</label>
                    <input type="text" name="remarks_JSON[items][0][desc]" class="soft-input" placeholder="Detail the item / expense">
                  </div>
                  <div>
                    <label class="soft-label" style="font-size:0.7rem">Est. Amount</label>
                    <input type="number" name="remarks_JSON[items][0][amount]" class="soft-input" placeholder="0">
                  </div>
                  <button type="button" class="btn-rm-row" tabindex="-1" style="visibility:hidden"><i class="fas fa-times"></i></button>
                </div>
              </div>
              <button type="button" class="btn-add-row" onclick="addDynRow('dyn-list-misc', ['Description', 'Est. Amount'], ['desc', 'amount'], 'number')">
                <i class="fas fa-plus"></i> Add Item
              </button>
            </div>
            <div class="soft-group">
              <label class="soft-label">Justification / Reason</label>
              <textarea name="remarks_JSON[justification]" class="soft-textarea" placeholder="Why is this expense required?"></textarea>
            </div>
          @endif
          
          <div class="soft-group mt-5 text-right" style="text-align:right">
            <button type="submit" class="btn-submit" id="mainSubmitBtn"><i class="fas fa-check-circle"></i> Save & Initialize Case</button>
          </div>
        </form>
      </div>

    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
          $('#transport-recurrent').hide();
          $('#transport-nonrecurrent').fadeIn(200);
          btnNon.style.background = '#fff'; btnNon.style.color = themeColor; btnNon.style.boxShadow = '0 2px 6px rgba(0,0,0,0.05)'; btnNon.style.fontWeight = '700';
          btnRec.style.background = 'transparent'; btnRec.style.color = '#64748b'; btnRec.style.boxShadow = 'none'; btnRec.style.fontWeight = '600';
      } else {
          $('#transport-nonrecurrent').hide();
          $('#transport-recurrent').fadeIn(200);
          btnRec.style.background = '#fff'; btnRec.style.color = themeColor; btnRec.style.boxShadow = '0 2px 6px rgba(0,0,0,0.05)'; btnRec.style.fontWeight = '700';
          btnNon.style.background = 'transparent'; btnNon.style.color = '#64748b'; btnNon.style.boxShadow = 'none'; btnNon.style.fontWeight = '600';
      }
  }

  // ----- Training Type Switcher Logic -----
  function switchTrnMode(mode) {
      document.getElementById('training_type_sel').value = mode;
      
      const themeColor = '<?= $theme['color'] ?? '#10b981' ?>';
      const btns = {
          'external': document.getElementById('btn-trn-external'),
          'online': document.getElementById('btn-trn-online'),
          'inhouse': document.getElementById('btn-trn-inhouse')
      };

      // Reset all buttons
      Object.keys(btns).forEach(key => {
          if(!btns[key]) return;
          btns[key].style.background = 'transparent'; 
          btns[key].style.color = '#64748b'; 
          btns[key].style.boxShadow = 'none'; 
          btns[key].style.fontWeight = '600';
      });

      // Highlight active button
      if(btns[mode]) {
          btns[mode].style.background = '#fff'; 
          btns[mode].style.color = themeColor; 
          btns[mode].style.boxShadow = '0 2px 6px rgba(0,0,0,0.05)'; 
          btns[mode].style.fontWeight = '700';
      }

      // Hide all dynamic areas and remove required attribute
      $('#trn_external, #trn_online, #trn_inhouse').hide();
      $('.trn_ext_req, .trn_onl_req, .trn_inh_req').prop('required', false);
      
      // Target area
      if(mode === 'external') {
          $('#trn_external').fadeIn(200);
          $('.trn_ext_req').prop('required', true);
      } else if(mode === 'online') {
          $('#trn_online').fadeIn(200);
          $('.trn_onl_req').prop('required', true);
      } else if(mode === 'inhouse') {
          $('#trn_inhouse').fadeIn(200);
          $('.trn_inh_req').prop('required', true);
      }
  }

  // Initialize correct training layout on load if category is Training
  <?php if($type == 'training'): ?>
  $(document).ready(function() {
      switchTrnMode('external'); // Starts at external by default
  });
  <?php endif; ?>

  // ----- Form Submission Handling -----
  function handleFormSubmit(e) {
      // Just showing notification and submitting normally
      fireToast('Saving case... Please wait', 'success');
      const btn = document.getElementById('mainSubmitBtn');
      btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Processing...`;
      btn.style.pointerEvents = 'none';
      btn.style.opacity = '0.8';
      return true; 
  }

  // ----- Dynamic Minute Number AJAX -----
  $('#pcs_hed_id').on('change', function() {
      const headId = $(this).val();
      const $input = $('#pcs_minute');
      const $hint = $('#minute-hint');
      if(headId) {
          $hint.html('<i class="fas fa-spinner fa-spin"></i> Fetching next available minute...');
          $.ajax({
              url: '/get-next-minute/' + headId,
              type: "GET",
              success: function(data) {
                  $input.val(data.next_minute);
                  $hint.html(`Last minute: <strong style="color:var(--rd-text1)">${data.last_minute}</strong> &nbsp;|&nbsp; Suggested: <strong style="color:var(--rd-success)">${data.next_minute}</strong>`);
                  fireToast(`Auto-filled minute: ${data.next_minute}`, 'success');
              },
              error: function() { $hint.html('<span style="color:#ef4444">Could not auto-fetch minute number. Please enter manually.</span>'); }
          });
      }
  });

  // ----- Hardware Shift Logic -----
  $('#add_hardware_chk').on('change', function() {
      if(this.checked) {
          // Show custom elegant popup instead of standard alert
          document.getElementById('sincCustomAlert').classList.add('show');
          
          $('#hardware_sub_options').slideDown(200);
          
          // Visually modify the title layout to show it's now Services
          $('.sinc-page-title').html('<span class="title-icon theme-icon"><i class="fas fa-layer-group"></i></span> Outsourced Services');
          
          // Submit as Service if possible, backend type injection via hidden field
          if($('#actual_type_override').length === 0) {
              $('#unifiedPurchaseForm').append('<input type="hidden" name="remarks_JSON[actual_type]" id="actual_type_override" value="services">');
          } else {
              $('#actual_type_override').val('services');
          }
      } else {
          $('#hardware_sub_options').slideUp(200);
          $('.sinc-page-title').html('<span class="title-icon theme-icon"><i class="fas fa-layer-group"></i></span> Consultancy Case');
          $('#actual_type_override').val('consultancy');
      }
  });

  // ----- Dynamic List Row Management -----
  function updateRmButtons(listId) {
      const list = document.getElementById(listId);
      if(!list) return;
      const rows = list.querySelectorAll('.dyn-row');
      rows.forEach((r, idx) => {
          const btn = r.querySelector('.btn-rm-row');
          if(rows.length === 1) {
              btn.style.visibility = 'hidden';
          } else {
              btn.style.visibility = 'visible';
              btn.onclick = function() {
                  r.style.opacity = '0';
                  setTimeout(() => { r.remove(); updateRmButtons(listId); }, 200);
              };
          }
      });
  }

  function addDynRow(listId, labels, keys, typeOverride='text') {
      const list = document.getElementById(listId);
      if(!list) return;
      const rows = Array.from(list.querySelectorAll('.dyn-row'));
      const newIdx = rows.length > 0 ? Math.max(...rows.map(r => parseInt(r.getAttribute('data-idx')))) + 1 : 0;
      
      const row = document.createElement('div');
      row.className = 'dyn-row';
      row.setAttribute('data-idx', newIdx);
      
      row.innerHTML = `
          <div>
            <label class="soft-label" style="font-size:0.7rem">${labels[0]}</label>
            <input type="text" name="remarks_JSON[${listId==='dyn-list-milestone'?'milestones':'items'}][${newIdx}][${keys[0]}]" class="soft-input">
          </div>
          <div>
            <label class="soft-label" style="font-size:0.7rem">${labels[1]}</label>
            <input type="${typeOverride}" name="remarks_JSON[${listId==='dyn-list-milestone'?'milestones':'items'}][${newIdx}][${keys[1]}]" class="soft-input">
          </div>
          <button type="button" class="btn-rm-row"><i class="fas fa-times"></i></button>
      `;
      list.appendChild(row);
      updateRmButtons(listId);
      
      // Auto focus first input
      row.querySelector('input').focus();
  }
  
  // Init
  document.addEventListener('DOMContentLoaded', () => {
    updateRmButtons('dyn-list-milestone');
    updateRmButtons('dyn-list-misc');
  });
</script>

@endsection
