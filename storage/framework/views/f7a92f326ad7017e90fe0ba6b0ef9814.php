<div class="empty-state text-center py-5">
    <div class="empty-icon mb-2">
        <i data-lucide="inbox"></i>
    </div>
    <div class="fw-semibold"><?php echo e($message ?? __('No data found')); ?></div>
    <?php if(!empty($helper)): ?>
        <div class="text-muted small mt-1"><?php echo e($helper); ?></div>
    <?php endif; ?>
    <?php if(!empty($action)): ?>
        <div class="mt-3"><?php echo $action; ?></div>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\AdminPanelBuilder\resources\views/admin/components/empty_state.blade.php ENDPATH**/ ?>