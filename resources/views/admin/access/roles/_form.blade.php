@php
    $isSuper = old('is_super', $role->is_super ?? false);
    
    // Label mapping
    $labelMap = [
        'index' => 'List',
        'list' => 'List',
        'store' => 'Create/Store',
        'create' => 'Create/Store',
        'edit' => 'Edit/Update',
        'update' => 'Edit/Update',
        'destroy' => 'Delete',
        'delete' => 'Delete',
        'show' => 'View',
        'view' => 'View',
    ];
    
    // Desired order for permission actions
    $actionOrder = [
        'List' => 1,
        'Create/Store' => 2,
        'Edit/Update' => 3,
        'Delete' => 4,
        'View' => 5,
    ];
    
    // Group permissions by module and action label (merge duplicates)
    $modulePermissions = [];
    
    foreach ($permissionGroups as $group) {
        $groupLabel = $group['label'] ?? 'Module';
        $permissions = $group['permissions'] ?? [];
        
        // Skip 'general' group as it's a fallback for ungrouped permissions
        if (strtolower($groupLabel) === 'general') {
            continue;
        }
        
        $groupPermissions = [];
        
        foreach ($permissions as $permission) {
            $permissionKey = (string) ($permission->key ?? '');
            $permissionKeyLower = strtolower($permissionKey);
            $actionKey = $permission->action ?: \Illuminate\Support\Str::afterLast($permissionKey, '.');
            $actionKeyLower = strtolower((string) $actionKey);
            
            // Skip status permissions
            if ($actionKeyLower === 'status'
                || \Illuminate\Support\Str::startsWith($permissionKeyLower, 'status.')
                || \Illuminate\Support\Str::contains($permissionKeyLower, '.status')) {
                continue;
            }
            
            $actionLabel = $labelMap[$actionKeyLower] ?? \Illuminate\Support\Str::headline($actionKey);
            
            // Create unique key for each action label within module
            if (!isset($groupPermissions[$actionLabel])) {
                $groupPermissions[$actionLabel] = [];
            }
            
            $groupPermissions[$actionLabel][] = $permission;
        }
        
        if (!empty($groupPermissions)) {
            // Sort by action order
            uksort($groupPermissions, function($a, $b) use ($actionOrder) {
                $orderA = $actionOrder[$a] ?? 99;
                $orderB = $actionOrder[$b] ?? 99;
                return $orderA <=> $orderB;
            });
            
            $modulePermissions[$groupLabel] = $groupPermissions;
        }
    }
@endphp

<div class="row g-3">
    <div class="col-lg-6">
        <div class="mb-3">
            <label class="form-label">@lang('Role name')</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $role->name ?? '') }}" required>
            @error('name')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-lg-6">
        <div class="mb-3">
            <label class="form-label">@lang('Description')</label>
            <input type="text" name="description" class="form-control" value="{{ old('description', $role->description ?? '') }}">
            @error('description')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-check form-switch mb-2">
    <input class="form-check-input" type="checkbox" role="switch" id="roleIsSuper" name="is_super" value="1" {{ $isSuper ? 'checked' : '' }}>
    <label class="form-check-label" for="roleIsSuper">@lang('Super admin (full access)')</label>
</div>
<div class="text-muted small mb-4">@lang('Grants all permissions, including future modules.')</div>

