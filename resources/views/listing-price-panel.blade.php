@php
    $prices = $listing->prices();
    $preferred = ['ru' => 'RUB', 'hy' => 'AMD', 'en' => 'USD'][app()->getLocale()] ?? 'RUB';
    $currency = isset($prices[$preferred]) ? $preferred : array_key_first($prices);
@endphp

@if($currency !== null)
    <div class="listing-price-panel">
        <div class="listing-price-display">
            <strong>{{ $listing->formatPrice($currency, $listing->salePriceInMinorUnits($prices[$currency])) }}</strong>
            @if($listing->discount_percent > 0)
                <div class="listing-price-discount">
                    <del class="original-price">{{ $listing->formatPrice($currency, $prices[$currency] * 100) }}</del>
                    <span class="discount" aria-label="{{ __('Скидка :percent процентов', ['percent' => $listing->discount_percent]) }}">−{{ $listing->discount_percent }}%</span>
                </div>
            @endif
        </div>
        @if(count($prices) > 1)
            <details class="listing-currency-details">
                <summary>{{ __('Цены в других валютах') }}</summary>
                <dl class="listing-price-alternatives">
                    @foreach(array_diff_key($prices, [$currency => true]) as $code => $amount)
                        <div>
                            <dt>{{ $code }}</dt>
                            <dd>
                                @if($listing->discount_percent > 0)
                                    <del class="original-price">{{ $listing->formatPrice($code, $amount * 100) }}</del>
                                @endif
                                {{ $listing->formatPrice($code, $listing->salePriceInMinorUnits($amount)) }}
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </details>
        @endif
    </div>
@endif
