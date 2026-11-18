<h1 align="center">
  <br>
  🏢 PBX — IP PBX Management System
  <br>
</h1>

<p align="center">
  <strong>A modern web-based PBX administration panel built with Laravel + Asterisk WebRTC.</strong><br>
  Manage extensions, ring groups, IVR menus, SIP trunks, inbound/outbound routes and live calls — all from a beautiful admin dashboard.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-red?logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue?logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/Asterisk-20+-orange" alt="Asterisk">
  <img src="https://img.shields.io/badge/WebRTC-Softphone-green" alt="WebRTC">
  <img src="https://img.shields.io/badge/License-MIT-yellow" alt="MIT License">
</p>

---

## 📋 Table of Contents

- [What is PBX?](#-what-is-pbx)
- [Features](#-features)
- [System Requirements](#-system-requirements)
- [Quick Start (Development)](#-quick-start-development)
- [Production Server Setup](#-production-server-setup-vps--docker)
- [Asterisk Configuration](#-asterisk-configuration)
- [Admin Panel First-Time Setup](#-admin-panel-first-time-setup)
- [SIP Trunk — Getting a Real Phone Number](#-sip-trunk--getting-a-real-phone-number)
- [Project Structure](#-project-structure)
- [Default Credentials](#-default-credentials)
- [FAQ](#-faq)

---

## 🤔 What is PBX?

**PBX** is a complete IP PBX (Private Branch Exchange) web management system. It acts as the **admin panel + softphone client** for an Asterisk VoIP server.

Think of it as an easy-to-use web interface that lets you:
- 🎧 Answer and make calls from any browser (no hardware phone needed)
- 🔀 Route incoming calls to the right team or department automatically
- 📋 Set up IVR menus ("Press 1 for Sales, Press 2 for Support…")
- 📊 View call history and recordings
- 👥 Manage employees and their phone extensions
- 🔌 Connect to real phone lines (SIP Trunk from Brilliant, AmberIT, Twilio, etc.)

---

## ✨ Features

| Feature | Description |
|---|---|
| **WebRTC Softphone** | Make & receive calls directly from the browser — no app or hardware needed |
| **Extension Management** | Create SIP extensions for each employee with auto Asterisk dialplan sync |
| **Ring Groups** | Group multiple extensions — all ring simultaneously or in sequence |
| **IVR Menu** | Auto-attendant with audio prompt upload and key-press routing |
| **SIP Trunks** | Connect to any SIP provider (Brilliant, AmberIT, Twilio, Telnyx, etc.) |
| **Inbound Routes** | Route incoming calls by DID number to IVR, ring group, or extension |
| **Outbound Routes** | Control which trunk to use for outgoing calls |
| **Call History** | Full CDR with duration, recording playback, and caller ID |
| **Live Call Monitor** | Real-time active call dashboard with Listen / Whisper / Barge |
| **PBX Settings** | Configure Asterisk server IP, WebSocket URL, AMI credentials from admin panel |
| **Role-based Access** | Admin + Staff roles with granular permissions |
| **Branding** | Upload your own logo and favicon from the admin panel |

---

## 🖥️ System Requirements

### For the Web Application (Laravel)
| Component | Minimum |
|---|---|
| PHP | 8.2+ |
| Composer | 2.x |
| Node.js | 18+ and npm |
| Database | MySQL 8+ **or** PostgreSQL 15+ |
| Web Server | Nginx or Apache |

### For the PBX Engine (Asterisk)
| Component | Requirement |
|---|---|
| Asterisk | 20+ (LTS recommended) |
| Modules | `res_pjsip`, `res_pjsip_session`, `res_http_websocket`, `res_srtp` |
| SSL Certificate | Required for WebRTC (Let's Encrypt is free) |
| OS | Ubuntu 22.04 / 24.04 LTS recommended |

> **Note:** The Laravel app and Asterisk can run on the **same server** or on **separate servers**.

---

## 🚀 Quick Start (Development)

### Step 1 — Clone the repository

```bash
git clone https://github.com/your-org/pbx.git
cd pbx
```

### Step 2 — Install PHP dependencies

```bash
composer install
```

### Step 3 — Install JS dependencies and build assets

```bash
npm install
npm run dev
```

### Step 4 — Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Now open `.env` and fill in your database and PBX settings (see [Environment Variables](#environment-variables) below).

### Step 5 — Run database migrations

```bash
php artisan migrate --seed
```

> This will create all tables and insert a default admin user.

### Step 6 — Start the development server

```bash
php artisan serve
```

Visit: **http://localhost:8000/admin/login**

---

## 🌐 Production Server Setup (VPS / Docker)

For a production deployment, the recommended stack is:

```
Ubuntu 22.04 VPS
   ├── Nginx (reverse proxy)
   ├── Docker (runs the Laravel app)
   │     └── PBX Laravel App (port 8085)
   ├── Asterisk (installed directly on the host)
   │     ├── SIP WebSocket on port 8088
   │     └── RTP media on ports 10000–20000
   └── Let's Encrypt SSL (via Certbot)
```

### Nginx Config Example

```nginx
server {
    listen 443 ssl;
    server_name pbx.yourdomain.com;

    ssl_certificate /etc/letsencrypt/live/pbx.yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/pbx.yourdomain.com/privkey.pem;

    # WebRTC WebSocket proxy
    location /ws {
        proxy_pass http://127.0.0.1:8088/ws;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_read_timeout 86400s;
    }

    # Laravel App
    location / {
        proxy_pass http://127.0.0.1:8085;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### Required Firewall Ports

```
TCP  80, 443       → HTTP / HTTPS (Nginx)
TCP  8088           → Asterisk WebRTC WebSocket (proxied via Nginx)
UDP  5060           → SIP signaling (from trunk provider)
UDP  10000–20000   → RTP audio media streams
```

---

## ⚙️ Asterisk Configuration

You do **not** need to manually edit Asterisk config files for extensions, ring groups, IVR, trunks, or routes — the admin panel generates and syncs the dialplan automatically.

However, the **initial Asterisk server setup** must be done once on the server.

### 1. Install Asterisk

```bash
sudo apt update
sudo apt install asterisk -y
```

### 2. Required Asterisk Config Files

Place these base config files in `/etc/asterisk/`:

**`/etc/asterisk/http.conf`** — Enables the WebRTC WebSocket:
```ini
[general]
enabled=yes
bindaddr=0.0.0.0
bindport=8088
tlsenable=no
```

**`/etc/asterisk/pjsip_wizard.conf`** — WebRTC endpoint template (auto-managed by PBX):
```ini
; Managed by PBX admin panel — do not edit manually
#include "pjsip_pbx_endpoints.conf"
```

**`/etc/asterisk/extensions.conf`** — Dialplan (auto-managed by PBX):
```ini
[globals]

[from-internal]
#include "/etc/asterisk/dialplan/pbx_extensions.conf"
#include "/etc/asterisk/dialplan/pbx_ring_groups.conf"

[from-trunk]
#include "/etc/asterisk/dialplan/pbx_inbound.conf"

[outbound-trunks]
#include "/etc/asterisk/dialplan/pbx_outbound.conf"
```

### 3. Reload Asterisk

```bash
sudo asterisk -rx "core reload"
```

> ✅ After this initial setup, **all further configuration is done from the admin panel** — no SSH required.

---

## 🛠️ Admin Panel First-Time Setup

After logging in to the admin panel for the first time, follow these steps in order:

### Step 1 — PBX Server Settings
**Menu:** `Admin → PBX → PBX Server Settings`

Configure your Asterisk server connection:
- **PBX Domain / Server IP** → Your server's public IP or domain (e.g. `pbx.yourdomain.com`)
- **WebRTC WebSocket URL** → `wss://pbx.yourdomain.com/ws` (use `wss://` for HTTPS, `ws://` for HTTP)
- **PBX Engine** → Select `Asterisk`
- **AMI Connection** → Fill in AMI Host, Port, Username, Secret and Rawman URL

### Step 2 — Create Extensions
**Menu:** `Admin → PBX → Extensions → Add Extension`

Create a SIP extension for each employee:
- **Extension Number** → e.g. `101`, `102`, `103`
- **Display Name** → Employee's full name
- **Assign User** → Link to an employee account

After saving, click **"Sync to Asterisk"** to push the config to the PBX server.

### Step 3 — Create Ring Groups
**Menu:** `Admin → PBX → Ring Groups → Add Ring Group`

Group extensions together so they all ring when a call comes in:
- **Group Extension** → e.g. `600`
- **Members** → Select the extensions in this group
- **Ring Strategy** → `ringall` (all ring at once) or `linear` (one by one)

### Step 4 — Upload IVR Audio & Create IVR
**Menu:** `Admin → PBX → IVR → Add IVR`

Create an auto-attendant menu:
- **IVR Extension** → e.g. `500`
- **Audio Prompt** → Upload an MP3/WAV file (your greeting message)
- **Key Options** → Set what happens when callers press 1, 2, 3, etc.

### Step 5 — Add SIP Trunk
**Menu:** `Admin → PBX → SIP Trunks → Add Trunk`

Connect a real phone line from your provider:
- **Trunk Name** → e.g. `Brilliant Main`
- **Host** → SIP server address from your provider
- **Username / Password** → Credentials from your provider

### Step 6 — Create Inbound Route
**Menu:** `Admin → PBX → Inbound Routes → Add Route`

Route incoming calls to the right destination:
- **DID Pattern** → Your phone number (e.g. `09638123456`)
- **Destination** → `IVR` or `Ring Group` or specific extension

### Step 7 — Create Outbound Route
**Menu:** `Admin → PBX → Outbound Routes → Add Route`

Control how outgoing calls are handled:
- **Dial Pattern** → e.g. `_X.` (matches all numbers)
- **Trunk** → Select the SIP trunk to use for outgoing calls

---

## 📞 SIP Trunk — Getting a Real Phone Number

A **SIP Trunk** is a virtual phone line that connects your PBX to the public telephone network. Without it, you can only call internally between extensions.

### Bangladesh Providers (Local Numbers)

| Provider | Number Series | Website |
|---|---|---|
| **Brilliant Connect (InterCloud)** | `09638-XXXXXX` | [intercloud.com.bd](https://intercloud.com.bd) |
| **AmberIT IPTSP** | `09611-XXXXXX` | [amberit.com.bd](https://amberit.com.bd) |
| **BDCOM Online** | `09666-XXXXXX` | [bdcom.com.bd](https://bdcom.com.bd) |
| **Link3 Technologies** | `09678-XXXXXX` | [link3.net](https://link3.net) |
| **BTCL** | `02XXXXXXX` | [btcl.com.bd](https://btcl.com.bd) |

> Contact these providers with your trade license to get a corporate IPTSP SIP Trunk. They will provide you with:
> - **SIP Host** (e.g. `sip.brilliant.com.bd`)
> - **SIP Username**
> - **SIP Password / Secret**
> - **Your DID Number(s)**

### International Providers (Global Calling)

| Provider | Notes |
|---|---|
| **Twilio** | Instant signup, any country's number, pay-per-minute |
| **Telnyx** | Very affordable, developer-friendly |
| **VoIP.ms** | Great for small businesses |
| **Vonage** | Enterprise grade |

---

## 🗂️ Environment Variables

Key variables in your `.env` file:

```env
# Application
APP_NAME=PBX
APP_URL=https://pbx.yourdomain.com

# Database (MySQL or PostgreSQL)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pbx_db
DB_USERNAME=pbx_user
DB_PASSWORD=your_password

# PBX / Asterisk Connection
PBX_DRIVER=asterisk            # asterisk or freeswitch
PBX_DOMAIN=pbx.yourdomain.com  # Public IP or domain of your Asterisk server
PBX_WS_URL=wss://pbx.yourdomain.com/ws  # WebRTC WebSocket URL
PBX_ADMIN_EMAILS=admin@yourdomain.com   # Email(s) of admin users (comma-separated)

# Asterisk AMI Connection (for Live Calls, Originate, Spy)
ASTERISK_AMI_HOST=127.0.0.1
ASTERISK_AMI_PORT=5038
ASTERISK_AMI_USER=admin
ASTERISK_AMI_SECRET=your_ami_password
ASTERISK_RAWMAN_URL=https://pbx.yourdomain.com/rawman
```

> **Tip:** All PBX and AMI settings can also be changed directly from the admin panel under **PBX → PBX Server Settings** without manually editing `.env`.

---

## 🗃️ Project Structure

```
pbx/
├── app/
│   ├── Http/Controllers/
│   │   ├── Web/Admin/          # Admin panel controllers
│   │   │   ├── PbxExtensionController.php
│   │   │   ├── PbxIvrController.php
│   │   │   ├── PbxRingGroupController.php
│   │   │   ├── PbxTrunkController.php
│   │   │   ├── PbxInboundRouteController.php
│   │   │   ├── PbxOutboundRouteController.php
│   │   │   ├── PbxLiveCallsController.php
│   │   │   ├── PbxCallLogController.php
│   │   │   ├── PbxStatusController.php   ← PBX & AMI Settings
│   │   │   └── BusinessSettingsController.php
│   ├── Models/                 # Eloquent models
│   │   ├── PbxExtension.php
│   │   ├── PbxIvr.php
│   │   ├── PbxRingGroup.php
│   │   ├── PbxTrunk.php
│   │   ├── PbxInboundRoute.php
│   │   └── PbxCallLog.php
│   └── Services/
│       └── Pbx/               # Asterisk AMI & dialplan sync
├── resources/views/
│   ├── admin/                  # Admin panel Blade views
│   └── user/                   # Softphone / employee views
├── database/
│   ├── migrations/             # DB table schema
│   └── seeders/                # Demo data seeder
├── routes/web/
│   ├── admin.php               # All admin routes
│   └── user.php                # Employee softphone routes
└── docs/
    └── server-setup-and-handover-guide.md
```

---

## 🔐 Default Credentials

After running `php artisan migrate --seed`:

| Role | Email | Password |
|---|---|---|
| **Admin** | `admin@admin.com` | `password` |

> ⚠️ **Change these immediately** after first login via **Admin → Business Settings**.

---

## ❓ FAQ

### "Incoming calls don't reach my extension"
1. Make sure you have a **SIP Trunk** configured and registered (green status).
2. Create an **Inbound Route** with your DID number pointing to an IVR or Ring Group.
3. Click **"Sync to Asterisk"** after every change.
4. Ensure your Asterisk firewall allows UDP port 5060 (SIP) and UDP 10000–20000 (RTP audio).

### "WebRTC softphone shows 'Disconnected'"
1. Go to **Admin → PBX → PBX Server Settings** and verify the **WebRTC WebSocket URL**.
2. For production: URL must start with `wss://` (not `ws://`) when using HTTPS.
3. Verify Asterisk's `http.conf` has `enabled=yes` and the port is accessible.

### "Mobile data can't access the system"
- Use a **registered domain** (e.g. `pbx.yourdomain.com`) instead of wildcard services like `sslip.io` or `nip.io`.
- Wildcard IP-based domains are blocked by Bangladeshi mobile carriers (Grameenphone, Robi, Banglalink) via DPI filtering.
- A real domain bypasses this restriction entirely.

### "Call audio works on WiFi but not on mobile data"
- Mobile users are behind **Carrier-Grade NAT (CGNAT)**. You need a **TURN server** for WebRTC to work on mobile data.
- Install [Coturn](https://github.com/coturn/coturn) on your server and configure the TURN URL in the softphone settings.

### "I can't hear audio in call recordings"
- Asterisk needs write permission to `/var/spool/asterisk/monitor/`.
- Run: `sudo chown -R asterisk:asterisk /var/spool/asterisk/monitor/`

---

## 📄 License

This project is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
