<?php
    $rows = $widget['rows'] ?? collect();
    $label = $widget['label'] ?? __('Recent');
    $displayColumn = $widget['display_column'] ?? null;
    $dateColumn = $widget['date_column'] ?? null;
?>

<div class="col-12 col-md-6 col-lg-6">
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0"><?php echo e(__('Recent :label', ['label' => $label])); ?></h5>
            <?php if(!empty($widget['route'])): ?>
                <a href="<?php echo e(route($widget['route'])); ?>" class="btn btn-sm btn-outline-secondary"><?php echo app('translator')->get('View all'); ?></a>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo app('translator')->get('ID'); ?></th>
                            <?php if($displayColumn): ?>
                                <th><?php echo e(\Illuminate\Support\Str::title(str_replace('_', ' ', $displayColumn))); ?></th>
                            <?php endif; ?>
                            <?php if($dateColumn): ?>
                                <th><?php echo app('translator')->get('Created'); ?></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($row->id ?? '-'); ?></td>
                                <?php if($displayColumn): ?>
                                    <td><?php echo e($row->{$displayColumn} ?? '-'); ?></td>
                                <?php endif; ?>
                                <?php if($dateColumn): ?>
                                    <td><?php echo e($row->{$dateColumn} ?? '-'); ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td class="text-muted text-center" colspan="100%"><?php echo app('translator')->get('No data found'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\AdminPanelBuilder\resources\views/admin/dashboard/widgets/recent.blade.php ENDPATH**/ ?>