@if(auth('web')->check() || auth('admin')->check())
<link rel="stylesheet" href="{{ asset('assets/site/css/pbx-dialer.css') }}">

<!-- Remote Audio Element for WebRTC Stream -->
<audio id="pbx-remote-audio" autoplay playsinline style="display: none;"></audio>

<!-- Floating Corner Dialer Container -->
<div id="pbx-floating-container">
    <!-- Floating Trigger Button (FAB) -->
    <button type="button" class="pbx-fab-btn" id="pbx-fab-btn" onclick="togglePbxDialer()" title="{{ __('PBX Phone Dialer') }}">
        <div class="pbx-fab-icon-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
            </svg>
            <span class="pbx-status-dot" id="pbx-status-dot"></span>
        </div>
        <span class="pbx-fab-label" id="pbx-fab-label">{{ __('PBX Phone') }}</span>
    </button>

    <!-- Dialer Pop-up Card -->
    <div class="pbx-dialer-card shadow-lg" id="pbx-dialer-card">
        <!-- Header -->
        <div class="pbx-header">
            <div class="pbx-user-info">
                <span class="pbx-badge-ext" id="pbx-header-ext">{{ __('Ext --') }}</span>
                <span class="pbx-user-name" id="pbx-header-user">{{ __('PBX User') }}</span>
            </div>
            <div class="pbx-header-actions">
                <button type="button" onclick="closePbxDialer()" title="{{ __('Minimize') }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </button>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="pbx-tabs">
            <button type="button" class="pbx-tab-btn active" id="pbx-tab-keypad" onclick="switchPbxTab('keypad')">
                {{ __('Keypad') }}
            </button>
            <button type="button" class="pbx-tab-btn" id="pbx-tab-contacts" onclick="switchPbxTab('contacts')">
                {{ __('Colleagues') }}
            </button>
        </div>

        <!-- View: Keypad -->
        <div id="pbx-view-keypad">
            <!-- Screen Display -->
            <div class="pbx-screen">
                <div class="pbx-callee-name" id="pbx-callee-name"></div>
                <div class="pbx-number-row">
                    <div class="pbx-number-display" id="pbx-display-input"></div>
                    <button type="button" class="pbx-backspace-btn" onclick="pbxBackspace()" title="{{ __('Backspace') }}">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"/><line x1="18" y1="9" x2="12" y2="15"/><line x1="12" y1="9" x2="18" y2="15"/></svg>
                    </button>
                </div>
            </div>

            <!-- Keypad Digits -->
            <div class="pbx-keypad">
                <div class="pbx-key" onclick="pbxDialDigit('1')"><span class="pbx-key-digit">1</span><span class="pbx-key-sub">&nbsp;</span></div>
                <div class="pbx-key" onclick="pbxDialDigit('2')"><span class="pbx-key-digit">2</span><span class="pbx-key-sub">ABC</span></div>
                <div class="pbx-key" onclick="pbxDialDigit('3')"><span class="pbx-key-digit">3</span><span class="pbx-key-sub">DEF</span></div>
                <div class="pbx-key" onclick="pbxDialDigit('4')"><span class="pbx-key-digit">4</span><span class="pbx-key-sub">GHI</span></div>
                <div class="pbx-key" onclick="pbxDialDigit('5')"><span class="pbx-key-digit">5</span><span class="pbx-key-sub">JKL</span></div>
                <div class="pbx-key" onclick="pbxDialDigit('6')"><span class="pbx-key-digit">6</span><span class="pbx-key-sub">MNO</span></div>
                <div class="pbx-key" onclick="pbxDialDigit('7')"><span class="pbx-key-digit">7</span><span class="pbx-key-sub">PQRS</span></div>
                <div class="pbx-key" onclick="pbxDialDigit('8')"><span class="pbx-key-digit">8</span><span class="pbx-key-sub">TUV</span></div>
                <div class="pbx-key" onclick="pbxDialDigit('9')"><span class="pbx-key-digit">9</span><span class="pbx-key-sub">WXYZ</span></div>
                <div class="pbx-key" onclick="pbxDialDigit('*')"><span class="pbx-key-digit">*</span><span class="pbx-key-sub">&nbsp;</span></div>
                <div class="pbx-key" onclick="pbxDialDigit('0')"><span class="pbx-key-digit">0</span><span class="pbx-key-sub">+</span></div>
                <div class="pbx-key" onclick="pbxDialDigit('#')"><span class="pbx-key-digit">#</span><span class="pbx-key-sub">&nbsp;</span></div>
            </div>

            <!-- Call Action Button -->
            <div class="pbx-actions-bar">
                <button type="button" class="pbx-call-btn" onclick="pbxMakeCall()" title="{{ __('Start Audio Call') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                </button>
            </div>
        </div>

        <!-- View: Contacts -->
        <div id="pbx-view-contacts" style="display: none;">
            <div class="pbx-contacts-list" id="pbx-contacts-container">
                <div class="text-center text-muted py-4 small">
                    <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                    <div>{{ __('Loading colleagues...') }}</div>
                </div>
            </div>
        </div>

        <!-- Active Call Screen Overlay -->
        <div class="pbx-call-overlay" id="pbx-call-overlay">
            <div class="pbx-overlay-avatar" id="pbx-overlay-avatar">?</div>
            <div>
                <div class="pbx-overlay-name" id="pbx-overlay-name">{{ __('Internal Call') }}</div>
                <div class="pbx-overlay-ext" id="pbx-overlay-ext">{{ __('Ext: ----') }}</div>
                <div class="pbx-overlay-timer" id="pbx-overlay-timer">00:00</div>
                <div class="pbx-overlay-status" id="pbx-overlay-status">{{ __('Connecting...') }}</div>
            </div>
            <div class="pbx-overlay-controls">
                <button type="button" class="pbx-ctrl-btn" id="pbx-mute-btn" onclick="pbxToggleMute()" title="{{ __('Mute Microphone') }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
                </button>
                <button type="button" class="pbx-ctrl-btn hangup" onclick="pbxHangup()" title="{{ __('End Call') }}">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.68 13.31a16 16 0 0 0 3.41 2.6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7 2 2 0 0 1 1.72 2v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.42 19.42 0 0 1-3.33-2.67m-2.67-3.34a19.79 19.79 0 0 1-3.07-8.63A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
            </div>
        </div>

        <!-- Incoming Call Modal -->
        <div class="pbx-incoming-modal" id="pbx-incoming-modal">
            <div class="pbx-incoming-avatar" id="pbx-incoming-avatar">?</div>
            <div class="pbx-incoming-title">{{ __('Incoming Call') }}</div>
            <div class="pbx-incoming-caller" id="pbx-incoming-caller">{{ __('Unknown Caller') }}</div>
            <div class="pbx-incoming-actions">
                <button type="button" class="pbx-reject-btn" onclick="pbxRejectIncoming()" title="{{ __('Decline') }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
                <button type="button" class="pbx-ans-btn" onclick="pbxAnswerIncoming()" title="{{ __('Answer') }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/vendor/jssip/jssip.min.js') }}"></script>
<script src="{{ asset('assets/site/js/pbx-dialer.js') }}"></script>
@endif
