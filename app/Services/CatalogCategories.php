<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CatalogCategories
{
    public function names(string $locale): array
    {
        $names = [];

        foreach (DB::table('catalog_categories')->orderBy('slug')->pluck('names', 'slug') as $slug => $translations) {
            $names[$slug] = json_decode($translations, true, flags: JSON_THROW_ON_ERROR)[$locale];
        }

        return $names;
    }

    public function keys(): array
    {
        return DB::table('catalog_categories')->pluck('slug')->all();
    }

    public function create(string $slug, array $names): void
    {
        DB::table('catalog_categories')->insert([
            'slug' => $slug,
            'names' => json_encode($names, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function withExistingCategory(string $slug, callable $operation): mixed
    {
        return DB::transaction(function () use ($slug, $operation) {
            $category = DB::table('catalog_categories')->where('slug', $slug)->lockForUpdate()->first();
            abort_if($category === null, 404);

            return $operation();
        });
    }

    public function deleteIfUnused(string $slug): bool
    {
        return $this->withExistingCategory($slug, function () use ($slug): bool {
            if (DB::table('listings')->where('category', $slug)->exists()
                || DB::table('category_subscriptions')->where('category', $slug)->exists()
                || DB::table('editorial_pages')->where('page', $slug)->exists()) {
                return false;
            }

            DB::table('storefront_menu_items')->where('key', 'category_'.$slug)->delete();
            DB::table('catalog_categories')->where('slug', $slug)->delete();

            return true;
        });
    }
}
