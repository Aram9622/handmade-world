<?php

use App\Models\Listing;

$categoryItems = [
    'category_all' => ['default_position' => 10, 'label' => 'Все категории', 'route' => 'catalog', 'area' => 'categories'],
];

foreach (Listing::CATEGORIES as $category => $label) {
    $categoryItems['category_'.$category] = [
        'default_position' => (count($categoryItems) + 1) * 10,
        'label' => $label,
        'route' => 'catalog',
        'parameters' => ['category' => $category],
        'area' => 'categories',
    ];
}

return [
    'home' => ['default_position' => 10, 'label' => 'Главная', 'route' => 'catalog', 'area' => 'main'],
    'categories' => ['default_position' => 20, 'label' => 'Категории каталога', 'route' => 'catalog', 'fragment' => 'category-heading', 'area' => 'main'],
    'catalog' => ['default_position' => 30, 'label' => 'Каталог', 'route' => 'catalog', 'fragment' => 'catalog', 'area' => 'main'],
    'subscriptions' => ['default_position' => 40, 'label' => 'Мои подписки', 'route' => 'subscriptions.index', 'area' => 'main', 'access' => 'auth'],
    'my_items' => ['default_position' => 50, 'label' => 'Мои изделия', 'route' => 'listings.mine', 'area' => 'account', 'access' => 'auth'],
    'admin' => ['default_position' => 60, 'label' => 'Админка', 'route' => 'admin.index', 'area' => 'account', 'access' => 'admin'],
    'login' => ['default_position' => 70, 'label' => 'Войти', 'route' => 'login', 'area' => 'tools', 'access' => 'guest'],
    'sell' => ['default_position' => 80, 'label' => 'Продать изделие', 'route' => 'listings.create', 'area' => 'tools'],
    ...$categoryItems,
];
