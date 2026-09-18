<?php

use App\Models\Registration;
use App\Models\User;

it('menggunakan created_at sebagai default sort terbaru di dashboard monitoring dan menampilkan sort selector', function () {
    $user = User::factory()->create();

    $beta = Registration::create([
        'nomor_antrian' => 'B-220926-002',
        'nomor_urut' => 2,
        'tanggal' => now()->toDateString(),
        'nama' => 'Beta',
        'email' => 'beta@example.com',
        'no_wa' => '081234567890',
        'alamat' => 'Alamat Beta',
        'jenis_layanan' => 'Konsultasi DTSEN',
        'tanggal_kunjungan' => now()->toDateString(),
        'status' => 'Menunggu',
        'created_at' => now()->subMinutes(5),
        'updated_at' => now()->subMinutes(5),
    ]);

    $alpha = Registration::create([
        'nomor_antrian' => 'A-220926-001',
        'nomor_urut' => 1,
        'tanggal' => now()->toDateString(),
        'nama' => 'Alpha',
        'email' => 'alpha@example.com',
        'no_wa' => '081234567891',
        'alamat' => 'Alamat Alpha',
        'jenis_layanan' => 'Konsultasi Statistik',
        'tanggal_kunjungan' => now()->toDateString(),
        'status' => 'Menunggu',
        'created_at' => now()->subMinutes(1),
        'updated_at' => now()->subMinutes(1),
    ]);

    $response = $this->actingAs($user)->get('/admin');

    $response->assertOk();
    $response->assertSee('Sort By / Urutkan');
    $response->assertSee('Berdasarkan Terbaru [Default]');
    $response->assertSee('Semua Layanan');
    $response->assertSee('Konsultasi Statistik (A)');
    $response->assertSeeInOrder([$alpha->nomor_antrian, $beta->nomor_antrian]);
});

it('mengembalikan quick filter ke semua layanan saat tombol aktif diklik lagi sambil menjaga tanggal dan sort', function () {
    $user = User::factory()->create();

    Registration::create([
        'nomor_antrian' => 'A-220926-001',
        'nomor_urut' => 1,
        'tanggal' => now()->toDateString(),
        'nama' => 'Alpha',
        'email' => 'alpha3@example.com',
        'no_wa' => '081234567894',
        'alamat' => 'Alamat Alpha 3',
        'jenis_layanan' => 'Konsultasi Statistik',
        'tanggal_kunjungan' => now()->toDateString(),
        'status' => 'Menunggu',
        'created_at' => now()->subMinutes(1),
        'updated_at' => now()->subMinutes(1),
    ]);

    $response = $this->actingAs($user)->get('/admin?jenis_layanan=Konsultasi+Statistik&sort=nama&tanggal=' . now()->toDateString());

    $response->assertOk();
    $response->assertSee(route('admin.dashboard', ['sort' => 'nama', 'tanggal' => now()->toDateString()]));
    $response->assertSee('name="jenis_layanan" value="Konsultasi Statistik"', false);
    $response->assertSee('Konsultasi Statistik (A)');
});

it('mengutamakan filter bulan daripada tanggal agar data hari ini tidak ikut tampil saat bulan dipilih', function () {
    $user = User::factory()->create();

    $bulanFilter = now()->subMonth()->format('Y-m');
    $today = now()->toDateString();
    $bulanDate = now()->subMonth()->toDateString();

    Registration::create([
        'nomor_antrian' => 'A-220926-001',
        'nomor_urut' => 1,
        'tanggal' => $today,
        'nama' => 'Hari Ini',
        'email' => 'today@example.com',
        'no_wa' => '081234567895',
        'alamat' => 'Alamat Hari Ini',
        'jenis_layanan' => 'Konsultasi Statistik',
        'tanggal_kunjungan' => $today,
        'status' => 'Menunggu',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Registration::create([
        'nomor_antrian' => 'B-220926-002',
        'nomor_urut' => 2,
        'tanggal' => $bulanDate,
        'nama' => 'Bulan Lalu',
        'email' => 'bulanlalu@example.com',
        'no_wa' => '081234567896',
        'alamat' => 'Alamat Bulan Lalu',
        'jenis_layanan' => 'Pengaduan',
        'tanggal_kunjungan' => $bulanDate,
        'status' => 'Menunggu',
        'created_at' => now()->subMonth(),
        'updated_at' => now()->subMonth(),
    ]);

    $response = $this->actingAs($user)->get('/admin?tanggal=' . $today . '&bulan=' . $bulanFilter);

    $response->assertOk();
    $response->assertSee('Bulan Lalu');
    $response->assertDontSee('Hari Ini');
});

it('mengurutkan data berdasarkan tiket dan menjaga tema layanan yang konsisten', function () {
    $user = User::factory()->create();

    $beta = Registration::create([
        'nomor_antrian' => 'B-220926-002',
        'nomor_urut' => 2,
        'tanggal' => now()->toDateString(),
        'nama' => 'Beta',
        'email' => 'beta2@example.com',
        'no_wa' => '081234567892',
        'alamat' => 'Alamat Beta 2',
        'jenis_layanan' => 'Pengaduan',
        'tanggal_kunjungan' => now()->toDateString(),
        'status' => 'Menunggu',
        'created_at' => now()->subMinutes(10),
        'updated_at' => now()->subMinutes(10),
    ]);

    $alpha = Registration::create([
        'nomor_antrian' => 'A-220926-001',
        'nomor_urut' => 1,
        'tanggal' => now()->toDateString(),
        'nama' => 'Alpha',
        'email' => 'alpha2@example.com',
        'no_wa' => '081234567893',
        'alamat' => 'Alamat Alpha 2',
        'jenis_layanan' => 'Konsultasi Statistik',
        'tanggal_kunjungan' => now()->toDateString(),
        'status' => 'Menunggu',
        'created_at' => now()->subMinutes(1),
        'updated_at' => now()->subMinutes(1),
    ]);

    $response = $this->actingAs($user)->get('/admin?sort=nomor_antrian');

    $response->assertOk();
    $response->assertSeeInOrder([$alpha->nomor_antrian, $beta->nomor_antrian]);
    expect(Registration::serviceBadgeMeta('Konsultasi Statistik')['badgeClass'])->toBe('bg-blue-100 text-blue-800');
    expect(Registration::serviceBadgeMeta('Pengaduan')['badgeClass'])->toBe('bg-red-100 text-red-800');
});
