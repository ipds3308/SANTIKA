<?php

use App\Models\NewsItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('public news page loads its ticker and polling entry point', function () {
    Http::fake([
        'calendar.google.com/*' => Http::response("BEGIN:VCALENDAR\r\nEND:VCALENDAR"),
    ]);

    $this->get('http://localhost/')
        ->assertOk()
        ->assertSee('id="newsTicker"', false)
        ->assertSee('assets/admin-dashboard-polling-', false);

    NewsItem::create([
        'position' => 1,
        'url' => 'https://example.com/news',
        'title' => 'Berita untuk API',
        'image_url' => 'https://example.com/news.jpg',
    ]);

    $this->get('http://localhost/api/berita')
        ->assertOk()
        ->assertJsonFragment(['title' => 'Berita untuk API']);
});
