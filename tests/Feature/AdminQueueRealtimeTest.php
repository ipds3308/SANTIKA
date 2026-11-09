<?php

use App\Events\QueueUpdated;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

test('admin queue fragment preserves its filters and renders only the table', function () {
    Event::fake([QueueUpdated::class]);

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

    Event::assertDispatched(QueueUpdated::class);

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

test('only admin and cs accounts can authorize the private queue channel', function (string $role, bool $authorized) {
    config([
        'broadcasting.default' => 'reverb',
        'broadcasting.connections.reverb.key' => 'test-key',
        'broadcasting.connections.reverb.secret' => 'test-secret',
        'broadcasting.connections.reverb.app_id' => 'test-app',
    ]);
    Broadcast::getFacadeRoot()->forgetDrivers();
    require base_path('routes/channels.php');

    $response = $this->actingAs(User::factory()->create(['role' => $role]))
        ->postJson('http://localhost/broadcasting/auth', [
            'socket_id' => '123.456',
            'channel_name' => 'private-admin.dashboard',
        ]);

    if ($authorized) {
        $response->assertSuccessful();

        return;
    }

    $response->assertForbidden();
})->with([
    'admin' => ['admin', true],
    'cs' => ['cs', true],
    'other role' => ['pemohon', false],
]);
