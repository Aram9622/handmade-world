<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use App\Services\StorefrontMenu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StorefrontMenuController extends Controller
{
    public function edit(StorefrontMenu $menu): View
    {
        $items = $menu->items(includeRemoved: true);

        return view('admin.menu', [
            'items' => $items->where('removed', false),
            'availableCategories' => $items->where('area', 'categories')->where('removed', true),
            'languages' => SetLocale::LANGUAGES,
        ]);
    }

    public function update(Request $request, StorefrontMenu $menu): RedirectResponse
    {
        $definitions = config('storefront_menu');
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

    public function addCategory(Request $request, StorefrontMenu $menu): RedirectResponse
    {
        $categories = collect(config('storefront_menu'))->where('area', 'categories')->keys()->all();
        $data = $request->validate(['item' => ['required', 'string', Rule::in($categories)]]);
        $menu->setCategoryRemoved($data['item'], false);

        return to_route('admin.menu.edit')->withFragment('category-menu')->with('status', __('Пункт меню добавлен.'));
    }

    public function removeCategory(string $item, StorefrontMenu $menu): RedirectResponse
    {
        abort_unless(config("storefront_menu.$item.area") === 'categories', 404);
        $menu->setCategoryRemoved($item, true);

        return to_route('admin.menu.edit')->withFragment('category-menu')->with('status', __('Пункт меню удалён.'));
    }
}
