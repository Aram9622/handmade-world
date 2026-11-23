<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use App\Services\CatalogCategories;
use App\Services\StorefrontMenu;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StorefrontMenuController extends Controller
{
    public function edit(StorefrontMenu $menu): View
    {
        $items = $menu->items(includeRemoved: true);
        return view('admin.menu', [
            'items' => $items->where('area', '!=', 'categories')->where('removed', false),
            'languages' => SetLocale::LANGUAGES,
            'categories' => $items->filter(fn (array $item) => isset($item['parameters']['category'])),
        ]);
    }

    public function update(Request $request, StorefrontMenu $menu): RedirectResponse
    {
        $definitions = $menu->definitions();
        $keys = implode(',', array_keys($definitions));
        $required = collect($definitions)->where('area', '!=', 'categories')->keys()->implode(',');
        $languages = implode(',', array_keys(SetLocale::LANGUAGES));
        $rules = [
            'items' => ["required", "array:$keys", "required_array_keys:$required"],
            'items.*' => ['required', 'array:labels,position,enabled'],
            'items.*.labels' => ["required", "array:$languages", "required_array_keys:$languages"],
            'items.*.position' => ['required', 'integer', 'between:0,999'],
            'items.*.enabled' => ['required', 'boolean'],
        ];
        foreach ($definitions as $key => $definition) {
            $rules["items.$key.labels.*"] = [isset($definition['parameters']['category']) ? 'nullable' : 'required', 'string', 'max:80'];
        }
        $data = $request->validate($rules);

        $menu->save($data['items']);

        return to_route('admin.menu.edit')->with('status', __('Изменения сохранены.'));
    }

    public function createCategory(Request $request, CatalogCategories $categories): RedirectResponse
    {
        $slug = $request->input('slug');
        if (is_string($slug)) {
            $request->merge(['slug' => Str::slug($slug)]);
        }

        $locales = implode(',', array_keys(SetLocale::LANGUAGES));
        $data = $request->validate([
            'slug' => [
                'required', 'string', 'max:23', 'regex:/^[a-z][a-z0-9-]*$/D',
                Rule::notIn(['home', 'all']),
                Rule::unique('catalog_categories', 'slug'),
            ],
            'names' => ['required', "array:$locales", "required_array_keys:$locales"],
            'names.*' => ['required', 'string', 'max:80'],
            ...$this->paletteRules($request),
        ], [], ['slug' => __('Адрес категории')]);

        try {
            $categories->create($data['slug'], $data['names'], $request->boolean('custom_palette') ? $data['palette'] : null);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['slug' => __('validation.unique', ['attribute' => __('Адрес категории')])]);
        }

        return to_route('admin.menu.edit')->withFragment('catalog-category-'.$data['slug'])->with('status', __('Категория создана.'));
    }

    public function updateCategoryPalette(Request $request, string $category, string $language, CatalogCategories $categories): RedirectResponse
    {
        abort_unless(isset(SetLocale::LANGUAGES[$language]), 404);
        $data = $request->validate($this->paletteRules($request));
        $categories->updatePalette($category, $request->boolean('custom_palette') ? $data['palette'] : null);

        return to_route('admin.editorial.edit', [$category, $language])->withFragment('category-palette')
            ->with('status', __('Изменения сохранены.'));
    }

    private function paletteRules(Request $request): array
    {
        $required = Rule::requiredIf($request->boolean('custom_palette'));
        $color = [$required, 'nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/D'];

        return [
            'custom_palette' => ['sometimes', 'boolean'],
            'palette' => [$required, 'array:background,accent'],
            'palette.background' => $color,
            'palette.accent' => $color,
        ];
    }

    public function orderCategories(Request $request, StorefrontMenu $menu): RedirectResponse
    {
        $data = $request->validate([
            'positions' => ['required', 'array'],
            'positions.*' => ['required', 'integer', 'between:0,999'],
        ]);

        $menu->setCategoryPositions($data['positions']);

        return to_route('admin.menu.edit')->withFragment('category-menu')
            ->with('status', __('Изменения сохранены.'));
    }

    public function deleteCategory(string $category, CatalogCategories $categories): RedirectResponse
    {
        $back = to_route('admin.menu.edit')->withFragment('category-menu');

        if (!$categories->deleteIfUnused($category)) {
            return $back->with('category_delete_error', __('Категорию используют объявления, подписки или сохранённый контент.'));
        }

        return $back->with('status', __('Категория удалена.'));
    }

    public function addCategory(Request $request, StorefrontMenu $menu): RedirectResponse
    {
        $categories = collect($menu->definitions())->where('area', 'categories')->keys()->all();
        $data = $request->validate(['item' => ['required', 'string', Rule::in($categories)]]);
        $menu->setCategoryRemoved($data['item'], false);

        return to_route('admin.menu.edit')->withFragment('category-menu')->with('status', __('Пункт меню добавлен.'));
    }

    public function removeCategory(string $item, StorefrontMenu $menu): RedirectResponse
    {
        abort_unless(($menu->definitions()[$item]['area'] ?? null) === 'categories', 404);
        $menu->setCategoryRemoved($item, true);

        return to_route('admin.menu.edit')->withFragment('category-menu')->with('status', __('Пункт меню удалён.'));
    }
}
