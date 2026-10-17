<?php $__env->startSection('title', __('Dashboard')); ?>
<?php $__env->startSection('page-title', __('Dashboard')); ?>
<?php $__env->startSection('content'); ?>
    <div class="row g-3">
        <?php $__empty_1 = true; $__currentLoopData = $widgets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $widget): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php if ($__env->exists('admin.dashboard.widgets.' . ($widget['type'] ?? ''), ['widget' => $widget])) echo $__env->make('admin.dashboard.widgets.' . ($widget['type'] ?? ''), ['widget' => $widget], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-12">
                <?php echo $__env->make('admin.components.empty_state', [
                    'message' => __('No dashboard widgets'),
                    'helper' => __('Add widgets in schema config to populate the dashboard.'),
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\AdminPanelBuilder\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>