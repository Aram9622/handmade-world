<span class="listing-price">
    @foreach($listing->prices() as $currency => $amount)
        <span class="listing-price-currency">
            @if($listing->discount_percent > 0)
                <del class="original-price">{{ $listing->formatPrice($currency, $amount * 100) }}</del>
            @endif
            <span>{{ $listing->formatPrice($currency, $listing->salePriceInMinorUnits($amount)) }}</span>
        </span>
    @endforeach
    @if($listing->discount_percent > 0)
        <span class="discount" aria-label="{{ __('Скидка :percent процентов', ['percent' => $listing->discount_percent]) }}">−{{ $listing->discount_percent }}%</span>
    @endif
</span>
