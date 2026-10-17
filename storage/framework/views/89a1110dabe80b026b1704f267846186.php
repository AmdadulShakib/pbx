<?php $__env->startSection('title', __('Admin Staff')); ?>
<?php $__env->startSection('page-title', __('Admin Staff')); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $hasActionColumn = false;
        if (auth('admin')->check()) {
            $user = auth('admin')->user();
            $hasActionColumn = $user
                && ($user->can('admin.staff.edit')
                    || $user->can('admin.staff.update')
                    || $user->can('admin.staff.destroy'));
        }
        $colspan = $hasActionColumn ? 5 : 4;
    ?>
    <div class="card table-card">
        <div class="card-header table-toolbar d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <div class="fw-semibold"><?php echo app('translator')->get('Admin staff'); ?></div>
                <div class="text-muted small"><?php echo app('translator')->get('Manage admin employees and role assignments.'); ?></div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <form method="get" class="d-flex align-items-center gap-2">
                    <input type="text" class="form-control form-control-sm" name="q" value="<?php echo e($search); ?>" placeholder="<?php echo app('translator')->get('Search staff...'); ?>">
                </form>
                <?php if(auth('admin')->check() && (auth('admin')->user()->can('admin.staff.create') || auth('admin')->user()->can('admin.staff.store'))): ?>
                    <a href="<?php echo e(route('admin.staff.create')); ?>" class="btn btn-primary"><?php echo app('translator')->get('Add staff'); ?></a>
                <?php endif; ?>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-premium table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?php echo app('translator')->get('Name'); ?></th>
                        <th><?php echo app('translator')->get('Email'); ?></th>
                        <th><?php echo app('translator')->get('Roles'); ?></th>
                        <th><?php echo app('translator')->get('Created'); ?></th>
                        <?php if($hasActionColumn): ?>
                            <th class="text-end"><?php echo app('translator')->get('Actions'); ?></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $staff; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="fw-semibold"><?php echo e($member->name); ?></td>
                            <td><?php echo e($member->email); ?></td>
                            <td>
                                <?php if(!empty($rolesEnabled)): ?>
                                    <?php $__empty_2 = true; $__currentLoopData = $member->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                        <span class="badge badge-soft"><?php echo e($role->name); ?></span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                        <span class="text-muted"><?php echo app('translator')->get('No roles'); ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted"><?php echo app('translator')->get('Roles unavailable'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($member->created_at?->format('M d, Y')); ?></td>
                            <?php if($hasActionColumn): ?>
                            <td class="text-end">
                                <?php if(auth('admin')->check() && (auth('admin')->user()->can('admin.staff.edit') || auth('admin')->user()->can('admin.staff.update'))): ?>
                                    <a href="<?php echo e(route('admin.staff.edit', $member)); ?>" class="btn btn-icon btn-light" title="<?php echo app('translator')->get('Edit'); ?>" aria-label="<?php echo app('translator')->get('Edit'); ?>">
                                        <i data-lucide="pencil"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if(auth('admin')->check() && auth('admin')->user()->can('admin.staff.destroy')): ?>
                                    <form method="post" action="<?php echo e(route('admin.staff.destroy', $member)); ?>" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('delete'); ?>
                                        <button type="submit" class="btn btn-icon btn-outline-danger" title="<?php echo app('translator')->get('Remove'); ?>" aria-label="<?php echo app('translator')->get('Remove'); ?>">
                                            <i data-lucide="trash-2"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="<?php echo e($colspan); ?>" class="text-center text-muted py-4"><?php echo app('translator')->get('No staff members found.'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if(method_exists($staff, 'links')): ?>
            <div class="card-footer bg-white">
                <?php echo e($staff->links()); ?>

            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\AdminPanelBuilder\resources\views/admin/access/staff/index.blade.php ENDPATH**/ ?>