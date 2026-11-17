<header class="admin-topbar">
    <div class="container-fluid d-flex align-items-center justify-content-between gap-3">
        <!-- Left Side: Sidebar Toggle & Telephony Core Status -->
        <div class="d-flex align-items-center gap-2.5">
            <button class="btn btn-icon btn-light" type="button" data-sidebar-toggle title="{{ __('Toggle Sidebar') }}">
                <i data-lucide="panel-left-close" data-sidebar-icon style="width: 18px; height: 18px;"></i>
            </button>

            <!-- Interactive Asterisk Engine Live Status Link -->
            <a href="{{ route('admin.pbx.status') }}" class="text-decoration-none badge bg-success-subtle text-success border border-success-subtle d-none d-sm-inline-flex align-items-center gap-1.5 px-2.5 py-1" title="{{ __('Inspect Asterisk Engine Status & Channels') }}">
                <span class="badge-dot-live"></span>
                <span class="fw-semibold">Asterisk 20.6 PJSIP • Operational</span>
            </a>
        </div>

        <!-- Right Side: Live Calls Pill, Softphone Launch, Theme & Profile -->
        <div class="d-flex align-items-center gap-2">
            <!-- Quick Live Calls Pill (Jumps directly to active calls) -->
            <a href="{{ route('admin.pbx.live-calls') }}" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill" title="{{ __('Monitor Ongoing Live Calls') }}">
                <span class="badge-dot-live" style="background-color: var(--bs-danger, #dc2626); box-shadow: 0 0 6px rgba(220, 38, 38, 0.6);"></span>
                <span class="fw-bold d-none d-md-inline small">{{ __('Live Calls') }}</span>
            </a>

            <!-- Quick Launch PBX Softphone -->
            <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1.5 px-3 py-1 rounded-pill" onclick="if (typeof togglePbxDialer === 'function') togglePbxDialer();" title="{{ __('Launch PBX Softphone Dialer') }}">
                <i data-lucide="phone-call" style="width: 14px; height: 14px;"></i>
                <span class="fw-semibold d-none d-md-inline small">{{ __('PBX Softphone') }}</span>
                <span class="badge bg-success rounded-circle p-1"></span>
            </button>

            <!-- Theme Toggle -->
            @if(config('admin_ui.enable_dark_mode', true))
                <button class="btn btn-icon btn-light" type="button" data-theme-toggle data-bs-toggle="tooltip" title="{{ __('Toggle Dark / Light Theme') }}">
                    <i data-lucide="moon" data-theme-icon style="width: 17px; height: 17px;"></i>
                </button>
            @endif

            <!-- PBX Settings Quick Link -->
            <a href="{{ route('admin.pbx.settings') }}" class="btn btn-icon btn-light" data-bs-toggle="tooltip" title="{{ __('PBX Gateway Settings') }}">
                <i data-lucide="sliders" style="width: 17px; height: 17px;"></i>
            </a>

            <!-- User Profile Dropdown -->
            <div class="dropdown">
                <button class="btn btn-light d-flex align-items-center gap-2 py-1 px-2 rounded-2 border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center" style="width: 26px; height: 26px; font-size: 11px;">
                        {{ strtoupper(substr(auth('admin')->user()->name ?? 'AD', 0, 2)) }}
                    </div>
                    <span class="small fw-semibold d-none d-lg-inline text-dark">{{ auth('admin')->user()->name ?? 'Admin' }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2" style="min-width: 220px;">
                    <li class="px-3 py-1.5">
                        <div class="fw-bold text-dark small">{{ auth('admin')->user()->name ?? 'Administrator' }}</div>
                        <div class="text-muted" style="font-size: 11px;">{{ auth('admin')->user()->email ?? 'admin@voxpulse.com' }}</div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle mt-1 font-monospace" style="font-size: 10px;">{{ __('Super Admin') }}</span>
                    </li>
                    <li><hr class="dropdown-divider my-1.5"></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-1.5 small" href="{{ route('admin.pbx.status') }}">
                            <i data-lucide="server" style="width: 15px; height: 15px;"></i>
                            <span>{{ __('PBX Live Telemetry') }}</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-1.5 small" href="{{ route('admin.pbx.settings') }}">
                            <i data-lucide="sliders" style="width: 15px; height: 15px;"></i>
                            <span>{{ __('PBX Gateway Settings') }}</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-1.5 small" href="{{ route('admin.business-settings.general') }}">
                            <i data-lucide="settings" style="width: 15px; height: 15px;"></i>
                            <span>{{ __('System Settings') }}</span>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1.5"></li>
                    <li>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item d-flex align-items-center gap-2 py-1.5 text-danger small">
                                <i data-lucide="log-out" style="width: 15px; height: 15px;"></i>
                                <span class="fw-semibold">{{ __('Logout Session') }}</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
