<?php
    $items = $widget['items'] ?? [];
?>

<?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card h-100">
            <div class="card-body position-relative">
                <div class="text-muted small"><?php echo e($item['label'] ?? __('Module')); ?></div>
                <div class="h3 mb-0"><?php echo e(number_format((int) ($item['count'] ?? 0))); ?></div>
                <?php if(!empty($item['route'])): ?>
                    <a href="<?php echo e(route($item['route'])); ?>" class="stretched-link"></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH C:\xampp\htdocs\AdminPanelBuilder\resources\views/admin/dashboard/widgets/stats.blade.php ENDPATH**/ ?>