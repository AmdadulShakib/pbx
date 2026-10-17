document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });

    var themeToggle = document.querySelector('[data-theme-toggle]');
    var html = document.documentElement;
    var storedTheme = localStorage.getItem('admin-theme');
    if (storedTheme) {
        html.setAttribute('data-theme', storedTheme);
        updateThemeIcon(storedTheme);
    } else {
        updateThemeIcon(html.getAttribute('data-theme') || 'light');
    }

    window.initLucideIcons = function (scope) {
        if (!window.lucide || typeof window.lucide.createIcons !== 'function') {
            return;
        }
        var fallbackIcon = 'more-horizontal';
        var validNamePattern = /^[a-zA-Z0-9-_]+$/;
        var root = scope && scope.querySelectorAll ? scope : document;
        root.querySelectorAll('[data-lucide]').forEach(function (node) {
            var rawName = node.getAttribute('data-lucide');
            var name = (rawName || '').trim();
            if (!name || !validNamePattern.test(name)) {
                node.setAttribute('data-lucide', fallbackIcon);
                return;
            }
        });
        try {
            window.lucide.createIcons({ attrs: { 'stroke-width': 1.8 } });
        } catch (e) {}

        var unresolvedIcons = root.querySelectorAll('i[data-lucide]');
        var needsRetry = false;
        unresolvedIcons.forEach(function (node) {
            var rawName = node.getAttribute('data-lucide');
            var name = (rawName || '').trim();
            if (!name || !validNamePattern.test(name) || name !== fallbackIcon) {
                node.setAttribute('data-lucide', fallbackIcon);
                needsRetry = true;
            }
        });

        if (needsRetry) {
            try {
                window.lucide.createIcons({ attrs: { 'stroke-width': 1.8 } });
            } catch (e) {}
        }
    };
    window.initAdminIcons = window.initLucideIcons;

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            var current = html.getAttribute('data-theme') || 'light';
            var next = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('admin-theme', next);
            updateThemeIcon(next);
            window.initLucideIcons(document);
        });
    }

    function updateThemeIcon(theme) {
        var icon = document.querySelector('[data-theme-icon]');
        if (!icon) {
            return;
        }
        icon.setAttribute('data-lucide', theme === 'dark' ? 'sun' : 'moon');
    }

    function initSelect2(container) {
        if (!window.jQuery || !window.jQuery.fn || typeof window.jQuery.fn.select2 !== 'function') {
            return;
        }
        var $container = container ? window.jQuery(container) : window.jQuery(document);
        $container.find('.js-select2, .js-select2-multiple').each(function () {
            var $el = window.jQuery(this);
            if ($el.data('select2')) {
                return;
            }
            var options = { width: '100%' };
            if ($el.hasClass('js-select2-multiple')) {
                options.closeOnSelect = false;
            }
            var placeholder = $el.data('placeholder');
            if (placeholder) {
                options.placeholder = placeholder;
            }
            var $modal = $el.closest('.modal');
            if ($modal.length) {
                options.dropdownParent = $modal;
            }
            $el.select2(options);
            if ($el.hasClass('is-invalid')) {
                $el.next('.select2-container').find('.select2-selection').addClass('is-invalid');
            }
        });
    }

    window.initSelect2 = initSelect2;

    var sidebarToggle = document.querySelector('[data-sidebar-toggle]');
    var overlay = document.querySelector('[data-sidebar-overlay]');
    var sidebarIcon = document.querySelector('[data-sidebar-icon]');
    function syncSidebarIcon() {
        if (!sidebarIcon) {
            return;
        }
        var collapsed = document.body.classList.contains('sidebar-collapsed');
        sidebarIcon.setAttribute('data-lucide', collapsed ? 'panel-left-open' : 'panel-left-close');
        window.initLucideIcons(document);
    }
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            if (window.innerWidth >= 992) {
                document.body.classList.toggle('sidebar-collapsed');
                syncSidebarIcon();
            } else {
                document.body.classList.toggle('sidebar-show');
            }
        });
    }
    if (overlay) {
        overlay.addEventListener('click', function () {
            document.body.classList.remove('sidebar-show');
        });
    }
    var sidebar = document.querySelector('.admin-sidebar');
    if (sidebar) {
        sidebar.addEventListener('click', function (event) {
            var link = event.target.closest('a.nav-link');
            if (!link) {
                return;
            }
            var isDesktop = window.innerWidth >= 992;
            var isCollapsed = document.body.classList.contains('sidebar-collapsed');
            if (isDesktop && isCollapsed && link.getAttribute('data-bs-toggle') === 'collapse') {
                event.preventDefault();
                document.body.classList.remove('sidebar-collapsed');
                syncSidebarIcon();
                var target = link.getAttribute('href');
                if (target && target.charAt(0) === '#') {
                    var collapseEl = document.querySelector(target);
                    if (collapseEl) {
                        bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false }).show();
                    }
                }
            }
        });
    }
    if (sidebar) {
        var hoverTimer;
        sidebar.addEventListener('mouseenter', function () {
            if (window.innerWidth < 992) {
                return;
            }
            if (!document.body.classList.contains('sidebar-collapsed')) {
                return;
            }
            clearTimeout(hoverTimer);
            document.body.classList.add('sidebar-hover');
        });
        sidebar.addEventListener('mouseleave', function () {
            clearTimeout(hoverTimer);
            hoverTimer = setTimeout(function () {
                document.body.classList.remove('sidebar-hover');
            }, 120);
        });

        sidebar.querySelectorAll('[data-sidebar-group] > .nav-link').forEach(function (link) {
            link.addEventListener('mouseenter', function () {
                if (window.innerWidth < 992) {
                    return;
                }
                if (!document.body.classList.contains('sidebar-collapsed')) {
                    return;
                }
                document.body.classList.add('sidebar-hover');
                var target = link.getAttribute('href');
                if (target && target.charAt(0) === '#') {
                    var collapseEl = document.querySelector(target);
                    if (collapseEl) {
                        bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false }).show();
                    }
                }
            });
        });
    }
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) {
            document.body.classList.remove('sidebar-show');
        }
        if (window.innerWidth < 992) {
            document.body.classList.remove('sidebar-hover');
        }
    });
    syncSidebarIcon();

    function initSidebarSearch() {
        var searchInput = document.querySelector('[data-sidebar-search]');
        var menu = document.querySelector('[data-sidebar-menu]');
        if (!searchInput || !menu) {
            return;
        }

        var groups = menu.querySelectorAll('[data-sidebar-group]');
        var items = menu.querySelectorAll('[data-sidebar-item]');

        searchInput.addEventListener('input', function () {
            var term = searchInput.value.toLowerCase().trim();
            if (!term) {
                items.forEach(function (item) {
                    item.classList.remove('d-none');
                });
                groups.forEach(function (group) {
                    group.classList.remove('d-none');
                    var collapse = group.querySelector('.collapse');
                    if (collapse) {
                        var isActive = group.getAttribute('data-group-active') === '1';
                        collapse.classList.toggle('show', isActive);
                    }
                });
                return;
            }

            items.forEach(function (item) {
                var label = (item.getAttribute('data-sidebar-label') || item.textContent || '').toLowerCase();
                item.classList.toggle('d-none', !label.includes(term));
            });

            groups.forEach(function (group) {
                var groupLabel = (group.getAttribute('data-sidebar-label') || '').toLowerCase();
                var childVisible = Array.from(group.querySelectorAll('[data-sidebar-item]')).some(function (child) {
                    return !child.classList.contains('d-none');
                });
                var showGroup = groupLabel.includes(term) || childVisible;
                group.classList.toggle('d-none', !showGroup);
                var collapse = group.querySelector('.collapse');
                if (collapse) {
                    collapse.classList.add('show');
                }
            });
        });
    }

    initSidebarSearch();

    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function () {
            var submitButtons = form.querySelectorAll('button[type="submit"]');
            submitButtons.forEach(function (button) {
                button.disabled = true;
                if (!button.querySelector('.spinner-border')) {
                    var spinner = document.createElement('span');
                    spinner.className = 'spinner-border spinner-border-sm ms-2';
                    spinner.setAttribute('role', 'status');
                    spinner.setAttribute('aria-hidden', 'true');
                    button.appendChild(spinner);
                }
            });
        });
    });

    document.addEventListener('shown.bs.modal', function (event) {
        var modal = event.target;
        var focusable = modal.querySelector('input, select, textarea, button');
        if (focusable) {
            focusable.focus();
        }
        if (window.initLucideIcons) {
            window.initLucideIcons(modal);
        }
        if (window.initSelect2) {
            window.initSelect2(modal);
        }
        if (window.initMediaPickers) {
            window.initMediaPickers(modal);
        }
    });

    document.addEventListener('shown.bs.dropdown', function () {
        if (window.initLucideIcons) {
            window.initLucideIcons(document);
        }
    });

    document.addEventListener('click', function (event) {
        var addBtn = event.target.closest('[data-compose-add]');
        if (addBtn) {
            var key = addBtn.getAttribute('data-compose-add');
            var container = document.querySelector('[data-compose-rows="' + key + '"]');
            var template = document.querySelector('template[data-compose-template="' + key + '"]');
            if (!container || !template) {
                return;
            }
            var nextIndex = parseInt(container.getAttribute('data-next-index') || container.children.length, 10);
            if (isNaN(nextIndex)) {
                nextIndex = 0;
            }
            var html = template.innerHTML.replace(/__INDEX__/g, String(nextIndex));
            container.insertAdjacentHTML('beforeend', html);
            container.setAttribute('data-next-index', String(nextIndex + 1));
            reindexComposeRows(container, key);

            var newRow = container.lastElementChild;
            if (window.initSelect2 && newRow) {
                window.initSelect2(newRow);
            }
            if (window.initMediaPickers && newRow) {
                window.initMediaPickers(newRow);
            }
            if (window.initLucideIcons && newRow) {
                window.initLucideIcons(newRow);
            }
            return;
        }

        var removeBtn = event.target.closest('[data-compose-remove]');
        if (removeBtn) {
            var row = removeBtn.closest('[data-compose-row]');
            if (!row) {
                return;
            }
            var deleteInput = row.querySelector('[data-row-delete]');
            var idInput = row.querySelector('[data-row-id]');
            var hasId = idInput && idInput.value;
            if (hasId && deleteInput) {
                deleteInput.value = '1';
                row.classList.add('d-none');
                row.querySelectorAll('input, select, textarea').forEach(function (field) {
                    if (field === deleteInput || field === idInput) {
                        field.disabled = false;
                        return;
                    }
                    field.disabled = true;
                });
            } else {
                row.remove();
            }
            var list = removeBtn.closest('[data-compose-repeatable]');
            if (list) {
                var key = list.getAttribute('data-compose-repeatable');
                var container = list.querySelector('[data-compose-rows]');
                if (container && key) {
                    reindexComposeRows(container, key);
                }
            }
        }
    });

    function reindexComposeRows(container, key) {
        var escapedKey = key.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        var rows = Array.from(container.querySelectorAll('[data-compose-row]'));
        var currentIndex = 0;
        rows.forEach(function (row) {
            row.setAttribute('data-row-index', String(currentIndex));
            row.querySelectorAll('input[name], select[name], textarea[name]').forEach(function (field) {
                var name = field.getAttribute('name');
                if (!name) {
                    return;
                }
                var pattern = new RegExp('^' + escapedKey + '\\\\[(\\\\d+|__INDEX__)\\\\]\\\\[(.+)\\\\](\\\\[\\\\])?$');
                var match = name.match(pattern);
                if (!match) {
                    return;
                }
                var suffix = match[2];
                var arraySuffix = match[3] || '';
                field.setAttribute('name', key + '[' + currentIndex + '][' + suffix + ']' + arraySuffix);
            });
            currentIndex += 1;
        });
        container.setAttribute('data-next-index', String(currentIndex));
    }

    document.addEventListener('admin:table-refreshed', function (event) {
        if (window.initLucideIcons) {
            window.initLucideIcons(event && event.target ? event.target : document);
        }
    });

    if (window.MutationObserver) {
        var iconObserverTimer = null;
        var observer = new MutationObserver(function (mutations) {
            var shouldUpdate = mutations.some(function (mutation) {
                return mutation.addedNodes && mutation.addedNodes.length;
            });
            if (!shouldUpdate) {
                return;
            }
            if (iconObserverTimer) {
                clearTimeout(iconObserverTimer);
            }
            iconObserverTimer = setTimeout(function () {
                if (window.initLucideIcons) {
                    window.initLucideIcons(document);
                }
            }, 150);
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }


    function initMediaPickers(container) {
        var scope = container || document;
        scope.querySelectorAll('[data-media-picker]').forEach(function (picker) {
            if (picker.dataset.mediaBound === '1') {
                return;
            }
            picker.dataset.mediaBound = '1';

            var field = picker.getAttribute('data-field') || '';
            var isImage = picker.getAttribute('data-is-image') === '1';
            var input = picker.querySelector('.media-input');
            var uploadBtn = picker.querySelector('.media-upload');
            var removeBtn = picker.querySelector('.media-remove');
            var preview = picker.querySelector('.media-preview');
            var fileLink = picker.querySelector('.js-file-link');
            var fileNameEl = picker.querySelector('[data-file-name]');
            var removeInput = picker.querySelector('[data-remove-field="' + field + '"]');

            if (uploadBtn && input) {
                uploadBtn.addEventListener('click', function () {
                    input.click();
                });
            }

            if (input) {
                input.addEventListener('change', function () {
                    if (!input.files || !input.files[0]) {
                        return;
                    }
                    var file = input.files[0];
                    if (fileNameEl) {
                        fileNameEl.textContent = file.name;
                    }
                    if (removeInput) {
                        removeInput.value = '0';
                    }
                    if (removeBtn) {
                        removeBtn.classList.remove('d-none');
                    }
                    if (isImage && preview) {
                        var reader = new FileReader();
                        reader.onload = function (event) {
                            preview.src = event.target.result;
                            preview.classList.remove('d-none');
                        };
                        reader.readAsDataURL(file);
                    } else if (fileLink) {
                        fileLink.classList.add('d-none');
                    }
                });
            }

            if (removeBtn) {
                removeBtn.addEventListener('click', function () {
                    if (input) {
                        input.value = '';
                    }
                    if (removeInput) {
                        removeInput.value = '1';
                    }
                    if (fileNameEl) {
                        fileNameEl.textContent = '';
                    }
                    if (isImage && preview) {
                        preview.src = preview.getAttribute('data-default') || '';
                        preview.classList.add('d-none');
                    }
                    if (fileLink) {
                        fileLink.classList.add('d-none');
                    }
                    removeBtn.classList.add('d-none');
                });
            }
        });
    }

    window.initMediaPickers = initMediaPickers;

    document.querySelectorAll('.clear-image').forEach(function (button) {
        button.addEventListener('click', function () {
            var previewSelector = button.getAttribute('data-preview');
            var inputSelector = button.getAttribute('data-input');
            var preview = previewSelector ? document.querySelector(previewSelector) : null;
            var input = inputSelector ? document.querySelector(inputSelector) : null;
            var fileNameEl = button.closest('.col-12') ? button.closest('.col-12').querySelector('[data-file-name]') : null;
            if (input) {
                input.value = '';
            }
            if (preview) {
                preview.src = preview.getAttribute('data-default') || preview.src;
            }
            if (fileNameEl) {
                fileNameEl.textContent = '';
            }
        });
    });

    document.querySelectorAll('.upload-image').forEach(function (input) {
        input.addEventListener('change', function () {
            var previewId = input.id.replace('_upload', '_preview');
            var preview = document.getElementById(previewId);
            var fileNameEl = input.closest('.col-12') ? input.closest('.col-12').querySelector('[data-file-name]') : null;
            if (!preview || !input.files || !input.files[0]) {
                return;
            }
            var file = input.files[0];
            if (file.type && file.type.startsWith('image/')) {
                preview.src = URL.createObjectURL(file);
            }
            if (fileNameEl) {
                fileNameEl.textContent = file.name;
            }
        });
    });


    document.querySelectorAll('[data-toast]').forEach(function (toastEl) {
        var toast = new bootstrap.Toast(toastEl, { delay: 4000 });
        toast.show();
    });

    document.querySelectorAll('[data-bulk-select-all]').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            var checked = toggle.checked;
            document.querySelectorAll('[data-bulk-row]').forEach(function (rowCheckbox) {
                rowCheckbox.checked = checked;
            });
            toggleBulkButtons();
        });
    });

    document.querySelectorAll('[data-bulk-row]').forEach(function (rowCheckbox) {
        rowCheckbox.addEventListener('change', toggleBulkButtons);
    });

    function toggleBulkButtons() {
        var anyChecked = Array.from(document.querySelectorAll('[data-bulk-row]')).some(function (rowCheckbox) {
            return rowCheckbox.checked;
        });
        document.querySelectorAll('[data-bulk-action]').forEach(function (button) {
            button.disabled = !anyChecked;
        });
    }

    document.querySelectorAll('[data-filter-reset]').forEach(function (button) {
        button.addEventListener('click', function () {
            var panel = button.closest('.collapse') || document;
            panel.querySelectorAll('input, select, textarea').forEach(function (field) {
                if (field.type === 'checkbox' || field.type === 'radio') {
                    field.checked = false;
                } else {
                    field.value = '';
                }
                field.dispatchEvent(new Event('change'));
            });
        });
    });

    document.querySelectorAll('[data-table-search]').forEach(function (input) {
        input.addEventListener('input', function () {
            var term = input.value.toLowerCase();
            var table = input.closest('.card').querySelector('table');
            if (!table) {
                return;
            }
            Array.from(table.tBodies[0].rows).forEach(function (row) {
                var text = row.textContent.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });
    });

    var topbar = document.querySelector('.admin-topbar');
    if (topbar) {
        window.addEventListener('scroll', function () {
            topbar.classList.toggle('is-scrolled', window.scrollY > 6);
        });
    }

    if (window.initLucideIcons) {
        window.initLucideIcons(document);
    }

    if (window.initSelect2) {
        window.initSelect2(document);
    }

    if (window.initMediaPickers) {
        window.initMediaPickers(document);
    }

    function initDashboardCharts(scope) {
        if (typeof window.ApexCharts !== 'function') {
            return;
        }
        var root = scope && scope.querySelectorAll ? scope : document;
        root.querySelectorAll('.js-apex-chart').forEach(function (node) {
            if (node.dataset.chartInit === '1') {
                return;
            }
            var type = node.getAttribute('data-chart-type') || 'line';
            var height = parseInt(node.getAttribute('data-chart-height') || '220', 10);
            var seriesRaw = node.getAttribute('data-chart-series') || '[]';
            var labelsRaw = node.getAttribute('data-chart-labels') || '[]';
            var series;
            var labels;
            try {
                series = JSON.parse(seriesRaw);
            } catch (e) {
                series = [];
            }
            try {
                labels = JSON.parse(labelsRaw);
            } catch (e) {
                labels = [];
            }

            var palette = ['#4f46e5', '#06b6d4', '#16a34a', '#f59e0b', '#ef4444', '#a855f7', '#0ea5e9', '#f97316'];
            var options = {
                chart: {
                    type: type === 'donut' ? 'donut' : type,
                    height: height,
                    toolbar: { show: false },
                    foreColor: '#64748b',
                },
                colors: palette,
                stroke: { width: type === 'line' ? 3 : 0, curve: 'smooth' },
                dataLabels: { enabled: type === 'donut' },
                grid: { strokeDashArray: 4, borderColor: '#e2e8f0' },
                xaxis: { categories: labels },
                series: type === 'donut' ? (Array.isArray(series) ? series : []) : (Array.isArray(series) ? series : []),
                legend: { position: 'bottom' },
                tooltip: { theme: 'light' },
            };

            if (type === 'bar') {
                options.plotOptions = {
                    bar: { borderRadius: 6, columnWidth: '55%', distributed: false },
                };
            }

            if (type === 'donut') {
                options.labels = labels;
            }

            var chart = new window.ApexCharts(node, options);
            chart.render();
            node.dataset.chartInit = '1';
        });
    }

    window.initDashboardCharts = initDashboardCharts;
    initDashboardCharts(document);

    if (window.toastr) {
        window.toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 4500,
            extendedTimeOut: 1500,
        };
        if (Array.isArray(window.adminToasts)) {
            window.adminToasts.forEach(function (toast) {
                if (!toast || !toast.message) {
                    return;
                }
                var type = toast.type || 'info';
                if (typeof window.toastr[type] === 'function') {
                    window.toastr[type](toast.message);
                } else {
                    window.toastr.info(toast.message);
                }
            });
        }
    }
});
