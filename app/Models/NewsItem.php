<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsItem extends Model
{
    protected $fillable = [
        'position',
        'url',
        'title',
        'image_url',
    ];
}
