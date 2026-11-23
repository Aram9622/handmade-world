<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Listing extends Model
{
    public const MAX_GALLERY_PHOTOS = 6;

    public const MAX_CHARACTERISTICS = 10;

    public const CURRENCIES = [
        'RUB' => ['column' => 'price', 'symbol' => '₽'],
        'AMD' => ['column' => 'price_amd', 'symbol' => '֏'],
        'USD' => ['column' => 'price_usd', 'symbol' => '$'],
    ];

    public const FULFILLMENT_METHODS = [
        'pickup' => 'Самовывоз',
        'delivery' => 'Доставка',
        'both' => 'Самовывоз и доставка',
    ];

    protected $fillable = ['user_id', 'title', 'category', 'description', 'description_html', 'characteristics', 'gallery', 'price', 'price_amd', 'price_usd', 'discount_percent', 'city', 'fulfillment_method', 'contact_email', 'contact_phone', 'photo_path'];

    protected $attributes = ['discount_percent' => 0];

    protected $casts = ['discount_percent' => 'integer', 'characteristics' => 'array', 'gallery' => 'array'];

    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereNull('hidden_at');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function likes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'listing_likes');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ListingComment::class);
    }

    /** @return array<string, int> */
    public function prices(): array
    {
        $prices = [];
        foreach (self::CURRENCIES as $currency => $definition) {
            $amount = $this->{$definition['column']};
            if ($amount !== null) {
                $prices[$currency] = (int) $amount;
            }
        }

        return $prices;
    }

    public function salePriceInMinorUnits(int $price): int
    {
        return $price * (100 - $this->discount_percent);
    }

    public function formatPrice(string $currency, int $minorUnits): string
    {
        return number_format($minorUnits / 100, $minorUnits % 100 === 0 ? 0 : 2, ',', ' ').' '.self::CURRENCIES[$currency]['symbol'];
    }

    public function formattedSalePrice(): string
    {
        $prices = [];
        foreach ($this->prices() as $currency => $amount) {
            $prices[] = $this->formatPrice($currency, $this->salePriceInMinorUnits($amount));
        }

        return implode(' / ', $prices);
    }
}
