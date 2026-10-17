<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.business-settings.general') ? 'active' : '' }}" href="{{ route('admin.business-settings.general') }}">
            @lang('General')
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.business-settings.branding') ? 'active' : '' }}" href="{{ route('admin.business-settings.branding') }}">
            @lang('Branding')
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.business-settings.advanced') ? 'active' : '' }}" href="{{ route('admin.business-settings.advanced') }}">
            @lang('Advanced')
        </a>
    </li>
</ul>
