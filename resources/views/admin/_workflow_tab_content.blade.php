@php
    $fChain = $matrix['forward_chain'] ?? [];
    $rChain = $matrix['return_chain'] ?? [];
    $returnPolicy = $matrix['return_policy'] ?? 'historical';

    // Defaults for PS vs PT/RB
    $defaultDivNext = ($typeKey === 'PS') ? 'DProc' : 'DFinance';
    $divNext = is_array($fChain['Division'] ?? null) ? ($fChain['Division']['next'] ?? $defaultDivNext) : ($fChain['Division'] ?? $defaultDivNext);
    $procNext = is_array($fChain['DProc'] ?? null) ? ($fChain['DProc']['next'] ?? 'Division') : ($fChain['DProc'] ?? 'Division');
    $finNext = is_array($fChain['DFinance'] ?? null) ? ($fChain['DFinance']['next'] ?? 'MD') : ($fChain['DFinance'] ?? 'MD');
    $mdNext = is_array($fChain['MD'] ?? null) ? ($fChain['MD']['next'] ?? 'DDG') : ($fChain['MD'] ?? 'DDG');
    $ddgNext = is_array($fChain['DDG'] ?? null) ? ($fChain['DDG']['next'] ?? 'DG') : ($fChain['DDG'] ?? 'DG');

    $procReturn = $rChain['DProc'] ?? 'Division';
    $finReturn = $rChain['DFinance'] ?? 'Division';
    $mdReturn = $rChain['MD'] ?? 'DFinance';
    $ddgReturn = $rChain['DDG'] ?? 'MD';
    $dgReturn = $rChain['DG'] ?? 'DDG';

    // Assigned case types list
    $defaultAssigned = ($typeKey === 'PS') 
        ? ['mat', 'lic', 'stat', 'book', 'cons', 'serv'] 
        : (($typeKey === 'PT') ? ['stat', 'tran', 'tada', 'mat'] : ['civ', 'serv', 'net', 'lic']);
    $currentAssigned = $matrix['assigned_types'] ?? $defaultAssigned;
@endphp

