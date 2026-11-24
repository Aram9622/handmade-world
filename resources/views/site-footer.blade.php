<footer class="footer">
    <div class="wrap">
        <div class="footer-copy">
            <strong>Handmade Work</strong>
            <span>{{ __('Площадка для мастеров и ценителей ручной работы') }}</span>
        </div>
        @if($storefrontMenu->has('main'))
            <nav class="footer-links" aria-label="{{ __('Навигация в подвале') }}">
                @include('site-links')
            </nav>
        @endif
    </div>
</footer>
