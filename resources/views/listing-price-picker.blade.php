@php
    $prices = $listing->prices();
    $preferred = ['ru' => 'RUB', 'hy' => 'AMD', 'en' => 'USD'][app()->getLocale()] ?? 'RUB';
    $currency = isset($prices[$preferred]) ? $preferred : array_key_first($prices);
@endphp

@if($currency !== null)
    <div class="listing-price-panel" data-price-picker>
        <div class="listing-price-display" aria-live="polite" aria-atomic="true">
            <strong data-sale-price>{{ $listing->formatPrice($currency, $listing->salePriceInMinorUnits($prices[$currency])) }}</strong>
            @if($listing->discount_percent > 0)
                <div class="listing-price-discount">
                    <del class="original-price" data-original-price>{{ $listing->formatPrice($currency, $prices[$currency] * 100) }}</del>
                    <span class="discount" aria-label="{{ __('Скидка :percent процентов', ['percent' => $listing->discount_percent]) }}">−{{ $listing->discount_percent }}%</span>
                </div>
            @endif
        </div>
        @if(count($prices) > 1)
            <label class="listing-currency-field" data-currency-field hidden>
                <span>{{ __('Валюта') }}</span>
                <select name="display_currency" data-currency-select>
                    @foreach($prices as $code => $amount)
                        <option value="{{ $code }}" @selected($code === $currency)
                            data-sale="{{ $listing->formatPrice($code, $listing->salePriceInMinorUnits($amount)) }}"
                            data-original="{{ $listing->formatPrice($code, $amount * 100) }}">{{ $code }}</option>
                    @endforeach
                </select>
            </label>
            <noscript>
                <div class="listing-price-alternatives">
                    @foreach(array_diff_key($prices, [$currency => true]) as $code => $amount)
                        <span>{{ $listing->formatPrice($code, $listing->salePriceInMinorUnits($amount)) }}</span>
                    @endforeach
                </div>
            </noscript>
        @endif
    </div>
@endif
