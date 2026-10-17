<?php
    $rows = $widget['rows'] ?? [];
    $label = $widget['label'] ?? __('Status Summary');
    $field = $widget['field'] ?? 'status';
?>

<div class="col-12 col-lg-6">
    <div class="card h-100">
        <div class="card-header">
            <h5 class="mb-0"><?php echo e(__(':label status summary', ['label' => $label])); ?></h5>
        </div>
        <div class="card-body">
            <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge bg-secondary"><?php echo e(ucwords((string) ($row->{$field} ?? ''))); ?></span>
                    <span class="fw-semibold"><?php echo e((int) ($row->count ?? 0)); ?></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-muted mb-0"><?php echo app('translator')->get('No data found'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\AdminPanelBuilder\resources\views/admin/dashboard/widgets/status_summary.blade.php ENDPATH**/ ?>