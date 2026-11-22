<?php

namespace App\Services;

use App\Http\Middleware\SetLocale;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StorefrontMenu
{
    public function items(): Collection
    {
        $stored = DB::table('storefront_menu_items')->get()->keyBy('key');

        $languages = array_keys(SetLocale::LANGUAGES);

        return collect(config('storefront_menu'))->map(function (array $definition, string $key) use ($stored, $languages): array {
            $item = $stored->get($key);

            return $definition + [
                'labels' => $item ? json_decode($item->labels, true) : array_map(
                    fn (string $language) => __($definition['label'], [], $language),
                    array_combine($languages, $languages),
                ),
                'position' => $item ? (int) $item->position : $definition['default_position'],
                'enabled' => $item ? (bool) $item->enabled : true,
            ];
        })->sortBy('position');
    }

    public function visible(?User $user): Collection
    {
        return $this->items()->filter(fn (array $item) => $item['enabled'] && match ($item['access'] ?? null) {
            'auth' => $user !== null,
            'guest' => $user === null,
            'admin' => $user?->can('manage-marketplace') ?? false,
            default => true,
        })->map(fn (array $item) => $item + [
            'text' => $item['labels'][app()->getLocale()],
            'href' => route($item['route']).(isset($item['fragment']) ? '#'.$item['fragment'] : ''),
        ])->groupBy('area', preserveKeys: true);
    }

    public function save(array $items): void
    {
        $rows = [];
        foreach ($items as $key => $item) {
            $rows[] = [
                'key' => $key,
                'labels' => json_encode($item['labels'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                'position' => $item['position'],
                'enabled' => $item['enabled'],
            ];
        }

        DB::table('storefront_menu_items')->upsert($rows, ['key'], ['labels', 'position', 'enabled']);
    }
}
