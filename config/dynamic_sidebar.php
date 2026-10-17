<?php

return array (
  0 => 
  array (
    'type' => 'route',
    'label' => 'Dashboard',
    'icon' => 'home',
    'route' => 'admin.dashboard',
  ),
  1 => 
  array (
    'type' => 'module',
    'module' => 'category',
    'label' => 'Category',
    'icon' => 'dot',
    'route' => 'admin.category.index',
  ),
  2 => 
  array (
    'type' => 'route',
    'label' => 'Users',
    'icon' => 'users',
    'route' => 'admin.users.index',
  ),
  3 => 
  array (
    'type' => 'group',
    'label' => 'Access Control',
    'icon' => 'lock',
    'items' => 
    array (
      0 => 
      array (
        'type' => 'route',
        'label' => 'Staff',
        'icon' => 'shield',
        'route' => 'admin.staff.index',
      ),
      1 => 
      array (
        'type' => 'route',
        'label' => 'Roles',
        'icon' => 'key',
        'route' => 'admin.roles.index',
      ),
    ),
  ),
);
