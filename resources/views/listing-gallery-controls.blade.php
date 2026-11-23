<div class="listing-gallery-controls" data-gallery-controls hidden>
    <button class="listing-gallery-arrow listing-gallery-prev" type="button" data-gallery-step="-1" aria-label="{{ __('Предыдущее фото') }}">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 5-7 7 7 7"/></svg>
    </button>
    <button class="listing-gallery-arrow listing-gallery-next" type="button" data-gallery-step="1" aria-label="{{ __('Следующее фото') }}">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg>
    </button>
    <span class="listing-gallery-counter" data-gallery-counter aria-live="polite" aria-atomic="true">1 / {{ count($gallery) }}</span>
</div>
