<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', config('app.name', 'App')); ?></title>
    <?php
        $faviconIco = public_path('branding/favicon.ico');
        $faviconPng = public_path('branding/favicon.png');
    ?>
    <?php if(file_exists($faviconIco)): ?>
        <link rel="icon" href="<?php echo e(asset('branding/favicon.ico')); ?>">
    <?php elseif(file_exists($faviconPng)): ?>
        <link rel="icon" href="<?php echo e(asset('branding/favicon.png')); ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?php echo e(asset('assets/site/site.css')); ?>">
</head>
<body class="<?php echo $__env->yieldContent('body-class', 'site-body'); ?>">
    <?php echo $__env->yieldContent('content'); ?>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>

<?php /**PATH C:\xampp\htdocs\AdminPanelBuilder\resources\views/layouts/site.blade.php ENDPATH**/ ?>