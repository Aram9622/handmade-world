<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use App\Models\Listing;
use App\Services\CatalogCategories;
use App\Services\StorefrontMenu;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StorefrontMenuController extends Controller
{
    public function edit(StorefrontMenu $menu): View
    {
        return view('admin.menu', [
            'items' => $menu->items()->where('area', '!=', 'categories'),
            'languages' => SetLocale::LANGUAGES,
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
        $locales = implode(',', array_keys(SetLocale::LANGUAGES));
        $data = $request->validate([
            'slug' => [
                'required', 'string', 'max:23', 'regex:/^[a-z][a-z0-9-]*$/D',
                Rule::notIn(['home', 'all', ...array_keys(Listing::CATEGORIES)]),
                Rule::unique('catalog_categories', 'slug'),
            ],
            'names' => ['required', "array:$locales", "required_array_keys:$locales"],
            'names.*' => ['required', 'string', 'max:80'],
        ]);

        try {
            $categories->create($data['slug'], $data['names']);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['slug' => __('validation.unique', ['attribute' => __('Адрес категории')])]);
        }

        return to_route('admin.menu.edit')->withFragment('category-menu')->with('status', __('Категория создана.'));
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
