<?php echo $__env->make('admin.components.empty_state', [
    'message' => $message ?? __('No data found'),
    'helper' => $helper ?? null,
    'action' => $action ?? null,
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH C:\xampp\htdocs\AdminPanelBuilder\resources\views/admin/partials/empty_state.blade.php ENDPATH**/ ?>