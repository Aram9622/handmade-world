<?php

namespace Tests;

use App\Services\CatalogCategories;

trait WithCatalogCategories
{
    protected function setUpWithCatalogCategories(): void
    {
        $catalog = app(CatalogCategories::class);
        foreach ([
            'paintings' => 'Картины',
            'ceramics' => 'Керамика',
            'jewelry' => 'Украшения',
            'textile' => 'Текстиль',
            'decor' => 'Декор',
            'other' => 'Другое',
        ] as $slug => $label) {
            $catalog->create($slug, [
                'ru' => $label,
                'hy' => __($label, locale: 'hy'),
                'en' => __($label, locale: 'en'),
            ]);
        }
    }
}
