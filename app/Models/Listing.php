<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Listing extends Model
{
    public const FULFILLMENT_METHODS = [
        'pickup' => 'Самовывоз',
        'delivery' => 'Доставка',
        'both' => 'Самовывоз и доставка',
    ];

    protected $fillable = ['user_id', 'title', 'category', 'description', 'price', 'discount_percent', 'city', 'fulfillment_method', 'contact_email', 'contact_phone', 'photo_path'];

    protected $attributes = ['discount_percent' => 0];

    protected $casts = ['discount_percent' => 'integer'];

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

    public function salePriceInKopecks(): int
    {
        return $this->price * (100 - $this->discount_percent);
    }

    public function formattedSalePrice(): string
    {
        $kopecks = $this->salePriceInKopecks();

        return number_format($kopecks / 100, $kopecks % 100 === 0 ? 0 : 2, ',', ' ').' ₽';
    }
}
