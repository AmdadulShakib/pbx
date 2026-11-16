<?php

return [
    [
        'type' => 'route',
        'label' => 'Dashboard',
        'icon' => 'home',
        'route' => 'admin.dashboard',
    ],
    [
        'type' => 'group',
        'label' => 'PBX Telecom System',
        'icon' => 'phone',
        'items' => [
            [
                'type' => 'route',
                'label' => 'Extensions',
                'icon' => 'phone-call',
                'route' => 'admin.pbx.extensions.index',
            ],
            [
                'type' => 'route',
                'label' => 'Live Active Calls',
                'icon' => 'radio',
                'route' => 'admin.pbx.live-calls',
            ],
            [
                'type' => 'route',
                'label' => 'IVR Receptionist',
                'icon' => 'layers',
                'route' => 'admin.pbx.ivr.index',
            ],
            [
                'type' => 'route',
                'label' => 'Call History (CDR)',
                'icon' => 'history',
                'route' => 'admin.pbx.calls.index',
            ],
            [
                'type' => 'route',
                'label' => 'PBX Live Status',
                'icon' => 'activity',
                'route' => 'admin.pbx.status',
            ],
            [
                'type' => 'route',
                'label' => 'PBX Settings',
                'icon' => 'settings',
                'route' => 'admin.pbx.settings',
            ],
        ],
    ],
    [
        'type' => 'group',
        'label' => 'Access Control',
        'icon' => 'lock',
        'items' => [
            [
                'type' => 'route',
                'label' => 'Staff',
                'icon' => 'shield',
                'route' => 'admin.staff.index',
            ],
            [
                'type' => 'route',
                'label' => 'Roles',
                'icon' => 'key',
                'route' => 'admin.roles.index',
            ],
        ],
    ],
];
