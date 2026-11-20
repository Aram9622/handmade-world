<span class="listing-price">
    @if($listing->discount_percent > 0)
        <del class="original-price">{{ number_format($listing->price, 0, ',', ' ') }} ₽</del>
    @endif
    <span>{{ $listing->formattedSalePrice() }}</span>
    @if($listing->discount_percent > 0)
        <span class="discount" aria-label="Скидка {{ $listing->discount_percent }} процентов">−{{ $listing->discount_percent }}%</span>
    @endif
</span>
