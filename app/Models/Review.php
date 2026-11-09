<?php

namespace App\Models;

use App\Events\AdminReviewsUpdated;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected static function booted(): void
    {
        static::saved(function (): void {
            AdminReviewsUpdated::dispatch();
        });

        static::deleted(function (): void {
            AdminReviewsUpdated::dispatch();
        });
    }

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
