<?php

use App\Models\Registration;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function createRegistrationsForPagination(int $count): void
{
    $visitDate = Carbon::now('Asia/Jakarta')->toDateString();

    foreach (range(1, $count) as $number) {
        Registration::create([
            'nama' => 'Pemohon '.$number,
            'email' => "pemohon{$number}@example.com",
            'no_wa' => '081234567890',
            'alamat' => 'Jl. Contoh No. 1',
            'jenis_layanan' => 'Konsultasi Statistik',
            'tanggal' => $visitDate,
            'tanggal_kunjungan' => $visitDate,
            'nomor_urut' => $number,
            'nomor_antrian' => 'A-'.str_pad((string) $number, 3, '0', STR_PAD_LEFT),
            'status' => 'Menunggu',
        ]);
    }
}

function createReviewsForPagination(int $count): void
{
    foreach (range(1, $count) as $number) {
        Review::create([
            'rating' => 5,
            'kritik_saran' => 'Ulasan nomor '.$number,
            'created_at' => now()->subMinutes($number),
            'updated_at' => now()->subMinutes($number),
        ]);
    }
}

test('admin queue defaults to 15 items and supports other page sizes', function () {
    Cache::flush();
    Http::fake([
        'calendar.google.com/*' => Http::response("BEGIN:VCALENDAR\r\nEND:VCALENDAR"),
    ]);

    createRegistrationsForPagination(51);
    $this->actingAs(User::factory()->create(['role' => 'admin']));

    $this->get('http://localhost/admin')
        ->assertOk()
        ->assertSee('Pemohon 1')
        ->assertDontSee('Pemohon 51')
        ->assertSee('name="per_page"', false)
        ->assertSee('value="15" selected', false)
        ->assertSee('col-12 col-sm-6 col-lg-4', false)
        ->assertSee('col-12 col-sm-6 col-lg-1', false)
        ->assertSee('col-12 col-sm-6 col-lg-3 d-flex align-items-end gap-2', false)
        ->assertSee('page=2', false)
        ->assertSee('Menampilkan 1–15 dari 51 data.')
        ->assertSee('Navigasi pagination')
        ->assertDontSee('Showing 1 to 15 of 51 results');

    $this->get('http://localhost/admin?per_page=30')
        ->assertOk()
        ->assertSee('Pemohon 30')
        ->assertDontSee('Pemohon 31')
        ->assertSee('value="30" selected', false)
        ->assertSee('per_page=30', false);

    $this->get('http://localhost/admin?per_page=50')
        ->assertOk()
        ->assertSee('Pemohon 50')
        ->assertDontSee('Pemohon 51')
        ->assertSee('value="50" selected', false);
});

test('admin queue can show all filtered results', function () {
    Cache::flush();
    Http::fake([
        'calendar.google.com/*' => Http::response("BEGIN:VCALENDAR\r\nEND:VCALENDAR"),
    ]);

    createRegistrationsForPagination(51);
    $this->actingAs(User::factory()->create(['role' => 'admin']));

    $this->get('http://localhost/admin?per_page=all')
        ->assertOk()
        ->assertSee('Pemohon 51')
        ->assertSee('Menampilkan seluruh 51 data.')
        ->assertSee('value="all" selected', false);
});

test('reviews default to 15 items and support other page sizes', function () {
    createReviewsForPagination(51);
    $this->actingAs(User::factory()->create(['role' => 'admin']));

    $this->get('http://localhost/admin/ulasan')
        ->assertOk()
        ->assertSee('Ulasan nomor 1')
        ->assertDontSee('Ulasan nomor 51')
        ->assertSee('name="per_page"', false)
        ->assertSee('value="15" selected', false)
        ->assertSee('page=2', false)
        ->assertSee('Menampilkan 1–15 dari 51 ulasan.')
        ->assertSee('Navigasi pagination')
        ->assertDontSee('Showing 1 to 15 of 51 results');

    $this->get('http://localhost/admin/ulasan?per_page=30')
        ->assertOk()
        ->assertSee('Ulasan nomor 30')
        ->assertSee('value="30" selected', false);

    $this->get('http://localhost/admin/ulasan?per_page=50')
        ->assertOk()
        ->assertSee('Ulasan nomor 50')
        ->assertSee('value="50" selected', false);
});

test('reviews can show all results and retain month filters', function () {
    createReviewsForPagination(16);
    $this->actingAs(User::factory()->create(['role' => 'admin']));

    $month = now()->format('Y-m');

    $this->get("http://localhost/admin/ulasan?bulan={$month}&per_page=15")
        ->assertOk()
        ->assertSee('page=2', false)
        ->assertSee("bulan={$month}", false);

    $this->get('http://localhost/admin/ulasan?per_page=all')
        ->assertOk()
        ->assertSee('Ulasan nomor 16')
        ->assertSee('Menampilkan seluruh 16 ulasan.')
        ->assertSee('value="all" selected', false);
});

test('list endpoints reject unsupported page sizes', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));

    $this->get('http://localhost/admin?per_page=100')
        ->assertRedirect()
        ->assertSessionHasErrors('per_page');

    $this->get('http://localhost/admin/ulasan?per_page=100')
        ->assertRedirect()
        ->assertSessionHasErrors('per_page');
});
