# VoxPulse PBX - Enterprise Server Setup & Handover Guide

This comprehensive guide details the complete deployment, configuration, and administrative handover procedure for the **VoxPulse Enterprise PBX & WebRTC Telecom Platform**.

---

## 1. System Architecture & Prerequisites

The platform consists of three core components:
1. **Laravel 11 Application & Web Softphone (JsSIP / WebRTC)**
2. **Asterisk 20+ LTS PJSIP Telecom Engine**
3. **Nginx Reverse Proxy & TLS/SSL Gateway (WSS)**

```
                      +---------------------------------------+
                      |   Client Browser (User / Softphone)   |
                      +---------------------------------------+
                                    |            |
                        HTTPS (443) |            | WSS Signaling (/ws)
                                    v            v
                      +---------------------------------------+
                      |       Nginx Reverse Proxy & SSL       |
                      +---------------------------------------+
                           |                     |
              HTTP (8000)  |                     | HTTP/WS (8088)
                           v                     v
            +--------------------+    +-----------------------------+
            | Laravel Application|    | Asterisk 20.6 PJSIP Server  |
            | (Docker or Native) |    |  - WSS Gateway (Port 8088)  |
            +--------------------+    |  - Manager AMI (Port 5038)  |
                     |       ^        |  - HTTP rawman (/rawman)    |
                     |       |        |  - RTP Media (10000-20000)  |
                     v       +--------+-----------------------------+
               MySQL 8.0+         AMI Actions / ChanSpy Coaching
```

### Server Specifications
- **Operating System:** Ubuntu 22.04 LTS or Ubuntu 24.04 LTS (x86_64)
- **CPU / RAM:** Minimum 2 vCPU, 2 GB RAM (Recommended: 4 vCPU, 4 GB RAM for 50+ concurrent calls)
- **Disk:** 25 GB+ SSD
- **Public Static IPv4** (with valid DNS A Record pointing to the server, e.g. `pbx.yourdomain.com`)

---

## 2. Server Package Installation

Run the following commands on a clean Ubuntu server:

```bash
sudo apt update && sudo apt upgrade -y

# 1. Install Asterisk 20 and development libraries
sudo apt install -y asterisk asterisk-modules asterisk-pjsip asterisk-core-sounds-en-wav

# 2. Install Nginx and Let's Encrypt Certbot
sudo apt install -y nginx certbot python3-certbot-nginx libnginx-mod-stream

# 3. Install PHP 8.2+ and Extensions
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-curl php8.2-mbstring \
                    php8.2-xml php8.2-bcmath php8.2-zip php8.2-intl

# 4. Install Composer & Node.js
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

---

## 3. Asterisk 20 PJSIP Configuration

Configure the essential Asterisk files located in `/etc/asterisk/`.

### A. HTTP & WebSocket Support (`/etc/asterisk/http.conf`)
Enables the internal HTTP server for WebSockets (WSS) and AMI HTTP rawman.
```ini
[general]
enabled=yes
bindaddr=0.0.0.0
bindport=8088
```

### B. Manager Interface & AMI (`/etc/asterisk/manager.conf`)
Enables the Asterisk Call Manager (AMI) and Web Manager (`webenabled=yes`).
```ini
[general]
enabled = yes
webenabled = yes
port = 5038
bindaddr = 0.0.0.0

#include "manager.d/*.conf"
```

Create `/etc/asterisk/manager.d/admin.conf`:
```ini
[admin]
secret = voxpulse2026
read = all
write = all
permit = 0.0.0.0/0.0.0.0
```

### C. RTP Audio Media Port Range (`/etc/asterisk/rtp.conf`)
Ensures voice audio RTP flows properly across NAT:
```ini
[general]
rtpstart=10000
rtpend=20000
icesupport=yes
stunaddr=stun.l.google.com:19302
```

### D. PJSIP Transport & WebRTC Configuration (`/etc/asterisk/pjsip.conf`)
Configure the WebSocket transport for WebRTC softphones:
```ini
[general]

; WebSocket Transport for In-Browser WebRTC Softphone
[transport-ws]
type=transport
protocol=ws
bind=0.0.0.0:8088

; UDP Transport for standard SIP Hardware Phones (Optional)
[transport-udp]
type=transport
protocol=udp
bind=0.0.0.0:5060

