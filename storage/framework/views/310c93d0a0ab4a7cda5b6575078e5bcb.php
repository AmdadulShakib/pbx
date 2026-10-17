<?php $__env->startSection('title', __('Category List')); ?>
<?php $__env->startSection('page-title', __('Category')); ?>
<?php $__env->startSection('content'); ?>
    <?php
        $emptyAction = null;
        $filtersPanel = trim($__env->yieldPushContent('filters-panel'));
        $totalCount = method_exists($categories, 'total') ? $categories->total() : $categories->count();
        $title = __('Category List');
        $subtitle = __(':count total', ['count' => $totalCount]);
        $search = true;
        $actionPermissions = [
            'admin.category.edit',
            'admin.category.update',
            'admin.category.destroy',
        ];
        $hasActionColumn = false;
        if (auth('admin')->check()) {
            $user = auth('admin')->user();
            $hasActionColumn = $user
                && ($user->can('admin.category.edit')
                    || $user->can('admin.category.update')
                    || $user->can('admin.category.destroy'));
        }
    ?>
    <?php $__env->startComponent('admin.components.table.card_table', compact('title', 'subtitle', 'search')); ?>
        <?php $__env->slot('actions'); ?>
            <div class="d-flex align-items-center gap-2">
                <?php if(config('admin_ui.enable_bulk_actions', false)): ?>
                    <?php if(true): ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('admin.category.destroy')): ?>
                        <button type="button" class="btn btn-outline-danger btn-sm" data-bulk-action="delete" disabled><?php echo app('translator')->get('Delete Selected'); ?></button>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if(false): ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('admin.category.status')): ?>
                        <button type="button" class="btn btn-outline-warning btn-sm" data-bulk-action="status" disabled><?php echo app('translator')->get('Change Status'); ?></button>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if($filtersPanel): ?>
                    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filtersPanel" aria-expanded="true"><?php echo app('translator')->get('Filters'); ?></button>
                <?php endif; ?>
                
	<?php if(auth('admin')->check() && (auth('admin')->user()->can('admin.category.create') || auth('admin')->user()->can('admin.category.store'))): ?>
<a href="<?php echo e(route('admin.category.create')); ?>" class="btn btn-primary"><i data-lucide="plus" class="me-1"></i><?php echo app('translator')->get("Add Category"); ?></a>
<?php endif; ?>

            </div>
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('filters'); ?>
            <?php if($filtersPanel): ?>
                <div class="card-body border-bottom">
                    <div class="collapse show" id="filtersPanel">
                        <div class="row g-2 align-items-end">
                            <?php echo $filtersPanel; ?>

                            <div class="col-auto">
                                <button class="btn btn-outline-secondary btn-sm" type="button" data-filter-reset><?php echo app('translator')->get('Reset filters'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('table'); ?>
            <div class="table-responsive">
                <table class="table table-premium table-hover align-middle mb-0 smart-table">
                    <thead class="table-light">
                        <tr>
                            <?php if(config('admin_ui.enable_bulk_actions', false)): ?>
                            <th class="text-center">
                                <input class="form-check-input" type="checkbox" data-bulk-select-all>
                            </th>
                            <?php endif; ?>
                            <th class="text-muted"><?php echo app('translator')->get('ID'); ?></th>
                            
                            <?php if($hasActionColumn): ?>
                                <th class="text-end"><?php echo app('translator')->get('Action'); ?></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <?php if(config('admin_ui.enable_bulk_actions', false)): ?>
                                <td class="text-center">
                                    <input class="form-check-input" type="checkbox" name="selected[]" value="<?php echo e($category->id); ?>" data-bulk-row>
                                </td>
                                <?php endif; ?>
                                <td class="text-muted"><?php echo e(++$key); ?></td>
                                
                                
                                <?php if($hasActionColumn): ?>
                                    <td class="text-end">
                                        <div class="d-inline-flex align-items-center gap-1 action-buttons">
                                        <?php if(auth('admin')->check() && (auth('admin')->user()->can('admin.category.edit') || auth('admin')->user()->can('admin.category.update'))): ?>
<a href="<?php echo e(route('admin.category.edit', $category->id)); ?>" class="btn btn-icon btn-outline-info" data-bs-toggle="tooltip" title="<?php echo app('translator')->get('Edit'); ?>"><i data-lucide="pencil"></i></a>
<?php endif; ?>
                                        <?php if(auth('admin')->check() && auth('admin')->user()->can('admin.category.destroy')): ?>
<button class="btn btn-icon btn-outline-danger deleteBtn" type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal"
                                            data-id="<?php echo e($category->id); ?>"
                                            data-action="<?php echo e(route('admin.category.destroy', $category->id)); ?>">
                                            <span data-bs-toggle="tooltip" title="<?php echo app('translator')->get('Delete'); ?>"><i data-lucide="trash-2"></i></span>
                                        </button>
<?php endif; ?>
                                        
                                    </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="100%">
                                    <?php echo $__env->make('admin.partials.table_empty', [
                                        'message' => __('Data Not Found'),
                                        'helper' => __('Create your first Category to get started.'),
                                        'action' => $emptyAction,
                                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('pagination'); ?>
            <?php
                $canPaginate = is_object($categories) && $categories instanceof \Illuminate\Contracts\Pagination\Paginator;
            ?>
            <?php if($canPaginate && $categories->hasPages()): ?>
                <?php echo $__env->make('admin.components.table.pagination', ['paginator' => $categories], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>
        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    







    <div id="deleteModal" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo app('translator')->get('Category Delete Confirmation'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <input type="hidden" name="id">
                    <div class="modal-body">
                        <p><?php echo app('translator')->get('Are you sure to delete this category?'); ?></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php echo app('translator')->get('Cancel'); ?></button>
                        <button type="submit" class="btn btn-danger"><?php echo app('translator')->get('Yes'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>


<?php $__env->stopSection(); ?>

<?php $__env->startPush('js'); ?>
	<script>
		(function($){
			"use strict";
			            $('.statusBtn').on('click', function () {
                var modal = $('#statusModal');
                modal.find('form').attr('action', $(this).data('action'));
                modal.find('input[name=id]').val($(this).data('id'));
            });

            $('.deleteBtn').on('click', function () {
                var modal = $('#deleteModal');
                modal.find('form').attr('action', $(this).data('action'));
                modal.find('input[name=id]').val($(this).data('id'));
            });

		})(jQuery);
	</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\AdminPanelBuilder\resources\views/admin/category/index.blade.php ENDPATH**/ ?>