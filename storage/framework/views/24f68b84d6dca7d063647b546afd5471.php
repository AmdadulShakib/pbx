<?php
    $toastMessages = [];
    foreach (['success', 'error', 'warning', 'info'] as $type) {
        if (session($type)) {
            $toastMessages[] = [
                'type' => $type,
                'message' => (string) session($type),
            ];
        }
    }
    if ($errors->any()) {
        foreach ($errors->all() as $error) {
            $toastMessages[] = [
                'type' => 'error',
                'message' => (string) $error,
            ];
        }
    }
?>

<?php if(!empty($toastMessages)): ?>
    <script>
        window.adminToasts = <?php echo json_encode($toastMessages, 15, 512) ?>;
    </script>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\AdminPanelBuilder\resources\views/admin/partials/toast-messages.blade.php ENDPATH**/ ?>