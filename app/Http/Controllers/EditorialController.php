<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use App\Models\EditorialPage;
use App\Services\CatalogCategories;
use App\Services\EditorialContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EditorialController extends Controller
{
    public function edit(EditorialContent $editorial, CatalogCategories $categories, string $page = 'home', string $language = 'ru'): View
    {
        $this->validateSelection($editorial, $page, $language);
        $content = $editorial->get($page, $language);

        $pages = $editorial->pages(app()->getLocale());

        $palette = $page === 'home' ? null : $categories->palette($page);

        return view('admin.editorial', compact('page', 'language', 'content', 'pages', 'palette'));
    }

    public function update(Request $request, EditorialContent $editorial, string $page, string $language): RedirectResponse
    {
        $this->validateSelection($editorial, $page, $language);
        $imageRules = ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'];
        $rules = [
            'title' => ['required', 'string', 'max:100'],
            'heading' => ['nullable', 'string', 'max:140'],
            'intro' => ['required', 'string', 'max:1500'],
            'cta' => ['required', 'string', 'max:80'],
            'alt' => ['required', 'string', 'max:180'],
            'cover' => $imageRules,
        ];
        if ($page === 'home') {
            $rules['directory_title'] = ['required', 'string', 'max:140'];
        } else {
            $rules += [
                'seo_title' => ['nullable', 'string', 'max:70'],
                'seo_description' => ['nullable', 'string', 'max:160'],
                'history_title' => ['required', 'string', 'max:140'],
                'gallery_title' => ['required', 'string', 'max:140'],
                'timeline' => ['array:0,1,2,3,4,5'],
                'timeline.*' => ['array:period,title,description'],
                'timeline.*.period' => ['nullable', 'required_with:timeline.*.title,timeline.*.description', 'string', 'max:100'],
                'timeline.*.title' => ['nullable', 'required_with:timeline.*.period,timeline.*.description', 'string', 'max:140'],
                'timeline.*.description' => ['nullable', 'required_with:timeline.*.period,timeline.*.title', 'string', 'max:700'],
                'works' => ['array:0,1,2,3'],
                'works.*' => ['array:title,museum,url,image'],
                'works.*.title' => ['nullable', 'required_with:works.*.museum,works.*.url,works.*.image', 'string', 'max:180'],
                'works.*.museum' => ['nullable', 'string', 'max:120'],
                'works.*.url' => ['nullable', 'url:http,https', 'max:1000'],
                'works.*.image' => $imageRules,
            ];
        }

        $content = $request->validate($rules);
        $uploads = $request->hasFile('cover') ? ['cover' => $content['cover']] : [];
        unset($content['cover']);

        if ($page !== 'home') {
            $content['timeline'] = array_filter($content['timeline'] ?? [], fn (array $entry) => ($entry['title'] ?? '') !== '');
            $content['works'] = array_filter($content['works'] ?? [], fn (array $entry) => ($entry['title'] ?? '') !== '');
            foreach ($content['works'] as $index => &$work) {
                if (isset($work['image'])) {
                    $uploads["work-$index"] = $work['image'];
                }
                unset($work['image']);
            }
            unset($work);
        }

        $editorial->save($page, $language, $content, $uploads);

        return to_route('admin.editorial.edit', [$page, $language])->with('status', __('Изменения сохранены.'));
    }

    public function image(EditorialContent $editorial, string $page, string $language, string $slot): StreamedResponse
    {
        $this->validateSelection($editorial, $page, $language);
        $record = EditorialPage::where(['page' => $page, 'locale' => $language])->firstOrFail();
        $path = $record->images[$slot] ?? null;
        $disk = Storage::disk('local');
        abort_if($path === null || ! $disk->exists($path), 404);

        return $disk->response($path, null, ['Cache-Control' => 'no-cache', 'X-Content-Type-Options' => 'nosniff']);
    }

    private function validateSelection(EditorialContent $editorial, string $page, string $language): void
    {
        abort_unless(isset(SetLocale::LANGUAGES[$language]) && isset($editorial->pages($language)[$page]), 404);
    }
}
