@extends('welcome')

@section('content')
<div class="content-wrapper" style="background: #080a12; min-height: 100vh; font-family: 'Outfit', sans-serif; color: #cbd5e1;">
    <div class="content-header py-1" style="background: rgba(13,15,26,0.95); border-bottom: 1px solid #1e2235; position: sticky; top: 0; z-index: 100; backdrop-filter: blur(10px);">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold" style="color: #fff; letter-spacing: -0.8px; font-size: 1.05rem;">
                    <span class="uc-dot"></span>
                    <i class="fas fa-microchip mr-2" style="color: #3b82f6;"></i> Raise Training Case
                </h6>
                <div class="d-flex align-items-center" style="gap: 8px;">
                    <span class="uc-badge-mode">MODE: PROCUREMENT</span>
                    <a href="{{ route('training.index') }}" class="uc-close-btn">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content pt-2 pb-4">
        <div class="container-fluid">
            <div class="mx-auto" style="max-width: 1340px;">
                <form id="trainingUltraSlimForm" action="{{ route('training.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row mx-n1" style="align-items: stretch;">

                        {{-- LEFT: CORE DATA --}}
                        <div class="col-md-5 px-1 d-flex flex-column">

                            {{-- Card 1: General Info --}}
                            <div class="uc-card mb-2">
                                <div class="uc-card-header">
                                    <span class="uc-section-num">01</span>
                                    GENERAL INFORMATION
                                </div>
                                <div class="uc-card-body">
                                    <div class="form-row mx-n1">
                                        <div class="col-md-3 mb-2 px-1">
                                            <label class="uc-label">CASE ID</label>
                                            <input type="text" value="#{{ $nextId }}" class="uc-input text-primary font-weight-bold" readonly title="Static Case ID">
                                        </div>
                                        <div class="col-md-5 mb-2 px-1">
                                            <label class="uc-label">REFERENCE DATE</label>
                                            <input type="text" name="pcs_date" value="{{ date('Y-m-d') }}" class="uc-input" readonly>
                                        </div>
                                        <div class="col-md-4 mb-2 px-1">
                                            <label class="uc-label">MINUTE NO</label>
                                            <input type="number" name="pcs_minute" id="pcs_minute" class="uc-input" placeholder="0" required>
                                        </div>
                                        <div class="col-md-12 mb-2 px-1">
                                            <label class="uc-label">SUBJECT / CASE TITLE</label>
                                            <input type="text" name="pcs_title" class="uc-input" placeholder="Describe the training requirement..." required>
                                        </div>
                                        <div class="col-md-12 mb-2 px-1">
                                            <label class="uc-label">BUDGET HEAD / PROJECT</label>
                                            <select name="pcs_hed_id" id="budget_head_select" class="uc-input" required>
                                                <option value="" selected disabled>-- Select Budget Head --</option>
                                                @foreach($heads as $head)
                                                    <option value="{{ $head->hed_id }}">{{ $head->hed_code }} - {{ $head->hed_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-2 px-1">
                                            <label class="uc-label">REQ. DEPARTMENT</label>
                                            <input type="text" name="remarks_JSON[dept]" class="uc-input" placeholder="e.g. IT Dept">
                                        </div>
                                        <div class="col-md-6 mb-2 px-1">
                                            <label class="uc-label">DESIGNATION</label>
                                            <input type="text" name="remarks_JSON[rank]" class="uc-input" placeholder="Expert Rank">
                                        </div>

                                        {{-- TRAINING CATEGORY --}}
                                        <div class="col-md-12 mb-2 px-1">
                                            <label class="uc-label" style="color: #60a5fa;">TRAINING CATEGORY</label>
                                            <select name="remarks_JSON[category]" id="ultra_cat_select" class="uc-input uc-input-accent" required>
                                                <option value="">-- Choose Category --</option>
                                                <option value="domestic">DOMESTIC / INLAND TRAINING</option>
                                                <option value="in-house">IN-HOUSE / INTERNAL EXPERT</option>
                                                <option value="online">ONLINE COURSE / CERTIFICATION</option>
                                                <option value="license">SOFTWARE LICENSE / SERVICE FEE</option>
                                            </select>
                                        </div>
                                        <div class="col-md-12 px-1">
                                            <label class="uc-label">INSTITUTE / PLATFORM NAME</label>
                                            <input type="text" name="remarks_JSON[institute]" class="uc-input" placeholder="e.g. LUMS / Coursera">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Card 3: Compliance --}}
                            <div class="uc-card mt-auto">
                                <div class="uc-card-header">
                                    <span class="uc-section-num">03</span>
                                    COMPLIANCE & DOCS
                                </div>
                                <div class="uc-card-body">
                                    <div class="form-row mx-n1">
                                        <div class="col-md-6 mb-2 px-1">
                                            <label class="uc-label text-muted">REF. DOCUMENT (PDF)</label>
                                            <input type="file" name="attachment" class="uc-file-input">
                                        </div>
                                        <div class="col-md-6 mb-2 px-1">
                                            <label class="uc-label text-muted">CERTIFICATE UPLOAD</label>
                                            <input type="file" name="certificate" class="uc-file-input">
                                        </div>
                                        <div class="col-md-12 mb-2 px-1">
                                            <textarea name="remarks_JSON[notes]" class="uc-input" style="height: auto; padding-top: 5px; padding-bottom: 5px; resize: none;" rows="2" placeholder="Internal remarks..."></textarea>
                                        </div>
                                        <div class="col-md-12 px-1">
                                            <button type="submit" class="uc-submit-btn">
                                                <i class="fas fa-shield-alt mr-1"></i> INITIALIZE PROCUREMENT CASE
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- RIGHT: DYNAMIC MODULES --}}
                        <div class="col-md-7 px-1">
                            <div class="uc-card" style="height: 100%;">
                                <div class="uc-card-header d-flex justify-content-between align-items-center">
                                    <span>
                                        <span class="uc-section-num">02</span>
                                        SPECIFIC CASE DETAILS
                                    </span>
                                    <span id="ultra_badge" class="uc-active-badge d-none"></span>
                                </div>

                                <div class="uc-card-body" style="height: calc(100% - 32px); position: relative; overflow: hidden;">

                                    {{-- Placeholder --}}
                                    <div id="ultra_placeholder" class="uc-placeholder-state">
                                        <div class="uc-placeholder-inner">
                                            <div class="uc-placeholder-icon">
                                                <i class="fas fa-layer-group"></i>
                                            </div>
                                            <p class="uc-placeholder-text">SELECT A CATEGORY<br><span style="color:#3b4060; font-weight:500;">to load the module</span></p>
                                        </div>
                                    </div>

                                    {{-- Module Content Wrapper --}}
                                    <div id="ultra_content" style="display: none; height: 100%; overflow-y: auto; padding: 10px 4px;">

                                        {{-- MOD: INLAND / DOMESTIC --}}
                                        <div id="mod_domestic" class="ultra-mod" style="display: none;">
                                            <div class="uc-mod-header">
                                                <span class="uc-mod-icon">🗺️</span> LOGISTICS & TA/DA ESTIMATE
                                            </div>
                                            <div class="form-row mx-n1">
                                                <div class="col-md-6 mb-2 px-1">
                                                    <label class="uc-label">DESTINATION CITY</label>
                                                    <input type="text" name="remarks_JSON[city]" class="uc-input" placeholder="e.g. Lahore">
                                                </div>
                                                <div class="col-md-6 mb-2 px-1">
                                                    <label class="uc-label">VENUE / CENTER</label>
                                                    <input type="text" name="remarks_JSON[venue]" class="uc-input" placeholder="Exact Location">
                                                </div>
                                                <div class="col-md-4 mb-2 px-1">
                                                    <label class="uc-label">TRAVEL MODE</label>
                                                    <select name="remarks_JSON[mode]" class="uc-input">
                                                        <option value="air">✈ Air</option>
                                                        <option value="train">🚆 Train</option>
                                                        <option value="bus">🚌 Bus</option>
                                                        <option value="car">🚗 Road</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-2 px-1">
                                                    <label class="uc-label">DISTANCE (KM)</label>
                                                    <input type="number" name="remarks_JSON[dist]" class="uc-input" placeholder="0">
                                                </div>
                                                <div class="col-md-4 mb-2 px-1">
                                                    <label class="uc-label" style="color:#38bdf8;">STAY DURATION</label>
                                                    <input type="text" id="ultra_days_text" class="uc-input uc-input-readonly" readonly>
                                                </div>
                                                <div class="col-md-6 mb-2 px-1">
                                                    <label class="uc-label">DEPARTURE DATE</label>
                                                    <input type="date" id="ultra_start" name="remarks_JSON[start_date]" class="uc-input">
                                                </div>
                                                <div class="col-md-6 mb-2 px-1">
                                                    <label class="uc-label">RETURN DATE</label>
                                                    <input type="date" id="ultra_end" name="remarks_JSON[end_date]" class="uc-input">
                                                </div>
                                            </div>

                                            <div class="uc-mod-header mt-3">
                                                <span class="uc-mod-icon">💰</span> EXPENSE BREAKDOWN
                                            </div>
                                            <div class="uc-expense-grid">
                                                <div class="uc-expense-cell">
                                                    <label class="uc-label" style="color:#f59e0b;">DA RATE (Rs.)</label>
                                                    <input type="number" id="ultra_da_rate" name="remarks_JSON[da_rate]" class="uc-input uc-input-warn" placeholder="0">
                                                </div>
                                                <div class="uc-expense-cell">
                                                    <label class="uc-label" style="color:#f59e0b;">EST. DAYS</label>
                                                    <input type="text" id="ultra_da_days" class="uc-input uc-input-readonly" readonly>
                                                </div>
                                                <div class="uc-expense-cell">
                                                    <label class="uc-label" style="color:#f59e0b;">TOTAL DA</label>
                                                    <input type="text" id="ultra_da_sum" class="uc-input uc-input-readonly font-weight-bold" readonly>
                                                </div>
                                                <div class="uc-expense-cell">
                                                    <label class="uc-label">TRAVEL COST</label>
                                                    <input type="number" id="ultra_travel" name="remarks_JSON[travel_cost]" class="uc-input" placeholder="0">
                                                </div>
                                                <div class="uc-expense-cell">
                                                    <label class="uc-label">ACCOMMODATION</label>
                                                    <input type="number" id="ultra_accomm" name="remarks_JSON[accomm_cost]" class="uc-input" placeholder="0">
                                                </div>
                                                <div class="uc-expense-cell">
                                                    <label class="uc-label">MISCELLANEOUS</label>
                                                    <input type="number" id="ultra_misc" name="remarks_JSON[misc_cost]" class="uc-input" placeholder="0">
                                                </div>
                                            </div>
                                            <div class="uc-total-box mt-3">
                                                <div>
                                                    <div style="font-size:8px; font-weight:800; letter-spacing:1px; opacity:0.75;">ESTIMATED TOTAL CLAIM</div>
                                                    <div style="font-size:9px; opacity:0.55;">DA + Travel + Accommodation + Misc</div>
                                                </div>
                                                <div id="ultra_grand" class="uc-total-val">Rs. 0</div>
                                            </div>
                                        </div>

                                        {{-- MOD: IN-HOUSE --}}
                                        <div id="mod_in-house" class="ultra-mod" style="display: none;">
                                            <div class="uc-mod-header">
                                                <span class="uc-mod-icon">🎓</span> INTERNAL TRAINING LOGS
                                            </div>
                                            <div class="uc-inhouse-grid">
                                                <div class="uc-inhouse-full">
                                                    <label class="uc-label">SPEAKER / EXPERT NAME</label>
                                                    <input type="text" name="remarks_JSON[speaker]" class="uc-input" placeholder="Expert Name">
                                                </div>
                                                <div class="uc-inhouse-full">
                                                    <label class="uc-label">INTERNAL VENUE</label>
                                                    <input type="text" name="remarks_JSON[venue_internal]" class="uc-input" placeholder="e.g. Conference Hall A">
                                                </div>
                                                <div>
                                                    <label class="uc-label">EST. PARTICIPANTS</label>
                                                    <input type="number" name="remarks_JSON[participants]" class="uc-input" placeholder="0">
                                                </div>
                                                <div>
                                                    <label class="uc-label">DURATION (DAYS)</label>
                                                    <input type="number" name="remarks_JSON[duration_days]" class="uc-input" placeholder="0">
                                                </div>
                                            </div>

                                            {{-- Visual info cards --}}
                                            <div class="uc-info-cards mt-3">
                                                <div class="uc-info-card">
                                                    <i class="fas fa-users-cog"></i>
                                                    <span>Internal resources utilized</span>
                                                </div>
                                                <div class="uc-info-card">
                                                    <i class="fas fa-building"></i>
                                                    <span>On-premises delivery</span>
                                                </div>
                                                <div class="uc-info-card">
                                                    <i class="fas fa-file-signature"></i>
                                                    <span>Attendance sheet required</span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- MOD: ONLINE --}}
                                        <div id="mod_online" class="ultra-mod" style="display: none;">
                                            <div class="uc-mod-header">
                                                <span class="uc-mod-icon">💻</span> ONLINE CERTIFICATION DETAILS
                                            </div>
                                            <div class="form-row mx-n1">
                                                <div class="col-md-12 mb-2 px-1">
                                                    <label class="uc-label">PLATFORM (MS, AWS, COURSERA...)</label>
                                                    <input type="text" name="remarks_JSON[platform]" class="uc-input" placeholder="e.g. Coursera / AWS / Microsoft Learn">
                                                </div>
                                                <div class="col-md-12 mb-2 px-1">
                                                    <label class="uc-label">COURSE LINK / URL</label>
                                                    <input type="url" name="remarks_JSON[url]" class="uc-input" placeholder="https://...">
                                                </div>
                                                <div class="col-md-6 mb-2 px-1">
                                                    <label class="uc-label">CERTIFICATION FEE</label>
                                                    <input type="number" name="remarks_JSON[fee]" class="uc-input" placeholder="0.00">
                                                </div>
                                                <div class="col-md-6 mb-2 px-1">
                                                    <label class="uc-label">CURRENCY</label>
                                                    <select name="remarks_JSON[currency]" class="uc-input">
                                                        <option value="PKR">PKR (Rs.)</option>
                                                        <option value="USD">USD ($)</option>
                                                    </select>
                                                </div>
                                            </div>

                                            {{-- Platform badges --}}
                                            <div class="uc-platform-chips mt-2">
                                                <span class="uc-chip">AWS</span>
                                                <span class="uc-chip">Microsoft</span>
                                                <span class="uc-chip">Coursera</span>
                                                <span class="uc-chip">Google</span>
                                                <span class="uc-chip">Udemy</span>
                                                <span class="uc-chip">LinkedIn</span>
                                            </div>
                                        </div>

                                        {{-- MOD: LICENSE / FEES --}}
                                        <div id="mod_license" class="ultra-mod" style="display: none;">
                                            <div class="uc-mod-header">
                                                <span class="uc-mod-icon">🔑</span> LICENSE & SUBSCRIPTION DETAILS
                                            </div>
                                            <div class="form-row mx-n1">
                                                <div class="col-md-12 mb-2 px-1">
                                                    <label class="uc-label">PRODUCT / SERVICE NAME</label>
                                                    <input type="text" name="remarks_JSON[product_name]" class="uc-input" placeholder="e.g. Adobe Creative Cloud, ISO Fee">
                                                </div>
                                                <div class="col-md-8 mb-2 px-1">
                                                    <label class="uc-label">VENDOR / AUTHORITY</label>
                                                    <input type="text" name="remarks_JSON[vendor]" class="uc-input" placeholder="Vendor Name">
                                                </div>
                                                <div class="col-md-4 mb-2 px-1">
                                                    <label class="uc-label">LICENSE TYPE</label>
                                                    <select name="remarks_JSON[type_detail]" class="uc-input">
                                                        <option value="single">Single User</option>
                                                        <option value="multi">Multi-User</option>
                                                        <option value="enterprise">Enterprise</option>
                                                        <option value="perpetual">Perpetual</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-2 px-1">
                                                    <label class="uc-label">DURATION</label>
                                                    <select name="remarks_JSON[duration]" class="uc-input">
                                                        <option value="1">1 Year</option>
                                                        <option value="lifetime">Lifetime</option>
                                                        <option value="monthly">Monthly</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-2 px-1">
                                                    <label class="uc-label">QUANTITY</label>
                                                    <input type="number" name="remarks_JSON[qty]" class="uc-input" value="1">
                                                </div>
                                                <div class="col-md-4 mb-2 px-1">
                                                    <label class="uc-label text-success">EST. TOTAL PRICE</label>
                                                    <input type="number" name="remarks_JSON[total_price]" class="uc-input uc-input-accent font-weight-bold" placeholder="0.00">
                                                </div>
                                                <div class="col-md-12 mb-2 px-1">
                                                    <label class="uc-label">PURPOSE / JUSTIFICATION</label>
                                                    <textarea name="remarks_JSON[justification]" class="uc-input" style="height: auto; padding-top: 5px;" rows="3" placeholder="Explain requirements..."></textarea>
                                                </div>
                                            </div>
                                            
                                            <div class="uc-info-cards mt-2">
                                                <div class="uc-info-card">
                                                    <i class="fas fa-key"></i>
                                                    <span>Digital Asset Procurement</span>
                                                </div>
                                                <div class="uc-info-card">
                                                    <i class="fas fa-sync-alt"></i>
                                                    <span>Renewal tracking enabled</span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&display=swap');

    :root {
        --uc-bg:      #080a12;
        --uc-card:    #0e1120;
        --uc-card2:   #111425;
        --uc-border:  #1e2235;
        --uc-border2: #252840;
        --uc-accent:  #3b82f6;
        --uc-accent2: #2563eb;
        --uc-text:    #cbd5e1;
        --uc-muted:   #475569;
        --uc-label:   #64748b;
    }

    /* ── CARD ───────────────────────────────────── */
    .uc-card {
        background: var(--uc-card);
        border: 1px solid var(--uc-border);
        border-radius: 6px;
        overflow: hidden;
    }
    .uc-card-header {
        background: linear-gradient(90deg, #0d1024, #111425);
        border-bottom: 1px solid var(--uc-border);
        padding: 6px 10px;
        font-weight: 900;
        font-size: 8.5px;
        letter-spacing: 1px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 8px;
        min-height: 32px;
    }
    .uc-card-body { padding: 10px; }

    /* ── SECTION NUM ─────────────────────────────── */
    .uc-section-num {
        background: var(--uc-accent);
        color: #fff;
        font-size: 7px;
        font-weight: 900;
        padding: 1px 4px;
        border-radius: 2px;
        letter-spacing: 0;
    }

    /* ── LABELS & INPUTS ─────────────────────────── */
    .uc-label {
        font-size: 8px;
        font-weight: 800;
        color: var(--uc-label);
        letter-spacing: 0.5px;
        margin-bottom: 3px;
        display: block;
        text-transform: uppercase;
    }
    .uc-input {
        width: 100%;
        height: 28px;
        background: #080b14 !important;
        border: 1px solid var(--uc-border2) !important;
        border-radius: 3px;
        padding: 0 7px;
        color: #e2e8f0 !important;
        font-size: 10.5px;
        font-weight: 500;
        font-family: 'Outfit', sans-serif;
        transition: border-color 0.2s, box-shadow 0.2s;
        -webkit-appearance: none;
    }
    .uc-input:focus {
        border-color: var(--uc-accent) !important;
        outline: none;
        box-shadow: 0 0 0 2px rgba(59,130,246,0.12);
    }
    .uc-input-accent { border-color: rgba(59,130,246,0.4) !important; }
    .uc-input-warn   { border-color: rgba(245,158,11,0.4) !important; }
    .uc-input-readonly {
        background: #06080f !important;
        border-color: transparent !important;
        color: #38bdf8 !important;
        font-weight: 700;
    }
    select.uc-input { cursor: pointer; }

    /* ── HEADER BADGES ───────────────────────────── */
    .uc-badge-mode {
        background: transparent;
        border: 1px solid #1e2235;
        color: #475569;
        font-size: 8px;
        font-weight: 800;
        letter-spacing: 1px;
        padding: 3px 7px;
        border-radius: 3px;
    }
    .uc-close-btn {
        background: #0e1120;
        border: 1px solid #1e2235;
        color: #475569;
        font-size: 9px;
        padding: 4px 8px;
        border-radius: 3px;
        text-decoration: none;
        transition: all 0.2s;
    }
    .uc-close-btn:hover { border-color: #ef4444; color: #ef4444; }

    .uc-active-badge {
        background: rgba(59,130,246,0.15);
        border: 1px solid rgba(59,130,246,0.35);
        color: #60a5fa;
        font-size: 7.5px;
        font-weight: 900;
        letter-spacing: 1px;
        padding: 2px 7px;
        border-radius: 2px;
    }

    /* ── PLACEHOLDER STATE ───────────────────────── */
    .uc-placeholder-state {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
    }
    .uc-placeholder-inner { text-align: center; }
    .uc-placeholder-icon {
        width: 64px; height: 64px;
        background: rgba(255,255,255,0.025);
        border: 1px solid #1a1d2e;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 12px;
        font-size: 22px;
        color: #232640;
    }
    .uc-placeholder-text {
        font-size: 8.5px;
        font-weight: 900;
        color: #2a2e48;
        letter-spacing: 1.5px;
        line-height: 1.8;
        margin: 0;
    }

    /* ── MODULE HEADER ───────────────────────────── */
    .uc-mod-header {
        font-size: 9px;
        font-weight: 900;
        color: #475569;
        border-bottom: 1px solid var(--uc-border);
        padding-bottom: 5px;
        margin-bottom: 12px;
        letter-spacing: 0.8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .uc-mod-icon { font-size: 12px; }

    /* ── EXPENSE GRID ────────────────────────────── */
    .uc-expense-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 6px;
    }
    .uc-expense-cell {}

    /* ── TOTAL BOX ───────────────────────────────── */
    .uc-total-box {
        background: linear-gradient(135deg, #1d3a6b, #1e3a8a);
        border: 1px solid rgba(59,130,246,0.3);
        padding: 12px 14px;
        border-radius: 5px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #fff;
    }
    .uc-total-val {
        font-size: 18px;
        font-weight: 900;
        color: #93c5fd;
        letter-spacing: -0.5px;
    }

    /* ── IN-HOUSE GRID ───────────────────────────── */
    .uc-inhouse-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .uc-inhouse-full { grid-column: 1 / -1; }

    /* ── INFO CARDS ──────────────────────────────── */
    .uc-info-cards { display: flex; gap: 6px; flex-wrap: wrap; }
    .uc-info-card {
        background: rgba(255,255,255,0.025);
        border: 1px solid var(--uc-border);
        border-radius: 4px;
        padding: 7px 10px;
        font-size: 8.5px;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 6px;
        flex: 1;
    }
    .uc-info-card i { color: #3b82f6; font-size: 10px; }

    /* ── PLATFORM CHIPS ──────────────────────────── */
    .uc-platform-chips { display: flex; gap: 5px; flex-wrap: wrap; }
    .uc-chip {
        background: rgba(59,130,246,0.08);
        border: 1px solid rgba(59,130,246,0.2);
        color: #3b82f6;
        font-size: 8px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 2px;
        letter-spacing: 0.5px;
    }

    /* ── FILE INPUT ──────────────────────────────── */
    .uc-file-input { font-size: 8.5px; color: #475569; width: 100%; }

    /* ── SUBMIT ──────────────────────────────────── */
    .uc-submit-btn {
        width: 100%;
        background: linear-gradient(135deg, var(--uc-accent), var(--uc-accent2));
        border: none;
        border-radius: 4px;
        padding: 8px;
        color: #fff;
        font-size: 9.5px;
        font-weight: 900;
        letter-spacing: 0.8px;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(59,130,246,0.2);
        font-family: 'Outfit', sans-serif;
    }
    .uc-submit-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(59,130,246,0.35);
    }
    .uc-submit-btn:active { transform: translateY(0); }

    /* ── STATUS DOT ──────────────────────────────── */
    .uc-dot {
        display: inline-block;
        width: 6px; height: 6px;
        background: #22c55e;
        border-radius: 50%;
        margin-right: 4px;
        box-shadow: 0 0 5px #22c55e;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50%       { opacity: 0.4; }
    }

    /* ── MODULE ANIMATION ────────────────────────── */
    .ultra-mod {
        animation: modIn 0.25s ease-out forwards;
    }
    @keyframes modIn {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── SCROLLBAR ───────────────────────────────── */
    #ultra_content::-webkit-scrollbar { width: 3px; }
    #ultra_content::-webkit-scrollbar-track { background: transparent; }
    #ultra_content::-webkit-scrollbar-thumb { background: #1e2235; border-radius: 3px; }
</style>

@endsection

@section('scripts')
<script>
$(document).ready(function() {

    // 1. Backend Sync: Minute Number — LOGIC UNCHANGED
    $('#budget_head_select').on('change', function() {
        const headId = $(this).val();
        if (headId) {
            $.ajax({
                url: '/get-next-minute-training/' + headId,
                type: "GET",
                success: function(data) {
                    $('#pcs_minute').val(data.next_minute);
                }
            });
        }
    });

    // 2. UI Logic: Replace Placeholder with Module — LOGIC UNCHANGED, display method fixed
    $('#ultra_cat_select').on('change', function() {
        const val = $(this).val();

        if (!val) {
            $('#ultra_placeholder').show();
            $('#ultra_content').hide();
            $('#ultra_badge').addClass('d-none');
        } else {
            // Hide placeholder, show content wrapper
            $('#ultra_placeholder').hide();
            $('#ultra_content').show();

            // Hide all modules first, then show the selected one
            $('.ultra-mod').hide();
            $('#mod_' + val).show();

            // Update badge
            const labels = {
                'domestic' : '🗺️ INLAND',
                'in-house' : '🎓 IN-HOUSE',
                'online'   : '💻 ONLINE',
                'license'  : '🔑 LICENSE'
            };
            $('#ultra_badge').removeClass('d-none').text(labels[val] || val.toUpperCase());
        }
    });

    // 3. Calculation Engine — LOGIC UNCHANGED
    $('#ultra_start, #ultra_end').on('change', function() {
        const d1 = new Date($('#ultra_start').val());
        const d2 = new Date($('#ultra_end').val());
        if (d1 && d2 && d2 >= d1) {
            const diff = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24)) + 1;
            $('#ultra_days_text').val(diff + ' DAY' + (diff > 1 ? 'S' : ''));
            $('#ultra_da_days').val(diff);
            $('#ultra_da_days').data('v', diff);
            calcAll();
        }
    });

    $('#ultra_da_rate, #ultra_travel, #ultra_accomm, #ultra_misc').on('input', function() {
        calcAll();
    });

    function calcAll() {
        const rate   = parseFloat($('#ultra_da_rate').val()) || 0;
        const days   = parseFloat($('#ultra_da_days').data('v')) || 0;
        const daTotal = rate * days;
        $('#ultra_da_sum').val('Rs. ' + daTotal.toLocaleString());

        const travel = parseFloat($('#ultra_travel').val()) || 0;
        const accomm = parseFloat($('#ultra_accomm').val()) || 0;
        const misc   = parseFloat($('#ultra_misc').val())   || 0;

        const grand = daTotal + travel + accomm + misc;
        $('#ultra_grand').text('Rs. ' + grand.toLocaleString());
    }

});
</script>
@endsection