<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceSetting extends Model
{
    public $timestamps = false;

    protected $casts = [
        'use_default_price' => 'boolean',
        'default_price' => 'integer',
    ];

    public static function current(): self
    {
        return self::findOrFail(1);
    }
}
