<div class="page-header">
    <div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <h1 class="page-title"><?php echo $__env->yieldContent('page-title'); ?></h1>
            <?php if (! empty(trim($__env->yieldContent('page-breadcrumb')))): ?>
                <nav class="breadcrumb-wrap"><?php echo $__env->yieldContent('page-breadcrumb'); ?></nav>
            <?php endif; ?>
        </div>
        <?php if (! empty(trim($__env->yieldContent('page-subtitle')))): ?>
            <div class="text-muted small"><?php echo $__env->yieldContent('page-subtitle'); ?></div>
        <?php endif; ?>
    </div>
    <div class="d-flex align-items-center gap-2">
        <?php echo $__env->yieldPushContent('filters'); ?>
        <?php echo $__env->yieldPushContent('top-area'); ?>
        <?php echo $__env->yieldPushContent('top-area-right'); ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\AdminPanelBuilder\resources\views/admin/components/page_header.blade.php ENDPATH**/ ?>