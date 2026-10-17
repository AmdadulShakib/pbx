@include('admin.components.empty_state', [
    'message' => $message ?? __('No data found'),
    'helper' => $helper ?? null,
    'action' => $action ?? null,
])
