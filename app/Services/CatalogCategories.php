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

    public function create(string $slug, array $names, ?array $palette = null): void
    {
        DB::table('catalog_categories')->insert([
            'slug' => $slug,
            'names' => json_encode($names, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'palette' => $palette === null ? null : json_encode($palette, JSON_THROW_ON_ERROR),
        ]);
    }

    public function palette(string $slug): ?array
    {
        $palette = DB::table('catalog_categories')->where('slug', $slug)->value('palette');

        return $palette === null ? null : json_decode($palette, true, flags: JSON_THROW_ON_ERROR);
    }

    public function updatePalette(string $slug, ?array $palette): void
    {
        $this->withExistingCategory($slug, fn () => DB::table('catalog_categories')->where('slug', $slug)
            ->update(['palette' => $palette === null ? null : json_encode($palette, JSON_THROW_ON_ERROR)]));
    }

    public function theme(string $slug): ?array
    {
        $palette = $this->palette($slug);

        return $palette === null ? null : $palette + [
            'on_background' => $this->foreground($palette['background']),
            'on_accent' => $this->foreground($palette['accent']),
        ];
    }

    private function foreground(string $color): string
    {
        [$red, $green, $blue] = array_map(function (string $channel): float {
            $value = hexdec($channel) / 255;

            return $value <= 0.04045 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        }, str_split(substr($color, 1), 2));

        return 0.2126 * $red + 0.7152 * $green + 0.0722 * $blue >= 0.179 ? '#000000' : '#ffffff';
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