<div class="permission-block" data-permission-block>
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h5 class="mb-0">@lang('Module Access')</h5>
            <div class="text-muted small mt-1">@lang('Select permissions for each module')</div>
        </div>
    </div>

    <div class="row g-3">
        @forelse($modulePermissions as $groupLabel => $actionPermissions)
            @php
                $selectAllId = 'select-all-' . \Illuminate\Support\Str::slug($groupLabel);
            @endphp
            <div class="col-12 col-lg-6">
                <div class="card permission-group h-100 border-0 shadow-sm" data-permission-group>
                    <!-- Card Header with Select All -->
                    <div class="card-header bg-light border-bottom d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="fw-semibold text-dark">{{ $groupLabel }}</div>
                        </div>
                        <div class="form-check m-0">
                            <input class="form-check-input" type="checkbox" id="{{ $selectAllId }}" data-permission-select-all>
                            <label class="form-check-label cursor-pointer small text-muted" for="{{ $selectAllId }}">@lang('All')</label>
                        </div>
                    </div>
                    
                    <!-- Permission Checkboxes in Card Body -->
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($actionPermissions as $actionLabel => $permissionsInAction)
                                @php
                                    // Create a unique ID for this action group
                                    $actionGroupId = 'action-' . \Illuminate\Support\Str::slug($groupLabel . '-' . $actionLabel);
                                    $allChecked = true;
                                    $anyChecked = false;
                                    
                                    foreach ($permissionsInAction as $perm) {
                                        $isChecked = in_array($perm->id, old('permissions', $selected ?? []), true);
                                        if ($isChecked) $anyChecked = true;
                                        else $allChecked = false;
                                    }
                                @endphp
                                
                                <div class="permission-item">
                                    <!-- Main checkbox for merged action -->
                                    <label class="form-check d-flex align-items-center gap-2 cursor-pointer mb-2" 
                                           style="padding: 8px 12px; border-radius: 6px; background: #f8f9fa; transition: all 0.2s ease;"
                                           data-action-group="{{ $actionGroupId }}">
                                        <input class="form-check-input m-0 action-checkbox" 
                                               type="checkbox" 
                                               data-action-id="{{ $actionGroupId }}"
                                               style="width: 18px; height: 18px;"
                                               {{ $anyChecked ? 'checked' : '' }}>
                                        <span class="text-nowrap" style="font-size: 0.875rem;">
                                            <strong>{{ $actionLabel }}</strong>
                                        </span>
                                    </label>
                                    
                                    <!-- Hidden checkboxes for individual permissions -->
                                    @foreach($permissionsInAction as $permission)
                                        @php
                                            $checked = in_array($permission->id, old('permissions', $selected ?? []), true);
                                        @endphp
                                        <input class="form-check-input d-none child-checkbox-{{ $actionGroupId }}" 
                                               type="checkbox" 
                                               name="permissions[]" 
                                               value="{{ $permission->id }}" 
                                               {{ $checked ? 'checked' : '' }}>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info mb-0">@lang('No permissions found.')</div>
            </div>
        @endforelse
    </div>
</div>

