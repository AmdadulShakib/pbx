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

    // Asterisk AMI Management
    'ami_host'     => env('ASTERISK_AMI_HOST', '10.0.1.1'),
    'ami_port'     => (int) env('ASTERISK_AMI_PORT', 5038),
    'ami_user'     => env('ASTERISK_AMI_USER', 'admin'),
    'ami_secret'   => env('ASTERISK_AMI_SECRET', 'voxpulse2026'),
    'rawman_url'   => env('ASTERISK_RAWMAN_URL', 'https://pbx.103.107.160.37.sslip.io/rawman'),
];
