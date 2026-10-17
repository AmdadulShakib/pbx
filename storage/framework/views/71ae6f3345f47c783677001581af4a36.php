<?php
    $points = $widget['points'] ?? [];
    $label = $widget['label'] ?? __('Activity');
    $labels = array_map(fn ($point) => $point['day'] ?? '', $points);
    $values = array_map(fn ($point) => (int) ($point['count'] ?? 0), $points);
?>

<div class="col-12 col-lg-6">
    <div class="card h-100 dashboard-chart-card">
        <div class="card-header">
            <h5 class="mb-0"><?php echo e(__(':label activity', ['label' => $label])); ?></h5>
        </div>
        <div class="card-body">
            <div class="dashboard-chart-canvas js-apex-chart"
                 data-chart-type="bar"
                 data-chart-height="220"
                 data-chart-series='<?php echo json_encode([["name" => "Count", "data" => $values]], 512) ?>'
                 data-chart-labels='<?php echo json_encode($labels, 15, 512) ?>'></div>
            <?php if(empty($points)): ?>
                <p class="text-muted mb-0"><?php echo app('translator')->get('No data found'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\AdminPanelBuilder\resources\views/admin/dashboard/widgets/chart_count_by_day.blade.php ENDPATH**/ ?>