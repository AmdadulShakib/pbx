<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function home(Request $request)
    {
        return response()->noContent();
    }

    public function noAccess()
    {
        return view('admin.access.no_access');
    }

    public function index()
    {
        $dashboardConfig = config('dashboard', []);
        $hasWidgetsKey = is_array($dashboardConfig) && array_key_exists('widgets', $dashboardConfig);
        $widgetsConfig = $hasWidgetsKey && is_array($dashboardConfig['widgets'])
            ? $dashboardConfig['widgets']
            : [];

        $modules = config('dynamic_modules', []);
        if (!is_array($modules)) {
            $modules = [];
        }

        $widgets = [];
        if ($hasWidgetsKey) {
            foreach ($widgetsConfig as $widgetConfig) {
                $widget = $this->buildWidget($widgetConfig, $modules);
                if ($widget !== null) {
                    $widgets[] = $widget;
                }
            }
        } else {
            $webModules = array_values(array_filter($modules, function ($module) {
                return !empty($module['web_routes'])
                    || !empty($module['web_controller'])
                    || !empty($module['views']);
            }));
            $statModules = array_slice($webModules, 0, 6);
            if (!empty($statModules)) {
                $widgets[] = $this->buildStatsWidget([
                    'modules' => array_column($statModules, 'key'),
                ], $modules);
            }
            if (!empty($webModules)) {
                $widgets[] = $this->buildRecentWidget([
                    'module' => $webModules[0]['key'],
                    'limit' => 10,
                ], $modules);
            }
        }

        $widgets = array_values(array_filter($widgets));

        return view('admin.dashboard', ['widgets' => $widgets]);
    }

    protected function buildWidget($config, array $modules): ?array
    {
        if (!is_array($config)) {
            return null;
        }

        $type = $config['type'] ?? null;
        if (!is_string($type) || $type === '') {
            return null;
        }

        return match ($type) {
            'stats' => $this->buildStatsWidget($config, $modules),
            'recent' => $this->buildRecentWidget($config, $modules),
            'status_summary' => $this->buildStatusSummaryWidget($config, $modules),
            'chart_count_by_day' => $this->buildChartCountWidget($config, $modules),
            'line_chart' => $this->buildLineChartWidget($config, $modules),
            'bar_chart' => $this->buildBarChartWidget($config, $modules),
            'pie_chart' => $this->buildPieChartWidget($config, $modules),
            'kpi_card' => $this->buildKpiWidget($config, $modules),
            'table_recent' => $this->buildTableRecentWidget($config, $modules),
            'trend_compare' => $this->buildTrendCompareWidget($config, $modules),
            default => null,
        };
    }

    protected function resolveModule(string $moduleKey, array $modules): ?array
    {
        $module = $modules[$moduleKey] ?? null;
        if (!is_array($module)) {
            return null;
        }

        if (!isWebEnabledModule($moduleKey)) {
            return null;
        }

        $table = $module['table'] ?? null;
        if (!is_string($table) || $table === '' || !Schema::hasTable($table)) {
            return null;
        }

        return $module;
    }

    protected function buildStatsWidget(array $config, array $modules): ?array
    {
        $moduleKeys = $config['modules'] ?? [];
        if (!is_array($moduleKeys)) {
            return null;
        }

        $items = [];
        foreach ($moduleKeys as $moduleKey) {
            if (!is_string($moduleKey) || $moduleKey === '') {
                continue;
            }
            $module = $this->resolveModule($moduleKey, $modules);
            if ($module === null) {
                continue;
            }

            $table = $module['table'];
            $count = DB::table($table)->count();
            $routeName = $module['route'] ?? null;
            $routeName = is_string($routeName) && $routeName !== '' && Route::has($routeName)
                ? $routeName
                : null;

            $items[] = [
                'label' => $module['label'] ?? Str::title(str_replace('_', ' ', $moduleKey)),
                'count' => $count,
                'route' => $routeName,
            ];
        }

        if (empty($items)) {
            return null;
        }

        return [
            'type' => 'stats',
            'items' => $items,
        ];
    }

    protected function buildRecentWidget(array $config, array $modules): ?array
    {
        $moduleKey = $config['module'] ?? null;
        if (!is_string($moduleKey) || $moduleKey === '') {
            return null;
        }

        $module = $this->resolveModule($moduleKey, $modules);
        if ($module === null) {
            return null;
        }

        $limit = isset($config['limit']) ? (int) $config['limit'] : 10;
        if ($limit < 1) {
            $limit = 10;
        }

        $table = $module['table'];
        $columns = Schema::getColumnListing($table);
        $displayColumn = $this->resolveDisplayColumn($columns);
        $dateColumn = in_array('created_at', $columns, true) ? 'created_at' : null;

        $rows = DB::table($table)->orderBy('id', 'desc')->limit($limit)->get();

        $routeName = $module['route'] ?? null;
        $routeName = is_string($routeName) && $routeName !== '' && Route::has($routeName)
            ? $routeName
            : null;

        return [
            'type' => 'recent',
            'label' => $module['label'] ?? Str::title(str_replace('_', ' ', $moduleKey)),
            'rows' => $rows,
            'display_column' => $displayColumn,
            'date_column' => $dateColumn,
            'route' => $routeName,
        ];
    }

    protected function buildStatusSummaryWidget(array $config, array $modules): ?array
    {
        $moduleKey = $config['module'] ?? null;
        if (!is_string($moduleKey) || $moduleKey === '') {
            return null;
        }

        $module = $this->resolveModule($moduleKey, $modules);
        if ($module === null) {
            return null;
        }

        $field = $config['field'] ?? 'status';
        if (!is_string($field) || $field === '') {
            $field = 'status';
        }

        $table = $module['table'];
        if (!Schema::hasColumn($table, $field)) {
            return null;
        }

        $rows = DB::table($table)
            ->select($field, DB::raw('count(*) as count'))
            ->groupBy($field)
            ->get();

        return [
            'type' => 'status_summary',
            'label' => $module['label'] ?? Str::title(str_replace('_', ' ', $moduleKey)),
            'field' => $field,
            'rows' => $rows,
        ];
    }

    protected function buildChartCountWidget(array $config, array $modules): ?array
    {
        $moduleKey = $config['module'] ?? null;
        if (!is_string($moduleKey) || $moduleKey === '') {
            return null;
        }

        $module = $this->resolveModule($moduleKey, $modules);
        if ($module === null) {
            return null;
        }

        $days = isset($config['days']) ? (int) $config['days'] : 14;
        if ($days < 1) {
            $days = 14;
        }

        $table = $module['table'];
        if (!Schema::hasColumn($table, 'created_at')) {
            return null;
        }

        $from = now()->subDays($days - 1)->startOfDay();
        $rows = DB::table($table)
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', $from)
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $max = 0;
        foreach ($rows as $row) {
            $max = max($max, (int) ($row->count ?? 0));
        }

        $points = [];
        foreach ($rows as $row) {
            $count = (int) ($row->count ?? 0);
            $points[] = [
                'day' => $row->day ?? '',
                'count' => $count,
                'percent' => $max > 0 ? round(($count / $max) * 100) : 0,
            ];
        }

        return [
            'type' => 'chart_count_by_day',
            'label' => $module['label'] ?? Str::title(str_replace('_', ' ', $moduleKey)),
            'points' => $points,
        ];
    }

    protected function buildLineChartWidget(array $config, array $modules): ?array
    {
        $moduleKey = $config['module'] ?? null;
        if (!is_string($moduleKey) || $moduleKey === '') {
            return null;
        }

        $module = $this->resolveModule($moduleKey, $modules);
        if ($module === null) {
            return null;
        }

        $days = isset($config['days']) ? (int) $config['days'] : 30;
        if ($days < 2) {
            $days = 30;
        }

        $dateField = $config['date_field'] ?? 'created_at';
        if (!is_string($dateField) || $dateField === '') {
            $dateField = 'created_at';
        }

        $table = $module['table'];
        if (!Schema::hasColumn($table, $dateField)) {
            return null;
        }

        $from = now()->subDays($days - 1)->startOfDay();
        $rows = DB::table($table)
            ->select(DB::raw("DATE($dateField) as bucket"), DB::raw('count(*) as count'))
            ->where($dateField, '>=', $from)
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(string) $row->bucket] = (int) ($row->count ?? 0);
        }

        $max = 0;
        $points = [];
        for ($i = 0; $i < $days; $i++) {
            $day = $from->copy()->addDays($i)->toDateString();
            $count = $counts[$day] ?? 0;
            $max = max($max, $count);
            $points[] = [
                'label' => $day,
                'value' => $count,
            ];
        }

        foreach ($points as $index => $point) {
            $points[$index]['percent'] = $max > 0 ? round(($point['value'] / $max) * 100) : 0;
        }

        return [
            'type' => 'line_chart',
            'label' => $config['label'] ?? ($module['label'] ?? Str::title(str_replace('_', ' ', $moduleKey))),
            'range_label' => $days . ' days',
            'points' => $points,
            'max' => $max,
        ];
    }

    protected function buildBarChartWidget(array $config, array $modules): ?array
    {
        $moduleKey = $config['module'] ?? null;
        $field = $config['field'] ?? null;
        if (!is_string($moduleKey) || $moduleKey === '' || !is_string($field) || $field === '') {
            return null;
        }

        $module = $this->resolveModule($moduleKey, $modules);
        if ($module === null) {
            return null;
        }

        $table = $module['table'];
        if (!Schema::hasColumn($table, $field)) {
            return null;
        }

        $limit = isset($config['limit']) ? (int) $config['limit'] : 8;
        if ($limit < 1) {
            $limit = 8;
        }

        $rows = DB::table($table)
            ->select($field, DB::raw('count(*) as count'))
            ->groupBy($field)
            ->orderByDesc('count')
            ->limit($limit)
            ->get();

        $max = 0;
        foreach ($rows as $row) {
            $max = max($max, (int) ($row->count ?? 0));
        }

        $items = [];
        foreach ($rows as $row) {
            $value = (int) ($row->count ?? 0);
            $items[] = [
                'label' => (string) ($row->{$field} ?? '-'),
                'value' => $value,
                'percent' => $max > 0 ? round(($value / $max) * 100) : 0,
            ];
        }

        if (empty($items)) {
            return null;
        }

        return [
            'type' => 'bar_chart',
            'label' => $config['label'] ?? ($module['label'] ?? Str::title(str_replace('_', ' ', $moduleKey))),
            'items' => $items,
        ];
    }

    protected function buildPieChartWidget(array $config, array $modules): ?array
    {
        $moduleKey = $config['module'] ?? null;
        $field = $config['field'] ?? null;
        if (!is_string($moduleKey) || $moduleKey === '' || !is_string($field) || $field === '') {
            return null;
        }

        $module = $this->resolveModule($moduleKey, $modules);
        if ($module === null) {
            return null;
        }

        $table = $module['table'];
        if (!Schema::hasColumn($table, $field)) {
            return null;
        }

        $rows = DB::table($table)
            ->select($field, DB::raw('count(*) as count'))
            ->groupBy($field)
            ->orderByDesc('count')
            ->get();

        $total = 0;
        foreach ($rows as $row) {
            $total += (int) ($row->count ?? 0);
        }

        if ($total === 0) {
            return null;
        }

        $palette = ['#5b7cfa', '#16a34a', '#f59e0b', '#ef4444', '#a855f7', '#0ea5e9', '#f97316', '#14b8a6'];
        $items = [];
        $index = 0;
        foreach ($rows as $row) {
            $value = (int) ($row->count ?? 0);
            $items[] = [
                'label' => (string) ($row->{$field} ?? '-'),
                'value' => $value,
                'percent' => $total > 0 ? round(($value / $total) * 100) : 0,
                'color' => $palette[$index % count($palette)],
            ];
            $index++;
        }

        return [
            'type' => 'pie_chart',
            'label' => $config['label'] ?? ($module['label'] ?? Str::title(str_replace('_', ' ', $moduleKey))),
            'items' => $items,
        ];
    }

    protected function buildKpiWidget(array $config, array $modules): ?array
    {
        $moduleKey = $config['module'] ?? null;
        if (!is_string($moduleKey) || $moduleKey === '') {
            return null;
        }

        $module = $this->resolveModule($moduleKey, $modules);
        if ($module === null) {
            return null;
        }

        $metric = $config['metric'] ?? 'count';
        if (!is_string($metric) || $metric === '') {
            $metric = 'count';
        }

        $table = $module['table'];
        $value = 0;
        if ($metric === 'sum' || $metric === 'avg') {
            $field = $config['field'] ?? null;
            if (!is_string($field) || $field === '' || !Schema::hasColumn($table, $field)) {
                return null;
            }
            $value = $metric === 'sum'
                ? (float) DB::table($table)->sum($field)
                : (float) DB::table($table)->avg($field);
        } else {
            $metric = 'count';
            $value = (int) DB::table($table)->count();
        }

        $delta = null;
        $trend = null;
        $compareDays = isset($config['compare_days']) ? (int) $config['compare_days'] : 0;
        $dateField = $config['date_field'] ?? 'created_at';
        if ($compareDays > 0 && Schema::hasColumn($table, $dateField)) {
            $currentFrom = now()->subDays($compareDays - 1)->startOfDay();
            $previousFrom = now()->subDays($compareDays * 2 - 1)->startOfDay();
            $previousTo = $currentFrom->copy()->subDay()->endOfDay();

            $current = (int) DB::table($table)->whereBetween($dateField, [$currentFrom, now()])->count();
            $previous = (int) DB::table($table)->whereBetween($dateField, [$previousFrom, $previousTo])->count();

            if ($previous > 0) {
                $deltaValue = (($current - $previous) / $previous) * 100;
                $delta = round($deltaValue, 1);
            } elseif ($current > 0) {
                $delta = 100;
            } else {
                $delta = 0;
            }

            if ($delta > 0) {
                $trend = 'up';
            } elseif ($delta < 0) {
                $trend = 'down';
            } else {
                $trend = 'same';
            }
        }

        return [
            'type' => 'kpi_card',
            'label' => $config['label'] ?? ($module['label'] ?? Str::title(str_replace('_', ' ', $moduleKey))),
            'value' => $metric === 'count' ? (int) $value : round((float) $value, 2),
            'suffix' => $config['suffix'] ?? '',
            'delta' => $delta,
            'trend' => $trend,
            'hint' => $config['hint'] ?? null,
        ];
    }

    protected function buildTableRecentWidget(array $config, array $modules): ?array
    {
        $moduleKey = $config['module'] ?? null;
        if (!is_string($moduleKey) || $moduleKey === '') {
            return null;
        }

        $module = $this->resolveModule($moduleKey, $modules);
        if ($module === null) {
            return null;
        }

        $limit = isset($config['limit']) ? (int) $config['limit'] : 10;
        if ($limit < 1) {
            $limit = 10;
        }

        $table = $module['table'];
        $columns = Schema::getColumnListing($table);
        $selected = $config['columns'] ?? [];
        if (!is_array($selected) || empty($selected)) {
            $display = $this->resolveDisplayColumn($columns);
            $selected = array_values(array_filter(['id', $display, 'created_at']));
        } else {
            $selected = array_values(array_filter($selected, function ($column) use ($columns) {
                return is_string($column) && in_array($column, $columns, true);
            }));
        }

        if (empty($selected)) {
            return null;
        }

        $rows = DB::table($table)->orderBy('id', 'desc')->limit($limit)->get($selected);

        $routeName = $module['route'] ?? null;
        $routeName = is_string($routeName) && $routeName !== '' && Route::has($routeName)
            ? $routeName
            : null;

        return [
            'type' => 'table_recent',
            'label' => $config['label'] ?? ($module['label'] ?? Str::title(str_replace('_', ' ', $moduleKey))),
            'rows' => $rows,
            'columns' => $selected,
            'route' => $routeName,
        ];
    }

    protected function buildTrendCompareWidget(array $config, array $modules): ?array
    {
        $moduleKey = $config['module'] ?? null;
        if (!is_string($moduleKey) || $moduleKey === '') {
            return null;
        }

        $module = $this->resolveModule($moduleKey, $modules);
        if ($module === null) {
            return null;
        }

        $days = isset($config['days']) ? (int) $config['days'] : 7;
        if ($days < 1) {
            $days = 7;
        }

        $dateField = $config['date_field'] ?? 'created_at';
        if (!Schema::hasColumn($module['table'], $dateField)) {
            return null;
        }

        $currentFrom = now()->subDays($days - 1)->startOfDay();
        $previousFrom = now()->subDays(($days * 2) - 1)->startOfDay();
        $previousTo = $currentFrom->copy()->subDay()->endOfDay();

        $current = (int) DB::table($module['table'])->whereBetween($dateField, [$currentFrom, now()])->count();
        $previous = (int) DB::table($module['table'])->whereBetween($dateField, [$previousFrom, $previousTo])->count();

        $delta = null;
        if ($previous > 0) {
            $delta = round((($current - $previous) / $previous) * 100, 1);
        } elseif ($current > 0) {
            $delta = 100;
        } else {
            $delta = 0;
        }

        $trend = $delta > 0 ? 'up' : ($delta < 0 ? 'down' : 'same');

        return [
            'type' => 'trend_compare',
            'label' => $config['label'] ?? ($module['label'] ?? Str::title(str_replace('_', ' ', $moduleKey))),
            'current' => $current,
            'previous' => $previous,
            'delta' => $delta,
            'trend' => $trend,
            'hint' => $days . ' day comparison',
        ];
    }

    protected function resolveDisplayColumn(array $columns): ?string
    {
        $ignore = ['id', 'created_at', 'updated_at', 'deleted_at'];
        foreach ($columns as $column) {
            if (!in_array($column, $ignore, true)) {
                return $column;
            }
        }

        return null;
    }
}