; Example Template for WebRTC Extension (Ext 101)
[101]
type=aor
max_contacts=10
remove_existing=no

[101]
type=auth
auth_type=userpass
username=101
password=YourSecurePassword101

[101]
type=endpoint
context=internal-calls
disallow=all
allow=opus,ulaw,alaw
aors=101
auth=101
dtls_auto_generate_cert=yes
webrtc=yes
media_encryption=dtls
dtls_verify=fingerprint
dtls_setup=actpass
ice_support=yes
use_avpf=yes
rtcp_mux=yes
direct_media=no
callerid=Admin Desk <101>
```

> [!TIP]
> The Laravel Admin Panel automatically generates and syncs extensions via `/admin/pbx/extensions`. Individual extensions do not need to be manually typed into `pjsip.conf`.

### E. Dialplan Routing & Call Coaching (`/etc/asterisk/extensions.conf`)
Configure internal dialing, multi-tab parallel ringing, echo tests, and supervisor coaching (ChanSpy):

```ini
[general]
static=yes
writeprotect=no

[internal-calls]
; Audio Echo Test (Mic & Speaker Diagnostics)
exten => *43,1,NoOp(Echo test for ${CALLERID(num)})
 same => n,Answer()
 same => n,Echo()
 same => n,Hangup()

; Music On Hold / Audio Playback Test
exten => 999,1,NoOp(Music on hold test for ${CALLERID(num)})
 same => n,Answer()
 same => n,Echo()
 same => n,Hangup()

; Supervisor Call Monitoring & Coaching (ChanSpy)
; *55<EXT> = Silent Listen (Admin listens silently to both parties)
exten => _*55[0-9].,1,NoOp(Supervisor Silent Listen on ${EXTEN:3})
 same => n,ChanSpy(PJSIP/${EXTEN:3},qud)
 same => n,Hangup()

; *56<EXT> = Whisper (Admin speaks directly to agent; caller cannot hear)
exten => _*56[0-9].,1,NoOp(Supervisor Whisper to ${EXTEN:3})
 same => n,ChanSpy(PJSIP/${EXTEN:3},quwd)
 same => n,Hangup()

; *57<EXT> = Barge (Admin enters as 3-way conference call)
exten => _*57[0-9].,1,NoOp(Supervisor Barge into ${EXTEN:3})
 same => n,ChanSpy(PJSIP/${EXTEN:3},quBd)
 same => n,Hangup()

; Standard Internal Extension Dialing (Rings all open tabs simultaneously)
exten => _[0-9].,1,NoOp(Internal call from ${CALLERID(num)} to ${EXTEN})
 same => n,Set(DIALSTR=${PJSIP_DIAL_CONTACTS(${EXTEN})})
 same => n,GotoIf($[${LEN(${DIALSTR})} > 0]?dial_contacts:dial_fallback)
 same => n(dial_contacts),Dial(${DIALSTR},60)
 same => n,Hangup()
 same => n(dial_fallback),Dial(PJSIP/${EXTEN},60)
 same => n,Hangup()
```

Apply Asterisk changes:
```bash
sudo asterisk -rx 'module reload'
sudo asterisk -rx 'dialplan reload'
sudo asterisk -rx 'manager reload'
```

---

## 4. Nginx Reverse Proxy & WSS SSL Setup

WebRTC **requires** HTTPS and secure WebSockets (`wss://`). Browsers strictly block microphone access on insecure connections.

Obtain a free SSL certificate:
```bash
sudo certbot certonly --nginx -d pbx.yourdomain.com
```

