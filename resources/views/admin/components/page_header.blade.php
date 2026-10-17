<div class="page-header">
    <div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <h1 class="page-title">@yield('page-title')</h1>
            @hasSection('page-breadcrumb')
                <nav class="breadcrumb-wrap">@yield('page-breadcrumb')</nav>
            @endif
        </div>
        @hasSection('page-subtitle')
            <div class="text-muted small">@yield('page-subtitle')</div>
        @endif
    </div>
    <div class="d-flex align-items-center gap-2">
        @stack('filters')
        @stack('top-area')
        @stack('top-area-right')
    </div>
</div>
