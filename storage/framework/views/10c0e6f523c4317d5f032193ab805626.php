<?php $__env->startSection('title', __('Roles')); ?>
<?php $__env->startSection('page-title', __('Roles & Permissions')); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $actionPermissions = [
            'admin.roles.edit',
            'admin.roles.update',
            'admin.roles.destroy',
        ];
        $hasActionColumn = false;
        if (auth('admin')->check()) {
            $user = auth('admin')->user();
            $hasActionColumn = $user
                && ($user->can('admin.roles.edit')
                    || $user->can('admin.roles.update')
                    || $user->can('admin.roles.destroy'));
        }
        $colspan = $hasActionColumn ? 5 : 4;
    ?>
    <div class="card table-card">
        <div class="card-header table-toolbar">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <div class="fw-semibold"><?php echo app('translator')->get('Manage Roles'); ?></div>
                    <div class="text-muted small mt-1"><?php echo app('translator')->get('Control admin access by assigning module permissions.'); ?></div>
                </div>
                <?php if(auth('admin')->check() && (auth('admin')->user()->can('admin.roles.create') || auth('admin')->user()->can('admin.roles.store'))): ?>
                    <a href="<?php echo e(route('admin.roles.create')); ?>" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus me-1"></i> <?php echo app('translator')->get('Create Role'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-premium table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 20%;"><?php echo app('translator')->get('Role'); ?></th>
                        <th style="width: 15%;"><?php echo app('translator')->get('Permissions'); ?></th>
                        <th style="width: 15%;"><?php echo app('translator')->get('Assigned'); ?></th>
                        <th style="width: 15%;"><?php echo app('translator')->get('Type'); ?></th>
                        <?php if($hasActionColumn): ?>
                            <th class="text-end" style="width: 15%;"><?php echo app('translator')->get('Actions'); ?></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?php echo e($role->name); ?></div>
                                <?php if($role->description): ?>
                                    <div class="text-muted small mt-1"><?php echo e(Str::limit($role->description, 50)); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-soft"><?php echo e($role->permissions_count ?? 0); ?></span>
                            </td>
                            <td>
                                <span class="badge badge-soft badge-soft-info"><?php echo e($role->users_count ?? 0); ?></span>
                            </td>
                            <td>
                                <?php if($role->is_super): ?>
                                    <span class="badge badge-soft badge-soft-success">
                                        <i class="fa fa-star me-1"></i> <?php echo app('translator')->get('Super'); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-soft"><?php echo app('translator')->get('Custom'); ?></span>
                                <?php endif; ?>
                            </td>
                            <?php if($hasActionColumn): ?>
                            <td class="text-end">
                                <?php if(auth('admin')->check() && (auth('admin')->user()->can('admin.roles.edit') || auth('admin')->user()->can('admin.roles.update'))): ?>
                                    <a href="<?php echo e(route('admin.roles.edit', $role)); ?>" 
                                       class="btn btn-icon btn-light btn-sm" 
                                       title="<?php echo app('translator')->get('Edit'); ?>" 
                                       aria-label="<?php echo app('translator')->get('Edit'); ?>">
                                        <i data-lucide="pencil" style="width: 16px; height: 16px;"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if(auth('admin')->check() && auth('admin')->user()->can('admin.roles.destroy')): ?>
                                    <?php if(!$role->is_super): ?>
                                        <form method="post" action="<?php echo e(route('admin.roles.destroy', $role)); ?>" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('delete'); ?>
                                            <button class="btn btn-icon btn-light btn-sm text-danger" 
                                                    type="submit" 
                                                    title="<?php echo app('translator')->get('Delete'); ?>" 
                                                    aria-label="<?php echo app('translator')->get('Delete'); ?>"
                                                    onclick="return confirm('<?php echo app('translator')->get('Are you sure?'); ?>')">
                                                <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="<?php echo e($colspan); ?>" class="text-center text-muted py-5">
                                <div>
                                    <i data-lucide="inbox" style="width: 48px; height: 48px; opacity: 0.3;"></i>
                                    <p class="mt-2 mb-0"><?php echo app('translator')->get('No roles created yet.'); ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\AdminPanelBuilder\resources\views/admin/access/roles/index.blade.php ENDPATH**/ ?>