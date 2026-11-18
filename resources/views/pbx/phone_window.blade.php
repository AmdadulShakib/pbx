<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('VoxPulse PBX Softphone') }} - Ext {{ $ext->extension ?? '' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('assets/site/css/pbx-dialer.css') }}?v={{ @filemtime(public_path('assets/site/css/pbx-dialer.css')) ?: time() }}">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: #0f172a;
            font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
            user-select: none;
        }
        #pbx-floating-container {
            position: fixed;
            inset: 0;
            width: 100vw;
            height: 100vh;
            margin: 0;
            padding: 0;
            z-index: 9999;
        }
        #pbx-fab-btn {
            display: none !important;
        }
        #pbx-dialer-card {
            display: flex !important;
            opacity: 1 !important;
            visibility: visible !important;
            transform: none !important;
            position: fixed !important;
            inset: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            max-width: 100% !important;
            max-height: 100% !important;
            border-radius: 0 !important;
            border: none !important;
            box-shadow: none !important;
        }
        .popout-hide-in-window {
            display: none !important;
        }
    </style>
</head>
<body>
    @include('components.pbx-phone-widget')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        if (window.lucide) lucide.createIcons();
    </script>
</body>
</html>
