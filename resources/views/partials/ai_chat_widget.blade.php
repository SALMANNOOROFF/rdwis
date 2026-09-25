{{-- RDWIS AI Assistant Floating Chat Widget (RIVA) --}}
@auth
<div id="rdwisAiWidgetContainer" class="rdwis-ai-widget" aria-label="RIVA - RDWIS Intelligent Virtual Assistant">
    {{-- Floating Toggle Launcher Button with Distinct Jewel Styling --}}
    <button id="rdwisAiToggleBtn" 
            type="button" 
            class="rdwis-ai-launcher-btn" 
            aria-expanded="false" 
            aria-controls="rdwisAiChatWindow" 
            title="Open RIVA (RDWIS Intelligent Virtual Assistant)"
            aria-label="Toggle RIVA Chat">
        <span class="rdwis-ai-floating-badge"><i class="fas fa-sparkles"></i> RIVA</span>
        <span class="rdwis-ai-icon-open"><i class="fas fa-robot"></i></span>
        <span class="rdwis-ai-icon-close" style="display:none;"><i class="fas fa-times"></i></span>
        <span class="rdwis-ai-pulse-ring"></span>
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
                            <span class="rdwis-ai-badge-ai">AI</span>
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
                    <p class="mb-1 font-weight-bold" style="font-size: 13.5px; color: #0f766e;">
                        <i class="fas fa-sparkles mr-1"></i> Welcome to RIVA
                    </p>
                    <p class="mb-2">I am <strong>RIVA</strong>, your official RDWIS Intelligent Virtual Assistant. I can assist you with querying <strong>Purchase Cases</strong>, <strong>Cheque & Payment Details</strong>, or <strong>Employee Attendance Summaries</strong> within your authorized division scope.</p>
                    <div class="rdwis-ai-suggestions">
                        <button type="button" class="rdwis-ai-chip" data-query="What is the status of purchase case 97?">
                            <span class="rdwis-ai-chip-icon">📦</span>
                            <span>Purchase Case Status</span>
                        </button>
                        <button type="button" class="rdwis-ai-chip" data-query="Lookup cheque and payment details">
                            <span class="rdwis-ai-chip-icon">💳</span>
                            <span>Cheque & Payment Details</span>
                        </button>
                        <button type="button" class="rdwis-ai-chip" data-query="Get attendance summary for employee">
                            <span class="rdwis-ai-chip-icon">📅</span>
                            <span>Employee Attendance Summary</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Typing / Loading Indicator (Hidden by default) --}}
        <div id="rdwisAiTypingIndicator" class="rdwis-ai-typing" style="display:none;" aria-hidden="true">
            <div class="rdwis-ai-msg-avatar"><i class="fas fa-robot"></i></div>
            <div class="rdwis-ai-typing-bubble">
                <span class="rdwis-ai-dot"></span>
                <span class="rdwis-ai-dot"></span>
                <span class="rdwis-ai-dot"></span>
                <span class="rdwis-ai-typing-label">RIVA is processing...</span>
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
/* ============================================================
   RIVA - RDWIS Intelligent Virtual Assistant Unique Theme
   Palette: Cyber-Teal & Electric Emerald over Deep Cosmic Slate
   Distinctive, modern, and prominently standing out from the standard theme
   ============================================================ */
.rdwis-ai-widget {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 99999;
    font-family: inherit;
}

