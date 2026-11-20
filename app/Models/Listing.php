<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    public const CATEGORIES = [
        'ceramics' => 'Керамика',
        'jewelry' => 'Украшения',
        'textile' => 'Текстиль',
        'decor' => 'Декор',
        'other' => 'Другое',
    ];

    protected $fillable = ['user_id', 'title', 'category', 'description', 'price', 'discount_percent', 'contact_email', 'photo_path'];

    protected $attributes = ['discount_percent' => 0];

    protected $casts = ['discount_percent' => 'integer'];

    public function formattedSalePrice(): string
    {
        $kopecks = $this->price * (100 - $this->discount_percent);

        return number_format($kopecks / 100, $kopecks % 100 === 0 ? 0 : 2, ',', ' ').' ₽';
    }
}
