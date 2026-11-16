<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'App'))</title>
    @php
        $faviconIco = public_path('branding/favicon.ico');
        $faviconPng = public_path('branding/favicon.png');
    @endphp
    <link rel="icon" type="image/svg+xml" href="{{ asset('branding/favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('assets/site/site.css') }}?v={{ @filemtime(public_path('assets/site/site.css')) ?: time() }}&t={{ time() }}">
</head>
<body class="@yield('body-class', 'site-body')">
    @yield('content')
    @stack('scripts')

    @include('components.pbx-phone-widget')
</body>
</html>

