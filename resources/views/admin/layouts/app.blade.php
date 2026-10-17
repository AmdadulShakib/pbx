<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <base href="./">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>@yield('title', config('app.name', 'Admin'))</title>
    @php
        $faviconIco = public_path('branding/favicon.ico');
        $faviconPng = public_path('branding/favicon.png');
    @endphp
    @if (file_exists($faviconIco))
        <link rel="icon" href="{{ asset('branding/favicon.ico') }}">
    @elseif (file_exists($faviconPng))
        <link rel="icon" href="{{ asset('branding/favicon.png') }}">
    @endif

    <link href="{{ asset('assets/admin/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/admin/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/select2/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/toast/toastr.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/admin/admin-ui.css') }}" rel="stylesheet">

    @stack('css')
    @stack('css_or_js')
</head>
<body class="admin-body">
    <div class="admin-layout">
        @include('admin.partials.sidebar')
        <div class="sidebar-overlay" data-sidebar-overlay></div>

        <div class="admin-main">
            @include('admin.partials.header')

            <main class="admin-content">
                <div class="container-fluid py-3 py-lg-4">
                    @include('admin.partials.page_header')
                    @yield('content')
                </div>
            </main>

            @include('admin.partials.footer')
        </div>
    </div>

    @include('admin.partials.toast-messages')

    <script src="{{ asset('assets/admin/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/admin/vendors/@coreui/coreui-pro/js/coreui.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/admin/vendors/simplebar/js/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/main.js') }}"></script>
    <script src="{{ asset('assets/admin/icons/lucide.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="{{ asset('assets/vendor/select2/select2.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/toast/toastr.min.js') }}"></script>
    <script src="{{ asset('assets/admin/admin-ui.js') }}"></script>

    @stack('js')
</body>
</html>

