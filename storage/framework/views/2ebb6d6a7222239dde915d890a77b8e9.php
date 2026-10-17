<?php
    $label = $widget['label'] ?? __('Trend');
    $points = $widget['points'] ?? [];
    $labels = array_map(fn ($point) => $point['label'] ?? '', $points);
    $values = array_map(fn ($point) => (int) ($point['value'] ?? 0), $points);
?>

<div class="col-12 col-lg-6">
    <div class="card h-100 dashboard-chart-card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0"><?php echo e($label); ?></h5>
            <?php if(!empty($widget['range_label'])): ?>
                <span class="badge text-bg-light"><?php echo e($widget['range_label']); ?></span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="dashboard-chart-canvas js-apex-chart"
                 data-chart-type="line"
                 data-chart-height="220"
                 data-chart-series='<?php echo json_encode([["name" => $label, "data" => $values]], 512) ?>'
                 data-chart-labels='<?php echo json_encode($labels, 15, 512) ?>'></div>
            <?php if(empty($points)): ?>
                <p class="text-muted mb-0"><?php echo app('translator')->get('No data found'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\AdminPanelBuilder\resources\views/admin/dashboard/widgets/line_chart.blade.php ENDPATH**/ ?>