Create `/etc/nginx/sites-available/pbx.conf`:
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name pbx.yourdomain.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name pbx.yourdomain.com;

    ssl_certificate     /etc/letsencrypt/live/pbx.yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/pbx.yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;

    client_max_body_size 100M;

    # 1. WSS Signaling Gateway (Asterisk WebRTC)
    location /ws {
        proxy_pass http://127.0.0.1:8088/ws;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 86400s;
        proxy_send_timeout 86400s;
    }

    # 2. Asterisk AMI HTTP Gateway (Protected, Internal Only)
    location /rawman {
        allow 10.0.0.0/8;
        allow 172.16.0.0/12;
        allow 192.168.0.0/16;
        allow 127.0.0.1;
        deny all;

        proxy_pass http://127.0.0.1:8088/rawman;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # 3. Main Web Application (Laravel PHP-FPM or Docker container)
    location / {
        proxy_pass http://127.0.0.1:8000; # Or fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 300s;
    }
}
```

Enable and reload Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/pbx.conf /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

---

## 5. Firewall & Port Rules (UFW)

Ensure the required VoIP and web ports are open:
```bash
sudo ufw allow 80/tcp comment 'HTTP Web'
sudo ufw allow 443/tcp comment 'HTTPS Web & WSS'
sudo ufw allow 5060/udp comment 'SIP Signaling'
sudo ufw allow 10000:20000/udp comment 'RTP Audio Media'
sudo ufw allow from 10.0.0.0/8 to any port 5038 proto tcp comment 'Asterisk AMI internal'
sudo ufw reload
```

---

## 6. Laravel Application Environment (`.env`)

Configure the `.env` file of your Laravel application:

```ini
APP_NAME=VoxPulse
APP_ENV=production
APP_DEBUG=false
APP_URL=https://pbx.yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pbx_production
DB_USERNAME=pbx_user
DB_PASSWORD=YourStrongDatabasePassword

# PBX Telecom Settings
PBX_DRIVER=asterisk
PBX_DOMAIN=pbx.yourdomain.com
PBX_WS_URL=wss://pbx.yourdomain.com/ws

# Asterisk AMI Management
ASTERISK_AMI_HOST=10.0.1.1
ASTERISK_AMI_PORT=5038
ASTERISK_AMI_USER=admin
ASTERISK_AMI_SECRET=voxpulse2026
ASTERISK_RAWMAN_URL=https://pbx.yourdomain.com/rawman
```

Run application initialization:
```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 7. Admin Panel Setup & Onboarding Steps

Once deployed, log into the Admin Panel (`https://pbx.yourdomain.com/admin/login`).

### Step 1: Create Employee Extensions
1. Navigate to **PBX Telecom System &rarr; Extensions**.
2. Click **+ Add Extension**.
3. Enter:
   - **Extension:** `101`, `102`, `103`, etc.
   - **Display Name:** e.g. "Admin Desk", "Sales Manager".
   - **Assign User:** Select the registered employee user.
   - **SIP Password:** Secure password (saved encrypted, auto-injected into the softphone).
4. Click **Save Extension**.

### Step 2: Configure Digital Receptionist (IVR)
1. Navigate to **PBX Telecom System &rarr; IVR Receptionist**.
2. Click **+ Create IVR Menu**.
3. Fill in:
   - **Name:** "Main Company Reception"
   - **Dial Extension:** `500`
   - **Timeout (seconds):** `10`
   - **DTMF Key Routing:**
     - Key `1` &rarr; Extension `101` (Sales)
     - Key `2` &rarr; Extension `102` (Support)
     - Key `3` &rarr; Extension `103` (Billing)
   - **Timeout Fallback:** Extension `101`
4. Click **Save IVR Menu**, then click **Sync Dialplan**.

### Step 3: Test Calling with the Softphone
1. Log in as an employee or admin on the frontend (`https://pbx.yourdomain.com`).
2. The **PBX Phone** floating pill appears at the bottom-right corner.
3. Click the pill to expand the dialer:
   - Status badge shows: `Ext 101 • Online` (Green dot).
4. Dial `*43` and press the green Call button:
   - Speak into your microphone; Asterisk will echo your voice back immediately with crystal clarity.
5. Dial `500` to test the automated IVR receptionist menu.

---

## 8. Supervisor Call Coaching (ChanSpy)

When team members are on active calls:
1. Navigate to **PBX Telecom System &rarr; Live Active Calls**.
2. The table displays real-time ongoing conversations with talk duration.
3. Select your desired supervisor action:
   - 🎧 **Listen:** Silently listen to both the agent and customer. Neither party can hear you.
   - 🗣️ **Whisper:** Coach the agent in real time. The customer cannot hear you.
   - 👥 **Barge:** Join the call directly as a 3-way conference.
4. Asterisk will automatically call your softphone. Click **Answer** to connect into the live audio stream.
5. **In-Call Keypad Switching:** While monitoring, press `4` (Listen), `5` (Whisper), or `6` (Barge) on your dialpad to switch modes on the fly.
