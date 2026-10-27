<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class NewsItem extends Model
{
    protected $fillable = [
        'position',
        'url',
        'title',
        'image_url',
    ];

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => self::resolveImageUrl($value),
            set: fn (?string $value): ?string => self::resolveImageUrl($value),
        );
    }

    private static function resolveImageUrl(?string $imageUrl): ?string
    {
        if ($imageUrl === null) {
            return null;
        }

        $parsedUrl = parse_url($imageUrl);

        if (! is_array($parsedUrl) || ($parsedUrl['path'] ?? null) !== '/_next/image' || ! isset($parsedUrl['query'])) {
            return $imageUrl;
        }

        parse_str($parsedUrl['query'], $query);
        $sourceUrl = $query['url'] ?? null;
        $scheme = is_string($sourceUrl) ? parse_url($sourceUrl, PHP_URL_SCHEME) : null;

        if (! is_string($sourceUrl) || ! filter_var($sourceUrl, FILTER_VALIDATE_URL) || ! in_array($scheme, ['http', 'https'], true)) {
            return $imageUrl;
        }

        return $sourceUrl;
    }
}
