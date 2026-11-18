/**
 * PBX Enterprise WebRTC Softphone & Floating Dialer Engine
 * Powered by JsSIP & Web Audio API
 */

(function () {
    'use strict';

    // Global PBX State
    let ua = null;
    let currentSession = null;
    let userCreds = null;
    let isCallMuted = false;
    let isCallHeld = false;
    let isSpeakerMuted = false;
    let callTimerInterval = null;
    let callStartTime = null;
    let audioCtx = null;
    let activeRingtone = null;
    let activeRingback = null;

    // Google Meet-Style Floating PiP Window Handle
    let pbxPipWindow = null;

    // Team Bridge Conference Polling State
    let confPollInterval = null;
    let currentConfRoom = null;

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Helper: Element Selector (Looks inside floating PiP window document first if active)
    function $(id) {
        if (typeof pbxPipWindow !== 'undefined' && pbxPipWindow && !pbxPipWindow.closed && pbxPipWindow.document) {
            try {
                const el = pbxPipWindow.document.getElementById(id);
                if (el) return el;
            } catch (e) {}
        }
        return document.getElementById(id);
    }

    // =========================================================================
    // Web Audio Synthesizer (Zero External MP3 Files Needed)
    // =========================================================================
    function getAudioContext() {
        if (!audioCtx) {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (AudioContext) {
                audioCtx = new AudioContext();
            }
        }
        if (audioCtx && audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
        return audioCtx;
    }

    // DTMF Frequencies (Dual-Tone Multi-Frequency)
    const dtmfFrequencies = {
        '1': [697, 1209], '2': [697, 1336], '3': [697, 1477],
        '4': [770, 1209], '5': [770, 1336], '6': [770, 1477],
        '7': [852, 1209], '8': [852, 1336], '9': [852, 1477],
        '*': [941, 1209], '0': [941, 1336], '#': [941, 1477]
    };

    function playDtmfTone(char) {
        const freqs = dtmfFrequencies[char];
        const ctx = getAudioContext();
        if (!ctx || !freqs) return;

        try {
            const now = ctx.currentTime;
            const osc1 = ctx.createOscillator();
            const osc2 = ctx.createOscillator();
            const gain = ctx.createGain();

            osc1.type = 'sine';
            osc2.type = 'sine';
            osc1.frequency.setValueAtTime(freqs[0], now);
            osc2.frequency.setValueAtTime(freqs[1], now);

            gain.gain.setValueAtTime(0.09, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.14);

            osc1.connect(gain);
            osc2.connect(gain);
            gain.connect(ctx.destination);

            osc1.start(now);
            osc2.start(now);
            osc1.stop(now + 0.14);
            osc2.stop(now + 0.14);
        } catch (e) {
            console.warn('[PBX Audio] Tone error:', e);
        }
    }

    let activeToneGain = null;

    // Incoming Call Ringtone Synthesizer (Realistic Phone Ring)
    function startIncomingRingtone() {
        stopAllAudioTones();
        const ctx = getAudioContext();
        if (!ctx) return;

        function playBurst() {
            try {
                const now = ctx.currentTime;
                const osc1 = ctx.createOscillator();
                const osc2 = ctx.createOscillator();
                const gain = ctx.createGain();
                activeToneGain = gain;

                osc1.type = 'sine';
                osc2.type = 'sine';
                osc1.frequency.setValueAtTime(440, now);
                osc2.frequency.setValueAtTime(480, now);

                gain.gain.setValueAtTime(0.12, now);
                gain.gain.setValueAtTime(0.12, now + 1.4);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 1.45);

                osc1.connect(gain);
                osc2.connect(gain);
                gain.connect(ctx.destination);

                osc1.start(now);
                osc2.start(now);
                osc1.stop(now + 1.45);
                osc2.stop(now + 1.45);
            } catch (e) {}
        }

        playBurst();
        activeRingtone = setInterval(playBurst, 3500);
    }

    // Outgoing Ringback Tone (Tut-tut when calling someone)
    let activeOscillators = [];

    function startOutgoingRingback() {
        stopAllAudioTones();
        const ctx = getAudioContext();
        if (!ctx) return;

        function playRingback() {
            try {
                const now = ctx.currentTime;
                const osc1 = ctx.createOscillator();
                const osc2 = ctx.createOscillator();
                const gain = ctx.createGain();
                activeToneGain = gain;
                activeOscillators = [osc1, osc2];

                osc1.type = 'sine';
                osc2.type = 'sine';
                osc1.frequency.setValueAtTime(440, now);
                osc2.frequency.setValueAtTime(480, now);

                gain.gain.setValueAtTime(0.05, now);
                gain.gain.setValueAtTime(0.05, now + 1.2);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 1.25);

                osc1.connect(gain);
                osc2.connect(gain);
                gain.connect(ctx.destination);

                osc1.start(now);
                osc2.start(now);
                osc1.stop(now + 1.25);
                osc2.stop(now + 1.25);
            } catch (e) {}
        }

        playRingback();
        activeRingback = setInterval(playRingback, 3000);
    }

    // Call End Beep
    function playCallEndTone() {
        stopAllAudioTones();
        const ctx = getAudioContext();
        if (!ctx) return;

        try {
            const now = ctx.currentTime;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(480, now);
            gain.gain.setValueAtTime(0.1, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(now);
            osc.stop(now + 0.3);
        } catch (e) {}
    }

    function stopAllAudioTones() {
        if (activeRingtone) {
            clearInterval(activeRingtone);
            activeRingtone = null;
        }
        if (activeRingback) {
            clearInterval(activeRingback);
            activeRingback = null;
        }
        if (activeOscillators && activeOscillators.length > 0) {
            activeOscillators.forEach(osc => {
                try {
                    osc.stop();
                    osc.disconnect();
                } catch (e) {}
            });
            activeOscillators = [];
        }
        if (activeToneGain) {
            try {
                const ctx = getAudioContext();
                if (ctx) {
                    activeToneGain.gain.cancelScheduledValues(ctx.currentTime);
                    activeToneGain.gain.setValueAtTime(0, ctx.currentTime);
                }
                activeToneGain.disconnect();
            } catch (e) {}
            activeToneGain = null;
        }
    }

    // =========================================================================
    // UI Helpers & Status Display
    // =========================================================================
    function setStatus(state, label) {
        const dot = $('pbx-status-dot');
        const fabSub = $('pbx-fab-sub');
        const badge = $('pbx-header-status-badge');
        const badgeText = $('pbx-header-status-text');

        if (dot) {
            dot.className = 'pbx-status-dot ' + state;
        }
        if (fabSub) {
            fabSub.textContent = label;
        }
        if (badge && badgeText) {
            badge.className = 'pbx-badge-status ' + state;
            badgeText.textContent = label;
        }
    }

    function restorePipBarPosition() {
        const pipBar = $('pbx-incall-pip-bar');
        if (!pipBar) return;
        try {
            const saved = sessionStorage.getItem('pbx_pip_pos');
            if (saved) {
                const pos = JSON.parse(saved);
                const rect = pipBar.getBoundingClientRect();
                const maxLeft = Math.max(10, window.innerWidth - (rect.width || 340) - 12);
                const maxTop = Math.max(10, window.innerHeight - (rect.height || 54) - 12);
                const left = Math.max(12, Math.min(maxLeft, pos.left));
                const top = Math.max(12, Math.min(maxTop, pos.top));
                pipBar.style.position = 'fixed';
                pipBar.style.left = left + 'px';
                pipBar.style.top = top + 'px';
                pipBar.style.right = 'auto';
                pipBar.style.bottom = 'auto';
            }
        } catch (e) {}
    }

    function initPipBarDraggable() {
        const pipBar = $('pbx-incall-pip-bar');
        if (!pipBar || pipBar._dragInit) return;
        pipBar._dragInit = true;

        let isDragging = false;
        let startX = 0, startY = 0;
        let initialLeft = 0, initialTop = 0;
        let hasMoved = false;

        function onPointerDown(e) {
            // Don't drag if clicking buttons
            if (e.target.closest('button')) return;

            isDragging = true;
            hasMoved = false;
            startX = e.clientX ?? (e.touches && e.touches[0].clientX) ?? 0;
            startY = e.clientY ?? (e.touches && e.touches[0].clientY) ?? 0;

            const rect = pipBar.getBoundingClientRect();
            initialLeft = rect.left;
            initialTop = rect.top;

            pipBar.style.position = 'fixed';
            pipBar.style.left = initialLeft + 'px';
            pipBar.style.top = initialTop + 'px';
            pipBar.style.right = 'auto';
            pipBar.style.bottom = 'auto';

            pipBar.classList.add('is-dragging');

            window.addEventListener('pointermove', onPointerMove, { passive: false });
            window.addEventListener('pointerup', onPointerUp);
            window.addEventListener('pointercancel', onPointerUp);
        }

        function onPointerMove(e) {
            if (!isDragging) return;
            const curX = e.clientX ?? (e.touches && e.touches[0].clientX) ?? 0;
            const curY = e.clientY ?? (e.touches && e.touches[0].clientY) ?? 0;

            const deltaX = curX - startX;
            const deltaY = curY - startY;

            if (Math.abs(deltaX) > 4 || Math.abs(deltaY) > 4) {
                hasMoved = true;
            }

            const rect = pipBar.getBoundingClientRect();
            let newLeft = initialLeft + deltaX;
            let newTop = initialTop + deltaY;

            const maxLeft = Math.max(10, window.innerWidth - rect.width - 12);
            const maxTop = Math.max(10, window.innerHeight - rect.height - 12);

            newLeft = Math.max(12, Math.min(maxLeft, newLeft));
            newTop = Math.max(12, Math.min(maxTop, newTop));

            pipBar.style.left = newLeft + 'px';
            pipBar.style.top = newTop + 'px';

            e.preventDefault();
        }

        function onPointerUp() {
            if (!isDragging) return;
            isDragging = false;
            pipBar.classList.remove('is-dragging');

            window.removeEventListener('pointermove', onPointerMove);
            window.removeEventListener('pointerup', onPointerUp);
            window.removeEventListener('pointercancel', onPointerUp);

            if (hasMoved) {
                const rect = pipBar.getBoundingClientRect();
                try {
                    sessionStorage.setItem('pbx_pip_pos', JSON.stringify({ left: rect.left, top: rect.top }));
                } catch (e) {}
            }
        }

        pipBar.addEventListener('pointerdown', onPointerDown);

        // Prevent click-to-expand if user was dragging
        const infoEl = pipBar.querySelector('.pbx-pip-info');
        if (infoEl) {
            infoEl.addEventListener('click', function (e) {
                if (hasMoved) {
                    e.stopImmediatePropagation();
                    e.preventDefault();
                    hasMoved = false;
                }
            }, true);
        }

        window.addEventListener('resize', function () {
            if (pipBar.style.display !== 'none') {
                restorePipBarPosition();
            }
        });
    }

    function initDialerCardDraggable() {
        const card = $('pbx-dialer-card');
        if (!card || card._dragInit) return;
        card._dragInit = true;

        let isDragging = false;
        let startX = 0, startY = 0;
        let initialLeft = 0, initialTop = 0;
        let hasMoved = false;

        function onPointerDown(e) {
            if (e.target.closest('button') || e.target.closest('input') || e.target.closest('a')) return;
            if (card.classList.contains('in-pip-mode')) return;

            isDragging = true;
            hasMoved = false;
            startX = e.clientX ?? (e.touches && e.touches[0].clientX) ?? 0;
            startY = e.clientY ?? (e.touches && e.touches[0].clientY) ?? 0;

            const rect = card.getBoundingClientRect();
            initialLeft = rect.left;
            initialTop = rect.top;

            card.style.position = 'fixed';
            card.style.left = initialLeft + 'px';
            card.style.top = initialTop + 'px';
            card.style.right = 'auto';
            card.style.bottom = 'auto';

            card.classList.add('is-dragging');

            window.addEventListener('pointermove', onPointerMove, { passive: false });
            window.addEventListener('pointerup', onPointerUp);
            window.addEventListener('pointercancel', onPointerUp);
        }

        function onPointerMove(e) {
            if (!isDragging) return;
            const curX = e.clientX ?? (e.touches && e.touches[0].clientX) ?? 0;
            const curY = e.clientY ?? (e.touches && e.touches[0].clientY) ?? 0;

            const deltaX = curX - startX;
            const deltaY = curY - startY;

            if (Math.abs(deltaX) > 4 || Math.abs(deltaY) > 4) {
                hasMoved = true;
            }

            const rect = card.getBoundingClientRect();
            let newLeft = initialLeft + deltaX;
            let newTop = initialTop + deltaY;

            const maxLeft = Math.max(10, window.innerWidth - rect.width - 12);
            const maxTop = Math.max(10, window.innerHeight - rect.height - 12);

            newLeft = Math.max(12, Math.min(maxLeft, newLeft));
            newTop = Math.max(12, Math.min(maxTop, newTop));

            card.style.left = newLeft + 'px';
            card.style.top = newTop + 'px';

            e.preventDefault();
        }

        function onPointerUp() {
            if (!isDragging) return;
            isDragging = false;
            card.classList.remove('is-dragging');

            window.removeEventListener('pointermove', onPointerMove);
            window.removeEventListener('pointerup', onPointerUp);
            window.removeEventListener('pointercancel', onPointerUp);
        }

        const header = card.querySelector('.pbx-header');
        const inCallTopbar = card.querySelector('.pbx-incall-topbar');
        if (header) {
            header.style.cursor = 'grab';
            header.addEventListener('pointerdown', onPointerDown);
        }
        if (inCallTopbar) {
            inCallTopbar.style.cursor = 'grab';
            inCallTopbar.addEventListener('pointerdown', onPointerDown);
        }
    }

    function syncInCallPipBar() {
        const pipBar = $('pbx-incall-pip-bar');
        const card = $('pbx-dialer-card');
        const fab = $('pbx-fab-btn');
        const isCallActive = !!(currentSession && !currentSession.isEnded());

        if (!pipBar) return;

        // Ensure drag listeners are active
        initPipBarDraggable();
        initDialerCardDraggable();

        if (isCallActive) {
            // When call is active:
            if (card && card.classList.contains('open')) {
                // Dialer card is open -> hide PiP mini bar, hide fab
                pipBar.style.display = 'none';
                if (fab) fab.style.display = 'none';
            } else {
                // Dialer card is minimized/closed -> SHOW FLOATING IN-CALL PIP BAR!
                pipBar.style.display = 'flex';
                if (fab) fab.style.display = 'none';

                restorePipBarPosition();

                const nameEl = $('pbx-pip-name');
                const subEl = $('pbx-pip-sub');
                const overlayName = $('pbx-overlay-name')?.textContent;
                const overlayExt = $('pbx-overlay-ext')?.textContent;
                const targetName = currentSession._targetName || (currentSession.remote_identity ? currentSession.remote_identity.display_name : null);
                const targetNum = currentSession._targetNum || (currentSession.remote_identity ? currentSession.remote_identity.uri.user : 'Caller');

                if (nameEl) nameEl.textContent = overlayName || targetName || targetNum;
                const cleanExt = (overlayExt || targetNum || '').replace(/^(ext:?\s*)+/i, '').trim();
                const callStatus = currentSession._callStatusText || 'Connected';
                if (subEl) subEl.textContent = cleanExt ? `Ext ${cleanExt} • ${callStatus}` : callStatus;

                const muteBtn = $('pbx-pip-mute-btn');
                if (muteBtn) {
                    if (isCallMuted) {
                        muteBtn.style.background = '#dc2626';
                        muteBtn.style.borderColor = '#ef4444';
                    } else {
                        muteBtn.style.background = '#1e293b';
                        muteBtn.style.borderColor = '#334155';
                    }
                }
            }
        } else {
            // Call not active -> Hide PiP bar, restore FAB
            pipBar.style.display = 'none';
            if (fab) fab.style.display = 'flex';
        }
    }

    window.syncPbxPipBar = syncInCallPipBar;

    window.togglePbxDialer = function () {
        const card = $('pbx-dialer-card');
        if (!card) return;
        getAudioContext();
        if (card.classList.contains('open')) {
            window.closePbxDialer();
        } else {
            // When opening, reset card position to dock nicely at bottom-right
            card.style.position = '';
            card.style.left = '';
            card.style.top = '';
            card.style.right = '';
            card.style.bottom = '';
            card.classList.add('open');
            syncInCallPipBar();
        }
    };

    window.closePbxDialer = function () {
        const card = $('pbx-dialer-card');
        if (card) {
            card.classList.remove('open');
            card.style.position = '';
            card.style.left = '';
            card.style.top = '';
            card.style.right = '';
            card.style.bottom = '';
        }
        // Auto-close Document Picture-in-Picture window if active (Google Meet standard)
        if (pbxPipWindow && !pbxPipWindow.closed) {
            try {
                pbxPipWindow.close();
            } catch (e) {
                console.warn('[PBX] Error closing PiP window:', e);
            }
            pbxPipWindow = null;
        }

        syncInCallPipBar();
    };

    window.pbxIsCallActive = function () {
        return !!(currentSession && !currentSession.isEnded());
    };

    window.switchPbxTab = function (tabName) {
        getAudioContext();
        ['keypad', 'contacts', 'rooms', 'recents'].forEach((tab) => {
            const btn = $('pbx-tab-' + tab);
            const view = $('pbx-view-' + tab);
            if (tab === tabName) {
                btn?.classList.add('active');
                if (view) view.style.display = 'flex';
            } else {
                btn?.classList.remove('active');
                if (view) view.style.display = 'none';
            }
        });
        if (tabName === 'rooms') {
            renderTeamRoomsList();
        }
    };

    // =========================================================================
    // Keypad & Number Input
    // =========================================================================
    window.pbxDialDigit = function (digit) {
        getAudioContext();
        playDtmfTone(digit);
        const display = $('pbx-display-input');
        if (display && display.textContent.length < 15) {
            display.textContent += digit;
            updateCalleeNameMatch();
        }
    };

    window.pbxBackspace = function () {
        getAudioContext();
        const display = $('pbx-display-input');
        if (display && display.textContent.length > 0) {
            display.textContent = display.textContent.slice(0, -1);
            updateCalleeNameMatch();
        }
    };

    // Physical Keyboard support for keypad
    document.addEventListener('keydown', function (e) {
        const card = $('pbx-dialer-card');
        if (!card || !card.classList.contains('open')) return;
        const tag = (e.target && e.target.tagName) ? e.target.tagName.toLowerCase() : '';
        if (tag === 'input' || tag === 'textarea') return;

        if (e.key >= '0' && e.key <= '9') {
            e.preventDefault();
            pbxDialDigit(e.key);
        } else if (e.key === '*' || e.key === '#') {
            e.preventDefault();
            pbxDialDigit(e.key);
        } else if (e.key === 'Backspace') {
            e.preventDefault();
            pbxBackspace();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            pbxMakeCall();
        }
    });

    function updateCalleeNameMatch() {
        const display = $('pbx-display-input');
        const matchEl = $('pbx-callee-name');
        if (!display || !matchEl || !userCreds) return;

        const val = display.textContent.trim();
        if (!val) {
            matchEl.textContent = '';
            return;
        }

        const match = userCreds.contacts?.find((c) => String(c.extension) === val);
        if (match) {
            matchEl.textContent = `${match.name} (${match.user_name || 'Colleague'})`;
        } else {
            matchEl.textContent = '';
        }
    }

    // =========================================================================
    // Colleagues Directory & Quick Call
    // =========================================================================
    function renderContactsList(contacts, conferences) {
        const container = $('pbx-contacts-container');
        if (!container) return;

        let html = '';

        // 1. Render Team Bridge Conference Rooms at top of contacts
        const confList = (conferences && conferences.length > 0) ? conferences : (userCreds && userCreds.conferences ? userCreds.conferences : []);
        if (confList && confList.length > 0) {
            html += `<div style="padding: 10px 14px 4px 14px; font-size: 10px; font-weight: 700; color: var(--pbx-text-dim); text-transform: uppercase; letter-spacing: 0.6px; display: flex; align-items: center; gap: 6px;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Team Audio Bridges</span>
            </div>`;

            confList.forEach((conf) => {
                html += `
                <div class="pbx-contact-row is-conference" data-ext="${conf.extension}" data-name="${conf.name.toLowerCase()}">
                    <div class="pbx-contact-info">
                        <div class="pbx-contact-avatar" style="background:#2563eb; color:#ffffff;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
                        </div>
                        <div class="pbx-contact-meta">
                            <div class="pbx-contact-name" style="color:#2563eb; font-weight:700;">${conf.name}</div>
                            <div class="pbx-contact-sub">Room ${conf.extension} • Multi-Party Conference</div>
                        </div>
                    </div>
                    <button type="button" class="pbx-contact-call-btn" style="background:#10b981; border-color:#10b981; color:#fff;" onclick="pbxCallContact('${conf.extension}', '${conf.name}')" title="Join ${conf.name}">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </button>
                </div>`;
            });

            html += `<div style="padding: 12px 14px 4px 14px; font-size: 10px; font-weight: 700; color: var(--pbx-text-dim); text-transform: uppercase; letter-spacing: 0.6px;">Colleagues Directory</div>`;
        }

        // 2. Render Colleagues
        if (!contacts || contacts.length === 0) {
            html += '<div class="pbx-empty-state">No other colleagues assigned to extensions.</div>';
            container.innerHTML = html;
            return;
        }

        contacts.forEach((c) => {
            const name = c.name || `Extension ${c.extension}`;
            const sub = c.user_name || 'Extension ' + c.extension;
            const initial = name.charAt(0).toUpperCase();
            const avatarContent = c.avatar
                ? `<img src="${c.avatar}" alt="${name}" onerror="this.onerror=null; this.parentElement.textContent='${initial}'">`
                : initial;

            html += `
            <div class="pbx-contact-row" data-ext="${c.extension}" data-name="${name.toLowerCase()}">
                <div class="pbx-contact-info">
                    <div class="pbx-contact-avatar">${avatarContent}</div>
                    <div class="pbx-contact-meta">
                        <div class="pbx-contact-name">${name}</div>
                        <div class="pbx-contact-sub">Ext ${c.extension} • ${sub}</div>
                    </div>
                </div>
                <button type="button" class="pbx-contact-call-btn" onclick="pbxCallContact('${c.extension}', '${name}')" title="Call ${name}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                </button>
            </div>`;
        });

        container.innerHTML = html;
    }

    window.filterColleaguesList = function (keyword) {
        const rows = document.querySelectorAll('.pbx-contact-row');
        const term = (keyword || '').toLowerCase().trim();
        rows.forEach((row) => {
            const ext = row.getAttribute('data-ext') || '';
            const name = row.getAttribute('data-name') || '';
            if (ext.includes(term) || name.includes(term)) {
                row.style.display = 'flex';
            } else {
                row.style.display = 'none';
            }
        });
    };

    window.pbxCallContact = function (extNumber, extName) {
        const display = $('pbx-display-input');
        if (display) display.textContent = extNumber;
        updateCalleeNameMatch();
        switchPbxTab('keypad');
        pbxMakeCall(extNumber, extName);
    };

    window.pbxJoinTeamBridge = function (roomNumber) {
        const room = String(roomNumber || '700').trim();
        window.pbxCallContact(room, 'Team Bridge Room ' + room);
    };

    function renderTeamRoomsList() {
        const container = $('pbx-team-rooms-container');
        if (!container) return;

        const confList = (userCreds && userCreds.conferences && userCreds.conferences.length > 0)
            ? userCreds.conferences
            : [
                { extension: '700', name: 'Company All-Hands Bridge', pin: null },
                { extension: '701', name: 'Executive Boardroom', pin: '1234' },
                { extension: '702', name: 'Support & Operations Bridge', pin: null }
            ];

        let html = '';
        confList.forEach((conf) => {
            const pinBadge = conf.pin 
                ? `<span class="badge bg-warning text-dark" style="font-size:9px;">PIN Protected</span>` 
                : `<span class="badge bg-success" style="font-size:9px;">Open Bridge</span>`;

            html += `
            <div class="pbx-room-card" onclick="pbxJoinTeamBridge('${escapeHtml(conf.extension)}')">
                <div class="pbx-room-card-header">
                    <div class="pbx-room-avatar">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div class="pbx-room-meta">
                        <div class="pbx-room-title">${escapeHtml(conf.name)}</div>
                        <div class="pbx-room-sub">Room ${escapeHtml(conf.extension)} &bull; ${pinBadge}</div>
                    </div>
                </div>
                <button type="button" class="pbx-room-join-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    <span>Join Room</span>
                </button>
            </div>`;
        });
        container.innerHTML = html;
    }

    // =========================================================================
    // Call History / Recents (Persisted in localStorage)
    // =========================================================================
    function addRecentCall(direction, ext, name, status, duration) {
        try {
            const key = 'pbx_recents_' + (userCreds ? userCreds.extension : 'default');
            let recents = JSON.parse(localStorage.getItem(key) || '[]');
            recents.unshift({
                direction: direction,
                ext: ext,
                name: name,
                status: status,
                duration: duration || '00:00',
                time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
            });
            if (recents.length > 20) recents = recents.slice(0, 20);
            localStorage.setItem(key, JSON.stringify(recents));
            renderRecentsList();
        } catch (e) {}
    }

    function renderRecentsList() {
        const container = $('pbx-recents-container');
        if (!container || !userCreds) return;

        try {
            const key = 'pbx_recents_' + userCreds.extension;
            const recents = JSON.parse(localStorage.getItem(key) || '[]');
            if (recents.length === 0) {
                container.innerHTML = `
                <div class="pbx-empty-state">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <div class="mt-2">No recent calls</div>
                    <small class="text-muted">Your call history will appear here</small>
                </div>`;
                return;
            }

            let html = '';
            recents.forEach((r) => {
                const isMissed = r.direction === 'missed';
                const iconSvg = isMissed
                    ? `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="1" y1="1" x2="23" y2="23"/><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/></svg>`
                    : r.direction === 'incoming'
                    ? `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>`
                    : `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="17 14 12 9 7 14"/><line x1="12" y1="9" x2="12" y2="21"/></svg>`;

                html += `
                <div class="pbx-recent-row">
                    <div class="pbx-recent-info">
                        <div class="pbx-recent-icon ${r.direction}">${iconSvg}</div>
                        <div>
                            <div class="pbx-recent-meta">${r.name || 'Ext ' + r.ext}</div>
                            <div class="pbx-recent-time">Ext ${r.ext} • ${r.time} (${r.duration})</div>
                        </div>
                    </div>
                    <button type="button" class="pbx-contact-call-btn" onclick="pbxCallContact('${r.ext}', '${r.name}')" title="Call back">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </button>
                </div>`;
            });
            container.innerHTML = html;
        } catch (e) {}
    }

    // =========================================================================
    // Core WebRTC Call Management
    // =========================================================================
    window.pbxMakeCall = function (targetNumber, targetName) {
        if (!ua || !ua.isRegistered()) {
            alert('PBX Phone is not connected yet. Please wait a moment.');
            return;
        }

        const num = (targetNumber || $('pbx-display-input')?.textContent || '').trim();
        if (!num) {
            alert('Please enter an extension number to call.');
            return;
        }

        if (userCreds && String(num) === String(userCreds.extension)) {
            alert('Cannot call your own extension.');
            return;
        }

        getAudioContext();

        const match = (userCreds && userCreds.contacts) ? userCreds.contacts.find((c) => String(c.extension) === String(num)) : null;
        const displayName = targetName || (match ? (match.name || match.user_name) : 'Extension ' + num);

        executeCall(num, displayName);
    };

    function ensureRemoteAudioUnlocked() {
        let remoteAudio = $('pbx-remote-audio');
        if (!remoteAudio) {
            remoteAudio = document.createElement('audio');
            remoteAudio.id = 'pbx-remote-audio';
            remoteAudio.autoplay = true;
            remoteAudio.playsInline = true;
            remoteAudio.style.cssText = 'position:absolute;width:0;height:0;opacity:0;pointer-events:none;';
            document.body.appendChild(remoteAudio);
        }
        try {
            const playPromise = remoteAudio.play();
            if (playPromise !== undefined) {
                playPromise.catch(() => {});
            }
        } catch (e) {}
    }

    function executeCall(num, displayName) {
        if (currentSession && !currentSession.isEnded()) {
            console.warn('[PBX] Already in call, cannot initiate another call.');
            alert('A call is already in progress.');
            return;
        }

        getAudioContext();
        ensureRemoteAudioUnlocked();

        const match = (userCreds && userCreds.contacts) ? userCreds.contacts.find((c) => String(c.extension) === String(num)) : null;
        const targetAvatar = match ? match.avatar : null;

        showCallOverlay('Calling...', displayName, num, targetAvatar);
        startOutgoingRingback();

        const domain = (userCreds && userCreds.domain) ? userCreds.domain : window.location.hostname;
        const targetUri = `sip:${num}@${domain}`;
        const options = {
            mediaConstraints: { audio: true, video: false },
            pcConfig: {
                iceServers: [
                    { urls: 'stun:stun.l.google.com:19302' },
                    { urls: 'stun:stun1.l.google.com:19302' },
                    { urls: 'stun:stun2.l.google.com:19302' },
                    { urls: 'stun:stun.cloudflare.com:3478' }
                ]
            }
        };

        console.log('[PBX] Initiating call to:', targetUri);

        try {
            const session = ua.call(targetUri, options);
            session._direction = 'outgoing';
            session._targetNum = num;
            session._targetName = displayName;
            bindSessionEvents(session);
        } catch (e) {
            console.error('[PBX] Call failed to initiate:', e);
            stopAllAudioTones();
            endCallUI();
            alert('Call initiation error: ' + e.message);
        }
    }

    // Attach remote audio track safely across all browser versions
    function attachRemoteAudio(stream) {
        let remoteAudio = $('pbx-remote-audio');
        if (!remoteAudio) {
            remoteAudio = document.createElement('audio');
            remoteAudio.id = 'pbx-remote-audio';
            remoteAudio.autoplay = true;
            remoteAudio.playsInline = true;
            remoteAudio.style.cssText = 'position:absolute;width:0;height:0;opacity:0;pointer-events:none;';
            document.body.appendChild(remoteAudio);
        }
        if (!stream) return;

        const newTrack = stream.getAudioTracks ? stream.getAudioTracks()[0] : null;
        if (remoteAudio.srcObject && newTrack) {
            const currentTracks = remoteAudio.srcObject.getAudioTracks ? remoteAudio.srcObject.getAudioTracks() : [];
            if (currentTracks.length > 0 && currentTracks[0].id === newTrack.id) {
                return; // Exact track already attached and playing
            }
        }

        console.log('[PBX Audio] Attaching remote audio stream:', stream.id);
        stopAllAudioTones();
        remoteAudio.srcObject = stream;
        const playPromise = remoteAudio.play();
        if (playPromise !== undefined) {
            playPromise.then(() => {
                console.log('[PBX Audio] Remote audio playback active.');
            }).catch((err) => {
                if (err.name === 'AbortError') return;
                console.warn('[PBX Audio] Autoplay policy prevented audio play:', err);
                const resumePlay = () => {
                    remoteAudio.play().catch(() => {});
                    document.removeEventListener('click', resumePlay);
                };
                document.addEventListener('click', resumePlay);
            });
        }
    }

    // Configure PeerConnection listeners safely for BOTH incoming and outgoing calls
    function setupPeerConnection(pc) {
        if (!pc || pc._pbxConfigured) return;
        pc._pbxConfigured = true;
        console.log('[PBX WebRTC] Setting up PeerConnection');

        pc.addEventListener('track', function (e) {
            console.log('[PBX WebRTC] Remote track received:', e.track ? e.track.kind : 'unknown');
            if (e.streams && e.streams[0]) {
                attachRemoteAudio(e.streams[0]);
            } else if (e.track && e.track.kind === 'audio') {
                const stream = new MediaStream([e.track]);
                attachRemoteAudio(stream);
            }
        });

        pc.addEventListener('iceconnectionstatechange', function () {
            console.log('[PBX WebRTC] ICE state:', pc.iceConnectionState);
            if (pc.iceConnectionState === 'connected' || pc.iceConnectionState === 'completed') {
                handleCallEstablished();
            } else if (pc.iceConnectionState === 'failed') {
                console.error('[PBX WebRTC] ICE connection failed!');
                updateCallOverlayState('Network Error (ICE Failed)', false);
            }
        });

        pc.addEventListener('connectionstatechange', function () {
            console.log('[PBX WebRTC] Connection state:', pc.connectionState);
            if (pc.connectionState === 'connected') {
                handleCallEstablished();
            }
        });
    }

    // Bind Session Lifecycle Events
    function bindSessionEvents(session) {
        currentSession = session;
        isCallMuted = false;
        isCallHeld = false;

        // Ultra-fast ICE candidate gathering:
        // Do NOT wait for multi-second timeouts. Once local/STUN candidates start gathering,
        // trigger ready() within 100ms so INVITE or 200 OK is transmitted instantly!
        let iceDone = false;
        let iceTimer = null;
        session.on('icecandidate', function (event) {
            if (iceDone) return;
            if (!iceTimer) {
                iceTimer = setTimeout(() => {
                    if (!iceDone && event && typeof event.ready === 'function') {
                        iceDone = true;
                        console.log('[PBX WebRTC] Fast ICE timer reached (100ms). Sending SDP immediately.');
                        try { event.ready(); } catch (err) {}
                    }
                }, 100);
            }
        });

        // If connection already exists (common on incoming sessions), set up immediately
        if (session.connection) {
            setupPeerConnection(session.connection);
        }

        // When PeerConnection is created (outgoing or incoming answer)
        session.on('peerconnection', function (data) {
            console.log('[PBX WebRTC] PeerConnection created event');
            setupPeerConnection(data.peerconnection || session.connection);
        });

        // 180 / 183 Ringing / Early Media
        session.on('progress', function (e) {
            if (session !== currentSession) return;
            console.log('[PBX] Remote phone is ringing / session progress...');
            if (e && (e.response && (e.response.status_code === 183 || e.response.body))) {
                // Remote early media received (IVR announcement or carrier tone)
                stopAllAudioTones();
            }
            updateCallOverlayState('Ringing...', false);
            setStatus('ringing', 'Ringing...');
        });

        // 200 OK Received or Sent (Call Answered)
        session.on('accepted', function () {
            if (session !== currentSession) return;
            console.log('[PBX] Call accepted / answered!');
            handleCallEstablished();
        });

        // ACK Received or Sent (Call Fully Confirmed)
        session.on('confirmed', function () {
            if (session !== currentSession) return;
            console.log('[PBX] Call confirmed (ACK received)!');
            handleCallEstablished();
        });

        // Call Ended
        session.on('ended', function () {
            console.log('[PBX] Call ended normally.');
            if (session !== currentSession) {
                console.log('[PBX] Ignoring ended event from previous/stale session.');
                return;
            }
            playCallEndTone();
            recordCallHistory(session, 'Completed');
            updateCallOverlayState('Call Ended', false);
            setTimeout(() => {
                if (session === currentSession) {
                    endCallUI();
                }
            }, 1200);
        });

        // Call Failed
        session.on('failed', function (e) {
            console.warn('[PBX] Call failed cause:', e.cause);
            if (session !== currentSession) {
                console.log('[PBX] Ignoring failed event from previous/stale session.');
                return;
            }

            // If this was an incoming call canceled because it was answered on another tab/browser, reset cleanly
            if (session._direction === 'incoming' && (e.cause === 'Canceled' || e.cause === JsSIP.C.causes.CANCELED)) {
                console.log('[PBX] Incoming call answered elsewhere or canceled. Silently resetting UI.');
                stopAllAudioTones();
                recordCallHistory(session, 'Missed');
                endCallUI();
                return;
            }

            playCallEndTone();
            recordCallHistory(session, e.cause || 'Failed');
            const reason = e.cause || 'No Answer';
            updateCallOverlayState(`Call Failed (${reason})`, false);
            if (e.cause === JsSIP.C.causes.USER_DENIED_MEDIA_ACCESS) {
                alert('Microphone permission is required to make or receive calls.');
            }
            setTimeout(() => {
                if (session === currentSession) {
                    endCallUI();
                }
            }, 2000);
        });
    }

    function handleCallEstablished() {
        stopAllAudioTones();
        if (!callTimerInterval) {
            startCallTimer();
        }
        updateCallOverlayState('Connected', true);
        setStatus('incall', 'In Call');
        syncInCallPipBar();

        // Check if remote stream is already present on PeerConnection
        if (currentSession && currentSession.connection) {
            setupPeerConnection(currentSession.connection);
            const receivers = currentSession.connection.getReceivers();
            receivers.forEach((r) => {
                if (r.track && r.track.kind === 'audio') {
                    const stream = new MediaStream([r.track]);
                    attachRemoteAudio(stream);
                }
            });
        }
    }

    function recordCallHistory(session, status) {
        if (!session) return;
        const durEl = $('pbx-overlay-timer');
        const duration = (durEl && durEl.textContent !== '00:00') ? durEl.textContent : '00:00';
        const isMissed = (status === 'Rejected' || status === 'Canceled' || status === 'Unavailable');

        let dir = session._direction || 'incoming';
        if (dir === 'incoming' && isMissed) {
            dir = 'missed';
        }

        const ext = session._targetNum || (session.remote_identity ? session.remote_identity.uri.user : 'Unknown');
        const name = session._targetName || (session.remote_identity ? session.remote_identity.display_name : 'Ext ' + ext);

        addRecentCall(dir, ext, name, status, duration);

        // Sync Call Detail Record (CDR) to Database
        try {
            const parts = duration.split(':');
            const sec = parts.length === 2 ? (parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10)) : 0;
            const myExt = (userCreds && userCreds.extension) ? userCreds.extension : '101';
            const myName = (userCreds && userCreds.name) ? userCreds.name : 'Agent';

            const callerNum = (session._direction === 'outgoing') ? myExt : ext;
            const calleeNum = (session._direction === 'outgoing') ? ext : myExt;

            let statusClean = 'answered';
            if (status === 'Completed' || status === 'Answered') {
                statusClean = 'answered';
            } else if (isMissed) {
                statusClean = 'missed';
            } else if (String(status).toLowerCase().includes('busy')) {
                statusClean = 'busy';
            } else {
                statusClean = 'failed';
            }

            let answeredBy = null;
            if (statusClean === 'answered') {
                if (session._direction === 'incoming') {
                    answeredBy = `Ext ${myExt} (${myName})`;
                } else {
                    answeredBy = name ? `Ext ${calleeNum} (${name})` : `Ext ${calleeNum}`;
                }
            }

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            };
            if (csrf) {
                headers['X-CSRF-TOKEN'] = csrf;
            }

            fetch('/pbx/call-log', {
                method: 'POST',
                headers: headers,
                body: JSON.stringify({
                    caller: callerNum,
                    callee: calleeNum,
                    status: statusClean,
                    duration: sec,
                    direction: session._direction || 'internal',
                    answered_by: answeredBy,
                    sip_call_id: session.id || null,
                })
            }).then(r => r.json()).then(res => {
                console.log('[PBX CDR] Call synced to database log:', res);
            }).catch(err => {
                console.warn('[PBX CDR] Logging error:', err);
            });
        } catch (cdrErr) {
            console.warn('[PBX CDR] Error preparing CDR record:', cdrErr);
        }
    }

    // Call Overlay State & Timer
    function showCallOverlay(status, name, ext, avatarUrl) {
        const overlay = $('pbx-call-overlay');
        if (!overlay) return;

        const rawExt = String(ext || '').replace(/^(ext:?\s*)+/i, '').trim();
        $('pbx-overlay-name').textContent = name || 'Internal Call';
        $('pbx-overlay-ext').textContent = rawExt ? ('Ext ' + rawExt) : '';
        $('pbx-overlay-timer').textContent = '00:00';

        const confTimer = $('pbx-conf-timer');
        if (confTimer) confTimer.textContent = '00:00';

        // Check if this is a Team Bridge multi-party conference call (e.g. 700, 701, 702 or 7xx)
        const isConference = /^7\d{2}$/.test(rawExt) ||
            (userCreds && userCreds.conferences && userCreds.conferences.some(c => String(c.extension) === String(rawExt)));

        const hero1on1 = $('pbx-hero-1on1');
        const heroConf = $('pbx-hero-conf');

        if (isConference) {
            if (hero1on1) hero1on1.style.display = 'none';
            if (heroConf) heroConf.style.display = 'flex';
            const badgeEl = $('pbx-conf-room-badge');
            if (badgeEl) badgeEl.textContent = 'Room ' + rawExt;
            const nameEl = $('pbx-conf-name');
            if (nameEl) nameEl.textContent = name || ('Team Bridge Room ' + rawExt);
            startConfPolling(rawExt);
        } else {
            stopConfPolling();
            if (heroConf) heroConf.style.display = 'none';
            if (hero1on1) hero1on1.style.display = 'flex';
        }

        // Auto-lookup avatar if not explicitly provided
        if (!avatarUrl && userCreds && userCreds.contacts) {
            const m = userCreds.contacts.find(c => String(c.extension) === String(rawExt));
            if (m && m.avatar) avatarUrl = m.avatar;
        }

        const avatarEl = $('pbx-overlay-avatar');
        if (avatarEl) {
            const initial = (name ? name.charAt(0).toUpperCase() : (rawExt ? rawExt.charAt(0) : '?'));
            if (avatarUrl) {
                avatarEl.innerHTML = `<img src="${avatarUrl}" alt="${name || 'Contact'}" onerror="this.onerror=null; this.parentElement.textContent='${initial}'">`;
            } else {
                avatarEl.textContent = initial;
            }
        }

        updateCallOverlayState(status, false);

        overlay.classList.add('active');
        $('pbx-dialer-card')?.classList.add('open');
    }

    function updateCallOverlayState(statusText, isConnected) {
        if (currentSession) {
            currentSession._callStatusText = statusText;
        }
        const statusEl = $('pbx-overlay-status');
        const badgeState = $('pbx-overlay-badge-state');
        const waves = $('pbx-audio-waves');
        const topStateEl = $('pbx-incall-top-state');
        const pipSubEl = $('pbx-pip-sub');

        if (statusEl) statusEl.textContent = statusText;
        if (topStateEl) topStateEl.textContent = statusText;
        if (badgeState) {
            if (isConnected) {
                badgeState.classList.add('connected');
                if (waves) waves.style.display = 'flex';
            } else {
                badgeState.classList.remove('connected');
                if (waves) waves.style.display = 'none';
            }
        }
        if (pipSubEl && currentSession) {
            const overlayExt = $('pbx-overlay-ext')?.textContent;
            const targetNum = currentSession._targetNum || (currentSession.remote_identity ? currentSession.remote_identity.uri.user : 'Caller');
            const cleanExt = (overlayExt || targetNum || '').replace(/^Ext:?\s*/i, '').trim();
            pipSubEl.textContent = `Ext ${cleanExt} • ${statusText}`;
        }
    }

    function startCallTimer() {
        stopCallTimer();
        callStartTime = Date.now();
        const timerEl = $('pbx-overlay-timer');
        if (timerEl) timerEl.textContent = '00:00';

        callTimerInterval = setInterval(function () {
            if (isCallHeld) return;
            const diff = Math.floor((Date.now() - callStartTime) / 1000);
            const mins = String(Math.floor(diff / 60)).padStart(2, '0');
            const secs = String(diff % 60).padStart(2, '0');
            const timeStr = `${mins}:${secs}`;
            if (timerEl) timerEl.textContent = timeStr;

            const confTimer = $('pbx-conf-timer');
            if (confTimer) confTimer.textContent = timeStr;

            const pipTimer = $('pbx-pip-timer');
            if (pipTimer) pipTimer.textContent = timeStr;

            const fabSub = $('pbx-fab-sub');
            if (fabSub) fabSub.textContent = `In Call (${timeStr})`;
        }, 1000);
    }

    function stopCallTimer() {
        if (callTimerInterval) {
            clearInterval(callTimerInterval);
            callTimerInterval = null;
        }
    }

    // =========================================================================
    // Team Bridge Conference Live Polling & Action Controls
    // =========================================================================
    function startConfPolling(room) {
        stopConfPolling();
        currentConfRoom = room;
        pollConferenceParticipants(room);
        confPollInterval = setInterval(() => {
            if (!currentSession || currentSession.isEnded()) {
                stopConfPolling();
                return;
            }
            pollConferenceParticipants(currentConfRoom);
        }, 1800);
    }

    function stopConfPolling() {
        if (confPollInterval) {
            clearInterval(confPollInterval);
            confPollInterval = null;
        }
        currentConfRoom = null;
    }

    async function pollConferenceParticipants(room) {
        if (!room) return;
        try {
            const res = await fetch(`/user/pbx/conference/${encodeURIComponent(room)}/live-status`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!res.ok) return;
            const data = await res.json();
            if (!data || !data.success) return;

            // Update room badge & title
            const badgeEl = $('pbx-conf-room-badge');
            if (badgeEl) badgeEl.textContent = `Room ${data.room}`;
            const titleEl = $('pbx-conf-name');
            if (titleEl && data.room_name) titleEl.textContent = data.room_name;

            // Update count
            const countEl = $('pbx-conf-count-label');
            const activeCount = data.active_count || (data.participants ? data.participants.length : 0);
            if (countEl) countEl.textContent = `In Room (${activeCount})`;

            // Active Participants List
            const listEl = $('pbx-conf-participants-list');
            if (listEl) {
                if (!data.participants || data.participants.length === 0) {
                    listEl.innerHTML = `
                        <div style="text-align: center; padding: 20px 10px; color: var(--pbx-text-dim, #94a3b8); font-size: 12px;">
                            <div class="spinner-grow spinner-grow-sm text-primary mb-2" role="status"></div>
                            <div>Waiting for team members to join Room ${escapeHtml(room)}...</div>
                        </div>
                    `;
                } else {
                    listEl.innerHTML = data.participants.map(p => {
                        const initial = p.name ? p.name.charAt(0).toUpperCase() : (p.extension ? p.extension.charAt(0) : '?');
                        const avatarHtml = p.avatar
                            ? `<img src="${p.avatar}" alt="${escapeHtml(p.name)}" class="pbx-conf-avatar-img" onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\\'pbx-conf-avatar-init\\'>${initial}</span>'">`
                            : `<span class="pbx-conf-avatar-init">${initial}</span>`;

                        const isMeBadge = p.is_me ? `<span class="pbx-conf-you-badge">(You)</span>` : '';
                        const adminBadge = p.is_admin ? `<span class="pbx-conf-admin-badge">Host</span>` : '';

                        let statusBadge = '';
                        if (p.is_muted) {
                            statusBadge = `
                                <span class="pbx-conf-tag tag-muted" title="Microphone Muted">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="1" y1="1" x2="23" y2="23"/><path d="M9 9v3a3 3 0 0 0 5.12 2.12M15 9.34V4a3 3 0 0 0-5.94-.6"/></svg>
                                    <span>Muted</span>
                                </span>
                            `;
                        } else {
                            statusBadge = `
                                <span class="pbx-conf-tag tag-talking" title="Speaking">
                                    <span class="pbx-mini-wave"><span></span><span></span><span></span></span>
                                    <span>Talking</span>
                                </span>
                            `;
                        }

                        return `
                            <div class="pbx-conf-participant-row ${p.is_me ? 'is-me' : ''}">
                                <div class="pbx-conf-avatar-wrapper">
                                    ${avatarHtml}
                                    ${p.is_talking ? '<span class="pbx-talking-pulse"></span>' : ''}
                                </div>
                                <div class="pbx-conf-info">
                                    <div class="pbx-conf-pname">
                                        <span>${escapeHtml(p.name)}</span>
                                        ${isMeBadge}
                                        ${adminBadge}
                                    </div>
                                    <div class="pbx-conf-pext">Ext ${escapeHtml(p.extension)}</div>
                                </div>
                                <div class="pbx-conf-actions">
                                    ${statusBadge}
                                </div>
                            </div>
                        `;
                    }).join('');
                }
            }

            // Offline Colleagues (Invite list)
            const inviteEl = $('pbx-conf-invite-list');
            if (inviteEl && data.offline_members) {
                if (data.offline_members.length === 0) {
                    inviteEl.innerHTML = `<div style="text-align: center; padding: 20px; color: var(--pbx-text-dim, #94a3b8); font-size: 12px;">All team colleagues are in this room!</div>`;
                } else {
                    inviteEl.innerHTML = data.offline_members.map(m => {
                        const initial = m.name ? m.name.charAt(0).toUpperCase() : (m.extension ? m.extension.charAt(0) : '?');
                        const avatarHtml = m.avatar
                            ? `<img src="${m.avatar}" alt="${escapeHtml(m.name)}" class="pbx-conf-avatar-img" onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\\'pbx-conf-avatar-init\\'>${initial}</span>'">`
                            : `<span class="pbx-conf-avatar-init">${initial}</span>`;

                        return `
                            <div class="pbx-conf-invite-row">
                                <div class="pbx-conf-avatar-wrapper">
                                    ${avatarHtml}
                                </div>
                                <div class="pbx-conf-info">
                                    <div class="pbx-conf-pname">${escapeHtml(m.name)}</div>
                                    <div class="pbx-conf-pext">Ext ${escapeHtml(m.extension)}</div>
                                </div>
                                <button type="button" class="pbx-conf-ring-btn" id="pbx-inv-btn-${escapeHtml(m.extension)}" onclick="pbxInviteToConference('${escapeHtml(m.extension)}', '${escapeHtml(room)}')" title="Ring extension now">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    <span>Ring</span>
                                </button>
                            </div>
                        `;
                    }).join('');
                }
            }
        } catch (err) {
            console.warn('[PBX Conference Status] Poll error:', err);
        }
    }

    window.pbxToggleInviteDrawer = function () {
        getAudioContext();
        const drawer = $('pbx-conf-invite-drawer');
        if (drawer) {
            drawer.classList.toggle('active');
        }
    };

    window.pbxInviteToConference = async function (ext, room) {
        getAudioContext();
        if (!room) {
            room = currentSession ? (currentSession._targetNum || '700') : '700';
        }
        const btn = $(`pbx-inv-btn-${ext}`);
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status"></span> Ringing...`;
        }
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        try {
            const res = await fetch(`/user/pbx/conference/${encodeURIComponent(room)}/invite`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ extension: ext })
            });
            const json = await res.json();
            if (json.success) {
                if (btn) {
                    btn.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Calling`;
                    btn.classList.add('btn-success');
                }
            } else {
                alert(json.message || 'Failed to ring extension');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = `Ring`;
                }
            }
        } catch (e) {
            console.error('[PBX Conference Invite] Error:', e);
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = `Ring`;
            }
        }
    };

    window.pbxConferenceAction = async function (action, channel, room) {
        getAudioContext();
        if (!room) {
            room = currentSession ? (currentSession._targetNum || '700') : '700';
        }
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        try {
            const res = await fetch(`/user/pbx/conference/${encodeURIComponent(room)}/action`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ action: action, channel: channel })
            });
            const json = await res.json();
            if (json.success) {
                pollConferenceParticipants(room);
            } else {
                alert(json.message || 'Action failed');
            }
        } catch (e) {
            console.error('[PBX Conference Action] Error:', e);
        }
    };

    function endCallUI() {
        stopCallTimer();
        stopConfPolling();
        try {
            stopAllAudioTones();
        } catch (e) {}

        let remoteAudio = $('pbx-remote-audio');
        if (remoteAudio) {
            try {
                remoteAudio.pause();
                remoteAudio.srcObject = null;
            } catch (e) {}
        }

        currentSession = null;
        isCallMuted = false;
        isCallHeld = false;

        // Immediately close the active call overlay in both main window and PiP document
        const overlay = $('pbx-call-overlay');
        if (overlay) overlay.classList.remove('active');
        const mainOverlay = document.getElementById('pbx-call-overlay');
        if (mainOverlay) mainOverlay.classList.remove('active');

        // Reset hero visibility
        const hero1on1 = $('pbx-hero-1on1');
        if (hero1on1) hero1on1.style.display = 'flex';
        const heroConf = $('pbx-hero-conf');
        if (heroConf) heroConf.style.display = 'none';

        const incomingModal = $('pbx-incoming-modal');
        if (incomingModal) incomingModal.classList.remove('active');
        const mainIncoming = document.getElementById('pbx-incoming-modal');
        if (mainIncoming) mainIncoming.classList.remove('active');

        const fab = $('pbx-fab-btn') || document.getElementById('pbx-fab-btn');
        if (fab) {
            fab.classList.remove('is-incoming');
            fab.style.display = 'flex';
        }

        // Hide floating In-Call PiP bar and reset its position back to bottom-right
        const pipBar = $('pbx-incall-pip-bar') || document.getElementById('pbx-incall-pip-bar');
        if (pipBar) {
            pipBar.style.display = 'none';
            pipBar.style.position = 'fixed';
            pipBar.style.bottom = '24px';
            pipBar.style.right = '24px';
            pipBar.style.left = 'auto';
            pipBar.style.top = 'auto';
            try {
                sessionStorage.removeItem('pbx_pip_pos');
            } catch (e) {}
        }

        // Snap the dialer card back to its natural bottom-right home
        const card = $('pbx-dialer-card') || document.getElementById('pbx-dialer-card');
        if (card) {
            card.style.position = '';
            card.style.left = '';
            card.style.top = '';
            card.style.right = '';
            card.style.bottom = '';
        }

        // Reset control buttons
        $('pbx-mute-btn')?.classList.remove('active');
        $('pbx-hold-btn')?.classList.remove('active');
        $('pbx-incall-dtmf-drawer')?.classList.remove('active');
        $('pbx-incall-transfer-drawer')?.classList.remove('active');
        $('pbx-conf-invite-drawer')?.classList.remove('active');

        if (ua && ua.isRegistered()) {
            setStatus('online', 'Online');
        } else {
            setStatus('offline', 'Offline');
        }

        // Auto-close Document Picture-in-Picture window if active (Google Meet standard)
        if (pbxPipWindow && !pbxPipWindow.closed) {
            try {
                pbxPipWindow.close();
            } catch (e) {
                console.warn('[PBX] Error closing PiP window:', e);
            }
            pbxPipWindow = null;
        }

        syncInCallPipBar();
    }

    // =========================================================================
    // In-Call PBX Action Controls
    // =========================================================================
    window.pbxHangup = function (e) {
        if (e && e.stopPropagation) {
            e.stopPropagation();
            e.preventDefault();
        }
        console.log('[PBX] Hangup button triggered');

        try {
            getAudioContext();
        } catch (err) {}

        try {
            stopAllAudioTones();
        } catch (err) {}

        if (currentSession && !currentSession.isEnded()) {
            try {
                currentSession.terminate({
                    status_code: 487,
                    reason_phrase: 'Terminated by User'
                });
            } catch (err) {
                console.warn('[PBX] Hangup terminate caught:', err);
            }
        }

        // ALWAYS immediately tear down UI so the call screen never freezes
        endCallUI();
    };

    window.pbxResetDialerPosition = function () {
        const card = $('pbx-dialer-card');
        if (card) {
            card.style.position = '';
            card.style.left = '';
            card.style.top = '';
            card.style.right = '';
            card.style.bottom = '';
        }
        const pipBar = $('pbx-incall-pip-bar');
        if (pipBar) {
            pipBar.style.position = 'fixed';
            pipBar.style.bottom = '24px';
            pipBar.style.right = '24px';
            pipBar.style.left = 'auto';
            pipBar.style.top = 'auto';
            try {
                sessionStorage.removeItem('pbx_pip_pos');
            } catch (e) {}
        }
    };

    window.pbxToggleMute = function () {
        getAudioContext();
        if (!currentSession) return;
        const btn = $('pbx-mute-btn');
        const iconOn = btn?.querySelector('.icon-mic-on');
        const iconOff = btn?.querySelector('.icon-mic-off');
        const label = $('pbx-mute-label');

        if (!isCallMuted) {
            currentSession.mute({ audio: true });
            isCallMuted = true;
            btn?.classList.add('active');
            if (iconOn) iconOn.style.display = 'none';
            if (iconOff) iconOff.style.display = 'block';
            if (label) label.textContent = 'Muted';
        } else {
            currentSession.unmute({ audio: true });
            isCallMuted = false;
            btn?.classList.remove('active');
            if (iconOn) iconOn.style.display = 'block';
            if (iconOff) iconOff.style.display = 'none';
            if (label) label.textContent = 'Mute';
        }
    };

    window.pbxToggleHold = function () {
        getAudioContext();
        if (!currentSession) return;
        const btn = $('pbx-hold-btn');
        const label = $('pbx-hold-label');

        if (!isCallHeld) {
            currentSession.hold();
            isCallHeld = true;
            btn?.classList.add('active');
            if (label) label.textContent = 'Resume';
            updateCallOverlayState('On Hold (Paused)', false);
        } else {
            currentSession.unhold();
            isCallHeld = false;
            btn?.classList.remove('active');
            if (label) label.textContent = 'Hold';
            updateCallOverlayState('Connected', true);
        }
    };

    window.pbxToggleInCallKeypad = function () {
        getAudioContext();
        const drawer = $('pbx-incall-dtmf-drawer');
        $('pbx-incall-transfer-drawer')?.classList.remove('active');
        drawer?.classList.toggle('active');
    };

    window.pbxSendDtmf = function (digit) {
        getAudioContext();
        playDtmfTone(digit);
        if (currentSession) {
            try {
                currentSession.sendDTMF(digit);
                console.log('[PBX DTMF] Sent tone:', digit);
            } catch (e) {
                console.warn('[PBX DTMF] Send error:', e);
            }
        }
    };

    window.pbxToggleTransferPanel = function () {
        getAudioContext();
        const drawer = $('pbx-incall-transfer-drawer');
        $('pbx-incall-dtmf-drawer')?.classList.remove('active');
        drawer?.classList.toggle('active');
    };

    window.pbxExecuteTransfer = function () {
        const input = $('pbx-transfer-target');
        const targetExt = input?.value.trim();
        if (!targetExt) {
            alert('Please enter a target extension number.');
            return;
        }

        if (!currentSession || !userCreds) return;

        try {
            const targetUri = `sip:${targetExt}@${userCreds.domain}`;
            currentSession.refer(targetUri);
            alert(`Transfer request sent to Ext ${targetExt}.`);
            $('pbx-incall-transfer-drawer')?.classList.remove('active');
        } catch (e) {
            alert('Transfer failed: ' + e.message);
        }
    };

    window.pbxToggleSpeaker = function () {
        getAudioContext();
        const remoteAudio = $('pbx-remote-audio');
        const btn = $('pbx-speaker-btn');
        const label = $('pbx-speaker-label');

        if (!remoteAudio) return;

        isSpeakerMuted = !isSpeakerMuted;
        remoteAudio.muted = isSpeakerMuted;

        if (isSpeakerMuted) {
            btn?.classList.add('active');
            if (label) label.textContent = 'Muted';
        } else {
            btn?.classList.remove('active');
            if (label) label.textContent = 'Audio';
        }
    };

    window.pbxShowConsultColleagues = function () {
        switchPbxTab('contacts');
    };

    // =========================================================================
    // Incoming Call Handling
    // =========================================================================
    function handleIncomingCall(session) {
        // If already in an active or ringing call, send busy
        if (currentSession && !currentSession.isEnded()) {
            console.warn('[PBX] Already in call or handling a session, rejecting new call with 486 Busy Here.');
            try {
                session.terminate({ status_code: 486, reason_phrase: 'Busy Here' });
            } catch (e) {}
            return;
        }

        session._direction = 'incoming';
        currentSession = session;

        const callerExt = session.remote_identity?.uri?.user || session._targetNum || 'Unknown';
        const contactMatch = (userCreds && userCreds.contacts) ? userCreds.contacts.find((c) => String(c.extension) === String(callerExt)) : null;
        const callerName = session.remote_identity?.display_name || (contactMatch ? contactMatch.name : ('Extension ' + callerExt));
        const callerAvatar = contactMatch ? contactMatch.avatar : null;
        session._targetNum = callerExt;
        session._targetName = callerName;

        bindSessionEvents(session);

        const callerEl = $('pbx-incoming-caller');
        const extEl = $('pbx-incoming-ext');
        const avatarEl = $('pbx-incoming-avatar');
        if (callerEl) callerEl.textContent = callerName;
        if (extEl) extEl.textContent = 'Ext ' + callerExt;
        if (avatarEl) {
            const initial = callerName ? callerName.charAt(0).toUpperCase() : '?';
            if (callerAvatar) {
                avatarEl.innerHTML = `<img src="${callerAvatar}" alt="${callerName}" onerror="this.onerror=null; this.parentElement.textContent='${initial}'">`;
            } else {
                avatarEl.textContent = initial;
            }
        }

        const incomingModal = $('pbx-incoming-modal');
        if (incomingModal) incomingModal.classList.add('active');

        const fab = $('pbx-fab-btn');
        if (fab) fab.classList.add('is-incoming');

        $('pbx-dialer-card')?.classList.add('open');

        startIncomingRingtone();
        setStatus('ringing', 'Incoming Call');
    }

    window.pbxAnswerIncoming = function () {
        console.log('[PBX] Answering incoming call...');
        stopAllAudioTones();
        getAudioContext();
        ensureRemoteAudioUnlocked();

        if (!currentSession || currentSession.isEnded()) {
            console.warn('[PBX] No active incoming session found to answer.');
            endCallUI();
            return;
        }

        const incomingModal = $('pbx-incoming-modal');
        if (incomingModal) incomingModal.classList.remove('active');

        const fab = $('pbx-fab-btn');
        if (fab) fab.classList.remove('is-incoming');

        const callerExt = currentSession.remote_identity?.uri?.user || currentSession._targetNum || '';
        const contactMatch = (userCreds && userCreds.contacts) ? userCreds.contacts.find((c) => String(c.extension) === String(callerExt)) : null;
        const callerName = currentSession.remote_identity?.display_name || (contactMatch ? contactMatch.name : (callerExt ? 'Ext ' + callerExt : 'Caller'));
        const callerAvatar = contactMatch ? contactMatch.avatar : null;

        showCallOverlay('Connecting...', callerName, callerExt, callerAvatar);

        const options = {
            mediaConstraints: { audio: true, video: false },
            pcConfig: {
                iceServers: [
                    { urls: 'stun:stun.l.google.com:19302' },
                    { urls: 'stun:stun1.l.google.com:19302' },
                    { urls: 'stun:stun2.l.google.com:19302' },
                    { urls: 'stun:stun.cloudflare.com:3478' }
                ]
            }
        };

        try {
            currentSession.answer(options);
            console.log('[PBX] currentSession.answer() executed successfully.');
        } catch (e) {
            console.error('[PBX] Exception in currentSession.answer():', e);
            alert('Failed to answer call: ' + e.message);
            endCallUI();
        }
    };

    window.pbxRejectIncoming = function () {
        stopAllAudioTones();
        if (currentSession && !currentSession.isEnded()) {
            try {
                currentSession.terminate({ status_code: 486, reason_phrase: 'Busy Here' });
            } catch (e) {
                console.warn('[PBX] Reject error:', e);
            }
        }
        endCallUI();
    };

    // =========================================================================
    // Extension Credentials Loader & JsSIP Initializer
    // =========================================================================
    async function initPbxClient() {
        try {
            console.log('[PBX Phone] Loading extension credentials...');
            setStatus('connecting', 'Connecting...');

            const res = await fetch('/user/pbx/phone/credentials', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!res.ok) {
                console.warn('[PBX Phone] Credentials response status:', res.status);
                setStatus('offline', 'Offline');
                return;
            }

            const data = await res.json();
            if (!data.assigned) {
                console.log('[PBX Phone] No extension assigned:', data.message);
                setStatus('offline', 'No Ext');
                return;
            }

            userCreds = data;
            console.log('[PBX Phone] Credentials loaded: Ext', data.extension, data.name);

            // Populate header info
            const userDisplay = $('pbx-header-user');
            const extBadge = $('pbx-header-ext');
            const avatar = $('pbx-header-avatar');
            if (userDisplay) userDisplay.textContent = data.name;
            if (extBadge) extBadge.textContent = 'Ext ' + data.extension;
            if (avatar) {
                const initial = data.name ? data.name.charAt(0).toUpperCase() : '?';
                if (data.avatar) {
                    avatar.innerHTML = `<img src="${data.avatar}" alt="${data.name}" onerror="this.onerror=null; this.parentElement.textContent='${initial}'">`;
                } else {
                    avatar.textContent = initial;
                }
            }

            // Populate Colleagues & Team Bridge Rooms
            renderContactsList(data.contacts || [], data.conferences || []);
            renderTeamRoomsList();
            renderRecentsList();

            // Check if JsSIP is loaded
            if (typeof JsSIP === 'undefined') {
                console.error('[PBX Phone] JsSIP library is missing.');
                setStatus('offline', 'Library Err');
                return;
            }

            // Intelligent WebSocket URL resolution for HTTPS
            let wsUrl = data.ws_url || `wss://${window.location.host}/ws`;
            if (window.location.protocol === 'https:' && wsUrl.startsWith('ws://')) {
                wsUrl = wsUrl.replace(/^ws:\/\//i, 'wss://');
            }
            if (/^(wss?:\/\/)(10\.|192\.168\.|172\.(1[6-9]|2[0-9]|3[0-1])\.|127\.|localhost)/.test(wsUrl) && !/^(10\.|192\.168\.|172\.|127\.|localhost)/.test(window.location.hostname)) {
                const proto = window.location.protocol === 'https:' ? 'wss://' : 'ws://';
                wsUrl = `${proto}${window.location.host}/ws`;
            }

            let sipDomain = data.domain || window.location.hostname;
            if (/^(10\.|192\.168\.|172\.(1[6-9]|2[0-9]|3[0-1])\.|127\.|localhost)/.test(sipDomain) && !/^(10\.|192\.168\.|172\.|127\.|localhost)/.test(window.location.hostname)) {
                sipDomain = window.location.hostname;
            }

            console.log('[PBX] Connecting to WebSocket:', wsUrl, 'Domain:', sipDomain);

            const socket = new JsSIP.WebSocketInterface(wsUrl);

            const config = {
                sockets: [socket],
                uri: `sip:${data.extension}@${sipDomain}`,
                password: data.password,
                display_name: data.name,
                session_timers: false,
                register: true,
                register_expires: 120,
                connection_recovery_min_interval: 2,
                connection_recovery_max_interval: 15,
                user_agent: 'PBX-WebPhone/1.0'
            };

            ua = new JsSIP.UA(config);

            ua.on('connecting', function () {
                console.log('[PBX] WebSocket connecting...');
                setStatus('connecting', 'Connecting...');
            });

            ua.on('connected', function () {
                console.log('[PBX] WebSocket connected.');
            });

            ua.on('disconnected', function () {
                console.warn('[PBX] WebSocket disconnected.');
                setStatus('offline', 'Offline');
            });

            ua.on('registered', function () {
                console.log('[PBX] Extension registered successfully: Ext', data.extension);
                setStatus('online', 'Online');
            });

            ua.on('unregistered', function () {
                console.warn('[PBX] Extension unregistered.');
                setStatus('offline', 'Offline');
            });

            ua.on('registrationFailed', function (e) {
                console.error('[PBX] SIP registration failed:', e.cause);
                setStatus('offline', 'Reg Failed');
            });

            ua.on('newRTCSession', function (data) {
                const session = data.session;
                const isIncoming = (session.direction === 'incoming' || data.originator === 'remote');
                if (isIncoming) {
                    const fromExt = session.remote_identity?.uri?.user || 'Unknown';
                    console.log('[PBX] Incoming call received from:', fromExt);
                    handleIncomingCall(session);
                } else {
                    console.log('[PBX] Outgoing session created for:', session.remote_identity?.uri?.user || 'Unknown');
                }
            });

            // Start User Agent
            ua.start();

        } catch (e) {
            console.error('[PBX Phone] Initialization error:', e);
            setStatus('offline', 'Offline');
        }
    }

    // Google Meet-Style Floating Picture-in-Picture & Dedicated Window
    window.pbxPopoutWindow = async function () {
        // 1. If already open in PiP, close it to dock back into main window
        if (pbxPipWindow && !pbxPipWindow.closed) {
            try {
                pbxPipWindow.close();
            } catch (e) {}
            return;
        }

        // 2. Google Meet Style: Document Picture-in-Picture API (Chrome 116+, Edge)
        // This opens a floating, OS-level ALWAYS ON TOP mini window without losing the call or opening a tab!
        // It floats over ALL software (Word, Excel, desktop, other browser tabs, etc.)
        if ('documentPictureInPicture' in window) {
            try {
                const pipWindow = await window.documentPictureInPicture.requestWindow({
                    width: 360,
                    height: 560,
                });

                pbxPipWindow = pipWindow;

                // Bridge all PBX functions to the PiP window's global scope so inline onclick="..." works 100%
                const pbxMethods = [
                    'pbxHangup', 'pbxToggleMute', 'pbxToggleHold', 'pbxToggleSpeaker',
                    'pbxToggleInCallKeypad', 'pbxToggleTransferPanel', 'pbxExecuteTransfer',
                    'pbxShowConsultColleagues', 'pbxSendDtmf', 'pbxBackspace', 'switchPbxTab',
                    'togglePbxDialer', 'closePbxDialer', 'pbxPopoutWindow', 'pbxAnswerIncoming',
                    'pbxRejectIncoming', 'pbxDial', 'pbxQuickCall', 'pbxCallColleague',
                    'pbxJoinTeamBridge', 'pbxCallContact', 'pbxMakeCall', 'pbxDialDigit',
                    'pbxResetDialerPosition', 'pbxToggleInviteDrawer', 'pbxInviteToConference',
                    'pbxConferenceAction'
                ];
                pbxMethods.forEach((name) => {
                    if (typeof window[name] === 'function') {
                        pipWindow[name] = window[name];
                    }
                });
                pipWindow.parentWindow = window;

                // Copy all page stylesheets and font links to PiP window
                document.querySelectorAll('link[rel="stylesheet"], style').forEach((styleEl) => {
                    try {
                        pipWindow.document.head.appendChild(styleEl.cloneNode(true));
                    } catch (e) {}
                });

                // Inherit active theme
                const activeTheme = document.documentElement.getAttribute('data-theme') || 'light';
                pipWindow.document.documentElement.setAttribute('data-theme', activeTheme);
                pipWindow.document.documentElement.setAttribute('data-bs-theme', activeTheme);

                pipWindow.document.body.style.margin = '0';
                pipWindow.document.body.style.padding = '0';
                pipWindow.document.body.style.width = '100vw';
                pipWindow.document.body.style.height = '100vh';
                pipWindow.document.body.style.overflow = 'hidden';
                pipWindow.document.body.style.background = activeTheme === 'dark' ? '#0b1120' : '#ffffff';

                const card = document.getElementById('pbx-dialer-card');
                const originalParent = card.parentElement;
                const originalNext = card.nextSibling;

                // Hide the in-page floating bar while in PiP
                const pipBar = document.getElementById('pbx-incall-pip-bar');
                if (pipBar) pipBar.style.display = 'none';

                // Move dialer card into the floating PiP window
                card.classList.add('in-pip-mode', 'open');
                pipWindow.document.body.appendChild(card);

                // Change all popout buttons to "Dock / Return to Page"
                function setDockIcon() {
                    const allPopoutBtns = [
                        ...document.querySelectorAll('.pbx-popout-btn'),
                        ...pipWindow.document.querySelectorAll('.pbx-popout-btn')
                    ];
                    allPopoutBtns.forEach(btn => {
                        btn.setAttribute('title', 'Dock back to page (Close Floating Window)');
                        btn.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 14h6m0 0v6m0-6L3 21"/><path d="M20 10h-6m0 0V4m0 6l7-7"/></svg>`;
                    });
                }
                setDockIcon();

                // When PiP window closes, safely return dialer card back to the page
                pipWindow.addEventListener('pagehide', () => {
                    if (originalNext) {
                        originalParent.insertBefore(card, originalNext);
                    } else {
                        originalParent.appendChild(card);
                    }
                    card.classList.remove('in-pip-mode');
                    pbxPipWindow = null;
                    document.querySelectorAll('.pbx-popout-btn').forEach(btn => {
                        btn.setAttribute('title', 'Float over all software & tabs (Google Meet Picture-in-Picture)');
                        btn.innerHTML = `<svg class="pbx-pip-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="4" width="20" height="16" rx="2"/><rect x="12" y="10" width="8" height="6" rx="1"/></svg>`;
                    });
                    if (!currentSession || currentSession.isEnded()) {
                        const ov = document.getElementById('pbx-call-overlay');
                        if (ov) ov.classList.remove('active');
                        const inc = document.getElementById('pbx-incoming-modal');
                        if (inc) inc.classList.remove('active');
                    }
                    syncInCallPipBar();
                });

                return;
            } catch (err) {
                console.warn('[PBX] Document PiP opening failed, falling back to standalone window:', err);
            }
        }

        // 3. Fallback for older browsers: Standalone Dedicated Chromeless Window (popup=1,popup=yes)
        const isAdmin = window.location.pathname.startsWith('/admin');
        const popoutUrl = isAdmin ? '/admin/pbx/phone-window' : '/user/pbx/phone-window';
        const winWidth = 360;
        const winHeight = 620;
        const left = window.screen.width ? (window.screen.width - winWidth - 30) : 100;
        const top = 60;
        const win = window.open(
            popoutUrl,
            'VoxPulsePbxSoftphone',
            `popup=1,popup=yes,width=${winWidth},height=${winHeight},top=${top},left=${left},scrollbars=no,resizable=yes,status=no,toolbar=no,menubar=no,location=no`
        );
        if (win) {
            win.focus();
            const card = document.getElementById('pbx-dialer-card');
            if (card && card.classList.contains('active')) {
                card.classList.remove('active');
            }
        } else {
            alert('Pop-up window was blocked by your browser. Please allow popups for this site.');
        }
    };

    // Auto-Register MediaSession Picture-in-Picture (Chrome 120+)
    if ('mediaSession' in navigator && 'documentPictureInPicture' in window) {
        try {
            navigator.mediaSession.setActionHandler('enterpictureinpicture', async () => {
                window.pbxPopoutWindow();
            });
        } catch (e) {}
    }

    // Accidental Navigation Guard: Prevent dropping calls when closing tab or reloading page
    window.addEventListener('beforeunload', function (e) {
        if (window.pbxIsSpaNavigating) return;

        if (currentSession && !currentSession.isEnded()) {
            const warningMsg = 'You have an active PBX call in progress! Leaving or refreshing this page will disconnect the call.';
            e.preventDefault();
            e.returnValue = warningMsg;
            return warningMsg;
        }
    });

    // Graceful Teardown on Unload: If user insists on closing/leaving, immediately terminate call
    window.addEventListener('pagehide', function () {
        if (currentSession && !currentSession.isEnded()) {
            try {
                currentSession.terminate({
                    status_code: 487,
                    reason_phrase: 'Request Terminated'
                });
            } catch (err) {}

            if (navigator.sendBeacon && userCreds && userCreds.extension) {
                const formData = new FormData();
                formData.append('extension', userCreds.extension);
                formData.append('channel', 'PJSIP/' + userCreds.extension);
                
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                if (csrf) formData.append('_token', csrf);

                const hangupUrl = window.location.pathname.startsWith('/admin')
                    ? '/admin/pbx/live-calls/hangup'
                    : '/admin/pbx/live-calls/hangup';
                navigator.sendBeacon(hangupUrl, formData);
            }
        }
    });

    // Auto initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPbxClient);
    } else {
        initPbxClient();
    }
})();
