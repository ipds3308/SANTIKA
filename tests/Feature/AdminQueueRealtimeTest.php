<?php

use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin queue fragment preserves its filters and renders only the table', function () {
    foreach (range(1, 16) as $number) {
        Registration::create([
            'nama' => $number === 1 ? 'Pemohon aktif' : 'Pemohon '.$number,
            'email' => "pemohon{$number}@example.com",
            'no_wa' => '081234567890',
            'alamat' => 'Jl. Contoh No. 1',
            'jenis_layanan' => 'Konsultasi Statistik',
            'tanggal' => now()->toDateString(),
            'tanggal_kunjungan' => now('Asia/Jakarta')->toDateString(),
            'nomor_urut' => $number,
            'nomor_antrian' => 'A-'.str_pad((string) $number, 3, '0', STR_PAD_LEFT),
            'status' => 'Menunggu',
        ]);
    }

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->withHeader('X-Queue-Fragment', '1')
        ->get('http://localhost/admin?per_page=15')
        ->assertOk()
        ->assertSee('id="admin-queue-table"', false)
        ->assertSee('Pemohon aktif')
        ->assertSee('Menampilkan 1–15 dari 16 data.')
        ->assertSee('page=2', false)
        ->assertSee('per_page=15', false)
        ->assertDontSee('Filter Tanggal')
        ->assertDontSee('fragment=', false);
});
