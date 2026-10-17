<?php
    $label = $widget['label'] ?? __('Comparison');
    $current = $widget['current'] ?? 0;
    $previous = $widget['previous'] ?? 0;
    $delta = $widget['delta'] ?? null;
    $trend = $widget['trend'] ?? null;
?>

<div class="col-12 col-md-6">
    <div class="card h-100 dashboard-kpi">
        <div class="card-body">
            <div class="text-muted small"><?php echo e($label); ?></div>
            <div class="d-flex align-items-center gap-3">
                <div>
                    <div class="h4 mb-0"><?php echo e(number_format((int) $current)); ?></div>
                    <div class="text-muted small"><?php echo app('translator')->get('Current'); ?></div>
                </div>
                <div>
                    <div class="h6 mb-0"><?php echo e(number_format((int) $previous)); ?></div>
                    <div class="text-muted small"><?php echo app('translator')->get('Previous'); ?></div>
                </div>
                <?php if($delta !== null): ?>
                    <span class="badge ms-auto <?php echo e($trend === 'down' ? 'text-bg-danger' : ($trend === 'up' ? 'text-bg-success' : 'text-bg-secondary')); ?>">
                        <?php echo e($delta); ?>%
                    </span>
                <?php endif; ?>
            </div>
            <?php if(!empty($widget['hint'])): ?>
                <div class="small text-muted mt-2"><?php echo e($widget['hint']); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\AdminPanelBuilder\resources\views/admin/dashboard/widgets/trend_compare.blade.php ENDPATH**/ ?>