<?php
    $label = $widget['label'] ?? __('Metric');
    $value = $widget['value'] ?? 0;
    $suffix = $widget['suffix'] ?? '';
    $delta = $widget['delta'] ?? null;
    $trend = $widget['trend'] ?? null;
?>

<div class="col-12 col-md-6 col-xl-4">
    <div class="card h-100 dashboard-kpi">
        <div class="card-body">
            <div class="text-muted small"><?php echo e($label); ?></div>
            <div class="d-flex align-items-end justify-content-between gap-2">
                <div class="h3 mb-0"><?php echo e(is_numeric($value) ? number_format((float) $value, 0) : $value); ?><?php echo e($suffix); ?></div>
                <?php if($delta !== null): ?>
                    <span class="badge <?php echo e($trend === 'down' ? 'text-bg-danger' : ($trend === 'up' ? 'text-bg-success' : 'text-bg-secondary')); ?>">
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
<?php /**PATH C:\xampp\htdocs\AdminPanelBuilder\resources\views/admin/dashboard/widgets/kpi_card.blade.php ENDPATH**/ ?>