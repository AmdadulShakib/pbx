<aside class="admin-sidebar sidebar sidebar-fixed" id="sidebar">
    <div class="sidebar-brand border-bottom px-3 py-3 d-flex align-items-center justify-content-between">
        <a href="<?php echo e(url('/admin')); ?>" class="brand d-flex align-items-center gap-2">
            <span class="brand-icon">AP</span>
            <span class="brand-name"><?php echo e(config('app.name', 'Admin')); ?></span>
        </a>
        <button class="btn btn-icon btn-light d-lg-none" type="button" onclick="coreui.Sidebar.getInstance(document.querySelector('#sidebar')).toggle()">
            <i data-lucide="x"></i>
        </button>
    </div>
    <div class="sidebar-menu px-3 py-3" data-simplebar>
        <div class="sidebar-search mb-3">
            <i data-lucide="search"></i>
            <input type="text" class="form-control form-control-sm" placeholder="<?php echo app('translator')->get('Search menu...'); ?>" data-sidebar-search>
        </div>
        <ul class="nav flex-column gap-1" data-sidebar-menu>
            <li class="nav-heading text-uppercase text-muted mt-2"><?php echo app('translator')->get('Menu'); ?></li>
            <?php
                $sidebarMenu = config('dynamic_sidebar', []);
            ?>
            <?php $__currentLoopData = $sidebarMenu; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $node): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(($node['type'] ?? '') === 'group'): ?>
                    <?php
                        $groupLabel = $node['label'] ?? 'Group';
                        $groupIcon = $node['icon'] ?? 'folder';
                        $groupIcon = is_string($groupIcon) && trim($groupIcon) !== '' ? $groupIcon : 'folder';
                        $groupId = 'group_' . \Illuminate\Support\Str::slug($groupLabel) . '_' . $loop->index;
                        $authUser = auth('admin')->user();
                        $visibleItems = [];
                        $groupPatterns = [];
                        foreach (($node['items'] ?? []) as $groupItem) {
                            if (!in_array(($groupItem['type'] ?? ''), ['module', 'route'], true)) {
                                continue;
                            }
                            if (array_key_exists('enabled', $groupItem) && $groupItem['enabled'] === false) {
                                continue;
                            }
                            if (($groupItem['type'] ?? '') === 'module' && !isWebEnabledModule($groupItem['module'] ?? '')) {
                                continue;
                            }
                            $itemIcon = $groupItem['icon'] ?? 'dot';
                            $itemIcon = is_string($itemIcon) && trim($itemIcon) !== '' ? $itemIcon : 'dot';
                            $itemRoute = $groupItem['route'] ?? null;
                            $itemPatterns = [];
                            $itemBase = null;
                            if ($itemRoute) {
                                if (\Illuminate\Support\Str::endsWith($itemRoute, '.index')) {
                                    $itemBase = \Illuminate\Support\Str::beforeLast($itemRoute, '.');
                                    if ($itemBase !== '') {
                                        $itemPatterns[] = $itemBase . '.*';
                                    }
                                } else {
                                    $itemPatterns[] = $itemRoute;
                                }
                            }
                            $itemActive = !empty($itemPatterns) ? nav_is_active($itemPatterns) : false;
                            $itemRouteName = null;
                            if ($itemRoute && \Illuminate\Support\Facades\Route::has($itemRoute)) {
                                $itemRouteName = $itemRoute;
                            } elseif ($itemBase) {
                                $fallbackRoute = $itemBase . '.create';
                                if (\Illuminate\Support\Facades\Route::has($fallbackRoute)) {
                                    $itemRouteName = $fallbackRoute;
                                }
                            }
                            if (!$itemRouteName) {
                                continue;
                            }
                            $canView = true;
                            if ($authUser && method_exists($authUser, 'hasPermission')) {
                                $canView = $authUser->hasPermission($itemRouteName);
                            }
                            if (!$canView) {
                                continue;
                            }
                            if (!empty($itemPatterns)) {
                                $groupPatterns = array_merge($groupPatterns, $itemPatterns);
                            }
                            $visibleItems[] = [
                                'label' => $groupItem['label'] ?? $groupItem['module'] ?? 'Item',
                                'icon' => $itemIcon,
                                'route' => $itemRouteName,
                                'active' => $itemActive,
                            ];
                        }
                        $groupActive = !empty($groupPatterns) ? nav_is_active($groupPatterns) : false;
                    ?>
                    <?php if(empty($visibleItems)): ?>
                        <?php continue; ?>
                    <?php endif; ?>
                    <li class="nav-item" data-sidebar-group data-sidebar-label="<?php echo e($groupLabel); ?>" data-group-active="<?php echo e($groupActive ? '1' : '0'); ?>">
                        <a class="nav-link d-flex align-items-center <?php echo e($groupActive ? '' : 'collapsed'); ?> <?php echo e($groupActive ? 'active' : ''); ?>" data-bs-toggle="collapse" href="#<?php echo e($groupId); ?>" role="button" aria-expanded="<?php echo e($groupActive ? 'true' : 'false'); ?>" aria-controls="<?php echo e($groupId); ?>">
                            <i data-lucide="<?php echo e($groupIcon); ?>"></i>
                            <span><?php echo e($groupLabel); ?></span>
                            <i data-lucide="chevron-down" class="ms-auto small"></i>
                        </a>
                        <div class="collapse <?php echo e($groupActive ? 'show' : ''); ?>" id="<?php echo e($groupId); ?>">
                            <ul class="nav flex-column gap-1 ms-3 mt-1">
                                <?php $__currentLoopData = $visibleItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="nav-item" data-sidebar-item data-sidebar-label="<?php echo e($item['label']); ?>">
                                        <a class="nav-link <?php echo e($item['active'] ? 'active' : ''); ?>" href="<?php echo e(route($item['route'])); ?>">
                                            <i data-lucide="<?php echo e($item['icon']); ?>"></i>
                                            <span><?php echo e($item['label']); ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    </li>
                <?php elseif(($node['type'] ?? '') === 'module'): ?>
                    <?php if(array_key_exists('enabled', $node) && $node['enabled'] === false): ?>
                        <?php continue; ?>
                    <?php endif; ?>
                    <?php if(!isWebEnabledModule($node['module'] ?? '')): ?>
                        <?php continue; ?>
                    <?php endif; ?>
                    <?php
                        $nodeIcon = $node['icon'] ?? 'dot';
                        $nodeIcon = is_string($nodeIcon) && trim($nodeIcon) !== '' ? $nodeIcon : 'dot';
                        $nodeRoute = $node['route'] ?? null;
                        $nodePatterns = [];
                        $nodeBase = null;
                        if ($nodeRoute) {
                            if (\Illuminate\Support\Str::endsWith($nodeRoute, '.index')) {
                                $nodeBase = \Illuminate\Support\Str::beforeLast($nodeRoute, '.');
                                if ($nodeBase !== '') {
                                    $nodePatterns[] = $nodeBase . '.*';
                                }
                            } else {
                                $nodePatterns[] = $nodeRoute;
                            }
                        }
                        $nodeActive = !empty($nodePatterns) ? nav_is_active($nodePatterns) : false;
                        $nodeRouteName = null;
                        if ($nodeRoute && \Illuminate\Support\Facades\Route::has($nodeRoute)) {
                            $nodeRouteName = $nodeRoute;
                        } elseif ($nodeBase) {
                            $fallbackRoute = $nodeBase . '.create';
                            if (\Illuminate\Support\Facades\Route::has($fallbackRoute)) {
                                $nodeRouteName = $fallbackRoute;
                            }
                        }
                        $canView = true;
                        $authUser = auth('admin')->user();
                        if ($authUser && method_exists($authUser, 'hasPermission') && $nodeRouteName) {
                            $canView = $authUser->hasPermission($nodeRouteName);
                        }
                    ?>
                    <?php if(!$nodeRouteName || !$canView): ?>
                        <?php continue; ?>
                    <?php endif; ?>
                    <li class="nav-item" data-sidebar-item data-sidebar-label="<?php echo e($node['label'] ?? $node['module'] ?? 'Module'); ?>">
                        <a class="nav-link <?php echo e($nodeActive ? 'active' : ''); ?>" href="<?php echo e(route($nodeRouteName)); ?>">
                            <i data-lucide="<?php echo e($nodeIcon); ?>"></i>
                            <span><?php echo e($node['label'] ?? $node['module'] ?? 'Module'); ?></span>
                        </a>
                    </li>
                <?php elseif(($node['type'] ?? '') === 'route'): ?>
                    <?php if(array_key_exists('enabled', $node) && $node['enabled'] === false): ?>
                        <?php continue; ?>
                    <?php endif; ?>
                    <?php
                        $nodeIcon = $node['icon'] ?? 'dot';
                        $nodeIcon = is_string($nodeIcon) && trim($nodeIcon) !== '' ? $nodeIcon : 'dot';
                        $nodeRoute = $node['route'] ?? null;
                        $nodePatterns = [];
                        $nodeBase = null;
                        if ($nodeRoute) {
                            if (\Illuminate\Support\Str::endsWith($nodeRoute, '.index')) {
                                $nodeBase = \Illuminate\Support\Str::beforeLast($nodeRoute, '.');
                                if ($nodeBase !== '') {
                                    $nodePatterns[] = $nodeBase . '.*';
                                }
                            } else {
                                $nodePatterns[] = $nodeRoute;
                            }
                        }
                        $nodeActive = !empty($nodePatterns) ? nav_is_active($nodePatterns) : false;
                        $nodeRouteName = $nodeRoute && \Illuminate\Support\Facades\Route::has($nodeRoute) ? $nodeRoute : null;
                        $canView = true;
                        $authUser = auth('admin')->user();
                        if ($authUser && method_exists($authUser, 'hasPermission') && $nodeRouteName) {
                            $canView = $authUser->hasPermission($nodeRouteName);
                        }
                    ?>
                    <?php if(!$nodeRouteName || !$canView): ?>
                        <?php continue; ?>
                    <?php endif; ?>
                    <li class="nav-item" data-sidebar-item data-sidebar-label="<?php echo e($node['label'] ?? 'Link'); ?>">
                        <a class="nav-link <?php echo e($nodeActive ? 'active' : ''); ?>" href="<?php echo e(route($nodeRouteName)); ?>">
                            <i data-lucide="<?php echo e($nodeIcon); ?>"></i>
                            <span><?php echo e($node['label'] ?? 'Link'); ?></span>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
</aside><?php /**PATH C:\xampp\htdocs\AdminPanelBuilder\resources\views/admin/partials/sidebar.blade.php ENDPATH**/ ?>