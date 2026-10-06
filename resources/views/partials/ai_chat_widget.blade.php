{{-- RDWIS AI Assistant Floating Chat Widget (RIVA) with Animated Robot Mascot --}}
@auth
<link rel="stylesheet" href="{{ asset('css/rdwis-ai-robot.css') }}?v={{ filemtime(public_path('css/rdwis-ai-robot.css')) }}">

<div id="rdwisAiWidgetContainer" class="rdwis-ai-widget" aria-label="RDWIS AI Assistant (RIVA)"{!! (isset($isAiAssistantEnabled) && !$isAiAssistantEnabled) ? ' style="display: none !important;"' : '' !!}>
    
    {{-- Interactive Speech Bubble --}}
    <div id="rdwisSpeechBubble" class="rdwis-speech-bubble" role="status" aria-live="polite" title="Click to chat with RIVA">
        <i class="fas fa-sparkles rdwis-bubble-sparkle"></i>
        <span class="rdwis-bubble-text">Hi, I'm RIVA! How may I help you today?</span>
    </div>

    {{-- Animated Robot Mascot Launcher Button --}}
    <button id="rdwisAiToggleBtn" 
            type="button" 
            class="rdwis-robot-launcher" 
            aria-expanded="false" 
            aria-controls="rdwisAiChatWindow" 
            title="Open RDWIS AI Assistant (RIVA)"
            aria-label="Toggle RDWIS AI Assistant">

        {{-- Layered Soft-3D Vector Robot Mascot --}}
        <div class="rdwis-robot-svg-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 240" class="rdwis-robot-svg" id="rdwisRobotSvg">
              <defs>
                <!-- Cyan Neon Visor Glow Filter -->
                <filter id="rivaCyanGlow" x="-50%" y="-50%" width="200%" height="200%">
                  <feGaussianBlur in="SourceGraphic" stdDeviation="2.5" result="blur1" />
                  <feGaussianBlur in="SourceGraphic" stdDeviation="5.5" result="blur2" />
                  <feMerge>
                    <feMergeNode in="blur2" />
                    <feMergeNode in="blur1" />
                    <feMergeNode in="SourceGraphic" />
                  </feMerge>
                </filter>

                <!-- Intense Blink / Thinking Pulse Filter -->
                <filter id="rivaCyanIntenseGlow" x="-60%" y="-60%" width="220%" height="220%">
                  <feGaussianBlur in="SourceGraphic" stdDeviation="3.5" result="blur1" />
                  <feGaussianBlur in="SourceGraphic" stdDeviation="8" result="blur2" />
                  <feMerge>
                    <feMergeNode in="blur2" />
                    <feMergeNode in="blur1" />
                    <feMergeNode in="SourceGraphic" />
                  </feMerge>
                </filter>

                <!-- Soft Drop Shadow Filter for Floating Pod -->
                <filter id="rivaPodShadow" x="-20%" y="-20%" width="140%" height="140%">
                  <feDropShadow dx="0" dy="4" stdDeviation="4" flood-color="#0f172a" flood-opacity="0.18" />
                </filter>

                <!-- Gradients -->
                <!-- Pearlescent White Body / Helmet -->
                <linearGradient id="rivaBodyGrad" x1="25%" y1="0%" x2="75%" y2="100%">
                  <stop offset="0%" stop-color="#FFFFFF" />
                  <stop offset="42%" stop-color="#F8FAFC" />
                  <stop offset="78%" stop-color="#E2E8F0" />
                  <stop offset="100%" stop-color="#CBD5E1" />
                </linearGradient>

                <!-- Body Bottom Ambient Occlusion / Metallic Rim -->
                <linearGradient id="rivaBodyShade" x1="50%" y1="0%" x2="50%" y2="100%">
                  <stop offset="0%" stop-color="#F1F5F9" stop-opacity="0" />
                  <stop offset="65%" stop-color="#CBD5E1" stop-opacity="0.45" />
                  <stop offset="100%" stop-color="#94A3B8" stop-opacity="0.9" />
                </linearGradient>

                <!-- Floating Arms Gradient -->
                <linearGradient id="rivaArmGradLeft" x1="15%" y1="10%" x2="85%" y2="90%">
                  <stop offset="0%" stop-color="#FFFFFF" />
                  <stop offset="50%" stop-color="#E2E8F0" />
                  <stop offset="100%" stop-color="#CBD5E1" />
                </linearGradient>

                <linearGradient id="rivaArmGradRight" x1="85%" y1="10%" x2="15%" y2="90%">
                  <stop offset="0%" stop-color="#FFFFFF" />
                  <stop offset="50%" stop-color="#E2E8F0" />
                  <stop offset="100%" stop-color="#CBD5E1" />
                </linearGradient>

                <!-- Dark Navy Visor Glass Screen -->
                <linearGradient id="rivaVisorGlass" x1="50%" y1="0%" x2="50%" y2="100%">
                  <stop offset="0%" stop-color="#0B132B" />
                  <stop offset="40%" stop-color="#101D3D" />
                  <stop offset="100%" stop-color="#1C2D54" />
                </linearGradient>

                <!-- Visor Bezel / Rim -->
                <linearGradient id="rivaVisorBezel" x1="0%" y1="0%" x2="0%" y2="100%">
                  <stop offset="0%" stop-color="#94A3B8" />
                  <stop offset="50%" stop-color="#64748B" />
                  <stop offset="100%" stop-color="#334155" />
                </linearGradient>

                <!-- Visor Specular Glass Gloss Arc -->
                <linearGradient id="rivaGlassReflection" x1="0%" y1="0%" x2="100%" y2="100%">
                  <stop offset="0%" stop-color="#FFFFFF" stop-opacity="0.45" />
                  <stop offset="45%" stop-color="#FFFFFF" stop-opacity="0.12" />
                  <stop offset="100%" stop-color="#FFFFFF" stop-opacity="0" />
                </linearGradient>

                <!-- Ear Pod Sensor Gradient -->
                <linearGradient id="rivaEarGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                  <stop offset="0%" stop-color="#F1F5F9" />
                  <stop offset="50%" stop-color="#CBD5E1" />
                  <stop offset="100%" stop-color="#94A3B8" />
                </linearGradient>

                <!-- Ground Shadow Radial Gradient -->
                <radialGradient id="rivaGroundShadowGrad" cx="50%" cy="50%" r="50%">
                  <stop offset="0%" stop-color="#0F172A" stop-opacity="0.32" />
                  <stop offset="55%" stop-color="#1E293B" stop-opacity="0.18" />
                  <stop offset="100%" stop-color="#0F172A" stop-opacity="0" />
                </radialGradient>

                <!-- Electric Cyan LED Gradient -->
                <linearGradient id="rivaCyanLed" x1="0%" y1="0%" x2="0%" y2="100%">
                  <stop offset="0%" stop-color="#7DF9FF" />
                  <stop offset="50%" stop-color="#00F2FE" />
                  <stop offset="100%" stop-color="#00C4EE" />
                </linearGradient>
              </defs>

              <!-- 1. Floating Ground Shadow -->
              <g id="robot-shadow" class="riva-shadow-rig">
                <ellipse cx="100" cy="226" rx="44" ry="7.5" fill="url(#rivaGroundShadowGrad)" />
              </g>

              <!-- 2. Master Floating Rig -->
              <g id="robot-float-rig" class="riva-float-rig">
                
                <!-- Torso / Pod Body -->
                <g id="robot-body" class="riva-body-rig" filter="url(#rivaPodShadow)">
                  <path d="M 64 126
                           C 56 142, 60 178, 80 198
                           C 88 206, 112 206, 120 198
                           C 140 178, 144 142, 136 126
                           C 130 114, 70 114, 64 126 Z"
                        fill="url(#rivaBodyGrad)" />
                  
                  <path d="M 64 126
                           C 56 142, 60 178, 80 198
                           C 88 206, 112 206, 120 198
                           C 140 178, 144 142, 136 126
                           C 130 114, 70 114, 64 126 Z"
                        fill="url(#rivaBodyShade)" />

                  <!-- Chest Seam Line & Division Lock -->
                  <path d="M 69 152 C 84 158, 92 161, 98 161 L 102 161 C 108 161, 116 158, 131 152"
                        fill="none" stroke="#94A3B8" stroke-width="1.8" stroke-linecap="round" opacity="0.8" />
                  <path d="M 94 159 L 94 165 L 106 165 L 106 159" 
                        fill="none" stroke="#94A3B8" stroke-width="1.8" stroke-linejoin="round" opacity="0.8" />

                  <!-- Chest Soft Highlight -->
                  <path d="M 78 126 C 74 136, 76 150, 88 156 C 82 144, 82 132, 88 126 Z" 
                        fill="#FFFFFF" opacity="0.65" />
                </g>

                <!-- Neck Collar -->
                <g id="robot-neck" class="riva-neck-rig">
                  <ellipse cx="100" cy="116" rx="23" ry="12" fill="#1E293B" />
                  <ellipse cx="100" cy="114" rx="20" ry="9" fill="#334155" />
                  <ellipse cx="100" cy="113" rx="16" ry="6" fill="#475569" />
                </g>

                <!-- Left Floating Arm (Waving Arm) -->
                <g id="robot-arm-left" class="riva-arm-left" filter="url(#rivaPodShadow)">
                  <path d="M 44 126
                           C 32 130, 26 150, 31 172
                           C 34 184, 46 186, 51 178
                           C 58 166, 61 144, 55 128
                           C 53 124, 47 124, 44 126 Z"
                        fill="url(#rivaArmGradLeft)" />
                  <path d="M 44 130 C 36 138, 33 154, 36 168 C 35 156, 38 142, 44 134 Z"
                        fill="#FFFFFF" opacity="0.55" />
                </g>

                <!-- Right Floating Arm (Elevates on Hover) -->
                <g id="robot-arm-right" class="riva-arm-right" filter="url(#rivaPodShadow)">
                  <path d="M 156 126
                           C 168 130, 174 150, 169 172
                           C 166 184, 154 186, 149 178
                           C 142 166, 139 144, 145 128
                           C 147 124, 153 124, 156 126 Z"
                        fill="url(#rivaArmGradRight)" />
                  <path d="M 156 130 C 164 138, 167 154, 164 168 C 165 156, 162 142, 156 134 Z"
                        fill="#FFFFFF" opacity="0.55" />
                </g>

                <!-- Head & Visor Articulated Rig -->
                <g id="robot-head" class="riva-head-rig" filter="url(#rivaPodShadow)">
                  
                  <!-- Left & Right Ear Sensor Pods -->
                  <rect x="23" y="58" width="13" height="30" rx="6.5" fill="url(#rivaEarGrad)" stroke="#94A3B8" stroke-width="1" />
                  <line x1="28" y1="65" x2="28" y2="81" stroke="#64748B" stroke-width="1.8" stroke-linecap="round" />

                  <rect x="164" y="58" width="13" height="30" rx="6.5" fill="url(#rivaEarGrad)" stroke="#94A3B8" stroke-width="1" />
                  <line x1="171" y1="65" x2="171" y2="81" stroke="#64748B" stroke-width="1.8" stroke-linecap="round" />

                  <!-- Helmet Shell -->
                  <path d="M 46 44
                           C 42 22, 64 12, 100 12
                           C 136 12, 158 22, 154 44
                           C 164 56, 166 84, 156 100
                           C 144 116, 126 120, 100 120
                           C 74 120, 56 116, 44 100
                           C 34 84, 36 56, 46 44 Z"
                        fill="url(#rivaBodyGrad)" />
                  
                  <!-- Top Helmet Gloss Highlight -->
                  <path d="M 68 18 C 84 15, 116 15, 132 18 C 142 21, 146 28, 132 26 C 114 23, 86 23, 68 26 C 58 28, 56 21, 68 18 Z"
                        fill="#FFFFFF" opacity="0.8" />

                  <!-- Head Bottom Shading -->
                  <path d="M 44 100 C 56 116, 74 120, 100 120 C 126 120, 144 116, 156 100 C 148 106, 128 112, 100 112 C 72 112, 52 106, 44 100 Z"
                        fill="url(#rivaBodyShade)" />

                  <!-- Visor Frame -->
                  <rect x="42" y="32" width="116" height="74" rx="30" fill="url(#rivaVisorBezel)" />
                  <rect x="43.5" y="33.5" width="113" height="71" rx="28.5" fill="#0A1128" />

                  <!-- Visor Screen Face (Deep Navy Glass) -->
                  <g id="robot-visor" class="riva-visor-rig">
                    <rect x="45" y="35" width="110" height="68" rx="27" fill="url(#rivaVisorGlass)" />

                    <!-- Visor Glass Gloss Reflection Arc -->
                    <path d="M 48 56 C 48 44, 58 37, 74 37 L 126 37 C 142 37, 152 44, 152 56 C 134 46, 112 43, 100 43 C 88 43, 66 46, 48 56 Z"
                          fill="url(#rivaGlassReflection)" />

                    <!-- Specular Corner Dot -->
                    <circle cx="56" cy="46" r="2.5" fill="#FFFFFF" opacity="0.35" />

                    <!-- Digital LED Face Features -->
                    <g id="robot-face-features" class="riva-face-features">

                      <!-- Left Glowing Cyan Eye (Target for Pupil Tracker & Blink) -->
                      <g id="eye-left" class="riva-eye riva-eye-left">
                        <path class="riva-eye-shape"
                              d="M 68 68 C 68 59, 86 59, 86 68 C 86 73, 68 73, 68 68 Z"
                              fill="url(#rivaCyanLed)"
                              filter="url(#rivaCyanGlow)" />
                        <circle cx="77" cy="66" r="2" fill="#FFFFFF" opacity="0.9" />
                      </g>

                      <!-- Right Glowing Cyan Eye (Target for Pupil Tracker & Blink) -->
                      <g id="eye-right" class="riva-eye riva-eye-right">
                        <path class="riva-eye-shape"
                              d="M 114 68 C 114 59, 132 59, 132 68 C 132 73, 114 73, 114 68 Z"
                              fill="url(#rivaCyanLed)"
                              filter="url(#rivaCyanGlow)" />
                        <circle cx="123" cy="66" r="2" fill="#FFFFFF" opacity="0.9" />
                      </g>

                      <!-- Glowing Cyan Digital Mouth (Morphs/Scales during Talking/Thinking) -->
                      <g id="robot-mouth" class="riva-mouth-rig">
                        <path class="riva-mouth-shape"
                              d="M 93 84 C 93 89, 107 89, 107 84 C 107 82, 93 82, 93 84 Z"
                              fill="url(#rivaCyanLed)"
                              filter="url(#rivaCyanGlow)" />
                      </g>

                    </g>
                  </g>
                </g>

              </g>
            </svg>
        </div>
    </button>

    {{-- Chat Window --}}
    <div id="rdwisAiChatWindow" class="rdwis-ai-chat-window" style="display:none;" role="dialog" aria-modal="false" aria-labelledby="rdwisAiHeaderTitle">
        {{-- Header: Cyber Teal & Deep Midnight Slate --}}
        <div class="rdwis-ai-header">
            <div class="rdwis-ai-header-info">
                <div class="rdwis-ai-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div>
                    <h6 id="rdwisAiHeaderTitle" class="rdwis-ai-title">
                        <div class="rdwis-ai-title-row">
                            <span class="rdwis-ai-riva-logo">RIVA</span>
                            <span class="rdwis-ai-badge-ai">AI Assistant</span>
                        </div>
                        <span class="rdwis-ai-riva-desc">RDWIS Intelligent Virtual Assistant</span>
                    </h6>
                    <div class="rdwis-ai-status">
                        <span class="rdwis-ai-status-dot"></span>
                        <span class="rdwis-ai-status-text">Online & Ready</span>
                    </div>
                </div>
            </div>
            <div class="rdwis-ai-header-actions">
                <button type="button" id="rdwisAiClearBtn" class="rdwis-ai-action-btn" title="Clear conversation" aria-label="Clear chat history">
                    <i class="fas fa-trash-alt"></i>
                </button>
                <button type="button" id="rdwisAiCloseBtn" class="rdwis-ai-action-btn" title="Close" aria-label="Close chat window">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        {{-- Messages Container --}}
        <div id="rdwisAiMessages" class="rdwis-ai-messages" role="log" aria-live="polite">
            {{-- Welcome Message --}}
            <div class="rdwis-ai-msg rdwis-ai-msg-assistant">
                <div class="rdwis-ai-msg-avatar"><i class="fas fa-robot"></i></div>
                <div class="rdwis-ai-msg-bubble">
                    <div class="rdwis-ai-bubble-header">
                        <span class="rdwis-ai-bubble-title"><i class="fas fa-sparkles mr-1 text-teal"></i> Welcome to RIVA</span>
                        <span class="rdwis-ai-verified-tag"><i class="fas fa-shield-alt mr-1"></i> Authorized AI</span>
                    </div>
                    <p class="rdwis-ai-welcome-text">I am <strong>RIVA</strong>, your official RDWIS Intelligent Virtual Assistant. I can assist you with querying <strong>Purchase Cases</strong>, <strong>Cheque & Payment Details</strong>, or <strong>Employee Attendance Summaries</strong> within your authorized division scope.</p>
                    
                    <div class="rdwis-ai-suggestions">
                        <div class="rdwis-ai-suggestions-title">
                            <i class="fas fa-bolt text-teal mr-1"></i> Quick Inquiries (Direct / Roman Urdu)
                        </div>
                        <button type="button" class="rdwis-ai-chip" data-query="Hamari division ka overall status aur overview batao">
                            <span class="rdwis-ai-chip-left">
                                <span class="rdwis-ai-chip-icon">🏢</span>
                                <span class="rdwis-ai-chip-text">Division Overview & Stats</span>
                            </span>
                            <i class="fas fa-chevron-right rdwis-ai-chip-arrow"></i>
                        </button>
                        <button type="button" class="rdwis-ai-chip" data-query="Hamari division ke projects aur unke funds ki detail batao (kab kitne paise aye)">
                            <span class="rdwis-ai-chip-left">
                                <span class="rdwis-ai-chip-icon">💰</span>
                                <span class="rdwis-ai-chip-text">Projects & Fund Cashflows</span>
                            </span>
                            <i class="fas fa-chevron-right rdwis-ai-chip-arrow"></i>
                        </button>
                        <button type="button" class="rdwis-ai-chip" data-query="Hamari division ke purchase cases ki list dikhao">
                            <span class="rdwis-ai-chip-left">
                                <span class="rdwis-ai-chip-icon">📦</span>
                                <span class="rdwis-ai-chip-text">Purchase Cases</span>
                            </span>
                            <i class="fas fa-chevron-right rdwis-ai-chip-arrow"></i>
                        </button>
                        <button type="button" class="rdwis-ai-chip" data-query="Hamari division ke contract cases ka status batao">
                            <span class="rdwis-ai-chip-left">
                                <span class="rdwis-ai-chip-icon">📄</span>
                                <span class="rdwis-ai-chip-text">Contract Cases (HR)</span>
                            </span>
                            <i class="fas fa-chevron-right rdwis-ai-chip-arrow"></i>
                        </button>
                        <button type="button" class="rdwis-ai-chip" data-query="Hamari division ke active employees ki list dikhao">
                            <span class="rdwis-ai-chip-left">
                                <span class="rdwis-ai-chip-icon">👥</span>
                                <span class="rdwis-ai-chip-text">HR & Staff Directory</span>
                            </span>
                            <i class="fas fa-chevron-right rdwis-ai-chip-arrow"></i>
                        </button>
                        <button type="button" class="rdwis-ai-chip" data-query="Lookup cheque and payment details">
                            <span class="rdwis-ai-chip-left">
                                <span class="rdwis-ai-chip-icon">💳</span>
                                <span class="rdwis-ai-chip-text">Cheque & Payment Details</span>
                            </span>
                            <i class="fas fa-chevron-right rdwis-ai-chip-arrow"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Typing / Loading Indicator --}}
        <div id="rdwisAiTypingIndicator" class="rdwis-ai-typing" style="display:none;" aria-hidden="true">
            <div class="rdwis-ai-msg-avatar"><i class="fas fa-robot"></i></div>
            <div class="rdwis-ai-typing-bubble">
                <span class="rdwis-ai-dot"></span>
                <span class="rdwis-ai-dot"></span>
                <span class="rdwis-ai-dot"></span>
                <span class="rdwis-ai-typing-label">RIVA is analyzing...</span>
            </div>
        </div>

        {{-- Input Form --}}
        <form id="rdwisAiChatForm" class="rdwis-ai-footer" onsubmit="return false;">
            <div class="rdwis-ai-input-wrap">
                <input type="text" 
                       id="rdwisAiInput" 
                       class="rdwis-ai-input" 
                       placeholder="Ask RIVA in English or Urdu..." 
                       autocomplete="off" 
                       maxlength="2000"
                       aria-label="Ask RIVA">
                <button type="submit" 
                        id="rdwisAiSendBtn" 
                        class="rdwis-ai-send-btn" 
                        title="Send message"
                        aria-label="Send message">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
            <div class="rdwis-ai-footer-hint">
                <small><i class="fas fa-shield-alt mr-1 text-teal"></i> Press <strong>Enter</strong> to send • RIVA is scoped to your authorized division</small>
            </div>
        </form>
    </div>
