<?php

namespace App\Services;

use App\Models\EditorialPage;
use App\Models\Listing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class EditorialContent
{
    public function __construct(private readonly CatalogCategories $categories) {}

    public function pages(string $locale): array
    {
        return ['home' => __('Главная', locale: $locale)] + $this->categories->names($locale);
    }

    public function all(string $locale): array
    {
        $savedPages = EditorialPage::where('locale', $locale)->get()->keyBy('page');

        $content = [];
        foreach ($this->pages($locale) as $page => $title) {
            $content[$page] = $this->resolve($page, $locale, $title, $savedPages->get($page));
        }

        return $content;
    }

    public function get(string $page, string $locale): array
    {
        return $this->resolve($page, $locale, $this->pages($locale)[$page], EditorialPage::where(['page' => $page, 'locale' => $locale])->first());
    }

    public function save(string $page, string $locale, array $content, array $uploads): void
    {
        $disk = Storage::disk('local');
        $newImages = [];

        try {
            foreach ($uploads as $slot => $image) {
                $path = $image->store('editorial', 'local');
                if ($path === false) {
                    throw new RuntimeException('Could not save editorial image.');
                }
                $newImages[$slot] = $path;
            }

            $oldImages = DB::transaction(function () use ($page, $locale, $content, $newImages): array {
                $record = EditorialPage::firstOrCreate(
                    ['page' => $page, 'locale' => $locale],
                    ['content' => [], 'images' => []],
                );
                $record = EditorialPage::whereKey($record->id)->lockForUpdate()->firstOrFail();
                $previous = $record->images;
                $record->update(['content' => $content, 'images' => array_replace($previous, $newImages)]);

                return array_intersect_key($previous, $newImages);
            });
        } catch (Throwable $exception) {
            $disk->delete(array_values($newImages));
            throw $exception;
        }

        $disk->delete(array_values($oldImages));
    }

    private function resolve(string $page, string $locale, string $title, ?EditorialPage $record): array
    {
        $translate = fn (string $value) => __($value, locale: $locale);
        $content = [
            'title' => $title,
            'heading' => '',
            'intro' => '',
            'cta' => '',
            'alt' => '',
            'timeline' => [],
            'works' => [],
        ];

        $story = [];
        if ($page === 'home') {
            $content = array_replace($content, [
                'title' => $translate('Сделано'),
                'heading' => $translate('руками.'),
                'intro' => $translate('Картины, керамика и вещи для дома от независимых мастеров.'),
                'cta' => $translate('Смотреть новинки'),
                'alt' => $translate('Коллаж с руками мастера, работающего с глиной'),
                'directory_title' => $translate('Ремёсла и искусство'),
            ]);
        } else {
            $story = config("category_editorial.$page", []);
            $content = array_replace($content, [
                'heading' => $translate($story['heading'] ?? ''),
                'intro' => $translate($story['intro'] ?? 'Объявления мастеров'),
                'cta' => $translate('К объявлениям'),
                'alt' => isset($story['alt']) ? $translate($story['alt']) : $title,
                'history_title' => $translate('История раздела'),
                'gallery_title' => $translate('По мотивам известных работ'),
                'timeline' => array_map(
                    fn (array $entry) => array_combine(['period', 'title', 'description'], array_map($translate, $entry)),
                    $story['timeline'] ?? [],
                ),
                'works' => array_map(
                    fn (array $work) => ['title' => $translate($work[1]), 'museum' => $translate($work[2]), 'url' => $work[3]],
                    $story['works'] ?? [],
                ),
            ]);
        }

        $content = array_replace($content, $record?->content ?? []);
        $content['cover_url'] = $this->imageUrl($page, $locale, 'cover', $record, $page === 'home' || isset(Listing::CATEGORIES[$page]) ? "$page.webp" : 'other.webp');

        foreach ($content['works'] as $index => &$work) {
            $work['image_url'] = $this->imageUrl($page, $locale, "work-$index", $record, $story['works'][$index][0] ?? null);
        }
        unset($work);

        return $content;
    }

    private function imageUrl(string $page, string $locale, string $slot, ?EditorialPage $record, ?string $fallback): ?string
    {
        return isset($record?->images[$slot])
            ? route('editorial.image', [$page, $locale, $slot])
            : ($fallback === null ? null : asset('images/editorial/'.$fallback));
    }
}
