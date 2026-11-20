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

    protected $fillable = ['user_id', 'title', 'category', 'description', 'price', 'contact_email', 'photo_path'];
}