</div>

<style>
/* Header Title & Badges */
.rdwis-ai-title {
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 1px;
    line-height: 1.2;
}
.rdwis-ai-title-row {
    display: flex;
    align-items: center;
    gap: 6px;
}
.rdwis-ai-riva-logo {
    font-size: 17px;
    font-weight: 900;
    letter-spacing: 1.2px;
    color: #FFFFFF;
    text-shadow: 0 1px 4px rgba(0,0,0,0.3);
}
.rdwis-ai-badge-ai {
    font-size: 9px;
    font-weight: 800;
    background: rgba(52, 211, 153, 0.25);
    color: #6ee7b7;
    border: 1px solid rgba(52, 211, 153, 0.5);
    border-radius: 4px;
    padding: 1px 5px;
    letter-spacing: 0.5px;
}
.rdwis-ai-riva-desc {
    font-size: 10.5px;
    font-weight: 500;
    color: #a7f3d0;
    letter-spacing: 0.2px;
    opacity: 0.95;
}
.rdwis-ai-status {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    margin-top: 3px;
}
.rdwis-ai-status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #34d399;
    box-shadow: 0 0 8px #34d399;
    display: inline-block;
    animation: rdwisAiStatusGlow 2.5s infinite ease-in-out;
}
@keyframes rdwisAiStatusGlow {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.65; transform: scale(0.9); }
}
.rdwis-ai-status-text {
    color: #e2e8f0;
    font-size: 10.5px;
}
.rdwis-ai-header-actions {
    display: flex;
    align-items: center;
    gap: 5px;
}
.rdwis-ai-action-btn {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.18);
    color: rgba(255, 255, 255, 0.88);
    padding: 6px 9px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.rdwis-ai-action-btn:hover {
    background: rgba(255, 255, 255, 0.24);
    color: #FFFFFF;
    border-color: rgba(255, 255, 255, 0.4);
    transform: translateY(-1px);
}

