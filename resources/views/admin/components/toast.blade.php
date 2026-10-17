@php
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
@endphp

@if(!empty($toastMessages))
    <script>
        window.adminToasts = @json($toastMessages);
    </script>
@endif
