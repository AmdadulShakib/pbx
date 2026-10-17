<?php

return array (
  'widgets' => 
  array (
    0 => 
    array (
      'type' => 'stats',
      'modules' => 
      array (
        0 => 'category',
      ),
    ),
    1 => 
    array (
      'type' => 'recent',
      'module' => 'category',
      'limit' => 10,
    ),
    2 => 
    array (
      'type' => 'status_summary',
      'module' => 'category',
      'field' => 'status',
    ),
    3 => 
    array (
      'type' => 'chart_count_by_day',
      'module' => 'category',
      'field' => 'created_at',
      'days' => 14,
    ),
    4 => 
    array (
      'type' => 'line_chart',
      'module' => 'category',
      'days' => 30,
      'label' => 'Activity trend',
    ),
    5 => 
    array (
      'type' => 'bar_chart',
      'module' => 'category',
      'field' => 'status',
      'limit' => 8,
      'label' => 'Top values',
    ),
    6 => 
    array (
      'type' => 'pie_chart',
      'module' => 'category',
      'field' => 'status',
      'label' => 'Status breakdown',
    ),
    7 => 
    array (
      'type' => 'kpi_card',
      'module' => 'category',
      'metric' => 'count',
      'compare_days' => 7,
      'label' => 'New entries',
    ),
    8 => 
    array (
      'type' => 'table_recent',
      'module' => 'category',
      'columns' => 
      array (
        0 => 'id',
        1 => 'name',
        2 => 'created_at',
      ),
      'limit' => 8,
      'label' => 'Latest entries',
    ),
    9 => 
    array (
      'type' => 'trend_compare',
      'module' => 'category',
      'days' => 7,
      'label' => 'This week vs last week',
    ),
  ),
);