/* Messages Area */
.rdwis-ai-messages {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    background: #F8FAFC;
    display: flex;
    flex-direction: column;
    gap: 14px;
    scroll-behavior: smooth;
}
.rdwis-ai-messages::-webkit-scrollbar {
    width: 5px;
}
.rdwis-ai-messages::-webkit-scrollbar-thumb {
    background: #CBD5E1;
    border-radius: 4px;
}

/* Message Bubbles */
.rdwis-ai-msg {
    display: flex;
    gap: 10px;
    max-width: 92%;
    animation: rdwisAiFadeIn 0.22s ease;
}
@keyframes rdwisAiFadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}
.rdwis-ai-msg-avatar {
    width: 30px;
    height: 30px;
    border-radius: 9px;
    background: linear-gradient(135deg, #0f766e, #059669);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
    margin-top: 2px;
    box-shadow: 0 2px 8px rgba(13, 148, 136, 0.3);
}
.rdwis-ai-msg-assistant {
    align-self: flex-start;
}
.rdwis-ai-msg-assistant .rdwis-ai-msg-bubble {
    background: #FFFFFF;
    color: #1E293B;
    border: 1px solid #E2E8F0;
    border-left: 4px solid #0D9488;
    border-radius: 16px 16px 16px 4px;
    padding: 12px 14px;
    font-size: 13px;
    line-height: 1.55;
    box-shadow: 0 3px 12px rgba(15, 23, 42, 0.04);
}
.rdwis-ai-bubble-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
    padding-bottom: 6px;
    border-bottom: 1px dashed rgba(13, 148, 136, 0.2);
}
.rdwis-ai-bubble-title {
    font-size: 13.5px;
    font-weight: 700;
    color: #0F766E;
}
.rdwis-ai-verified-tag {
    font-size: 9.5px;
    font-weight: 700;
    color: #047857;
    background: #ECFDF5;
    padding: 1.5px 6px;
    border-radius: 10px;
    border: 1px solid #A7F3D0;
    display: flex;
    align-items: center;
}
.rdwis-ai-welcome-text {
    margin-bottom: 10px;
    color: #334155;
    font-size: 13px;
    line-height: 1.55;
}
.rdwis-ai-suggestions-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #0F766E;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
}

