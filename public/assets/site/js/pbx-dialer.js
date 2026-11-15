/**
 * PBX WebRTC Floating Corner Dialer Engine
 * Powered by JsSIP & Web Audio API
 */

(function () {
    'use strict';

    // State Variables
    let ua = null;
    let currentSession = null;
    let userCreds = null;
    let callTimerInterval = null;
    let callStartTime = null;
    let audioCtx = null;
    let ringtoneInterval = null;
    let isMuted = false;

    // Web Audio Synthesizer for DTMF and Ringtones
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

    // Play DTMF Frequency Pairs
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

            gain.gain.setValueAtTime(0.08, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.15);

            osc1.connect(gain);
            osc2.connect(gain);
            gain.connect(ctx.destination);

            osc1.start(now);
            osc2.start(now);
            osc1.stop(now + 0.15);
            osc2.stop(now + 0.15);
        } catch (e) {
            console.warn('Audio feedback failed:', e);
        }
    }

    // Synthesized Telephone Ringing Sound
    function startRingtone() {
        stopRingtone();
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
                gain.gain.setValueAtTime(0.12, now + 1.2);
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

        playBurst();
        ringtoneInterval = setInterval(playBurst, 3000);
    }

    function stopRingtone() {
        if (ringtoneInterval) {
            clearInterval(ringtoneInterval);
            ringtoneInterval = null;
        }
    }

    // DOM Elements Helpers
    const $ = (id) => document.getElementById(id);

    // Update Status Light and Tooltip
    function setStatus(statusClass, labelText) {
        const dot = $('pbx-status-dot');
        const fabLabel = $('pbx-fab-label');
        if (dot) {
            dot.className = 'pbx-status-dot ' + statusClass;
        }
        if (fabLabel && labelText) {
            fabLabel.textContent = labelText;
        }
    }

    // Toggle Dialer Card Open/Close
    window.togglePbxDialer = function () {
        const card = $('pbx-dialer-card');
        if (!card) return;
        getAudioContext();
        card.classList.toggle('open');
    };

    // Close / Minimize Dialer
    window.closePbxDialer = function () {
        const card = $('pbx-dialer-card');
        if (card) card.classList.remove('open');
    };

    // Keypad Functions
    window.pbxDialDigit = function (digit) {
        const display = $('pbx-display-input');
        if (display) {
            display.textContent += digit;
            playDtmfTone(digit);
            updateCalleeNameMatch();

            // Send DTMF in-call if session is active
            if (currentSession && currentSession.isEstablished()) {
                currentSession.sendDTMF(digit);
            }
        }
    };

    window.pbxBackspace = function () {
        const display = $('pbx-display-input');
        if (display && display.textContent.length > 0) {
            display.textContent = display.textContent.slice(0, -1);
            updateCalleeNameMatch();
        }
    };

    window.pbxClearDisplay = function () {
        const display = $('pbx-display-input');
        if (display) {
            display.textContent = '';
            updateCalleeNameMatch();
        }
    };

    function updateCalleeNameMatch() {
        const num = $('pbx-display-input')?.textContent.trim();
        const nameEl = $('pbx-callee-name');
        if (!nameEl) return;

        if (!num || !userCreds || !userCreds.contacts) {
            nameEl.textContent = '';
            return;
        }

        const match = userCreds.contacts.find((c) => c.extension === num);
        if (match) {
            nameEl.textContent = (match.name || match.user_name || 'Extension ' + match.extension);
        } else {
            nameEl.textContent = '';
        }
    }

    // Tab Switcher
    window.switchPbxTab = function (tabName) {
        const keypadTab = $('pbx-tab-keypad');
        const contactsTab = $('pbx-tab-contacts');
        const viewKeypad = $('pbx-view-keypad');
        const viewContacts = $('pbx-view-contacts');

        if (tabName === 'keypad') {
            keypadTab?.classList.add('active');
            contactsTab?.classList.remove('active');
            if (viewKeypad) viewKeypad.style.display = 'block';
            if (viewContacts) viewContacts.style.display = 'none';
        } else {
            contactsTab?.classList.add('active');
            keypadTab?.classList.remove('active');
            if (viewKeypad) viewKeypad.style.display = 'none';
            if (viewContacts) viewContacts.style.display = 'block';
        }
    };

    // Contacts Quick Call
    window.pbxCallContact = function (extNumber, extName) {
        const display = $('pbx-display-input');
        if (display) display.textContent = extNumber;
        updateCalleeNameMatch();
        switchPbxTab('keypad');
        pbxMakeCall(extNumber, extName);
    };

    // Make Outgoing Call
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

        showCallOverlay('Calling ' + num + '...', displayName, num);

        const targetUri = `sip:${num}@${userCreds.domain}`;
        const options = {
            mediaConstraints: { audio: true, video: false },
            pcConfig: {
                iceServers: [{ urls: 'stun:stun.l.google.com:19302' }]
            }
        };

        try {
            const session = ua.call(targetUri, options);
            bindSessionEvents(session);
        } catch (e) {
            console.error('Call failed to initiate:', e);
            endCallUI();
            alert('Call initiation error: ' + e.message);
        }
    };

    // Bind Session Call Events
    function bindSessionEvents(session) {
        currentSession = session;

        session.on('progress', function () {
            console.log('[PBX] Call in progress / ringing...');
            $('pbx-overlay-status').textContent = 'Ringing...';
            setStatus('ringing', 'Ringing...');
        });

        session.on('confirmed', function () {
            console.log('[PBX] Call confirmed / answered');
            stopRingtone();
            startCallTimer();
            $('pbx-overlay-status').textContent = 'Call in progress';
            setStatus('incall', 'In Call');
        });

        session.on('peerconnection', function (data) {
            const pc = data.peerconnection;
            pc.addEventListener('track', function (e) {
                console.log('[PBX] Remote audio track received');
                const remoteAudio = $('pbx-remote-audio');
                if (remoteAudio && e.streams && e.streams[0]) {
                    remoteAudio.srcObject = e.streams[0];
                    remoteAudio.play().catch((err) => console.warn('Audio play autoplay blocked:', err));
                }
            });
        });

        session.on('ended', function () {
            console.log('[PBX] Call ended');
            stopRingtone();
            endCallUI();
        });

        session.on('failed', function (e) {
            console.log('[PBX] Call failed:', e.cause);
            stopRingtone();
            $('pbx-overlay-status').textContent = 'Call Failed: ' + (e.cause || 'No answer');
            setTimeout(endCallUI, 2000);
        });
    }

    // Call Overlay UI Controls
    function showCallOverlay(status, name, ext) {
        const overlay = $('pbx-call-overlay');
        if (!overlay) return;

        $('pbx-overlay-name').textContent = name || 'Internal Call';
        $('pbx-overlay-ext').textContent = 'Ext: ' + ext;
        $('pbx-overlay-status').textContent = status || 'Connecting...';
        $('pbx-overlay-timer').textContent = '00:00';
        $('pbx-overlay-avatar').textContent = (name ? name.charAt(0).toUpperCase() : ext.charAt(0));

        overlay.classList.add('active');
        $('pbx-dialer-card')?.classList.add('open');
    }

    function startCallTimer() {
        stopCallTimer();
        callStartTime = Date.now();
        callTimerInterval = setInterval(function () {
            const diff = Math.floor((Date.now() - callStartTime) / 1000);
            const mins = String(Math.floor(diff / 60)).padStart(2, '0');
            const secs = String(diff % 60).padStart(2, '0');
            const timerEl = $('pbx-overlay-timer');
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
        stopRingtone();
        currentSession = null;
        isMuted = false;

        const overlay = $('pbx-call-overlay');
        if (overlay) overlay.classList.remove('active');

        const incomingModal = $('pbx-incoming-modal');
        if (incomingModal) incomingModal.classList.remove('active');

        const fab = $('pbx-fab-btn');
        if (fab) fab.classList.remove('is-incoming');

        const muteBtn = $('pbx-mute-btn');
        if (muteBtn) muteBtn.classList.remove('active');

        if (ua && ua.isRegistered()) {
            setStatus('online', userCreds ? `Ext ${userCreds.extension}` : 'Online');
        } else {
            setStatus('offline', 'Offline');
        }
    }

    // Call Actions
    window.pbxHangup = function () {
        if (currentSession) {
            try {
                currentSession.terminate();
            } catch (e) {
                console.warn('Hangup terminate error:', e);
            }
        }
        endCallUI();
    };

    window.pbxToggleMute = function () {
        if (!currentSession) return;
        const btn = $('pbx-mute-btn');
        if (!isMuted) {
            currentSession.mute({ audio: true });
            isMuted = true;
            btn?.classList.add('active');
        } else {
            currentSession.unmute({ audio: true });
            isMuted = false;
            btn?.classList.remove('active');
        }
    };

    // Incoming Call Modal Handlers
    window.pbxAnswerIncoming = function () {
        stopRingtone();
        if (!currentSession) return;

        const incomingModal = $('pbx-incoming-modal');
        if (incomingModal) incomingModal.classList.remove('active');

        const fab = $('pbx-fab-btn');
        if (fab) fab.classList.remove('is-incoming');

        showCallOverlay('Connected', currentSession.remote_identity.display_name, currentSession.remote_identity.uri.user);

        currentSession.answer({
            mediaConstraints: { audio: true, video: false },
            pcConfig: {
                iceServers: [{ urls: 'stun:stun.l.google.com:19302' }]
            }
        });
    };

    window.pbxRejectIncoming = function () {
        stopRingtone();
        if (currentSession) {
            try {
                currentSession.terminate({ status_code: 486, reason_phrase: 'Busy Here' });
            } catch (e) {}
        }
        endCallUI();
    };

    // Handle Incoming Call
    function handleIncomingCall(session) {
        currentSession = session;
        bindSessionEvents(session);

        const callerName = session.remote_identity.display_name || 'Extension ' + session.remote_identity.uri.user;
        const callerExt = session.remote_identity.uri.user;

        $('pbx-incoming-caller').textContent = `${callerName} (${callerExt})`;
        $('pbx-incoming-avatar').textContent = callerName.charAt(0).toUpperCase();

        const incomingModal = $('pbx-incoming-modal');
        if (incomingModal) incomingModal.classList.add('active');

        const fab = $('pbx-fab-btn');
        if (fab) fab.classList.add('is-incoming');

        $('pbx-dialer-card')?.classList.add('open');

        startRingtone();
        setStatus('ringing', 'Incoming Call');
    }

    // Populate Contacts in Tab
    function renderContactsList(contacts) {
        const container = $('pbx-contacts-container');
        if (!container) return;

        if (!contacts || contacts.length === 0) {
            container.innerHTML = '<div class="text-center text-muted py-4 small">No other colleagues found.</div>';
            return;
        }

        let html = '';
        contacts.forEach(function (c) {
            const name = c.name || c.user_name || 'Extension ' + c.extension;
            const initial = name.charAt(0).toUpperCase();
            html += `
                <div class="pbx-contact-item">
                    <div class="pbx-contact-info">
                        <div class="pbx-contact-avatar">${initial}</div>
                        <div>
                            <div class="pbx-contact-name">${name}</div>
                            <div class="pbx-contact-ext">Ext ${c.extension}</div>
                        </div>
                    </div>
                    <button class="pbx-quick-call-btn" title="Call ${name}" onclick="pbxCallContact('${c.extension}', '${name}')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </button>
                </div>
            `;
        });
        container.innerHTML = html;
    }

    // Initialize Auto Registration on Login
    async function initPbxClient() {
        console.log('[PBX Phone] Checking employee extension assignment...');
        setStatus('connecting', 'Connecting...');

        try {
            const res = await fetch('/user/pbx/phone/credentials', {
                headers: { 'Accept': 'application/json' },
                cache: 'no-store'
            });

            if (!res.ok) {
                console.log('[PBX Phone] No extension assigned or unauthorized.');
                setStatus('offline', 'No Extension');
                const widget = $('pbx-floating-container');
                if (widget) widget.title = 'No extension assigned to your account';
                return;
            }

            const data = await res.json();
            if (!data.assigned) {
                console.log('[PBX Phone] No extension assigned:', data.message);
                setStatus('offline', 'No Extension');
                return;
            }

            userCreds = data;
            console.log('[PBX Phone] Credentials loaded for Ext:', data.extension, data.name);

            // Update UI elements with user data
            const userDisplay = $('pbx-header-user');
            const extBadge = $('pbx-header-ext');
            if (userDisplay) userDisplay.textContent = data.name;
            if (extBadge) extBadge.textContent = 'Ext ' + data.extension;

            // Render Contacts List
            renderContactsList(data.contacts || []);

            // Check if JsSIP is loaded
            if (typeof JsSIP === 'undefined') {
                console.error('[PBX Phone] JsSIP library is missing.');
                setStatus('offline', 'Library Error');
                return;
            }

            // Auto upgrade WS URL for HTTPS compatibility and prevent Mixed Content blocking
            let wsUrl = data.ws_url || `wss://${window.location.host}/ws`;
            if (window.location.protocol === 'https:' && wsUrl.startsWith('ws://')) {
                wsUrl = wsUrl.replace(/^ws:\/\//i, 'wss://');
            }
            if (/^(wss?:\/\/)(10\.|192\.168\.|172\.(1[6-9]|2[0-9]|3[0-1])\.|127\.|localhost)/.test(wsUrl) && !/^(10\.|192\.168\.|172\.|127\.|localhost)/.test(window.location.hostname)) {
                const proto = window.location.protocol === 'https:' ? 'wss://' : 'ws://';
                wsUrl = `${proto}${window.location.host}/ws`;
                console.warn('[PBX] Rewrote private WS URL to public host:', wsUrl);
            }

            let sipDomain = data.domain || window.location.hostname;
            if (/^(10\.|192\.168\.|172\.(1[6-9]|2[0-9]|3[0-1])\.|127\.|localhost)/.test(sipDomain) && !/^(10\.|192\.168\.|172\.|127\.|localhost)/.test(window.location.hostname)) {
                sipDomain = window.location.hostname;
            }

            console.log('[PBX] Connecting to WebSocket:', wsUrl, 'Domain:', sipDomain);

            // Create WebSocket Transport
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
                console.log('[PBX] Connecting to PBX WebSocket server...');
                setStatus('connecting', 'Connecting...');
            });

            ua.on('connected', function () {
                console.log('[PBX] WebSocket connection established.');
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
                    console.log('[PBX] Incoming call detected from:', session.remote_identity.uri.user);
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
