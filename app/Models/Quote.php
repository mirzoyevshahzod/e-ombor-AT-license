<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $fillable = ['text', 'author', 'tags'];

    protected $casts = [
        'tags' => 'array',
    ];
}