.rdwis-ai-msg-user {
    align-self: flex-end;
    flex-direction: row-reverse;
}
.rdwis-ai-msg-user .rdwis-ai-msg-bubble {
    background: linear-gradient(135deg, #0d9488 0%, #059669 100%);
    color: #FFFFFF;
    border-radius: 16px 16px 4px 16px;
    padding: 11px 15px;
    font-size: 13px;
    line-height: 1.55;
    box-shadow: 0 4px 14px rgba(13, 148, 136, 0.28);
    word-break: break-word;
}
.rdwis-ai-msg-tool-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 10.5px;
    font-weight: 600;
    padding: 3px 8px;
    background: #F0FDFA;
    color: #0F766E;
    border: 1px solid #99F6E4;
    border-radius: 6px;
    margin-bottom: 8px;
    font-family: inherit;
}
.rdwis-ai-msg-error .rdwis-ai-msg-bubble {
    background: #FFF5F5;
    color: #9B1C1C;
    border: 1px solid #F8B4B4;
    border-left: 4px solid #DC2626;
}

/* Quick Suggestion Chips */
.rdwis-ai-suggestions {
    display: flex;
    flex-direction: column;
    gap: 7px;
    margin-top: 10px;
}
.rdwis-ai-chip {
    background: #FFFFFF;
    border: 1.5px solid #E2E8F0;
    border-radius: 10px;
    padding: 8px 12px;
    font-size: 12.5px;
    font-weight: 600;
    color: #1E293B;
    cursor: pointer;
    transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
}
.rdwis-ai-chip-left {
    display: flex;
    align-items: center;
    gap: 8px;
}
.rdwis-ai-chip-icon {
    font-size: 14px;
}
.rdwis-ai-chip-text {
    font-size: 12.5px;
}
.rdwis-ai-chip-arrow {
    font-size: 10px;
    color: #94A3B8;
    transition: transform 0.2s ease, color 0.2s ease;
}
.rdwis-ai-chip:hover {
    background: linear-gradient(135deg, #F0FDFA 0%, #E6FFFA 100%);
    border-color: #0D9488;
    color: #0F766E;
    transform: translateY(-2px);
    box-shadow: 0 5px 14px rgba(13, 148, 136, 0.16);
}
.rdwis-ai-chip:hover .rdwis-ai-chip-arrow {
    transform: translateX(3px);
    color: #0D9488;
}

/* Typing Indicator */
.rdwis-ai-typing {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 9px 16px;
    background: #F8FAFC;
}
.rdwis-ai-typing-bubble {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 8px 13px;
    display: flex;
    align-items: center;
    gap: 5px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
}
.rdwis-ai-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #0D9488;
    animation: rdwisAiDotPulse 1.2s infinite ease-in-out;
}
.rdwis-ai-dot:nth-child(2) { animation-delay: 0.2s; }
.rdwis-ai-dot:nth-child(3) { animation-delay: 0.4s; }
@keyframes rdwisAiDotPulse {
    0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
    30% { transform: translateY(-4px); opacity: 1; }
}
.rdwis-ai-typing-label {
    font-size: 11px;
    color: #64748B;
    margin-left: 4px;
    font-weight: 500;
}

