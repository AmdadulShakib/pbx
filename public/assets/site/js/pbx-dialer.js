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

    // Helper: Element Selector
    function $(id) {
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

    window.togglePbxDialer = function () {
        const card = $('pbx-dialer-card');
        if (!card) return;
        getAudioContext();
        card.classList.toggle('open');
    };

    window.closePbxDialer = function () {
        $('pbx-dialer-card')?.classList.remove('open');
    };

    window.switchPbxTab = function (tabName) {
        getAudioContext();
        ['keypad', 'contacts', 'recents'].forEach((tab) => {
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

    function updateCalleeNameMatch() {
        const display = $('pbx-display-input');
        const matchEl = $('pbx-callee-name');
        if (!display || !matchEl || !userCreds) return;

        const val = display.textContent.trim();
        if (!val) {
            matchEl.textContent = '';
            return;
        }

        const match = userCreds.contacts?.find((c) => c.extension === val);
        if (match) {
            matchEl.textContent = `${match.name} (${match.user_name || 'Colleague'})`;
        } else {
            matchEl.textContent = '';
        }
    }

    // =========================================================================
    // Colleagues Directory & Quick Call
    // =========================================================================
    function renderContactsList(contacts) {
        const container = $('pbx-contacts-container');
        if (!container) return;

        if (!contacts || contacts.length === 0) {
            container.innerHTML = '<div class="pbx-empty-state">No other colleagues assigned to extensions.</div>';
            return;
        }

        let html = '';
        contacts.forEach((c) => {
            const name = c.name || `Extension ${c.extension}`;
            const sub = c.user_name || 'Extension ' + c.extension;
            const initial = name.charAt(0).toUpperCase();

            html += `
            <div class="pbx-contact-row" data-ext="${c.extension}" data-name="${name.toLowerCase()}">
                <div class="pbx-contact-info">
                    <div class="pbx-contact-avatar">${initial}</div>
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

    // =========================================================================
    // Call History / Recents (Persisted in localStorage)
    // =========================================================================
    function addRecentCall(direction, ext, name, status, duration) {
        try {
            const key = 'pbx_recents_' + (userCreds ? userCreds.extension : 'default');
            let recents = JSON.parse(localStorage.getItem(key) || '[]');
            recents.unshift({
                direction: direction, // 'incoming', 'outgoing', 'missed'
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

        const num = targetNumber || $('pbx-display-input')?.textContent.trim();
        if (!num) {
            alert('Please enter an extension number to call.');
            return;
        }

        if (num === userCreds.extension) {
            alert('Cannot call your own extension.');
            return;
        }

        getAudioContext();

        const match = userCreds.contacts ? userCreds.contacts.find((c) => c.extension === num) : null;
        const displayName = targetName || (match ? (match.name || match.user_name) : 'Extension ' + num);

        // Pre-request microphone permission so browser does not block media
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ audio: true }).then((stream) => {
                // Stop the preliminary test stream
                stream.getTracks().forEach((t) => t.stop());
                executeCall(num, displayName);
            }).catch((err) => {
                console.warn('[PBX] Mic access error:', err);
                executeCall(num, displayName);
            });
        } else {
            executeCall(num, displayName);
        }
    };

    function executeCall(num, displayName) {
        showCallOverlay('Calling...', displayName, num);
        startOutgoingRingback();

        const targetUri = `sip:${num}@${userCreds.domain}`;
        const options = {
            mediaConstraints: { audio: true, video: false },
            pcConfig: {
                iceServers: [{ urls: 'stun:stun.l.google.com:19302' }]
            }
        };

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
        const remoteAudio = $('pbx-remote-audio');
        if (!remoteAudio || !stream) return;

        console.log('[PBX Audio] Attaching remote audio stream:', stream.id);
        remoteAudio.srcObject = stream;
        remoteAudio.play().then(() => {
            console.log('[PBX Audio] Remote audio playback active.');
        }).catch((err) => {
            console.warn('[PBX Audio] Autoplay policy prevented audio play:', err);
            // On user click anywhere, retry play
            const resumePlay = () => {
                remoteAudio.play();
                document.removeEventListener('click', resumePlay);
            };
            document.addEventListener('click', resumePlay);
        });
    }

    // Bind Session Lifecycle Events
    function bindSessionEvents(session) {
        currentSession = session;
        isCallMuted = false;
        isCallHeld = false;

        // PeerConnection track received
        session.on('peerconnection', function (data) {
            const pc = data.peerconnection;
            pc.addEventListener('track', function (e) {
                console.log('[PBX WebRTC] Remote track received:', e.track.kind);
                if (e.streams && e.streams[0]) {
                    attachRemoteAudio(e.streams[0]);
                }
            });
        });

        // 180 / 183 Ringing
        session.on('progress', function () {
            console.log('[PBX] Remote phone is ringing...');
            updateCallOverlayState('Ringing...', false);
            setStatus('ringing', 'Ringing...');
        });

        // 200 OK Received or Sent (Call Answered)
        session.on('accepted', function () {
            console.log('[PBX] Call accepted / answered!');
            handleCallEstablished();
        });

        // ACK Received or Sent (Call Fully Confirmed)
        session.on('confirmed', function () {
            console.log('[PBX] Call confirmed (ACK received)!');
            handleCallEstablished();
        });

        // Call Ended
        session.on('ended', function () {
            console.log('[PBX] Call ended normally.');
            playCallEndTone();
            recordCallHistory(session, 'Completed');
            updateCallOverlayState('Call Ended', false);
            setTimeout(endCallUI, 1200);
        });

        // Call Failed
        session.on('failed', function (e) {
            console.warn('[PBX] Call failed cause:', e.cause);
            playCallEndTone();
            recordCallHistory(session, e.cause || 'Failed');
            updateCallOverlayState(`Call Failed (${e.cause || 'No Answer'})`, false);
            setTimeout(endCallUI, 2200);
        });
    }

    function handleCallEstablished() {
        stopAllAudioTones();
        if (!callTimerInterval) {
            startCallTimer();
        }
        updateCallOverlayState('Connected', true);
        setStatus('incall', 'In Call');

        // Check if remote stream is already present on PeerConnection
        if (currentSession && currentSession.connection) {
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
    }

    // Call Overlay State & Timer
    function showCallOverlay(status, name, ext) {
        const overlay = $('pbx-call-overlay');
        if (!overlay) return;

        $('pbx-overlay-name').textContent = name || 'Internal Call';
        $('pbx-overlay-ext').textContent = 'Ext ' + ext;
        $('pbx-overlay-timer').textContent = '00:00';
        $('pbx-overlay-avatar').textContent = (name ? name.charAt(0).toUpperCase() : ext.charAt(0));

        updateCallOverlayState(status, false);

        overlay.classList.add('active');
        $('pbx-dialer-card')?.classList.add('open');
    }

    function updateCallOverlayState(statusText, isConnected) {
        const statusEl = $('pbx-overlay-status');
        const badgeState = $('pbx-overlay-badge-state');
        const waves = $('pbx-audio-waves');

        if (statusEl) statusEl.textContent = statusText;
        if (badgeState) {
            if (isConnected) {
                badgeState.classList.add('connected');
                if (waves) waves.style.display = 'flex';
            } else {
                badgeState.classList.remove('connected');
                if (waves) waves.style.display = 'none';
            }
        }
    }

    function startCallTimer() {
        stopCallTimer();
        callStartTime = Date.now();
        const timerEl = $('pbx-overlay-timer');
        if (timerEl) timerEl.textContent = '00:00';

        callTimerInterval = setInterval(function () {
            if (isCallHeld) return; // Don't tick timer while on hold
            const diff = Math.floor((Date.now() - callStartTime) / 1000);
            const mins = String(Math.floor(diff / 60)).padStart(2, '0');
            const secs = String(diff % 60).padStart(2, '0');
            if (timerEl) timerEl.textContent = `${mins}:${secs}`;
        }, 1000);
    }

    function stopCallTimer() {
        if (callTimerInterval) {
            clearInterval(callTimerInterval);
            callTimerInterval = null;
        }
    }

    function endCallUI() {
        stopCallTimer();
        stopAllAudioTones();
        currentSession = null;
        isCallMuted = false;
        isCallHeld = false;

        const overlay = $('pbx-call-overlay');
        if (overlay) overlay.classList.remove('active');

        const incomingModal = $('pbx-incoming-modal');
        if (incomingModal) incomingModal.classList.remove('active');

        const fab = $('pbx-fab-btn');
        if (fab) fab.classList.remove('is-incoming');

        // Reset control buttons
        $('pbx-mute-btn')?.classList.remove('active');
        $('pbx-hold-btn')?.classList.remove('active');
        $('pbx-incall-dtmf-drawer')?.classList.remove('active');
        $('pbx-incall-transfer-drawer')?.classList.remove('active');

        if (ua && ua.isRegistered()) {
            setStatus('online', userCreds ? `Ext ${userCreds.extension}` : 'Online');
        } else {
            setStatus('offline', 'Offline');
        }
    }

    // =========================================================================
    // In-Call PBX Action Controls
    // =========================================================================
    window.pbxHangup = function () {
        getAudioContext();
        if (currentSession) {
            try {
                currentSession.terminate();
            } catch (e) {
                console.warn('[PBX] Hangup error:', e);
            }
        }
        endCallUI();
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
        session._direction = 'incoming';
        currentSession = session;
        bindSessionEvents(session);

        const callerName = session.remote_identity.display_name || 'Extension ' + session.remote_identity.uri.user;
        const callerExt = session.remote_identity.uri.user;

        $('pbx-incoming-caller').textContent = callerName;
        $('pbx-incoming-ext').textContent = 'Ext ' + callerExt;
        $('pbx-incoming-avatar').textContent = callerName.charAt(0).toUpperCase();

        const incomingModal = $('pbx-incoming-modal');
        if (incomingModal) incomingModal.classList.add('active');

        const fab = $('pbx-fab-btn');
        if (fab) fab.classList.add('is-incoming');

        $('pbx-dialer-card')?.classList.add('open');

        startIncomingRingtone();
        setStatus('ringing', 'Incoming Call');
    }

    window.pbxAnswerIncoming = function () {
        stopAllAudioTones();
        if (!currentSession) return;

        const incomingModal = $('pbx-incoming-modal');
        if (incomingModal) incomingModal.classList.remove('active');

        const fab = $('pbx-fab-btn');
        if (fab) fab.classList.remove('is-incoming');

        showCallOverlay('Connected', currentSession.remote_identity.display_name, currentSession.remote_identity.uri.user);

        // Pre-request user media permission and answer
        const options = {
            mediaConstraints: { audio: true, video: false },
            pcConfig: {
                iceServers: [{ urls: 'stun:stun.l.google.com:19302' }]
            }
        };

        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ audio: true }).then((stream) => {
                stream.getTracks().forEach((t) => t.stop());
                currentSession.answer(options);
            }).catch(() => {
                currentSession.answer(options);
            });
        } else {
            currentSession.answer(options);
        }
    };

    window.pbxRejectIncoming = function () {
        stopAllAudioTones();
        if (currentSession) {
            try {
                currentSession.terminate({ status_code: 486, reason_phrase: 'Busy Here' });
            } catch (e) {}
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
            if (avatar) avatar.textContent = data.name.charAt(0).toUpperCase();

            // Populate Colleagues & Recents
            renderContactsList(data.contacts || []);
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
                connection_recovery_min_interval: 2,
                connection_recovery_max_interval: 15
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
                setStatus('online', `Ext ${data.extension}`);
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
                if (session.direction === 'incoming') {
                    console.log('[PBX] Incoming call received from:', session.remote_identity.uri.user);
                    handleIncomingCall(session);
                }
            });

            // Start User Agent
            ua.start();

        } catch (e) {
            console.error('[PBX Phone] Initialization error:', e);
            setStatus('offline', 'Offline');
        }
    }

    // Auto initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPbxClient);
    } else {
        initPbxClient();
    }
})();
