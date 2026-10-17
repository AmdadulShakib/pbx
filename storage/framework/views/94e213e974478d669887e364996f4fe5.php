<header class="admin-topbar">
    <div class="container-fluid d-flex align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-icon btn-light" type="button" data-sidebar-toggle>
                <i data-lucide="panel-left-close" data-sidebar-icon></i>
            </button>
            <span class="text-muted small d-none d-md-inline"><?php echo app('translator')->get('Admin Panel'); ?></span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?php if(config('admin_ui.enable_dark_mode', true)): ?>
                <button class="btn btn-icon btn-light" type="button" data-theme-toggle data-bs-toggle="tooltip" title="<?php echo app('translator')->get('Toggle theme'); ?>">
                    <i data-lucide="moon" data-theme-icon></i>
                </button>
            <?php endif; ?>
            <a href="<?php echo e(route('admin.business-settings.general')); ?>" class="btn btn-light d-inline-flex align-items-center gap-2" data-bs-toggle="tooltip" title="<?php echo app('translator')->get('Business Settings'); ?>">
                <i data-lucide="settings"></i>
                <span class="d-none d-md-inline"><?php echo app('translator')->get('Business Settings'); ?></span>
            </a>
            <button class="btn btn-icon btn-light position-relative" type="button" data-bs-toggle="tooltip" title="<?php echo app('translator')->get('Notifications'); ?>">
                <i data-lucide="bell"></i>
                <span class="badge bg-danger position-absolute top-0 start-100 translate-middle p-1 rounded-circle"></span>
            </button>
            <div class="dropdown">
                <button class="btn btn-icon btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i data-lucide="user"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><span class="dropdown-item-text text-muted"><?php echo app('translator')->get('Signed in as Admin'); ?></span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#"><?php echo app('translator')->get('Profile'); ?></a></li>
                    <li><a class="dropdown-item" href="#"><?php echo app('translator')->get('Settings'); ?></a></li>
                    <li>
                        <form method="post" action="<?php echo e(route('admin.logout')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="dropdown-item d-flex align-items-center gap-2">
                                <i data-lucide="log-out"></i>
                                <span><?php echo app('translator')->get('Logout'); ?></span>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
<?php /**PATH C:\xampp\htdocs\AdminPanelBuilder\resources\views/admin/partials/header.blade.php ENDPATH**/ ?>