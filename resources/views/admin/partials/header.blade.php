<header class="admin-topbar">
    <div class="container-fluid d-flex align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-icon btn-light" type="button" data-sidebar-toggle>
                <i data-lucide="panel-left-close" data-sidebar-icon></i>
            </button>
            <span class="text-muted small d-none d-md-inline">@lang('Admin Panel')</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(config('admin_ui.enable_dark_mode', true))
                <button class="btn btn-icon btn-light" type="button" data-theme-toggle data-bs-toggle="tooltip" title="@lang('Toggle theme')">
                    <i data-lucide="moon" data-theme-icon></i>
                </button>
            @endif
            <a href="{{ route('admin.business-settings.general') }}" class="btn btn-light d-inline-flex align-items-center gap-2" data-bs-toggle="tooltip" title="@lang('Business Settings')">
                <i data-lucide="settings"></i>
                <span class="d-none d-md-inline">@lang('Business Settings')</span>
            </a>
            <button class="btn btn-icon btn-light position-relative" type="button" data-bs-toggle="tooltip" title="@lang('Notifications')">
                <i data-lucide="bell"></i>
                <span class="badge bg-danger position-absolute top-0 start-100 translate-middle p-1 rounded-circle"></span>
            </button>
            <div class="dropdown">
                <button class="btn btn-icon btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i data-lucide="user"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><span class="dropdown-item-text text-muted">@lang('Signed in as Admin')</span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#">@lang('Profile')</a></li>
                    <li><a class="dropdown-item" href="#">@lang('Settings')</a></li>
                    <li>
                        <form method="post" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item d-flex align-items-center gap-2">
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
