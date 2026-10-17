<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" data-theme="light">
<head>
    <base href="./">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo $__env->yieldContent('title', config('app.name', 'Admin')); ?></title>
    <?php
        $faviconIco = public_path('branding/favicon.ico');
        $faviconPng = public_path('branding/favicon.png');
    ?>
    <?php if(file_exists($faviconIco)): ?>
        <link rel="icon" href="<?php echo e(asset('branding/favicon.ico')); ?>">
    <?php elseif(file_exists($faviconPng)): ?>
        <link rel="icon" href="<?php echo e(asset('branding/favicon.png')); ?>">
    <?php endif; ?>

    <link href="<?php echo e(asset('assets/admin/css/bootstrap.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('assets/admin/css/style.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('assets/vendor/select2/select2.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('assets/vendor/toast/toastr.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('assets/admin/admin-ui.css')); ?>" rel="stylesheet">

    <?php echo $__env->yieldPushContent('css'); ?>
    <?php echo $__env->yieldPushContent('css_or_js'); ?>
</head>
<body class="admin-body">
    <div class="admin-layout">
        <?php echo $__env->make('admin.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="sidebar-overlay" data-sidebar-overlay></div>

        <div class="admin-main">
            <?php echo $__env->make('admin.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <main class="admin-content">
                <div class="container-fluid py-3 py-lg-4">
                    <?php echo $__env->make('admin.partials.page_header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php echo $__env->yieldContent('content'); ?>
                </div>
            </main>

            <?php echo $__env->make('admin.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>

    <?php echo $__env->make('admin.partials.toast-messages', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <script src="<?php echo e(asset('assets/admin/js/jquery.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/admin/js/bootstrap.bundle.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/admin/vendors/@coreui/coreui-pro/js/coreui.bundle.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/admin/vendors/simplebar/js/simplebar.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/admin/js/main.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/admin/icons/lucide.min.js')); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="<?php echo e(asset('assets/vendor/select2/select2.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/vendor/toast/toastr.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/admin/admin-ui.js')); ?>"></script>

    <?php echo $__env->yieldPushContent('js'); ?>
</body>
</html>

<?php /**PATH C:\xampp\htdocs\AdminPanelBuilder\resources\views/admin/layouts/app.blade.php ENDPATH**/ ?>