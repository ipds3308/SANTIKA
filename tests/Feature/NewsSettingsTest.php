<?php

use App\Models\NewsItem;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

test('admin stores manually entered news data', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post(route('admin.news.store'), [
        'title' => 'Manual 1',
        'url' => 'https://bps.example.test/news/1',
        'image_url' => 'https://bps.example.test/manual-1.jpg',
    ]);

    $response->assertRedirect();

    expect(NewsItem::latest('created_at')->get()->map(fn (NewsItem $item) => [
        $item->title,
        $item->image_url,
    ])->first())->toBe(['Manual 1', 'https://bps.example.test/manual-1.jpg']);
});

test('new news rotates the oldest item after three active items', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    foreach (range(1, 4) as $number) {
        $this->actingAs($admin)->post(route('admin.news.store'), [
            'title' => 'Berita ' . $number,
            'url' => 'https://bps.example.test/news/' . $number,
            'image_url' => 'https://images.example.test/' . $number . '.jpg',
        ])->assertRedirect();
    }

    expect(NewsItem::count())->toBe(3)
        ->and(NewsItem::pluck('title')->all())->not->toContain('Berita 1')
        ->and(NewsItem::pluck('title')->all())->toEqualCanonicalizing(['Berita 2', 'Berita 3', 'Berita 4']);
});

test('admin manual metadata is retained when fetching a news link fails', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Http::fake(['https://blocked.example.test/*' => Http::response('', 503)]);

    $this->actingAs($admin)->post(route('admin.news.store'), [
        'title' => 'Judul Manual',
        'url' => 'https://blocked.example.test/news/1',
        'image_url' => 'https://images.example.test/manual.jpg',
    ])->assertRedirect();

    $item = NewsItem::where('position', 1)->firstOrFail();

    expect($item->title)->toBe('Judul Manual')
        ->and($item->image_url)->toBe('https://images.example.test/manual.jpg');
});

test('connection failures do not crash the news settings endpoint', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Http::fake([
        'https://unstable.example.test/*' => fn () => throw new ConnectionException('SSL connection closed'),
    ]);

    $this->actingAs($admin)->post(route('admin.news.store'), [
        'title' => 'Judul Cadangan',
        'url' => 'https://unstable.example.test/news/1',
        'image_url' => 'https://images.example.test/manual.jpg',
    ])->assertRedirect();

    expect(NewsItem::where('position', 1)->value('title'))->toBe('Judul Cadangan');
});

test('cs users can manage news settings', function () {
    $user = User::factory()->create(['role' => 'cs']);

    $this->actingAs($user)->post(route('admin.news.store'), [
        'title' => 'Berita CS',
        'url' => 'https://bps.example.test/news/1',
        'image_url' => 'https://images.example.test/manual.jpg',
    ])->assertRedirect();

    expect(NewsItem::latest('created_at')->first()->title)->toBe('Berita CS');
});

test('admin can edit an active news item without rotating the collection', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $newsItem = NewsItem::create([
        'position' => 1,
        'title' => 'Judul Lama',
        'url' => 'https://bps.example.test/old',
        'image_url' => 'https://images.example.test/old.jpg',
    ]);
    $createdAt = $newsItem->created_at;

    $this->actingAs($admin)
        ->putJson(route('admin.news.edit', $newsItem->id), [
            'title' => 'Judul Baru',
            'url' => 'https://bps.example.test/new',
            'image_url' => 'https://images.example.test/new.jpg',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'Judul Baru');

    expect(NewsItem::count())->toBe(1)
        ->and($newsItem->fresh()->url)->toBe('https://bps.example.test/new')
        ->and($newsItem->fresh()->created_at->equalTo($createdAt))->toBeTrue();
});