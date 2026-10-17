<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name', 'App'))</title>
    @php
        $faviconIco = public_path('branding/favicon.ico');
        $faviconPng = public_path('branding/favicon.png');
    @endphp
    @if (file_exists($faviconIco))
        <link rel="icon" href="{{ asset('branding/favicon.ico') }}">
    @elseif (file_exists($faviconPng))
        <link rel="icon" href="{{ asset('branding/favicon.png') }}">
    @endif
    <link rel="stylesheet" href="{{ asset('assets/site/site.css') }}">
</head>
<body class="@yield('body-class', 'site-body')">
    @yield('content')
    @stack('scripts')
</body>
</html>