<div class="workflow-tab-wrapper" data-type="{{ $typeKey }}">
    
    {{-- Case Type Description & Scope Alert Banner --}}
    <div class="p-3.5 rounded-lg mb-4" style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-left: 5px solid {{ $typeKey === 'PS' ? '#059669' : ($typeKey === 'PT' ? '#2563eb' : '#d97706') }} !important;">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
            <div class="d-flex align-items-center gap-2">
                <span class="badge px-2.5 py-1 font-weight-bold rajdhani text-white" style="background: {{ $typeKey === 'PS' ? '#059669' : ($typeKey === 'PT' ? '#2563eb' : '#d97706') }}; font-size: 13px;">
                    {{ $typeTitle }}
                </span>
            </div>
            <span class="badge badge-light border text-muted px-2 py-1 font-weight-bold" style="font-size: 11px;">
                @if($typeKey === 'PS')
                    SEQUENCE: Division ➔ DProc ➔ Division ➔ DFinance ➔ MD ➔ DDG ➔ DG
                @else
                    DIRECT SEQUENCE: Division ➔ DFinance ➔ MD ➔ DDG ➔ DG (DProc Bypassed)
                @endif
            </span>
        </div>
        <p class="text-muted small mb-0 mt-1" style="font-size: 12.5px; line-height: 1.5;">
            {{ $typeDescription ?? '' }}
        </p>
    </div>

    {{-- 1. ASSIGNED CASE CATEGORIES & TYPES CHECKBOXES CARD --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: #ffffff; border: 1.5px solid #cbd5e1 !important;">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(59, 130, 246, 0.1); color: #2563eb;">
                    <i class="fas fa-check-double"></i>
                </div>
                <div>
                    <h6 class="mb-0 font-weight-bold text-dark rajdhani" style="font-size: 16px;">
                        ASSIGNED CASE CATEGORIES & SUB-TYPES ({{ strtoupper($typeKey) }})
                    </h6>
                    <span class="text-muted small" style="font-size: 11.5px;">
                        Check or uncheck which specific case categories will follow this <strong>{{ strtoupper($typeKey) }}</strong> workflow route:
                    </span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-xs btn-outline-primary font-weight-bold rajdhani px-2.5 py-1" onclick="toggleAllCheckboxes('{{ $typeKey }}', true)">
                    <i class="fas fa-check-square mr-1"></i> SELECT ALL
                </button>
                <button type="button" class="btn btn-xs btn-outline-secondary font-weight-bold rajdhani px-2.5 py-1" onclick="toggleAllCheckboxes('{{ $typeKey }}', false)">
                    <i class="far fa-square mr-1"></i> DESELECT ALL
                </button>
            </div>
        </div>

        <div class="card-body p-3.5" style="background: #f8fafc;">
            <div class="row">
                @if(isset($availableCaseTypes) && is_array($availableCaseTypes))
                    @foreach($availableCaseTypes as $cKey => $cInfo)
                        @php
                            $isChecked = in_array($cKey, (array) $currentAssigned);
                        @endphp
                        <div class="col-md-6 col-lg-4 mb-2.5">
                            <div class="p-2.5 rounded d-flex align-items-start gap-2.5 h-100 case-chk-card" 
                                 id="card_chk_{{ $typeKey }}_{{ $cKey }}" 
                                 style="background: #ffffff; border: 1.5px solid {{ $isChecked ? '#2563eb' : '#e2e8f0' }}; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                <div class="custom-control custom-checkbox pt-0.5">
                                    <input type="checkbox" 
                                           name="workflows[{{ $typeKey }}][assigned_types][]" 
                                           value="{{ $cKey }}" 
                                           id="chk_{{ $typeKey }}_{{ $cKey }}" 
                                           class="custom-control-input chk-case-type-{{ $typeKey }}" 
                                           onchange="updateCardBorder('{{ $typeKey }}', '{{ $cKey }}', this.checked)"
                                           {{ $isChecked ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="chk_{{ $typeKey }}_{{ $cKey }}" style="cursor: pointer;"></label>
                                </div>
                                <div style="flex: 1; cursor: pointer;" onclick="document.getElementById('chk_{{ $typeKey }}_{{ $cKey }}').click()">
                                    <div class="d-flex align-items-center justify-content-between mb-0.5">
                                        <span class="font-weight-bold text-dark rajdhani" style="font-size: 13.5px;">
                                            <i class="{{ $cInfo['icon'] }} text-primary mr-1"></i> {{ $cInfo['label'] }}
                                        </span>
                                        <span class="badge badge-light border text-muted font-weight-bold px-1.5 py-0.5" style="font-size: 9.5px;">{{ strtoupper($cKey) }}</span>
                                    </div>
                                    <span class="text-muted small d-block" style="font-size: 11px; line-height: 1.3;">
                                        {{ $cInfo['desc'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    {{-- 2. LIVE INTERACTIVE VISUAL FLOWCHART DIAGRAM WITH CRYSTAL CLEAR HIGH CONTRAST COLORS --}}
    <div class="flowchart-container mb-4 p-4 rounded-xl" style="background: linear-gradient(135deg, #090d16 0%, #0f172a 50%, #1e293b 100%) !important; border: 2px solid #334155 !important; border-radius: 14px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4);">
        
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2.5" style="border-bottom: 1px solid rgba(255,255,255,0.12);">
            <div class="d-flex align-items-center gap-2">
                <span class="badge px-3 py-1.5 font-weight-bold rajdhani" style="background: {{ $typeKey === 'PS' ? '#059669' : ($typeKey === 'PT' ? '#2563eb' : '#d97706') }} !important; color: #ffffff !important; font-size: 13px; letter-spacing: 0.5px; border-radius: 6px;">
                    <i class="fas fa-project-diagram mr-1"></i> LIVE VISUAL FLOWCHART
                </span>
                <span class="font-weight-bold rajdhani" style="color: #38bdf8 !important; font-size: 17px; letter-spacing: 0.5px; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                    {{ strtoupper($typeKey) }} FLOW ARCHITECTURE
                </span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="small font-weight-bold rajdhani d-flex align-items-center px-2 py-1 rounded" style="color: #34d399 !important; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3);">
                    <span class="d-inline-block rounded-circle mr-1.5" style="width: 8px; height: 8px; background: #34d399;"></span> FORWARD PATHWAY
                </span>
                <span class="small font-weight-bold rajdhani d-flex align-items-center px-2 py-1 rounded" style="color: #fbbf24 !important; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3);">
                    <span class="d-inline-block rounded-circle mr-1.5" style="width: 8px; height: 8px; background: #fbbf24;"></span> RETURN PATHWAY
                </span>
            </div>
        </div>

        {{-- Interactive Flow Diagram Visual Nodes --}}
        <div class="flowchart-nodes-wrapper d-flex align-items-center justify-content-between flex-wrap gap-2 py-2">
            
            {{-- Node 1: Division --}}
            <div class="flow-node-card text-center p-3 rounded-lg" style="background: #1e293b !important; border: 1.5px solid #475569 !important; border-radius: 10px; min-width: 135px; flex: 1; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
                <span class="badge mb-1.5 px-2 py-0.5" style="background: #475569 !important; color: #f8fafc !important; font-size: 10px; font-weight: 700;">STEP 1</span>
                <div class="rajdhani" style="color: #ffffff !important; font-size: 15px; font-weight: 700; letter-spacing: 0.3px;">Division</div>
                <div style="color: #94a3b8 !important; font-size: 11px; font-weight: 500;">Initiating Unit</div>
                <div class="mt-2 pt-2" style="border-top: 1px dashed rgba(255,255,255,0.15);">
                    <span class="badge px-2 py-1 font-weight-bold" style="background: #059669 !important; color: #ffffff !important; font-size: 10px;" id="fc_fwd_Division_{{ $typeKey }}">➔ {{ $divNext }}</span>
                </div>
            </div>

            {{-- Forward Arrow Connector --}}
            <div class="flow-connector px-1 text-center" style="color: #34d399 !important;">
                <i class="fas fa-long-arrow-alt-right" style="font-size: 24px; filter: drop-shadow(0 0 6px rgba(52,211,153,0.6));"></i>
            </div>

            @if($typeKey === 'PS')
            {{-- Node 2: DProc (Active for PS Tenders) --}}
            <div class="flow-node-card text-center p-3 rounded-lg" style="background: #1e293b !important; border: 1.5px solid #0284c7 !important; border-radius: 10px; min-width: 135px; flex: 1; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
                <span class="badge mb-1.5 px-2 py-0.5" style="background: #0284c7 !important; color: #ffffff !important; font-size: 10px; font-weight: 700;">STEP 2 (COLLABORATIVE)</span>
                <div class="rajdhani" style="color: #ffffff !important; font-size: 15px; font-weight: 700; letter-spacing: 0.3px;">DProc</div>
                <div style="color: #94a3b8 !important; font-size: 11px; font-weight: 500;">Quotations & IT Scrutiny</div>
                <div class="mt-2 pt-2 d-flex justify-content-center gap-1.5" style="border-top: 1px dashed rgba(255,255,255,0.15);">
                    <span class="badge px-2 py-1 font-weight-bold" style="background: #059669 !important; color: #ffffff !important; font-size: 10px;" id="fc_fwd_DProc_{{ $typeKey }}">➔ {{ $procNext }}</span>
                    <span class="badge px-2 py-1 font-weight-bold" style="background: #d97706 !important; color: #ffffff !important; font-size: 10px;" id="fc_ret_DProc_{{ $typeKey }}">⬅ {{ $procReturn }}</span>
                </div>
            </div>

            {{-- Forward Arrow Connector --}}
            <div class="flow-connector px-1 text-center" style="color: #34d399 !important;">
                <i class="fas fa-long-arrow-alt-right" style="font-size: 24px; filter: drop-shadow(0 0 6px rgba(52,211,153,0.6));"></i>
            </div>
            @endif

            {{-- Node 3: DFinance --}}
            <div class="flow-node-card text-center p-3 rounded-lg" style="background: #1e293b !important; border: 1.5px solid #2563eb !important; border-radius: 10px; min-width: 135px; flex: 1; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
                <span class="badge mb-1.5 px-2 py-0.5" style="background: #2563eb !important; color: #ffffff !important; font-size: 10px; font-weight: 700;">{{ $typeKey === 'PS' ? 'STEP 3' : 'STEP 2' }}</span>
                <div class="rajdhani" style="color: #ffffff !important; font-size: 15px; font-weight: 700; letter-spacing: 0.3px;">Director Finance</div>
                <div style="color: #94a3b8 !important; font-size: 11px; font-weight: 500;">Budget Scrutiny</div>
                <div class="mt-2 pt-2 d-flex justify-content-center gap-1.5" style="border-top: 1px dashed rgba(255,255,255,0.15);">
                    <span class="badge px-2 py-1 font-weight-bold" style="background: #059669 !important; color: #ffffff !important; font-size: 10px;" id="fc_fwd_DFinance_{{ $typeKey }}">➔ {{ $finNext }}</span>
                    <span class="badge px-2 py-1 font-weight-bold" style="background: #d97706 !important; color: #ffffff !important; font-size: 10px;" id="fc_ret_DFinance_{{ $typeKey }}">⬅ {{ $finReturn }}</span>
                </div>
            </div>

            {{-- Forward Arrow Connector --}}
            <div class="flow-connector px-1 text-center" style="color: #34d399 !important;">
                <i class="fas fa-long-arrow-alt-right" style="font-size: 24px; filter: drop-shadow(0 0 6px rgba(52,211,153,0.6));"></i>
            </div>

            {{-- Node 4: MD (R&D) --}}
            <div class="flow-node-card text-center p-3 rounded-lg" style="background: #1e293b !important; border: 1.5px solid #d97706 !important; border-radius: 10px; min-width: 135px; flex: 1; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
                <span class="badge mb-1.5 px-2 py-0.5" style="background: #d97706 !important; color: #ffffff !important; font-size: 10px; font-weight: 700;">&le; 4 LAKH APPROVES</span>
                <div class="rajdhani" style="color: #ffffff !important; font-size: 15px; font-weight: 700; letter-spacing: 0.3px;">MD (R&D)</div>
                <div style="color: #94a3b8 !important; font-size: 11px; font-weight: 500;">HQ Approval</div>
                <div class="mt-2 pt-2 d-flex justify-content-center gap-1.5" style="border-top: 1px dashed rgba(255,255,255,0.15);">
                    <span class="badge px-2 py-1 font-weight-bold" style="background: #059669 !important; color: #ffffff !important; font-size: 10px;" id="fc_fwd_MD_{{ $typeKey }}">➔ {{ $mdNext }}</span>
                    <span class="badge px-2 py-1 font-weight-bold" style="background: #d97706 !important; color: #ffffff !important; font-size: 10px;" id="fc_ret_MD_{{ $typeKey }}">⬅ {{ $mdReturn }}</span>
                </div>
            </div>

            {{-- Forward Arrow Connector --}}
            <div class="flow-connector px-1 text-center" style="color: #34d399 !important;">
                <i class="fas fa-long-arrow-alt-right" style="font-size: 24px; filter: drop-shadow(0 0 6px rgba(52,211,153,0.6));"></i>
            </div>

            {{-- Node 5: DDG --}}
            <div class="flow-node-card text-center p-3 rounded-lg" style="background: #1e293b !important; border: 1.5px solid #0891b2 !important; border-radius: 10px; min-width: 135px; flex: 1; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
                <span class="badge mb-1.5 px-2 py-0.5" style="background: #0891b2 !important; color: #ffffff !important; font-size: 10px; font-weight: 700;">&le; 10 LAKH APPROVES</span>
                <div class="rajdhani" style="color: #ffffff !important; font-size: 15px; font-weight: 700; letter-spacing: 0.3px;">DDG Office</div>
                <div style="color: #94a3b8 !important; font-size: 11px; font-weight: 500;">Executive Review</div>
                <div class="mt-2 pt-2 d-flex justify-content-center gap-1.5" style="border-top: 1px dashed rgba(255,255,255,0.15);">
                    <span class="badge px-2 py-1 font-weight-bold" style="background: #059669 !important; color: #ffffff !important; font-size: 10px;" id="fc_fwd_DDG_{{ $typeKey }}">➔ {{ $ddgNext }}</span>
                    <span class="badge px-2 py-1 font-weight-bold" style="background: #d97706 !important; color: #ffffff !important; font-size: 10px;" id="fc_ret_DDG_{{ $typeKey }}">⬅ {{ $ddgReturn }}</span>
                </div>
            </div>

            {{-- Forward Arrow Connector --}}
            <div class="flow-connector px-1 text-center" style="color: #34d399 !important;">
                <i class="fas fa-long-arrow-alt-right" style="font-size: 24px; filter: drop-shadow(0 0 6px rgba(52,211,153,0.6));"></i>
            </div>

            {{-- Node 6: DG Terminal --}}
            <div class="flow-node-card text-center p-3 rounded-lg" style="background: #1e293b !important; border: 2px solid #10b981 !important; border-radius: 10px; min-width: 135px; flex: 1; box-shadow: 0 4px 12px rgba(16,185,129,0.2);">
                <span class="badge mb-1.5 px-2 py-0.5" style="background: #10b981 !important; color: #ffffff !important; font-size: 10px; font-weight: 700;">TERMINAL APPROVAL</span>
                <div class="rajdhani" style="color: #ffffff !important; font-size: 15px; font-weight: 700; letter-spacing: 0.3px;">DG (NRDI)</div>
                <div style="color: #94a3b8 !important; font-size: 11px; font-weight: 500;">Final Authority</div>
                <div class="mt-2 pt-2 d-flex justify-content-center gap-1.5" style="border-top: 1px dashed rgba(255,255,255,0.15);">
                    <span class="badge px-2 py-1 font-weight-bold" style="background: #d97706 !important; color: #ffffff !important; font-size: 10px;" id="fc_ret_DG_{{ $typeKey }}">⬅ {{ $dgReturn }}</span>
                </div>
            </div>

        </div>

    </div>

    {{-- 3. STEP BY STEP EDITABLE CONFIGURATION CARDS --}}
    <div class="row">
        
        {{-- STEP 1: DIVISION (INITIATOR) --}}
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 10px; background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-secondary px-2 py-0.5 font-weight-bold" style="font-size: 11px;">STEP 1</span>
                        <span class="font-weight-bold text-dark rajdhani" style="font-size: 14px;">Division (Initiator)</span>
                    </div>
                    <i class="fas fa-play-circle text-primary"></i>
                </div>
                <div class="card-body p-3">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold text-muted mb-1 rajdhani" style="font-size: 12px;">FORWARD / RELEASE TARGET:</label>
                        <select name="workflows[{{ $typeKey }}][forward][Division]" data-stage="Division" data-type="{{ $typeKey }}" data-role="fwd" class="form-control form-control-sm font-weight-bold text-dark rajdhani fc-select" style="font-size: 13px;">
                            @if($typeKey === 'PS')
                                <option value="DProc" {{ $divNext === 'DProc' ? 'selected' : '' }}>➔ Director Procurement (Tender Scrutiny)</option>
                                <option value="DFinance" {{ $divNext === 'DFinance' ? 'selected' : '' }}>➔ Director Finance (Direct Release)</option>
                            @else
                                <option value="DFinance" {{ $divNext === 'DFinance' ? 'selected' : '' }}>➔ Director Finance (Direct Release)</option>
                                <option value="DProc" {{ $divNext === 'DProc' ? 'selected' : '' }}>➔ Director Procurement (Optional)</option>
                            @endif
                        </select>
                    </div>
                    <small class="text-muted font-italic d-block" style="font-size: 11px;">
                        @if($typeKey === 'PS')
                            Note: For PS cases, Division sends case to DProc for quotation floating & IT letter scrutiny.
                        @else
                            Note: For {{ $typeKey }} cases, Division releases directly to Director Finance.
                        @endif
                    </small>
                </div>
            </div>
        </div>

        {{-- STEP 2: DIRECTOR PROCUREMENT (DPROC) --}}
        @if($typeKey === 'PS')
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 10px; background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-info px-2 py-0.5 font-weight-bold" style="font-size: 11px;">STEP 2 (COLLABORATIVE)</span>
                        <span class="font-weight-bold text-dark rajdhani" style="font-size: 14px;">Director Procurement</span>
                    </div>
                    <i class="fas fa-shopping-bag text-info"></i>
                </div>
                <div class="card-body p-3">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold text-muted mb-1 rajdhani" style="font-size: 12px;">ON SCRUTINY FINALIZE TARGET:</label>
                        <select name="workflows[{{ $typeKey }}][forward][DProc]" data-stage="DProc" data-type="{{ $typeKey }}" data-role="fwd" class="form-control form-control-sm font-weight-bold text-dark rajdhani fc-select" style="font-size: 13px;">
                            <option value="Division" {{ $procNext === 'Division' ? 'selected' : '' }}>➔ Division (Initiator for Review)</option>
                            <option value="DFinance" {{ $procNext === 'DFinance' ? 'selected' : '' }}>➔ Director Finance (Direct Forward)</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-muted mb-1 rajdhani" style="font-size: 12px;">RETURN TARGET:</label>
                        <select name="workflows[{{ $typeKey }}][return][DProc]" data-stage="DProc" data-type="{{ $typeKey }}" data-role="ret" class="form-control form-control-sm text-dark rajdhani fc-select" style="font-size: 12px;">
                            <option value="Division" {{ $procReturn === 'Division' ? 'selected' : '' }}>⬅ Division (Initiator)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- STEP 3: DIRECTOR FINANCE (DFINANCE) --}}
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 10px; background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-primary px-2 py-0.5 font-weight-bold" style="font-size: 11px;">{{ $typeKey === 'PS' ? 'STEP 3' : 'STEP 2' }}</span>
                        <span class="font-weight-bold text-dark rajdhani" style="font-size: 14px;">Director Finance</span>
                    </div>
                    <i class="fas fa-file-invoice-dollar text-primary"></i>
                </div>
                <div class="card-body p-3">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold text-muted mb-1 rajdhani" style="font-size: 12px;">RECOMMEND / FORWARD TARGET:</label>
                        <select name="workflows[{{ $typeKey }}][forward][DFinance]" data-stage="DFinance" data-type="{{ $typeKey }}" data-role="fwd" class="form-control form-control-sm font-weight-bold text-dark rajdhani fc-select" style="font-size: 13px;">
                            <option value="MD" {{ $finNext === 'MD' ? 'selected' : '' }}>➔ MD (R&D) Office</option>
                            <option value="DDG" {{ $finNext === 'DDG' ? 'selected' : '' }}>➔ DDG Office</option>
                            <option value="DG" {{ $finNext === 'DG' ? 'selected' : '' }}>➔ Director General</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-muted mb-1 rajdhani" style="font-size: 12px;">DEFAULT RETURN TARGET:</label>
                        <select name="workflows[{{ $typeKey }}][return][DFinance]" data-stage="DFinance" data-type="{{ $typeKey }}" data-role="ret" class="form-control form-control-sm text-dark rajdhani fc-select" style="font-size: 12px;">
                            <option value="Division" {{ $finReturn === 'Division' ? 'selected' : '' }}>⬅ Division (Initiator)</option>
                            @if($typeKey === 'PS')
                            <option value="DProc" {{ $finReturn === 'DProc' ? 'selected' : '' }}>⬅ Director Procurement</option>
                            @endif
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 4: MD (R&D) --}}
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 10px; background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-warning text-dark px-2 py-0.5 font-weight-bold" style="font-size: 11px;">{{ $typeKey === 'PS' ? 'STEP 4' : 'STEP 3' }}</span>
                        <span class="font-weight-bold text-dark rajdhani" style="font-size: 14px;">MD (R&D) Office</span>
                    </div>
                    <i class="fas fa-user-tie text-warning"></i>
                </div>
                <div class="card-body p-3">
                    <div class="mb-2 p-2 rounded" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
                        <span class="text-success small font-weight-bold rajdhani" style="font-size: 11.5px;">
                            <i class="fas fa-check-circle mr-1"></i> IF &le; MD LIMIT: Direct Final Approval
                        </span>
                    </div>
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold text-muted mb-1 rajdhani" style="font-size: 12px;">IF &gt; MD LIMIT FORWARD TARGET:</label>
                        <select name="workflows[{{ $typeKey }}][forward][MD]" data-stage="MD" data-type="{{ $typeKey }}" data-role="fwd" class="form-control form-control-sm font-weight-bold text-dark rajdhani fc-select" style="font-size: 13px;">
                            <option value="DDG" {{ $mdNext === 'DDG' ? 'selected' : '' }}>➔ DDG Office (Recommended)</option>
                            <option value="DG" {{ $mdNext === 'DG' ? 'selected' : '' }}>➔ DG (NRDI) Direct</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-muted mb-1 rajdhani" style="font-size: 12px;">DEFAULT RETURN TARGET:</label>
                        <select name="workflows[{{ $typeKey }}][return][MD]" data-stage="MD" data-type="{{ $typeKey }}" data-role="ret" class="form-control form-control-sm text-dark rajdhani fc-select" style="font-size: 12px;">
                            <option value="DFinance" {{ $mdReturn === 'DFinance' ? 'selected' : '' }}>⬅ Director Finance</option>
                            <option value="Division" {{ $mdReturn === 'Division' ? 'selected' : '' }}>⬅ Division (Initiator)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 5: DDG OFFICE --}}
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 10px; background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-info px-2 py-0.5 font-weight-bold" style="font-size: 11px;">{{ $typeKey === 'PS' ? 'STEP 5' : 'STEP 4' }}</span>
                        <span class="font-weight-bold text-dark rajdhani" style="font-size: 14px;">DDG Office</span>
                    </div>
                    <i class="fas fa-user-shield text-info"></i>
                </div>
                <div class="card-body p-3">
                    <div class="mb-2 p-2 rounded" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
                        <span class="text-success small font-weight-bold rajdhani" style="font-size: 11.5px;">
                            <i class="fas fa-check-circle mr-1"></i> IF &le; DDG LIMIT: Direct Final Approval
                        </span>
                    </div>
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold text-muted mb-1 rajdhani" style="font-size: 12px;">IF &gt; DDG LIMIT FORWARD TARGET:</label>
                        <select name="workflows[{{ $typeKey }}][forward][DDG]" data-stage="DDG" data-type="{{ $typeKey }}" data-role="fwd" class="form-control form-control-sm font-weight-bold text-dark rajdhani fc-select" style="font-size: 13px;">
                            <option value="DG" {{ $ddgNext === 'DG' ? 'selected' : '' }}>➔ DG (NRDI) Terminal</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-muted mb-1 rajdhani" style="font-size: 12px;">DEFAULT RETURN TARGET:</label>
                        <select name="workflows[{{ $typeKey }}][return][DDG]" data-stage="DDG" data-type="{{ $typeKey }}" data-role="ret" class="form-control form-control-sm text-dark rajdhani fc-select" style="font-size: 12px;">
                            <option value="MD" {{ $ddgReturn === 'MD' ? 'selected' : '' }}>⬅ MD Office</option>
                            <option value="DFinance" {{ $ddgReturn === 'DFinance' ? 'selected' : '' }}>⬅ Director Finance</option>
                            <option value="Division" {{ $ddgReturn === 'Division' ? 'selected' : '' }}>⬅ Division (Initiator)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 6: DG (NRDI) TERMINAL --}}
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 10px; background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-success px-2 py-0.5 font-weight-bold" style="font-size: 11px;">{{ $typeKey === 'PS' ? 'STEP 6' : 'STEP 5' }}</span>
                        <span class="font-weight-bold text-dark rajdhani" style="font-size: 14px;">DG (NRDI) Terminal</span>
                    </div>
                    <i class="fas fa-crown text-success"></i>
                </div>
                <div class="card-body p-3">
                    <div class="mb-3 p-2 rounded" style="background: #eff6ff; border: 1px solid #bfdbfe;">
                        <span class="text-primary small font-weight-bold rajdhani" style="font-size: 11.5px;">
                            <i class="fas fa-star mr-1"></i> FINAL EXECUTIVE APPROVAL AUTHORITY
                        </span>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-muted mb-1 rajdhani" style="font-size: 12px;">DEFAULT RETURN TARGET:</label>
                        <select name="workflows[{{ $typeKey }}][return][DG]" data-stage="DG" data-type="{{ $typeKey }}" data-role="ret" class="form-control form-control-sm text-dark rajdhani fc-select" style="font-size: 12px;">
                            <option value="DDG" {{ $dgReturn === 'DDG' ? 'selected' : '' }}>⬅ DDG Office</option>
                            <option value="MD" {{ $dgReturn === 'MD' ? 'selected' : '' }}>⬅ MD Office</option>
                            <option value="DFinance" {{ $dgReturn === 'DFinance' ? 'selected' : '' }}>⬅ Director Finance</option>
                            <option value="Division" {{ $dgReturn === 'Division' ? 'selected' : '' }}>⬅ Division (Initiator)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Return Policy Configuration for this flow --}}
    <div class="p-3 rounded mb-2" style="background: #ffffff; border: 1.5px solid #cbd5e1;">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h6 class="font-weight-bold text-dark mb-0 rajdhani" style="font-size: 14px;">
                    <i class="fas fa-undo text-warning mr-1"></i> RETURN SELECTION POLICY ({{ strtoupper($typeKey) }})
                </h6>
                <span class="text-muted small">Choose how authorities select return targets when returning cases</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="custom-control custom-radio">
                    <input type="radio" id="retPolHist_{{ $typeKey }}" name="workflows[{{ $typeKey }}][return_policy]" value="historical" class="custom-control-input" {{ $returnPolicy === 'historical' ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-bold text-dark rajdhani" for="retPolHist_{{ $typeKey }}" style="font-size: 13px; cursor: pointer;">
                        FLEXIBLE TRAIL (SELECT ANY PAST PARTICIPANT)
                    </label>
                </div>
                <div class="custom-control custom-radio">
                    <input type="radio" id="retPolPrev_{{ $typeKey }}" name="workflows[{{ $typeKey }}][return_policy]" value="previous" class="custom-control-input" {{ $returnPolicy === 'previous' ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-bold text-dark rajdhani" for="retPolPrev_{{ $typeKey }}" style="font-size: 13px; cursor: pointer;">
                        STRICT STEP-BY-STEP ONLY
                    </label>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function toggleAllCheckboxes(flowKey, state) {
    const boxes = document.querySelectorAll('.chk-case-type-' + flowKey);
    boxes.forEach(b => {
        b.checked = state;
        updateCardBorder(flowKey, b.value, state);
    });
}

function updateCardBorder(flowKey, code, checked) {
    const card = document.getElementById('card_chk_' + flowKey + '_' + code);
    if (card) {
        card.style.borderColor = checked ? '#2563eb' : '#e2e8f0';
    }
}
</script>
