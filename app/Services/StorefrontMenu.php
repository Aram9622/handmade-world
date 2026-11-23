<?php

namespace App\Services;

use App\Http\Middleware\SetLocale;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StorefrontMenu
{
    public function __construct(private readonly CatalogCategories $categories) {}

    public function definitions(): array
    {
        $definitions = config('storefront_menu');
        foreach ($this->categories->names(app()->getLocale()) as $category => $label) {
            $definitions['category_'.$category] ??= [
                'default_position' => 80,
                'label' => $label,
                'route' => 'catalog',
                'parameters' => ['category' => $category],
                'area' => 'categories',
            ];
        }

        return $definitions;
    }

    public function items(bool $includeRemoved = false): Collection
    {
        $stored = DB::table('storefront_menu_items')->get()->keyBy('key');

        $languages = array_keys(SetLocale::LANGUAGES);

        return collect($this->definitions())->map(function (array $definition, string $key) use ($stored, $languages): array {
            $item = $stored->get($key);

            return $definition + [
                'labels' => $item ? json_decode($item->labels, true) : array_map(
                    fn (string $language) => isset($definition['parameters']['category']) ? null : __($definition['label'], [], $language),
                    array_combine($languages, $languages),
                ),
                'position' => $item ? (int) $item->position : $definition['default_position'],
                'enabled' => $item ? (bool) $item->enabled : true,
                'removed' => (bool) ($item->removed ?? false),
            ];
        })->filter(fn (array $item) => $includeRemoved || !$item['removed'])->sortBy('position');
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
            'href' => route($item['route'], $item['parameters'] ?? []).(isset($item['fragment']) ? '#'.$item['fragment'] : ''),
        ])->groupBy('area', preserveKeys: true);
    }

    public function setCategoryPosition(string $category, int $position): void
    {
        $this->categories->withExistingCategory($category, function () use ($category, $position): void {
            $key = 'category_'.$category;
            $item = $this->items(includeRemoved: true)->get($key);
            $item['position'] = $position;

            DB::table('storefront_menu_items')->upsert([$this->row($key, $item)], ['key'], ['position']);
        });
    }

    public function setCategoryRemoved(string $key, bool $removed): void
    {
        $item = $this->items(includeRemoved: true)->get($key);

        $row = $this->row($key, $item) + ['removed' => $removed];
        $columns = ['removed'];
        if (!$removed) {
            $row['enabled'] = true;
            $columns[] = 'enabled';
        }

        DB::table('storefront_menu_items')->upsert([$row], ['key'], $columns);
    }

    public function save(array $items): void
    {
        $rows = [];
        foreach ($items as $key => $item) {
            $rows[] = $this->row($key, $item);
        }

        DB::table('storefront_menu_items')->upsert($rows, ['key'], ['labels', 'position', 'enabled']);
    }

    private function row(string $key, array $item): array
    {
        return [
            'key' => $key,
            'labels' => json_encode($item['labels'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'position' => $item['position'],
            'enabled' => $item['enabled'],
        ];
    }
}
