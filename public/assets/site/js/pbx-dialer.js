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

        showCallOverlay('Calling...', displayName, num);
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
    function showCallOverlay(status, name, ext) {
        const overlay = $('pbx-call-overlay');
        if (!overlay) return;

        $('pbx-overlay-name').textContent = name || 'Internal Call';
        $('pbx-overlay-ext').textContent = 'Ext ' + ext;
        $('pbx-overlay-timer').textContent = '00:00';
        $('pbx-overlay-avatar').textContent = (name ? name.charAt(0).toUpperCase() : (ext ? ext.charAt(0) : '?'));

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
            if (isCallHeld) return;
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
            setStatus('online', 'Online');
        } else {
            setStatus('offline', 'Offline');
        }
    }

    // =========================================================================
    // In-Call PBX Action Controls
    // =========================================================================
    window.pbxHangup = function () {
        getAudioContext();
        stopAllAudioTones();
        if (currentSession && !currentSession.isEnded()) {
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
        const callerName = session.remote_identity?.display_name || ('Extension ' + callerExt);
        session._targetNum = callerExt;
        session._targetName = callerName;

        bindSessionEvents(session);

        const callerEl = $('pbx-incoming-caller');
        const extEl = $('pbx-incoming-ext');
        const avatarEl = $('pbx-incoming-avatar');
        if (callerEl) callerEl.textContent = callerName;
        if (extEl) extEl.textContent = 'Ext ' + callerExt;
        if (avatarEl) avatarEl.textContent = callerName ? callerName.charAt(0).toUpperCase() : '?';

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
        const callerName = currentSession.remote_identity?.display_name || (callerExt ? 'Ext ' + callerExt : 'Caller');

        showCallOverlay('Connecting...', callerName, callerExt);

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

    // Auto initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPbxClient);
    } else {
        initPbxClient();
    }
})();