@push('js')
<script>
    (function () {
        const superToggle = document.getElementById('roleIsSuper');
        const block = document.querySelector('[data-permission-block]');
        const groups = Array.from(document.querySelectorAll('[data-permission-group]'));
        const previousState = new Map();
        let superActive = false;

        // Handle action group checkboxes
        const actionCheckboxes = document.querySelectorAll('.action-checkbox');
        actionCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const actionId = this.getAttribute('data-action-id');
                const childCheckboxes = document.querySelectorAll(`.child-checkbox-${actionId}`);
                childCheckboxes.forEach(child => {
                    if (!child.disabled) {
                        child.checked = this.checked;
                    }
                });
                updateGroupState(this.closest('[data-permission-group]'));
            });
        });

        // Handle individual permission changes
        const permissionCheckboxes = document.querySelectorAll('input[name="permissions[]"]');
        permissionCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const group = this.closest('[data-permission-group]');
                updateGroupState(group);
                
                // Update parent action checkbox state
                const labels = group.querySelectorAll('[data-action-group]');
                labels.forEach(label => {
                    const actionId = label.querySelector('.action-checkbox').getAttribute('data-action-id');
                    const childCheckboxes = document.querySelectorAll(`.child-checkbox-${actionId}`);
                    const allChecked = Array.from(childCheckboxes).every(cb => cb.checked);
                    const anyChecked = Array.from(childCheckboxes).some(cb => cb.checked);
                    
                    const actionCheckbox = label.querySelector('.action-checkbox');
                    actionCheckbox.checked = allChecked;
                    actionCheckbox.indeterminate = anyChecked && !allChecked;
                });
            });
        });

        const getGroupCheckboxes = (group) =>
            Array.from(group.querySelectorAll('input[name="permissions[]"]'));
        const getSelectAll = (group) => group.querySelector('[data-permission-select-all]');

        const updateGroupState = (group) => {
            const boxes = getGroupCheckboxes(group);
            const selectAll = getSelectAll(group);
            if (!selectAll || boxes.length === 0) return;
            const checkedCount = boxes.filter((box) => box.checked).length;
            selectAll.checked = checkedCount === boxes.length;
            selectAll.indeterminate = checkedCount > 0 && checkedCount < boxes.length;
        };

        const captureState = () => {
            previousState.clear();
            block.querySelectorAll('input[name="permissions[]"]').forEach((box) => {
                previousState.set(box, box.checked);
            });
        };

        const restoreState = () => {
            block.querySelectorAll('input[name="permissions[]"]').forEach((box) => {
                box.checked = previousState.get(box) ?? false;
            });
            
            // Update action checkboxes
            actionCheckboxes.forEach(checkbox => {
                const actionId = checkbox.getAttribute('data-action-id');
                const childCheckboxes = document.querySelectorAll(`.child-checkbox-${actionId}`);
                const allChecked = Array.from(childCheckboxes).every(cb => cb.checked);
                const anyChecked = Array.from(childCheckboxes).some(cb => cb.checked);
                
                checkbox.checked = allChecked;
                checkbox.indeterminate = anyChecked && !allChecked;
            });
        };

        const applySuperState = () => {
            if (!superToggle || !block) return;
            const enabled = superToggle.checked;
            if (enabled && !superActive) {
                captureState();
            }
            block.classList.toggle('opacity-50', enabled);

            const allPermissionBoxes = block.querySelectorAll('input[name="permissions[]"]');
            const allSelectAll = block.querySelectorAll('[data-permission-select-all]');
            const allActionCheckboxes = block.querySelectorAll('.action-checkbox');

            allPermissionBoxes.forEach((box) => {
                if (enabled) {
                    box.checked = true;
                }
                box.disabled = enabled;
            });

            allActionCheckboxes.forEach((checkbox) => {
                checkbox.disabled = enabled;
                if (enabled) {
                    checkbox.checked = true;
                    checkbox.indeterminate = false;
                }
            });

            allSelectAll.forEach((toggle) => {
                toggle.disabled = enabled;
                if (enabled) {
                    toggle.checked = true;
                    toggle.indeterminate = false;
                }
            });

            if (!enabled && superActive) {
                restoreState();
                groups.forEach(updateGroupState);
            }
            superActive = enabled;
        };

        groups.forEach((group) => {
            const selectAll = getSelectAll(group);
            const boxes = getGroupCheckboxes(group);

            if (selectAll) {
                selectAll.addEventListener('change', () => {
                    boxes.forEach((box) => {
                        if (!box.disabled) {
                            box.checked = selectAll.checked;
                        }
                    });
                    
                    // Update action checkboxes
                    const actionCheckboxesInGroup = group.querySelectorAll('.action-checkbox');
                    actionCheckboxesInGroup.forEach(checkbox => {
                        checkbox.checked = selectAll.checked;
                        checkbox.indeterminate = false;
                    });
                    
                    updateGroupState(group);
                });
            }

            updateGroupState(group);
        });

        if (superToggle) {
            superToggle.addEventListener('change', applySuperState);
            applySuperState();
        }
    })();
</script>

<style>
    .permission-group .form-check label {
        user-select: none;
        transition: all 0.2s ease;
    }
    
    .permission-group .form-check label:hover {
        background: #e8eef5 !important;
        border-radius: 6px;
    }
    
    .permission-group .form-check input[type="checkbox"]:checked + span {
        color: #0d6efd;
    }
    
    .permission-group .form-check input[type="checkbox"]:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .permission-group .card-body {
        padding: 1.25rem;
    }
    
    .permission-item {
        display: inline-block;
    }
</style>
@endpush
