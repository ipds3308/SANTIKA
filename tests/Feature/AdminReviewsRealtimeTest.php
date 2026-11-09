<?php

use App\Events\AdminReviewsUpdated;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

test('new reviews broadcast an update and the admin endpoint returns only the filtered fragment', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Event::fake([AdminReviewsUpdated::class]);

    foreach (range(1, 16) as $number) {
        Review::create([
            'rating' => ($number % 5) + 1,
            'kritik_saran' => 'Komentar ulasan '.$number,
            'created_at' => now()->subMinutes($number),
            'updated_at' => now()->subMinutes($number),
        ]);
    }

    Event::assertDispatched(AdminReviewsUpdated::class);

    $this->actingAs($admin)
        ->withHeader('X-Reviews-Fragment', '1')
        ->get('http://localhost/admin/ulasan?per_page=15')
        ->assertOk()
        ->assertSee('id="admin-reviews-fragment"', false)
        ->assertSee('Komentar ulasan 1')
        ->assertDontSee('Komentar ulasan 16')
        ->assertSee('Menampilkan 1–15 dari 16 ulasan.')
        ->assertSee('page=2', false)
        ->assertDontSee('Filter Bulan')
        ->assertDontSee('fragment=', false);
});
