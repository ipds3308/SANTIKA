<?php

use App\Events\NewsUpdated;
use App\Models\NewsItem;
use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('saving and deleting news dispatches a public update event', function () {
    Event::fake([NewsUpdated::class]);

    $newsItem = NewsItem::create([
        'position' => 1,
        'url' => 'https://example.com/news',
        'title' => 'Berita baru',
        'image_url' => 'https://example.com/news.jpg',
    ]);

    Event::assertDispatched(NewsUpdated::class);

    $channels = (new NewsUpdated)->broadcastOn();
    expect($channels)
        ->toHaveCount(1)
        ->and($channels[0])
        ->toBeInstanceOf(Channel::class)
        ->and($channels[0]->name)
        ->toBe('news');

    Event::assertDispatchedTimes(NewsUpdated::class, 1);
    $newsItem->delete();
    Event::assertDispatchedTimes(NewsUpdated::class, 2);
});

test('public news page refreshes its ticker only on events', function () {
    Http::fake([
        'calendar.google.com/*' => Http::response("BEGIN:VCALENDAR\r\nEND:VCALENDAR"),
    ]);

    $this->get('http://localhost/')
        ->assertOk()
        ->assertSee('id="newsTicker"', false)
        ->assertSee('admin-dashboard-realtime.js')
        ->assertDontSee('setInterval(refreshNews', false);

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