/* Footer / Input Area */
.rdwis-ai-footer {
    padding: 12px 14px;
    background: #FFFFFF;
    border-top: 1px solid #E2E8F0;
}
.rdwis-ai-input-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #F8FAFC;
    border: 1.5px solid #CBD5E1;
    border-radius: 14px;
    padding: 5px 6px 5px 14px;
    transition: all 0.22s ease;
}
.rdwis-ai-input-wrap:focus-within {
    border-color: #0D9488;
    box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.18), 0 4px 12px rgba(13, 148, 136, 0.08);
    background: #FFFFFF;
}
.rdwis-ai-input {
    flex: 1;
    border: none;
    background: transparent;
    font-size: 13px;
    color: #1E293B;
    outline: none;
}
.rdwis-ai-input::placeholder {
    color: #94A3B8;
}
.rdwis-ai-send-btn {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, #0f766e, #059669);
    color: #FFFFFF;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    flex-shrink: 0;
}
.rdwis-ai-send-btn:hover:not(:disabled) {
    transform: scale(1.06) translateY(-1px);
    box-shadow: 0 4px 14px rgba(13, 148, 136, 0.4);
}
.rdwis-ai-send-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}
.rdwis-ai-footer-hint {
    text-align: center;
    font-size: 10.5px;
    color: #64748B;
    margin-top: 7px;
}
.text-teal {
    color: #0D9488 !important;
}
</style>

