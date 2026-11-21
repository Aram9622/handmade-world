<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EditorialPage extends Model
{
    protected $fillable = ['page', 'locale', 'content', 'images'];

    protected $casts = ['content' => 'array', 'images' => 'array'];
}