/* Floating Launcher Button with Jewel Gradient */
.rdwis-ai-launcher-btn {
    position: relative;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #091e2f 0%, #0f4c47 50%, #059669 100%);
    color: #ffffff;
    border: 2px solid rgba(52, 211, 153, 0.45);
    cursor: pointer;
    box-shadow: 0 8px 26px rgba(9, 30, 47, 0.45), 0 0 18px rgba(16, 185, 129, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    outline: none;
}
.rdwis-ai-launcher-btn:hover {
    transform: scale(1.08) translateY(-2px);
    box-shadow: 0 12px 32px rgba(9, 30, 47, 0.55), 0 0 26px rgba(16, 185, 129, 0.6);
}
.rdwis-ai-launcher-btn:focus-visible {
    outline: 3px solid rgba(16, 185, 129, 0.65);
    outline-offset: 3px;
}

/* Floating Launcher Badge */
.rdwis-ai-floating-badge {
    position: absolute;
    top: -9px;
    right: -4px;
    background: linear-gradient(135deg, #10b981, #0d9488);
    color: #ffffff;
    font-size: 9.5px;
    font-weight: 800;
    letter-spacing: 0.6px;
    padding: 2px 7px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
    border: 1px solid rgba(255, 255, 255, 0.7);
    pointer-events: none;
    white-space: nowrap;
    text-transform: uppercase;
}

.rdwis-ai-pulse-ring {
    position: absolute;
    top: -4px;
    left: -4px;
    right: -4px;
    bottom: -4px;
    border-radius: 50%;
    border: 2px solid #10b981;
    animation: rdwisAiPulse 2.8s infinite;
    pointer-events: none;
    opacity: 0.75;
}
@keyframes rdwisAiPulse {
    0% { transform: scale(1); opacity: 0.85; }
    50% { transform: scale(1.18); opacity: 0; }
    100% { transform: scale(1); opacity: 0; }
}

/* Chat Window Container */
.rdwis-ai-chat-window {
    position: absolute;
    bottom: 76px;
    right: 0;
    width: 395px;
    height: 540px;
    max-height: calc(100vh - 100px);
    background: #FFFFFF;
    border-radius: 16px;
    border: 1px solid rgba(13, 148, 136, 0.3);
    box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.3), 0 0 0 1px rgba(0, 0, 0, 0.04);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: rdwisAiSlideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes rdwisAiSlideUp {
    from {
        opacity: 0;
        transform: translateY(18px) scale(0.96);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Header */
.rdwis-ai-header {
    background: linear-gradient(135deg, #091e2f 0%, #0d3835 55%, #064e3b 100%);
    color: #FFFFFF;
    padding: 13px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(52, 211, 153, 0.25);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
}
.rdwis-ai-header-info {
    display: flex;
    align-items: center;
    gap: 12px;
}
.rdwis-ai-avatar {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    background: linear-gradient(135deg, #0d9488 0%, #10b981 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: #ffffff;
    box-shadow: 0 0 12px rgba(16, 185, 129, 0.45);
    border: 1.5px solid rgba(255, 255, 255, 0.35);
    flex-shrink: 0;
}
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
    padding: 1px 4px;
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
    box-shadow: 0 0 6px #34d399;
    display: inline-block;
}
.rdwis-ai-status-text {
    color: #e2e8f0;
    font-size: 10.5px;
}
.rdwis-ai-header-actions {
    display: flex;
    align-items: center;
    gap: 4px;
}
.rdwis-ai-action-btn {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: rgba(255, 255, 255, 0.88);
    padding: 6px 9px;
    border-radius: 7px;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.15s ease;
}
.rdwis-ai-action-btn:hover {
    background: rgba(255, 255, 255, 0.22);
    color: #FFFFFF;
    border-color: rgba(255, 255, 255, 0.35);
}

/* Messages Area */
.rdwis-ai-messages {
    flex: 1;
    overflow-y: auto;
    padding: 15px;
    background: #F8FAFC;
    display: flex;
    flex-direction: column;
    gap: 13px;
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
    gap: 9px;
    max-width: 90%;
    animation: rdwisAiFadeIn 0.2s ease;
}
@keyframes rdwisAiFadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}
.rdwis-ai-msg-avatar {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: linear-gradient(135deg, #0f766e, #059669);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
    margin-top: 2px;
    box-shadow: 0 2px 6px rgba(13, 148, 136, 0.25);
}
.rdwis-ai-msg-assistant {
    align-self: flex-start;
}
.rdwis-ai-msg-assistant .rdwis-ai-msg-bubble {
    background: #FFFFFF;
    color: #1E293B;
    border: 1px solid #E2E8F0;
    border-left: 3.5px solid #0D9488;
    border-radius: 14px 14px 14px 4px;
    padding: 11px 13px;
    font-size: 13px;
    line-height: 1.5;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}
.rdwis-ai-msg-user {
    align-self: flex-end;
    flex-direction: row-reverse;
}
.rdwis-ai-msg-user .rdwis-ai-msg-bubble {
    background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%);
    color: #FFFFFF;
    border-radius: 14px 14px 4px 14px;
    padding: 11px 14px;
    font-size: 13px;
    line-height: 1.5;
    box-shadow: 0 3px 12px rgba(13, 148, 136, 0.3);
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
    border-left: 3.5px solid #DC2626;
}

/* Quick Suggestion Chips */
.rdwis-ai-suggestions {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-top: 10px;
}
.rdwis-ai-chip {
    background: #FFFFFF;
    border: 1px solid #CBD5E1;
    border-radius: 8px;
    padding: 7px 11px;
    text-align: left;
    font-size: 12px;
    font-weight: 500;
    color: #334155;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}
.rdwis-ai-chip-icon {
    font-size: 14px;
}
.rdwis-ai-chip:hover {
    background: #F0FDFA;
    border-color: #0D9488;
    color: #0F766E;
    transform: translateX(3px);
    box-shadow: 0 3px 8px rgba(13, 148, 136, 0.15);
}

/* Typing Indicator */
.rdwis-ai-typing {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 6px 15px;
    background: #F8FAFC;
}
.rdwis-ai-typing-bubble {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-left: 3.5px solid #0D9488;
    border-radius: 12px 12px 12px 2px;
    padding: 8px 12px;
    display: flex;
    align-items: center;
    gap: 5px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
}
.rdwis-ai-dot {
    width: 6px;
    height: 6px;
    background: #0D9488;
    border-radius: 50%;
    animation: rdwisAiBounce 1.4s infinite ease-in-out both;
}
.rdwis-ai-dot:nth-child(1) { animation-delay: -0.32s; }
.rdwis-ai-dot:nth-child(2) { animation-delay: -0.16s; }
.rdwis-ai-typing-label {
    font-size: 11.5px;
    font-weight: 500;
    color: #0F766E;
    margin-left: 6px;
}
@keyframes rdwisAiBounce {
    0%, 80%, 100% { transform: scale(0); }
    40% { transform: scale(1); }
}

/* Footer / Input Wrap */
.rdwis-ai-footer {
    padding: 11px 13px;
    background: #FFFFFF;
    border-top: 1px solid #E2E8F0;
}
.rdwis-ai-input-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #F8FAFC;
    border: 1.5px solid #CBD5E1;
    border-radius: 10px;
    padding: 4px 6px 4px 12px;
    transition: all 0.2s ease;
}
.rdwis-ai-input-wrap:focus-within {
    border-color: #0D9488;
    box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.18);
    background: #FFFFFF;
}
.rdwis-ai-input {
    flex: 1;
    border: none;
    background: transparent;
    outline: none;
    font-size: 13px;
    color: #0F172A;
    padding: 6px 0;
}
.rdwis-ai-input::placeholder {
    color: #94A3B8;
}
.rdwis-ai-send-btn {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: linear-gradient(135deg, #0D9488 0%, #059669 100%);
    color: #FFFFFF;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    transition: all 0.2s ease;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(13, 148, 136, 0.35);
}
.rdwis-ai-send-btn:hover:not(:disabled) {
    background: linear-gradient(135deg, #0F766E 0%, #047857 100%);
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(13, 148, 136, 0.45);
}
.rdwis-ai-send-btn:disabled {
    background: #CBD5E1;
    cursor: not-allowed;
    opacity: 0.65;
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

/* Mobile & Tablet Responsiveness */
@media (max-width: 480px) {
    .rdwis-ai-widget {
        bottom: 16px;
        right: 16px;
    }
    .rdwis-ai-chat-window {
        position: fixed;
        bottom: 12px;
        right: 12px;
        left: 12px;
        width: auto;
        height: 84vh;
        max-height: 84vh;
        border-radius: 14px;
    }
}
</style>

<script>
(function() {
    // In-memory conversation history for current page session
    var conversationHistory = [];
    var isSubmitting = false;

    // Elements
    var toggleBtn = document.getElementById('rdwisAiToggleBtn');
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
        
        var iconOpen = toggleBtn.querySelector('.rdwis-ai-icon-open');
        var iconClose = toggleBtn.querySelector('.rdwis-ai-icon-close');
        if (iconOpen && iconClose) {
            iconOpen.style.display = willOpen ? 'none' : 'inline-block';
            iconClose.style.display = willOpen ? 'inline-block' : 'none';
        }

        if (willOpen) {
            setTimeout(function() { chatInput.focus(); }, 100);
            scrollToBottom();
        }
    }

    toggleBtn.addEventListener('click', function() { toggleChat(); });
    closeBtn.addEventListener('click', function() { toggleChat(false); });

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
})();
</script>
@endauth
