<header class="admin-topbar">
    <div class="container-fluid d-flex align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-icon btn-light" type="button" data-sidebar-toggle>
                <i data-lucide="panel-left-close" data-sidebar-icon></i>
            </button>
            <span class="badge bg-success-subtle text-success border border-success-subtle d-none d-sm-inline-flex align-items-center gap-1.5 px-2.5 py-1">
                <span style="width: 6px; height: 6px; border-radius: 50%; background-color: var(--bs-success, #16a34a);"></span>
                <span>Asterisk 20.6 PJSIP • Operational</span>
            </span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <!-- Quick Launch PBX Softphone -->
            <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1.5 px-3 py-1.5 rounded-pill" onclick="if (typeof togglePbxDialer === 'function') togglePbxDialer();" title="Launch PBX Softphone Dialer">
                <i data-lucide="phone-call" style="width: 14px; height: 14px;"></i>
                <span class="fw-semibold d-none d-md-inline">PBX Softphone</span>
                <span class="badge bg-success rounded-circle p-1"></span>
            </button>

            @if(config('admin_ui.enable_dark_mode', true))
                <button class="btn btn-icon btn-light" type="button" data-theme-toggle data-bs-toggle="tooltip" title="@lang('Toggle theme')">
                    <i data-lucide="moon" data-theme-icon></i>
                </button>
            @endif
            <a href="{{ route('admin.business-settings.general') }}" class="btn btn-light d-inline-flex align-items-center gap-2" data-bs-toggle="tooltip" title="@lang('Business Settings')">
                <i data-lucide="settings"></i>
                <span class="d-none d-lg-inline">@lang('Settings')</span>
            </a>
            <div class="dropdown">
                <button class="btn btn-icon btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i data-lucide="user"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><span class="dropdown-item-text text-muted">@lang('Signed in as Admin')</span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('admin.pbx.status') }}">@lang('PBX Live Telemetry')</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.pbx.settings') }}">@lang('PBX Gateway Settings')</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="post" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger">
                                <i data-lucide="log-out"></i>
                                <span>@lang('Logout')</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
