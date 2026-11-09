<?php

use App\Events\QueueUpdated;
use App\Models\Registration;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('waiting room receives a public invalidation event when a queue entry changes', function () {
    Event::fake([QueueUpdated::class]);

    $registration = Registration::create([
        'nama' => 'Pemohon Ruang Tunggu',
        'email' => 'pemohon-ruang-tunggu@example.com',
        'no_wa' => '081234567890',
        'alamat' => 'Jl. Contoh No. 1',
        'jenis_layanan' => 'Konsultasi Statistik',
        'tanggal' => now()->toDateString(),
        'tanggal_kunjungan' => now('Asia/Jakarta')->toDateString(),
        'nomor_urut' => 1,
        'nomor_antrian' => 'A-001',
        'status' => 'Menunggu',
    ]);

    Event::assertDispatched(QueueUpdated::class);

    $channels = (new QueueUpdated)->broadcastOn();

    expect($channels)
        ->toHaveCount(2)
        ->and($channels[0])
        ->toBeInstanceOf(PrivateChannel::class)
        ->and($channels[1])
        ->toBeInstanceOf(Channel::class)
        ->and($channels[1]->name)
        ->toBe('waiting-room');

    $registration->update(['status' => 'Dipanggil']);
    Event::assertDispatchedTimes(QueueUpdated::class, 2);
});

test('waiting room page loads initial data and does not poll every few seconds', function () {
    Http::fake([
        'calendar.google.com/*' => Http::response("BEGIN:VCALENDAR\r\nEND:VCALENDAR"),
    ]);

    $this->get('http://localhost/ruang-tunggu')
        ->assertOk()
        ->assertSee('admin-dashboard-realtime.js')
        ->assertSee('ambilDataRuangTunggu();', false)
        ->assertDontSee('setInterval(ambilDataRuangTunggu', false);
});
