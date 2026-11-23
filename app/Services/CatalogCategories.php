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
}
