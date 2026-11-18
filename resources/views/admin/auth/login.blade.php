<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@lang('Admin Portal Login') — VoxPulse PBX</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('branding/favicon.svg') }}">
    <link href="{{ asset('assets/admin/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/admin/admin-ui.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/toast/toastr.min.css') }}" rel="stylesheet">
    <style>
        :root {
            --login-primary: #2563eb;
            --login-primary-hover: #1d4ed8;
            --login-bg: #0b0f19;
            --login-card-bg: #ffffff;
        }

        body.admin-login-modern {
            min-height: 100vh;
            background-color: var(--login-bg);
            background-image: 
                radial-gradient(circle at 15% 20%, rgba(37, 99, 235, 0.15) 0%, transparent 45%),
                radial-gradient(circle at 85% 80%, rgba(14, 165, 233, 0.12) 0%, transparent 45%),
                radial-gradient(circle at 50% 50%, rgba(99, 102, 241, 0.08) 0%, transparent 55%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            position: relative;
            overflow-x: hidden;
        }

        /* Subtle grid background overlay */
        body.admin-login-modern::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 32px 32px;
            pointer-events: none;
            z-index: 0;
        }

        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 440px;
        }

        .login-card-modern {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .login-header {
            padding: 2.25rem 2rem 1.5rem 2rem;
            text-align: center;
            border-bottom: 1px solid #f1f5f9;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .login-body {
            padding: 2rem;
        }

        .form-floating > .form-control {
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            padding-left: 2.75rem;
            height: calc(3.5rem + 2px);
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .form-floating > .form-control:focus {
            border-color: var(--login-primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .form-floating > label {
            padding-left: 2.75rem;
            font-size: 0.875rem;
            color: #64748b;
        }

        .input-icon-box {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
            z-index: 4;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-control:focus ~ .input-icon-box {
            color: var(--login-primary);
        }

        .password-toggle-btn {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #94a3b8;
            padding: 0.5rem;
            border-radius: 8px;
            cursor: pointer;
            z-index: 5;
            transition: color 0.15s ease;
        }

        .password-toggle-btn:hover {
            color: #334155;
        }

        .btn-login-submit {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: none;
            border-radius: 12px;
            padding: 0.875rem 1.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.28);
            transition: all 0.2s ease;
        }

        .btn-login-submit:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.35);
            transform: translateY(-1px);
            color: #ffffff;
        }

        .btn-login-submit:active {
            transform: translateY(0);
        }

        .badge-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .badge-status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
            animation: pulse-dot 2s infinite;
        }

        @keyframes pulse-dot {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5); }
            70% { box-shadow: 0 0 0 5px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .demo-chip {
            background: #f1f5f9;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 8px 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 12px;
        }

        .demo-chip:hover {
            background: #e2e8f0;
            border-color: #94a3b8;
        }

        .security-badge {
            color: rgba(255, 255, 255, 0.55);
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-top: 1.5rem;
        }
    </style>
</head>
<body class="admin-login-modern">
    <div class="login-wrapper">
        <!-- Main Card -->
        <div class="login-card-modern">
            <!-- Header with Logo and Status -->
            <div class="login-header">
                <a href="{{ url('/') }}" class="d-inline-block mb-3">
                    <img src="{{ asset('branding/logo.svg') }}" alt="VoxPulse PBX" height="40" style="max-width: 100%;">
                </a>
                <div>
                    <span class="badge-status-pill mb-2">
                        <span class="badge-status-dot"></span>
                        Asterisk 20.6 PJSIP • Operational
                    </span>
                </div>
                <h1 class="h5 mb-1 fw-bold text-dark">@lang('Administrator Portal')</h1>
                <p class="text-muted mb-0 small">@lang('Sign in to manage telephony routing, extensions, and live calls')</p>
            </div>

            <!-- Login Form -->
            <div class="login-body">
                <!-- Inline Flash Messages -->
                @if(session('error'))
                    <div class="alert alert-danger d-flex align-items-center gap-2 py-2 px-3 mb-3 rounded-3 small">
                        <i data-lucide="alert-circle" style="width: 16px; height: 16px; flex-shrink: 0;"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success d-flex align-items-center gap-2 py-2 px-3 mb-3 rounded-3 small">
                        <i data-lucide="check-circle" style="width: 16px; height: 16px; flex-shrink: 0;"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger py-2 px-3 mb-3 rounded-3 small">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="post" action="{{ route('admin.login') }}" id="adminLoginForm">
                    @csrf

                    <!-- Email Input -->
                    <div class="form-floating mb-3 position-relative">
                        <input type="email" 
                               name="email" 
                               class="form-control" 
                               id="admin-email" 
                               placeholder="admin@voxpulse.com" 
                               value="{{ old('email', 'admin@admin.com') }}" 
                               required 
                               autofocus>
                        <label for="admin-email">@lang('Admin Email')</label>
                        <div class="input-icon-box">
                            <i data-lucide="mail" style="width: 17px; height: 17px;"></i>
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="form-floating mb-3 position-relative">
                        <input type="password" 
                               name="password" 
                               class="form-control" 
                               id="admin-password" 
                               placeholder="••••••••" 
                               required>
                        <label for="admin-password">@lang('Password')</label>
                        <div class="input-icon-box">
                            <i data-lucide="lock" style="width: 17px; height: 17px;"></i>
                        </div>
                        <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility()" aria-label="@lang('Toggle password')">
                            <i data-lucide="eye" id="passwordEyeIcon" style="width: 16px; height: 16px;"></i>
                        </button>
                    </div>

                    <!-- Remember & Quick Helper -->
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
                            <label class="form-check-label small text-muted" for="remember">
                                @lang('Keep me signed in')
                            </label>
                        </div>
                        <span class="badge bg-light text-secondary border small py-1 px-2 font-monospace" style="font-size: 11px;">
                            {{ request()->getHost() }}
                        </span>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-login-submit w-100 d-flex align-items-center justify-content-center gap-2">
                        <span>@lang('Sign In to Console')</span>
                        <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
                    </button>
                </form>

                <!-- Quick Autofill Helper -->
                <div class="demo-chip mt-4 d-flex align-items-center justify-content-between" onclick="fillAdminCredentials()">
                    <div class="d-flex align-items-center gap-2">
                        <i data-lucide="key" class="text-primary" style="width: 15px; height: 15px;"></i>
                        <div>
                            <div class="fw-semibold text-dark">Default Super Admin</div>
                            <div class="text-muted font-monospace" style="font-size: 11px;">admin@admin.com</div>
                        </div>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-1 px-2" style="font-size: 10px;">
                        Click to Fill
                    </span>
                </div>
            </div>
        </div>

        <!-- Footer Security Badges -->
        <div class="security-badge text-center">
            <span class="d-inline-flex align-items-center gap-1">
                <i data-lucide="shield-check" style="width: 14px; height: 14px; color: #10b981;"></i>
                256-bit TLS Encrypted
            </span>
            <span>•</span>
            <span class="d-inline-flex align-items-center gap-1">
                <i data-lucide="cpu" style="width: 14px; height: 14px; color: #38bdf8;"></i>
                Asterisk PJSIP
            </span>
            <span>•</span>
            <span class="d-inline-flex align-items-center gap-1">
                <i data-lucide="phone-call" style="width: 14px; height: 14px; color: #a855f7;"></i>
                WebRTC HD
            </span>
        </div>
    </div>

    @include('admin.partials.toast-messages')

    <script src="{{ asset('assets/admin/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/toast/toastr.min.js') }}"></script>
    <script src="{{ asset('assets/admin/icons/lucide.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });

        function togglePasswordVisibility() {
            const passInput = document.getElementById('admin-password');
            const eyeIcon = document.getElementById('passwordEyeIcon');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                eyeIcon.setAttribute('data-lucide', 'eye-off');
            } else {
                passInput.type = 'password';
                eyeIcon.setAttribute('data-lucide', 'eye');
            }
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }

        function fillAdminCredentials() {
            document.getElementById('admin-email').value = 'admin@admin.com';
            const passInput = document.getElementById('admin-password');
            if (!passInput.value) {
                passInput.focus();
            }
        }
    </script>
</body>
</html>
