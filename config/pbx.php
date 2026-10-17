<?php

return [
    'host'         => env('PBX_ESL_HOST', '127.0.0.1'),
    'port'         => (int) env('PBX_ESL_PORT', 8021),
    'password'     => env('PBX_ESL_PASSWORD', 'ClueCon'),
    'domain'       => env('PBX_DOMAIN'),
    'ws_url'       => env('PBX_WS_URL'),
    'conf_path'    => storage_path('freeswitch'),
    'admin_emails' => env('PBX_ADMIN_EMAILS', ''),
];