<?php

return [
    'driver'       => env('PBX_DRIVER', 'asterisk'), // 'asterisk' or 'freeswitch'
    'host'         => env('PBX_ESL_HOST', '127.0.0.1'),
    'port'         => (int) env('PBX_ESL_PORT', 8021),
    'password'     => env('PBX_ESL_PASSWORD', 'ClueCon'),
    'domain'       => env('PBX_DOMAIN', '10.211.10.238'),
    'ws_url'       => env('PBX_WS_URL', 'ws://10.211.10.238:8088/ws'),
    'conf_path'    => storage_path('freeswitch'),
    'admin_emails' => env('PBX_ADMIN_EMAILS', ''),
];
