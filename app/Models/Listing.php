<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    public const CATEGORIES = [
        'paintings' => 'Картины',
        'ceramics' => 'Керамика',
        'jewelry' => 'Украшения',
        'textile' => 'Текстиль',
        'decor' => 'Декор',
        'other' => 'Другое',
    ];

    public const FULFILLMENT_METHODS = [
        'pickup' => 'Самовывоз',
        'delivery' => 'Доставка',
        'both' => 'Самовывоз и доставка',
    ];

    protected $fillable = ['user_id', 'title', 'category', 'description', 'price', 'discount_percent', 'city', 'fulfillment_method', 'contact_email', 'contact_phone', 'photo_path'];

    protected $attributes = ['discount_percent' => 0];

    protected $casts = ['discount_percent' => 'integer'];

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
