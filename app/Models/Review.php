<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'registration_id', // Pastikan ini ada di sini
        'rating',
        'kritik_saran',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }
}