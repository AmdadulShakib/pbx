<aside class="admin-sidebar" id="sidebar">
    <!-- Brand Header (Permanently Pinned at Top) -->
    <div class="sidebar-brand border-bottom px-3 py-3 d-flex align-items-center justify-content-between">
        <a href="{{ route('admin.dashboard') }}" class="brand d-flex align-items-center gap-2 text-decoration-none">
            <img src="{{ asset('branding/logo-white.svg') }}" alt="VoxPulse PBX" height="30" class="d-inline-block brand-logo">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-1.5 py-0.5 ms-1" style="font-size: 10px;">v2.4</span>
        </a>
        <button class="btn btn-icon btn-light d-lg-none" type="button" onclick="document.querySelector('#sidebar').classList.toggle('show')">
            <i data-lucide="x" style="width: 18px; height: 18px;"></i>
        </button>
    </div>

    <!-- Navigation Menu Body (Independent Scrollable Container) -->
    <div class="sidebar-menu px-3 py-2">
        <!-- Real-Time Menu Search Filter -->
        <div class="sidebar-search my-2">
            <i data-lucide="search" style="width: 14px; height: 14px;"></i>
            <input type="text" class="form-control form-control-sm" placeholder="{{ __('Quick find menu...') }}" data-sidebar-search autocomplete="off">
        </div>

        @php
            $currentRoute = request()->route() ? request()->route()->getName() : '';
            $menuSections = [
                [
                    'heading' => __('OVERVIEW'),
                    'items' => [
                        [
                            'label'    => __('Dashboard'),
                            'icon'     => 'layout-dashboard',
                            'route'    => 'admin.dashboard',
                            'patterns' => ['admin.dashboard'],
                        ],
                        [
                            'label'       => __('Live Active Calls'),
                            'icon'        => 'radio',
                            'route'       => 'admin.pbx.live-calls',
                            'patterns'    => ['admin.pbx.live-calls*'],
                            'badge'       => 'LIVE',
                            'badge_class' => 'bg-danger-subtle text-danger border border-danger-subtle',
                        ],
                    ],
                ],
                [
                    'heading' => __('TELEPHONY & PBX'),
                    'items' => [
                        [
                            'label'    => __('Extensions'),
                            'icon'     => 'phone-call',
                            'route'    => 'admin.pbx.extensions.index',
                            'patterns' => ['admin.pbx.extensions.*'],
                        ],
                        [
                            'label'    => __('Ring Groups'),
                            'icon'     => 'users-2',
                            'route'    => 'admin.pbx.ring-groups.index',
                            'patterns' => ['admin.pbx.ring-groups.*'],
                        ],
                        [
                            'label'    => __('IVR Receptionist'),
                            'icon'     => 'layers',
                            'route'    => 'admin.pbx.ivr.index',
                            'patterns' => ['admin.pbx.ivr.*'],
                        ],
                    ],
                ],
                [
                    'heading' => __('CARRIER & ROUTING'),
                    'items' => [
                        [
                            'label'    => __('Inbound Routes'),
                            'icon'     => 'phone-incoming',
                            'route'    => 'admin.pbx.inbound-routes.index',
                            'patterns' => ['admin.pbx.inbound-routes.*'],
                        ],
                        [
                            'label'    => __('Outbound Routes'),
                            'icon'     => 'phone-outgoing',
                            'route'    => 'admin.pbx.outbound-routes.index',
                            'patterns' => ['admin.pbx.outbound-routes.*'],
                        ],
                        [
                            'label'    => __('SIP Trunks'),
                            'icon'     => 'network',
                            'route'    => 'admin.pbx.trunks.index',
                            'patterns' => ['admin.pbx.trunks.*'],
                        ],
                    ],
                ],
                [
                    'heading' => __('INTELLIGENCE & CONTROL'),
                    'items' => [
                        [
                            'label'    => __('Call History (CDR)'),
                            'icon'     => 'history',
                            'route'    => 'admin.pbx.calls.index',
                            'patterns' => ['admin.pbx.calls.*'],
                        ],
                        [
                            'label'    => __('PBX Live Status'),
                            'icon'     => 'server',
                            'route'    => 'admin.pbx.status',
                            'patterns' => ['admin.pbx.status'],
                        ],
                        [
                            'label'    => __('PBX Settings'),
                            'icon'     => 'sliders',
                            'route'    => 'admin.pbx.settings',
                            'patterns' => ['admin.pbx.settings*'],
                        ],
                    ],
                ],
                [
                    'heading' => __('ADMINISTRATION'),
                    'items' => [
                        [
                            'label'    => __('Staff Members'),
                            'icon'     => 'shield',
                            'route'    => 'admin.staff.index',
                            'patterns' => ['admin.staff.*'],
                        ],
                        [
                            'label'    => __('Roles & Permissions'),
                            'icon'     => 'key',
                            'route'    => 'admin.roles.index',
                            'patterns' => ['admin.roles.*'],
                        ],
                        [
                            'label'    => __('Business Settings'),
                            'icon'     => 'settings',
                            'route'    => 'admin.business-settings.general',
                            'patterns' => ['admin.business-settings.*'],
                        ],
                    ],
                ],
            ];
        @endphp

        <ul class="nav flex-column gap-0.5 pb-4" data-sidebar-menu>
            @foreach($menuSections as $section)
                <li class="sidebar-section-heading px-2 pt-3 pb-1 text-uppercase small fw-bold" style="font-size: 11px; letter-spacing: 0.08em; color: #64748b;">
                    {{ $section['heading'] }}
                </li>

                @foreach($section['items'] as $item)
                    @php
                        $isActive = false;
                        if (!empty($item['patterns'])) {
                            $isActive = nav_is_active($item['patterns']);
                        } elseif (!empty($item['route'])) {
                            $isActive = ($currentRoute === $item['route']);
                        }
                    @endphp
                    <li class="nav-item my-0.5" data-sidebar-item data-sidebar-label="{{ $item['label'] }}">
                        <a class="nav-link d-flex align-items-center gap-2.5 px-2.5 py-1.5 rounded-2 {{ $isActive ? 'active' : '' }}" href="{{ route($item['route']) }}">
                            <i data-lucide="{{ $item['icon'] }}" style="width: 17px; height: 17px; flex-shrink: 0;"></i>
                            <span class="fw-medium" style="font-size: 13.5px;">{{ $item['label'] }}</span>

                            @if(!empty($item['badge']))
                                <span class="badge ms-auto px-1.5 py-0.5 rounded-pill font-monospace fw-bold {{ $item['badge_class'] ?? 'bg-primary' }}" style="font-size: 9px; letter-spacing: 0.5px;">
                                    {{ $item['badge'] }}
                                </span>
                            @endif
                        </a>
                    </li>
                @endforeach
            @endforeach
        </ul>
    </div>

    <!-- Bottom Operator Deck -->
    <div class="sidebar-footer border-top p-3 mt-auto d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2 overflow-hidden">
            <div class="rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; font-size: 12px;">
                {{ strtoupper(substr(auth('admin')->user()->name ?? 'AD', 0, 2)) }}
            </div>
            <div class="overflow-hidden">
                <div class="small fw-bold text-truncate text-light" style="max-width: 140px;">{{ auth('admin')->user()->name ?? 'Administrator' }}</div>
                <div class="text-muted text-truncate" style="font-size: 10.5px;">{{ __('Telecom Supervisor') }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.logout') }}" class="m-0">
            @csrf
            <button type="submit" class="btn btn-icon btn-sm btn-outline-secondary p-1 text-muted" title="{{ __('Logout') }}">
                <i data-lucide="log-out" style="width: 15px; height: 15px;"></i>
            </button>
        </form>
    </div>
</aside>
