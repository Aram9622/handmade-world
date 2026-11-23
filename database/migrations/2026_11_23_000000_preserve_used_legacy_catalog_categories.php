<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $legacy = [
            'paintings' => 'Картины',
            'ceramics' => 'Керамика',
            'jewelry' => 'Украшения',
            'textile' => 'Текстиль',
            'decor' => 'Декор',
            'other' => 'Другое',
        ];
        $used = DB::table('listings')->select('category')
            ->union(DB::table('category_subscriptions')->select('category'))
            ->union(DB::table('editorial_pages')->select('page as category'))
            ->pluck('category')->all();
        $menuKeys = DB::table('storefront_menu_items')
            ->whereIn('key', array_map(fn (string $slug) => 'category_'.$slug, array_keys($legacy)))
            ->pluck('key');
        $used = array_merge($used, $menuKeys->map(fn (string $key) => substr($key, strlen('category_')))->all());

        foreach (array_intersect_key($legacy, array_flip($used)) as $slug => $label) {
            DB::table('catalog_categories')->insertOrIgnore([
                'slug' => $slug,
                'names' => json_encode([
                    'ru' => $label,
                    'hy' => __($label, locale: 'hy'),
                    'en' => __($label, locale: 'en'),
                ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            ]);
        }
    }

    public function down(): void
    {
        // Imported categories may have gained new listings or subscriptions.
    }
};
