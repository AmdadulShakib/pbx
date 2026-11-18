@if(auth('web')->check() || auth('admin')->check())
<link rel="stylesheet" href="{{ asset('assets/site/css/pbx-dialer.css') }}?v={{ @filemtime(public_path('assets/site/css/pbx-dialer.css')) ?: time() }}&t={{ time() }}">

<!-- Remote Audio Element for WebRTC Stream -->
<audio id="pbx-remote-audio" autoplay playsinline style="position: absolute; width: 0; height: 0; opacity: 0; pointer-events: none;"></audio>

<!-- Floating Corner Dialer Container -->
<div id="pbx-floating-container">
    <!-- Floating Trigger Button (FAB) -->
    <button type="button" class="pbx-fab-btn" id="pbx-fab-btn" onclick="togglePbxDialer()" title="{{ __('PBX Phone Dialer') }}">
        <div class="pbx-fab-icon-wrap">
            <svg class="pbx-phone-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
            </svg>
            <span class="pbx-status-dot" id="pbx-status-dot"></span>
        </div>
        <div class="pbx-fab-text">
            <span class="pbx-fab-title" id="pbx-fab-label">{{ __('PBX Phone') }}</span>
            <span class="pbx-fab-subtitle" id="pbx-fab-sub">{{ __('Connecting...') }}</span>
        </div>
    </button>

    <!-- Floating In-Call PiP Mini Bar (Active Call Floating Widget - Fully Draggable like Google Meet) -->
    <div class="pbx-incall-pip-bar" id="pbx-incall-pip-bar" ondblclick="if (typeof pbxResetDialerPosition === 'function') pbxResetDialerPosition();" style="display: none;">
        <div class="pbx-pip-drag-handle" ondblclick="if (typeof pbxResetDialerPosition === 'function') pbxResetDialerPosition();" title="{{ __('Drag to move anywhere, double-click to reset to corner') }}">
            <svg width="10" height="16" viewBox="0 0 10 16" fill="currentColor">
                <circle cx="2.5" cy="2.5" r="1.5"/>
                <circle cx="7.5" cy="2.5" r="1.5"/>
                <circle cx="2.5" cy="8" r="1.5"/>
                <circle cx="7.5" cy="8" r="1.5"/>
                <circle cx="2.5" cy="13.5" r="1.5"/>
                <circle cx="7.5" cy="13.5" r="1.5"/>
            </svg>
        </div>
        <div class="pbx-pip-badge">
            <span class="pbx-pip-live-dot"></span>
            <span class="pbx-pip-timer" id="pbx-pip-timer">00:00</span>
        </div>
        <div class="pbx-pip-info" onclick="togglePbxDialer()" title="{{ __('Click to expand softphone') }}">
            <div class="pbx-pip-name" id="pbx-pip-name">{{ __('Connected Call') }}</div>
            <div class="pbx-pip-sub" id="pbx-pip-sub">{{ __('Voice Call Active') }}</div>
        </div>
        <div class="pbx-pip-actions">
            <!-- Mute Toggle -->
            <button type="button" class="pbx-pip-action-btn" id="pbx-pip-mute-btn" onclick="if (typeof pbxToggleMute === 'function') pbxToggleMute(event);" title="{{ __('Mute Microphone') }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
            </button>
            <!-- Hangup Call -->
            <button type="button" class="pbx-pip-action-btn pbx-pip-hangup" onclick="pbxHangup(event);" title="{{ __('End Call') }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 9c-1.6 0-3.15.25-4.6.72v3.1c0 .39-.23.74-.56.9-.98.49-1.87 1.12-2.66 1.85-.18.18-.43.28-.7.28-.28 0-.53-.11-.71-.29L.29 13.08a.996.996 0 0 1 0-1.41C3.34 8.78 7.46 7 12 7s8.66 1.78 11.71 4.67c.39.39.39 1.02 0 1.41l-2.48 2.48c-.18.18-.43.29-.71.29-.27 0-.52-.11-.7-.28-.79-.74-1.69-1.36-2.67-1.85-.33-.16-.56-.5-.56-.9v-3.1C15.15 9.25 13.6 9 12 9z"/></svg>
            </button>
            <!-- Float Over All Tabs & Software / Separate Window (Move to any monitor) -->
            <button type="button" class="pbx-pip-action-btn pbx-popout-btn" onclick="if (typeof pbxPopoutWindow === 'function') pbxPopoutWindow();" title="{{ __('Pop out to separate window (Move to any monitor or screen)') }}">
                <svg class="pbx-pip-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="4" width="20" height="16" rx="2"/><rect x="12" y="10" width="8" height="6" rx="1"/></svg>
            </button>
            <!-- Reset Position to Corner -->
            <button type="button" class="pbx-pip-action-btn" onclick="if (typeof pbxResetDialerPosition === 'function') pbxResetDialerPosition();" title="{{ __('Reset position to bottom-right corner') }}">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>
            </button>
            <!-- Expand Softphone -->
            <button type="button" class="pbx-pip-action-btn pbx-pip-expand" onclick="togglePbxDialer()" title="{{ __('Expand Softphone') }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
            </button>
        </div>
    </div>

    <!-- Dialer Pop-up Card -->
    <div class="pbx-dialer-card shadow-2xl" id="pbx-dialer-card">
        <!-- Header -->
        <div class="pbx-header">
            <div class="pbx-user-profile">
                <div class="pbx-user-avatar" id="pbx-header-avatar">?</div>
                <div class="pbx-user-meta">
                    <div class="pbx-user-name" id="pbx-header-user">{{ __('Loading...') }}</div>
                    <div class="pbx-user-status-row">
                        <span class="pbx-badge-ext" id="pbx-header-ext">{{ __('Ext --') }}</span>
                        <span class="pbx-badge-status" id="pbx-header-status-badge">
                            <span class="pbx-status-indicator"></span>
                            <span id="pbx-header-status-text">{{ __('Offline') }}</span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="pbx-header-actions">
                <button type="button" class="pbx-icon-btn popout-hide-in-window" onclick="if (typeof pbxResetDialerPosition === 'function') pbxResetDialerPosition();" title="{{ __('Dock / Reset to bottom-right corner') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>
                </button>
                <button type="button" class="pbx-icon-btn pbx-popout-btn popout-hide-in-window" onclick="if (typeof pbxPopoutWindow === 'function') pbxPopoutWindow();" title="{{ __('Pop out to separate window (Move to any monitor or screen)') }}">
                    <svg class="pbx-pip-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="4" width="20" height="16" rx="2"/><rect x="12" y="10" width="8" height="6" rx="1"/></svg>
                </button>
                <button type="button" class="pbx-icon-btn pbx-minimize-btn popout-hide-in-window" onclick="togglePbxDialer()" title="{{ __('Minimize') }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </button>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="pbx-nav-tabs">
            <button type="button" class="pbx-tab-item active" id="pbx-tab-keypad" onclick="switchPbxTab('keypad')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                <span>{{ __('Keypad') }}</span>
            </button>
            <button type="button" class="pbx-tab-item" id="pbx-tab-contacts" onclick="switchPbxTab('contacts')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>{{ __('Colleagues') }}</span>
            </button>
            <button type="button" class="pbx-tab-item" id="pbx-tab-recents" onclick="switchPbxTab('recents')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span>{{ __('Recent') }}</span>
            </button>
        </div>

        <!-- View: Keypad -->
        <div id="pbx-view-keypad" class="pbx-tab-view">
            <!-- Screen Display -->
            <div class="pbx-screen">
                <div class="pbx-callee-match" id="pbx-callee-name"></div>
                <div class="pbx-screen-row">
                    <div class="pbx-number-display" id="pbx-display-input" placeholder="{{ __('Dial extension...') }}"></div>
                    <button type="button" class="pbx-backspace-btn" id="pbx-backspace-btn" onclick="pbxBackspace()" title="{{ __('Backspace') }}">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"/><line x1="18" y1="9" x2="12" y2="15"/><line x1="12" y1="9" x2="18" y2="15"/></svg>
                    </button>
                </div>
            </div>

            <!-- Team Bridge Shortcut Pill -->
            <div class="pbx-team-bridge-pill" onclick="if (typeof pbxJoinTeamBridge === 'function') pbxJoinTeamBridge('700');" title="{{ __('Join Company Multi-Party Voice Room') }}">
                <div class="pbx-bridge-pill-left">
                    <div class="pbx-bridge-pill-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div>
                        <div class="pbx-bridge-pill-title">{{ __('Team Bridge (Ext 700)') }}</div>
                        <div class="pbx-bridge-pill-sub">{{ __('Multi-party conference room') }}</div>
                    </div>
                </div>
                <button type="button" class="pbx-bridge-pill-btn">
                    <span>{{ __('Join') }}</span>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>

            <!-- 12-Key Dialpad -->
            <div class="pbx-keypad-grid">
                <button type="button" class="pbx-key-btn" onclick="pbxDialDigit('1')"><span class="num">1</span><span class="sub">&nbsp;</span></button>
                <button type="button" class="pbx-key-btn" onclick="pbxDialDigit('2')"><span class="num">2</span><span class="sub">ABC</span></button>
                <button type="button" class="pbx-key-btn" onclick="pbxDialDigit('3')"><span class="num">3</span><span class="sub">DEF</span></button>
                <button type="button" class="pbx-key-btn" onclick="pbxDialDigit('4')"><span class="num">4</span><span class="sub">GHI</span></button>
                <button type="button" class="pbx-key-btn" onclick="pbxDialDigit('5')"><span class="num">5</span><span class="sub">JKL</span></button>
                <button type="button" class="pbx-key-btn" onclick="pbxDialDigit('6')"><span class="num">6</span><span class="sub">MNO</span></button>
                <button type="button" class="pbx-key-btn" onclick="pbxDialDigit('7')"><span class="num">7</span><span class="sub">PQRS</span></button>
                <button type="button" class="pbx-key-btn" onclick="pbxDialDigit('8')"><span class="num">8</span><span class="sub">TUV</span></button>
                <button type="button" class="pbx-key-btn" onclick="pbxDialDigit('9')"><span class="num">9</span><span class="sub">WXYZ</span></button>
                <button type="button" class="pbx-key-btn special" onclick="pbxDialDigit('*')"><span class="num">*</span><span class="sub">&nbsp;</span></button>
                <button type="button" class="pbx-key-btn" onclick="pbxDialDigit('0')"><span class="num">0</span><span class="sub">+</span></button>
                <button type="button" class="pbx-key-btn special" onclick="pbxDialDigit('#')"><span class="num">#</span><span class="sub">&nbsp;</span></button>
            </div>

            <!-- Call Trigger Action -->
            <div class="pbx-call-bar">
                <button type="button" class="pbx-main-call-btn" onclick="pbxMakeCall()" title="{{ __('Start Audio Call') }}">
                    <div class="pbx-call-btn-inner">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </div>
                </button>
            </div>
        </div>

        <!-- View: Colleagues Directory -->
        <div id="pbx-view-contacts" class="pbx-tab-view" style="display: none;">
            <div class="pbx-search-wrap">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="pbx-contacts-search" placeholder="{{ __('Search colleague or extension...') }}" oninput="filterColleaguesList(this.value)">
            </div>
            <div class="pbx-contacts-scroll" id="pbx-contacts-container">
                <div class="pbx-empty-state">
                    <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                    <div>{{ __('Loading colleagues...') }}</div>
                </div>
            </div>
        </div>

        <!-- View: Recent Calls -->
        <div id="pbx-view-recents" class="pbx-tab-view" style="display: none;">
            <div class="pbx-recents-scroll" id="pbx-recents-container">
                <div class="pbx-empty-state">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <div class="mt-2">{{ __('No recent calls') }}</div>
                    <small class="text-muted">{{ __('Your call logs will appear here') }}</small>
                </div>
            </div>
        </div>

        <!-- ACTIVE CALL SCREEN OVERLAY (Premium PBX Call State) -->
        <div class="pbx-call-overlay" id="pbx-call-overlay">
            <!-- In-Call Top Header Bar (Minimize & Meet-Style Floating PiP) -->
            <div class="pbx-incall-topbar" ondblclick="if (typeof pbxResetDialerPosition === 'function') pbxResetDialerPosition();">
                <div class="pbx-incall-top-status">
                    <span class="pbx-live-pulse-dot"></span>
                    <span class="pbx-incall-state-text" id="pbx-incall-top-state">{{ __('Voice Call Active') }}</span>
                </div>
                <div class="pbx-incall-top-actions">
                    <!-- Reset position to corner -->
                    <button type="button" class="pbx-icon-btn" onclick="if (typeof pbxResetDialerPosition === 'function') pbxResetDialerPosition();" title="{{ __('Dock / Reset to bottom-right corner') }}">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>
                    </button>
                    <!-- Google Meet Style OS-Level Floating PiP Window (Always on Top over all software) -->
                    <button type="button" class="pbx-icon-btn pbx-popout-btn" onclick="if (typeof pbxPopoutWindow === 'function') pbxPopoutWindow();" title="{{ __('Pop out to separate window (Move to any monitor or screen)') }}">
                        <svg class="pbx-pip-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="4" width="20" height="16" rx="2"/><rect x="12" y="10" width="8" height="6" rx="1"/></svg>
                    </button>
                    <!-- Minimize to Draggable Floating PiP Pill -->
                    <button type="button" class="pbx-icon-btn pbx-incall-minimize-btn" onclick="if (typeof closePbxDialer === 'function') closePbxDialer(); else togglePbxDialer();" title="{{ __('Minimize to floating bar') }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    </button>
                </div>
            </div>

            <!-- Callee Info -->
            <div class="pbx-call-hero">
                <div class="pbx-avatar-halo" id="pbx-avatar-halo">
                    <div class="pbx-call-avatar" id="pbx-overlay-avatar">?</div>
                </div>
                <div class="pbx-call-details">
                    <h4 class="pbx-call-name" id="pbx-overlay-name">{{ __('Internal Call') }}</h4>
                    <div class="pbx-call-ext-pill" id="pbx-overlay-ext">{{ __('Ext: ----') }}</div>
                    <div class="pbx-call-badge-state" id="pbx-overlay-badge-state">
                        <span class="pbx-badge-dot"></span>
                        <span id="pbx-overlay-status">{{ __('Connecting...') }}</span>
                    </div>
                    <div class="pbx-call-duration" id="pbx-overlay-timer">00:00</div>

                    <!-- Audio Equalizer Waves -->
                    <div class="pbx-audio-waves" id="pbx-audio-waves">
                        <span></span><span></span><span></span><span></span><span></span>
                    </div>
                </div>
            </div>

            <!-- In-Call Control Grid (6 Essential PBX Action Buttons) -->
            <div class="pbx-incall-grid">
                <!-- 1. Mute -->
                <button type="button" class="pbx-grid-btn" id="pbx-mute-btn" onclick="pbxToggleMute()" title="{{ __('Mute / Unmute') }}">
                    <div class="pbx-btn-icon">
                        <svg class="icon-mic-on" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
                        <svg class="icon-mic-off" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;"><line x1="1" y1="1" x2="23" y2="23"/><path d="M9 9v3a3 3 0 0 0 5.12 2.12M15 9.34V4a3 3 0 0 0-5.94-.6"/><path d="M17 16.95A7 7 0 0 1 5 12v-2m14 0v2a7 7 0 0 1-.11 1.23"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
                    </div>
                    <span class="pbx-btn-label" id="pbx-mute-label">{{ __('Mute') }}</span>
                </button>

                <!-- 2. Hold -->
                <button type="button" class="pbx-grid-btn" id="pbx-hold-btn" onclick="pbxToggleHold()" title="{{ __('Hold / Resume') }}">
                    <div class="pbx-btn-icon">
                        <svg class="icon-hold" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="10" y1="15" x2="10" y2="9"/><line x1="14" y1="15" x2="14" y2="9"/></svg>
                    </div>
                    <span class="pbx-btn-label" id="pbx-hold-label">{{ __('Hold') }}</span>
                </button>

                <!-- 3. Keypad (DTMF in call) -->
                <button type="button" class="pbx-grid-btn" id="pbx-dtmf-btn" onclick="pbxToggleInCallKeypad()" title="{{ __('Send DTMF Tones') }}">
                    <div class="pbx-btn-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    </div>
                    <span class="pbx-btn-label">{{ __('Keypad') }}</span>
                </button>

                <!-- 4. Transfer -->
                <button type="button" class="pbx-grid-btn" id="pbx-transfer-btn" onclick="pbxToggleTransferPanel()" title="{{ __('Transfer Call') }}">
                    <div class="pbx-btn-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 14 20 9 15 4"/><path d="M4 20v-7a4 4 0 0 1 4-4h12"/></svg>
                    </div>
                    <span class="pbx-btn-label">{{ __('Transfer') }}</span>
                </button>

                <!-- 5. Speaker / Audio toggle -->
                <button type="button" class="pbx-grid-btn" id="pbx-speaker-btn" onclick="pbxToggleSpeaker()" title="{{ __('Speaker Audio') }}">
                    <div class="pbx-btn-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
                    </div>
                    <span class="pbx-btn-label" id="pbx-speaker-label">{{ __('Audio') }}</span>
                </button>

                <!-- 6. Contacts Consult -->
                <button type="button" class="pbx-grid-btn" onclick="pbxShowConsultColleagues()" title="{{ __('Colleagues') }}">
                    <div class="pbx-btn-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                    </div>
                    <span class="pbx-btn-label">{{ __('Directory') }}</span>
                </button>
            </div>

            <!-- In-Call DTMF Sheet Overlay -->
            <div class="pbx-incall-drawer" id="pbx-incall-dtmf-drawer">
                <div class="pbx-drawer-header">
                    <span>{{ __('Send DTMF Tone') }}</span>
                    <button type="button" onclick="pbxToggleInCallKeypad()">&times;</button>
                </div>
                <div class="pbx-mini-keypad">
                    <button type="button" onclick="pbxSendDtmf('1')">1</button>
                    <button type="button" onclick="pbxSendDtmf('2')">2</button>
                    <button type="button" onclick="pbxSendDtmf('3')">3</button>
                    <button type="button" onclick="pbxSendDtmf('4')">4</button>
                    <button type="button" onclick="pbxSendDtmf('5')">5</button>
                    <button type="button" onclick="pbxSendDtmf('6')">6</button>
                    <button type="button" onclick="pbxSendDtmf('7')">7</button>
                    <button type="button" onclick="pbxSendDtmf('8')">8</button>
                    <button type="button" onclick="pbxSendDtmf('9')">9</button>
                    <button type="button" onclick="pbxSendDtmf('*')">*</button>
                    <button type="button" onclick="pbxSendDtmf('0')">0</button>
                    <button type="button" onclick="pbxSendDtmf('#')">#</button>
                </div>
            </div>

            <!-- In-Call Transfer Drawer -->
            <div class="pbx-incall-drawer" id="pbx-incall-transfer-drawer">
                <div class="pbx-drawer-header">
                    <span>{{ __('Transfer Call') }}</span>
                    <button type="button" onclick="pbxToggleTransferPanel()">&times;</button>
                </div>
                <div class="p-3">
                    <!-- Quick Bridge Transfer Button -->
                    <div class="d-flex align-items-center justify-content-between mb-2.5 p-2 rounded bg-primary-subtle border border-primary-subtle" onclick="document.getElementById('pbx-transfer-target').value='700'; pbxExecuteTransfer();" style="cursor: pointer;" title="{{ __('Instantly bridge current call to Team Bridge 700') }}">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary font-monospace">700</span>
                            <span class="small fw-bold text-primary">{{ __('Move to Team Bridge') }}</span>
                        </div>
                        <span class="badge bg-success small">{{ __('Join 700') }}</span>
                    </div>

                    <label class="form-label text-white small fw-bold mb-1">{{ __('Transfer to Extension:') }}</label>
                    <div class="input-group mb-2">
                        <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" id="pbx-transfer-target" placeholder="e.g. 10003 or 700">
                        <button class="btn btn-primary btn-sm" type="button" onclick="pbxExecuteTransfer()">
                            {{ __('Transfer') }}
                        </button>
                    </div>
                    <div class="small text-muted">{{ __('Select colleague or enter extension number.') }}</div>
                </div>
            </div>

            <!-- Large Prominent Hangup Button -->
            <div class="pbx-hangup-wrap">
                <button type="button" class="pbx-hangup-btn" onclick="pbxHangup()" title="{{ __('End Call') }}">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.68 13.31a16 16 0 0 0 3.41 2.6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7 2 2 0 0 1 1.72 2v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.42 19.42 0 0 1-3.33-2.67m-2.67-3.34a19.79 19.79 0 0 1-3.07-8.63A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
            </div>
        </div>

        <!-- INCOMING CALL MODAL (Ultra-Modern Glassmorphism) -->
        <div class="pbx-incoming-modal" id="pbx-incoming-modal">
            <div class="pbx-incoming-content">
                <div class="pbx-incoming-halo">
                    <div class="pbx-incoming-avatar" id="pbx-incoming-avatar">?</div>
                </div>
                <div class="pbx-incoming-badge">{{ __('INCOMING PBX CALL') }}</div>
                <h4 class="pbx-incoming-caller-name" id="pbx-incoming-caller">{{ __('Unknown Caller') }}</h4>
                <div class="pbx-incoming-caller-ext" id="pbx-incoming-ext">{{ __('Ext: ----') }}</div>

                <div class="pbx-incoming-actions">
                    <button type="button" class="pbx-ans-circle-btn decline" onclick="pbxRejectIncoming()" title="{{ __('Decline Call') }}">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        <span>{{ __('Decline') }}</span>
                    </button>
                    <button type="button" class="pbx-ans-circle-btn answer" onclick="pbxAnswerIncoming()" title="{{ __('Answer Call') }}">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <span>{{ __('Answer') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/vendor/jssip/jssip.min.js') }}"></script>
<script src="{{ asset('assets/site/js/pbx-dialer.js') }}?v={{ @filemtime(public_path('assets/site/js/pbx-dialer.js')) ?: time() }}&t={{ time() }}"></script>
@endif
