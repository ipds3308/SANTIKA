<?php

use App\Models\NewsItem;

test('resolves next image URLs to their source image URL', function () {
    $sourceUrl = 'https://web-api.bps.go.id/image?token=sample-token';
    $optimizedUrl = 'https://magelangkab.bps.go.id/_next/image?url='.urlencode($sourceUrl).'&w=1080&q=75';

    $newsItem = new NewsItem(['image_url' => $optimizedUrl]);

    expect($newsItem->getAttributes()['image_url'])->toBe($sourceUrl)
        ->and($newsItem->image_url)->toBe($sourceUrl)
        ->and($newsItem->toArray()['image_url'])->toBe($sourceUrl);

    $existingNewsItem = new NewsItem;
    $existingNewsItem->setRawAttributes(['image_url' => $optimizedUrl]);

    expect($existingNewsItem->image_url)->toBe($sourceUrl);
});

test('leaves direct image URLs unchanged', function () {
    $sourceUrl = 'https://web-api.bps.go.id/image.jpg';
    $newsItem = new NewsItem(['image_url' => $sourceUrl]);

    expect($newsItem->image_url)->toBe($sourceUrl);
});