<?php

return [
    'driver'       => env('PBX_DRIVER', 'asterisk'), // 'asterisk' or 'freeswitch'
    'host'         => env('PBX_ESL_HOST', '127.0.0.1'),
    'port'         => (int) env('PBX_ESL_PORT', 8021),
    'password'     => env('PBX_ESL_PASSWORD', 'ClueCon'),
    'domain'       => env('PBX_DOMAIN', 'pbx.103.107.160.37.sslip.io'),
    'ws_url'       => env('PBX_WS_URL', 'wss://pbx.103.107.160.37.sslip.io/ws'),
    'conf_path'    => storage_path('freeswitch'),
    'admin_emails' => env('PBX_ADMIN_EMAILS', ''),
];
