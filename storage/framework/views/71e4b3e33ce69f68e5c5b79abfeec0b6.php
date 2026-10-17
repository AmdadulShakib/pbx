<div class="card table-card">
    <div class="card-header table-toolbar d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <div class="fw-semibold"><?php echo e($title ?? ''); ?></div>
            <?php if(!empty($subtitle)): ?>
                <div class="text-muted small"><?php echo e($subtitle); ?></div>
            <?php endif; ?>
        </div>
        <div class="d-flex align-items-center gap-2 ms-auto">
            <?php if(!empty($search)): ?>
                <div class="table-search-input">
                    <i data-lucide="search"></i>
                    <input type="text" class="form-control form-control-sm table-search" data-table-search placeholder="<?php echo e($searchPlaceholder ?? __('Quick search...')); ?>">
                </div>
            <?php endif; ?>
            <?php echo $actions ?? ''; ?>

        </div>
    </div>
    <?php if(!empty($filters)): ?>
        <?php echo $filters; ?>

    <?php endif; ?>
    <div class="card-body p-0">
        <?php echo $table ?? ''; ?>

    </div>
    <?php if(!empty(trim($pagination ?? ''))): ?>
        <div class="card-footer">
            <?php echo $pagination; ?>

        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\AdminPanelBuilder\resources\views/admin/components/table/card_table.blade.php ENDPATH**/ ?>