<script src="{{ asset('js/rdwis-ai-robot.js') }}?v={{ filemtime(public_path('js/rdwis-ai-robot.js')) }}"></script>

<script>
(function() {
    // In-memory conversation history for current session
    var conversationHistory = [];
    var isSubmitting = false;

    // Elements
    var toggleBtn = document.getElementById('rdwisAiToggleBtn') || document.getElementById('rdwisRobotLauncher');
    var chatWindow = document.getElementById('rdwisAiChatWindow');
    var closeBtn = document.getElementById('rdwisAiCloseBtn');
    var clearBtn = document.getElementById('rdwisAiClearBtn');
    var messagesContainer = document.getElementById('rdwisAiMessages');
    var typingIndicator = document.getElementById('rdwisAiTypingIndicator');
    var chatForm = document.getElementById('rdwisAiChatForm');
    var chatInput = document.getElementById('rdwisAiInput');
    var sendBtn = document.getElementById('rdwisAiSendBtn');

    if (!toggleBtn || !chatWindow) return;

    // Toggle Chat Window
    function toggleChat(show) {
        var willOpen = (typeof show === 'boolean') ? show : (chatWindow.style.display === 'none');
        chatWindow.style.display = willOpen ? 'flex' : 'none';
        toggleBtn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        
        // Notify Robot Controller
        if (window.RivaRobot) {
            window.RivaRobot.setChatOpen(willOpen);
        }

        if (willOpen) {
            setTimeout(function() { chatInput.focus(); }, 120);
            scrollToBottom();
        }
    }

    toggleBtn.addEventListener('click', function(e) {
        e.preventDefault();
        if (window.__RIVA_WAS_DRAGGED__) return;
        toggleChat();
    });
    
    closeBtn.addEventListener('click', function() {
        toggleChat(false);
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && chatWindow.style.display !== 'none') {
            toggleChat(false);
        }
    });

    // Clear Conversation History
    clearBtn.addEventListener('click', function() {
        conversationHistory = [];
        var messages = messagesContainer.querySelectorAll('.rdwis-ai-msg:not(:first-child)');
        messages.forEach(function(el) { el.remove(); });
    });

    // Handle Quick Suggestion Chips
    messagesContainer.addEventListener('click', function(e) {
        var chip = e.target.closest('.rdwis-ai-chip');
        if (chip) {
            var query = chip.getAttribute('data-query');
            if (query && !isSubmitting) {
                chatInput.value = query;
                sendMessage(query);
            }
        }
    });

    // Form Submit
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        var msg = chatInput.value.trim();
        if (msg && !isSubmitting) {
            sendMessage(msg);
        }
    });

    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.innerText = str;
        return div.innerHTML;
    }

    function appendMessage(role, text, toolCalled, isError) {
        var msgDiv = document.createElement('div');
        msgDiv.className = 'rdwis-ai-msg ' + (role === 'user' ? 'rdwis-ai-msg-user' : 'rdwis-ai-msg-assistant') + (isError ? ' rdwis-ai-msg-error' : '');

        var avatarHtml = role === 'user' 
            ? '' 
            : '<div class="rdwis-ai-msg-avatar"><i class="fas ' + (isError ? 'fa-exclamation-triangle' : 'fa-robot') + '"></i></div>';

        var formattedText = escapeHtml(text).replace(/\n/g, '<br>');

        var toolBadgeHtml = '';
        if (toolCalled) {
            toolBadgeHtml = '<div class="rdwis-ai-msg-tool-badge"><i class="fas fa-database mr-1"></i> Data: ' + escapeHtml(toolCalled) + '</div>';
        }

        msgDiv.innerHTML = avatarHtml + '<div class="rdwis-ai-msg-bubble">' + toolBadgeHtml + '<p class="mb-0">' + formattedText + '</p></div>';
        messagesContainer.appendChild(msgDiv);
        scrollToBottom();
    }

    function setInFlight(loading) {
        isSubmitting = loading;
        chatInput.disabled = loading;
        sendBtn.disabled = loading;
        sendBtn.innerHTML = loading ? '<i class="fas fa-spinner fa-spin"></i>' : '<i class="fas fa-paper-plane"></i>';
        typingIndicator.style.display = loading ? 'flex' : 'none';
        
        if (window.RivaRobot) {
            window.RivaRobot.setMood(loading ? 'thinking' : 'idle');
        }

        if (loading) {
            scrollToBottom();
        } else {
            chatInput.focus();
        }
    }

    function sendMessage(messageText) {
        setInFlight(true);
        appendMessage('user', messageText);
        chatInput.value = '';

        // Retrieve CSRF token following standard RDWIS convention
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
            || '{{ csrf_token() }}';

        fetch('/api/ai/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                message: messageText,
                history: conversationHistory
            })
        })
        .then(function(response) {
            return response.json().then(function(data) {
                return { status: response.status, data: data };
            }).catch(function() {
                return { status: response.status, data: null };
            });
        })
        .then(function(res) {
            setInFlight(false);
            if (res.status === 200 && res.data && res.data.success) {
                var reply = res.data.reply || 'No response received from RIVA.';
                appendMessage('assistant', reply, res.data.tool_called, false);

                // Update in-memory session history
                conversationHistory.push({ role: 'user', content: messageText });
                conversationHistory.push({ role: 'assistant', content: reply });

                // Animate robot talking mood briefly while user reads the reply
                if (window.RivaRobot) {
                    window.RivaRobot.setMood('talking');
                    setTimeout(function() {
                        if (window.RivaRobot) window.RivaRobot.setMood('idle');
                    }, 3500);
                }
            } else if (res.status === 503) {
                appendMessage(
                    'assistant', 
                    'RIVA is temporarily unavailable. Please try again shortly.', 
                    null, 
                    true
                );
            } else {
                var errDetail = (res.data && res.data.message) ? res.data.message : 'An error occurred while processing your request.';
                appendMessage('assistant', 'Error: ' + errDetail, null, true);
            }
        })
        .catch(function(err) {
            setInFlight(false);
            appendMessage(
                'assistant', 
                'Network connection error: Unable to reach the server. Please check your network connection.', 
                null, 
                true
            );
        });
    }

    // ============================================================
    // RIVA DRAGGABLE MASCOT & 2-MINUTE AUTO-RETURN CONTROLLER
    // ============================================================
    (function setupDraggableRiva() {
        var container = document.getElementById('rdwisAiWidgetContainer');
        var launcher = document.getElementById('rdwisAiToggleBtn') || document.getElementById('rdwisRobotLauncher');
        var bubble = document.getElementById('rdwisSpeechBubble');
        var header = document.querySelector('.rdwis-ai-header');

        if (!container || !launcher) return;

        var isDragging = false;
        var isPressed = false;
        var startPointerX = 0;
        var startPointerY = 0;
        var startBoxLeft = 0;
        var startBoxTop = 0;
        var autoReturnTimer = null;
        var AUTO_RETURN_MS = 120000; // 2 minutes (120,000 ms)
        var wasMovedAway = false;

        function getZoomRatio() {
            try {
                var z = parseFloat(window.getComputedStyle(document.body).zoom);
                if (!isNaN(z) && z > 0) return z;
            } catch(e) {}
            return 1.0;
        }

        function onStart(e) {
            // Ignore right clicks or middle clicks
            if (e.button !== undefined && e.button !== 0) return;
            // Ignore clicks on header controls (close, clear) or inputs
            if (e.target.closest('#rdwisAiCloseBtn, #rdwisAiClearBtn, input, textarea, select')) return;

            // Clear any pending return timer while user is holding/moving
            if (autoReturnTimer) {
                clearTimeout(autoReturnTimer);
                autoReturnTimer = null;
            }

            var rect = container.getBoundingClientRect();
            var pointer = (e.touches && e.touches.length > 0) ? e.touches[0] : e;

            isPressed = true;
            isDragging = false;
            startPointerX = pointer.clientX;
            startPointerY = pointer.clientY;
            startBoxLeft = rect.left;
            startBoxTop = rect.top;

            document.addEventListener('mousemove', onMove, { passive: false });
            document.addEventListener('mouseup', onEnd, { passive: false });
            document.addEventListener('touchmove', onMove, { passive: false });
            document.addEventListener('touchend', onEnd, { passive: false });
            document.addEventListener('touchcancel', onEnd, { passive: false });
        }

        function onMove(e) {
            if (!isPressed) return;

            var pointer = (e.touches && e.touches.length > 0) ? e.touches[0] : e;
            var deltaX = pointer.clientX - startPointerX;
            var deltaY = pointer.clientY - startPointerY;
            var dist = Math.hypot(deltaX, deltaY);

            if (!isDragging && dist > 4) {
                isDragging = true;
                window.__RIVA_WAS_DRAGGED__ = true;
                container.classList.add('is-dragging');
                document.body.style.userSelect = 'none';
                document.body.style.webkitUserSelect = 'none';
            }

            if (isDragging) {
                if (e.cancelable) e.preventDefault();

                var zoom = getZoomRatio();
                var visualLeft = startBoxLeft + deltaX;
                var visualTop = startBoxTop + deltaY;

                // Viewport boundaries clamping
                var maxLeft = window.innerWidth - container.offsetWidth - 8;
                var maxTop = window.innerHeight - container.offsetHeight - 8;
                visualLeft = Math.max(8, Math.min(visualLeft, maxLeft));
                visualTop = Math.max(8, Math.min(visualTop, maxTop));

                // Scale to CSS units according to body zoom
                var cssLeft = Math.round(visualLeft / zoom);
                var cssTop = Math.round(visualTop / zoom);

                container.style.setProperty('transition', 'none', 'important');
                container.style.setProperty('right', 'auto', 'important');
                container.style.setProperty('bottom', 'auto', 'important');
                container.style.setProperty('left', cssLeft + 'px', 'important');
                container.style.setProperty('top', cssTop + 'px', 'important');

                // Adaptive placement for window & speech bubble
                adaptQuadrant(visualLeft, visualTop);
            }
        }

        function adaptQuadrant(visualLeft, visualTop) {
            if (chatWindow) {
                if (visualTop < 380) {
                    chatWindow.classList.add('open-downwards');
                } else {
                    chatWindow.classList.remove('open-downwards');
                }
                if (visualLeft < 380) {
                    chatWindow.classList.add('open-rightwards');
                } else {
                    chatWindow.classList.remove('open-rightwards');
                }
            }
            if (bubble) {
                if (visualTop < 120) {
                    bubble.classList.add('bubble-downwards');
                } else {
                    bubble.classList.remove('bubble-downwards');
                }
            }
        }

        function onEnd() {
            if (!isPressed) return;
            isPressed = false;

            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onEnd);
            document.removeEventListener('touchmove', onMove);
            document.removeEventListener('touchend', onEnd);
            document.removeEventListener('touchcancel', onEnd);
            document.body.style.userSelect = '';
            document.body.style.webkitUserSelect = '';

            if (container) {
                container.classList.remove('is-dragging');
            }

            if (isDragging) {
                isDragging = false;
                wasMovedAway = true;

                // Keep flag briefly so following click event doesn't toggle chat
                setTimeout(function() {
                    window.__RIVA_WAS_DRAGGED__ = false;
                }, 200);

                // Start 2-Minute Return Countdown
                scheduleReturnHome();
            } else {
                window.__RIVA_WAS_DRAGGED__ = false;
            }
        }

        function scheduleReturnHome() {
            if (autoReturnTimer) clearTimeout(autoReturnTimer);
            autoReturnTimer = setTimeout(function() {
                flyHome();
            }, AUTO_RETURN_MS);
        }

        function flyHome() {
            if (!container || isPressed || isDragging) return;

            var zoom = getZoomRatio();
            var isPwa = document.body.classList.contains('has-pwa-banner');
            var homeBottom = isPwa ? 96 : 24;
            var homeRight = 28;

            var targetVisualLeft = window.innerWidth - container.offsetWidth - homeRight;
            var targetVisualTop = window.innerHeight - container.offsetHeight - homeBottom;

            var targetCssLeft = Math.round(targetVisualLeft / zoom);
            var targetCssTop = Math.round(targetVisualTop / zoom);

            container.classList.add('riva-flying-home');
            container.style.setProperty('transition', 'left 1s cubic-bezier(0.34, 1.25, 0.64, 1), top 1s cubic-bezier(0.34, 1.25, 0.64, 1)', 'important');
            container.style.setProperty('left', targetCssLeft + 'px', 'important');
            container.style.setProperty('top', targetCssTop + 'px', 'important');

            setTimeout(function() {
                container.style.removeProperty('transition');
                container.style.removeProperty('left');
                container.style.removeProperty('top');
                container.style.removeProperty('right');
                container.style.removeProperty('bottom');
                container.classList.remove('riva-flying-home');
                wasMovedAway = false;

                if (chatWindow) {
                    chatWindow.classList.remove('open-downwards', 'open-rightwards');
                }
                if (bubble) {
                    bubble.classList.remove('bubble-downwards');
                }

                if (window.RivaRobot && window.RivaRobot.wave) {
                    window.RivaRobot.wave();
                }
                if (window.RivaRobot && window.RivaRobot.showBubble) {
                    window.RivaRobot.showBubble("Back at my station! 🤖");
                }
            }, 1050);
        }

        // Attach dragstart preventer to avoid browser HTML5 ghost drag
        [launcher, bubble, header, container].forEach(function(el) {
            if (!el) return;
            el.addEventListener('dragstart', function(e) {
                e.preventDefault();
                return false;
            });
        });

        // Attach mousedown & touchstart handlers
        [launcher, bubble, header].forEach(function(el) {
            if (!el) return;
            el.addEventListener('mousedown', onStart);
            el.addEventListener('touchstart', onStart, { passive: true });
        });

        // Expose helper on window for debugging or manual trigger
        window.rivaFlyHome = flyHome;
    })();
})();
</script>
@endauth
