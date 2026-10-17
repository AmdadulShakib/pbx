<?php $__env->startSection('title', __('Users')); ?>
<?php $__env->startSection('page-title', __('Users')); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $hasActionColumn = false;
        if (auth('admin')->check()) {
            $user = auth('admin')->user();
            $hasActionColumn = $user
                && ($user->can('admin.users.edit')
                    || $user->can('admin.users.update')
                    || $user->can('admin.users.destroy'));
        }
        $colspan = $hasActionColumn ? 4 : 3;
    ?>
    <div class="card table-card">
        <div class="card-header table-toolbar d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <div class="fw-semibold"><?php echo app('translator')->get('Users'); ?></div>
                <div class="text-muted small"><?php echo app('translator')->get('Manage registered customers.'); ?></div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <form method="get" class="d-flex align-items-center gap-2">
                    <input type="text" class="form-control form-control-sm" name="q" value="<?php echo e($search); ?>" placeholder="<?php echo app('translator')->get('Search users...'); ?>">
                </form>
                <?php if(auth('admin')->check() && (auth('admin')->user()->can('admin.users.create') || auth('admin')->user()->can('admin.users.store'))): ?>
                    <a href="<?php echo e(route('admin.users.create')); ?>" class="btn btn-primary"><?php echo app('translator')->get('Add user'); ?></a>
                <?php endif; ?>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-premium table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?php echo app('translator')->get('Name'); ?></th>
                        <th><?php echo app('translator')->get('Email'); ?></th>
                        <th><?php echo app('translator')->get('Created'); ?></th>
                        <?php if($hasActionColumn): ?>
                            <th class="text-end"><?php echo app('translator')->get('Actions'); ?></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="fw-semibold"><?php echo e($user->name); ?></td>
                            <td><?php echo e($user->email); ?></td>
                            <td><?php echo e($user->created_at?->format('M d, Y')); ?></td>
                            <?php if($hasActionColumn): ?>
                            <td class="text-end">
                                <?php if(auth('admin')->check() && (auth('admin')->user()->can('admin.users.edit') || auth('admin')->user()->can('admin.users.update'))): ?>
                                    <a href="<?php echo e(route('admin.users.edit', $user)); ?>" class="btn btn-sm btn-light"><?php echo app('translator')->get('Edit'); ?></a>
                                <?php endif; ?>
                                <?php if(auth('admin')->check() && auth('admin')->user()->can('admin.users.destroy')): ?>
                                    <form method="post" action="<?php echo e(route('admin.users.destroy', $user)); ?>" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('delete'); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><?php echo app('translator')->get('Remove'); ?></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="<?php echo e($colspan); ?>" class="text-center text-muted py-4"><?php echo app('translator')->get('No users found.'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if(method_exists($users, 'links')): ?>
            <div class="card-footer bg-white">
                <?php echo e($users->links()); ?>

            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\AdminPanelBuilder\resources\views/admin/access/users/index.blade.php ENDPATH**/ ?>