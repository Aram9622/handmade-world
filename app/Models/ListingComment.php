<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingComment extends Model
{
    protected $fillable = ['user_id', 'body'];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
