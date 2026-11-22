<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use App\Services\StorefrontMenu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontMenuController extends Controller
{
    public function edit(StorefrontMenu $menu): View
    {
        return view('admin.menu', ['items' => $menu->items(), 'languages' => SetLocale::LANGUAGES]);
    }

    public function update(Request $request, StorefrontMenu $menu): RedirectResponse
    {
        $definitions = config('storefront_menu');
        $keys = implode(',', array_keys($definitions));
        $languages = implode(',', array_keys(SetLocale::LANGUAGES));
        $rules = [
            'items' => ["required", "array:$keys", "required_array_keys:$keys"],
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
}
