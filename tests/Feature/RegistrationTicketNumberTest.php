<?php

use App\Mail\AntrianMail;
use App\Models\Registration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

test('assigns the configured prefix and independent sequence for each service', function () {
    Mail::fake();

    $tanggalKunjungan = Carbon::now('Asia/Jakarta')->nextWeekday()->format('Y-m-d');
    $layananDanPrefix = [
        'Konsultasi Statistik' => 'A',
        'Konsultasi DTSEN' => 'B',
        'Permintaan Data' => 'C',
        'Rekomendasi Kegiatan Statistik' => 'D',
        'Pengaduan' => 'E',
        'Lainnya' => 'F',
    ];

    foreach ($layananDanPrefix as $jenisLayanan => $prefix) {
        $payload = [
            'nama' => 'Pemohon ' . $prefix,
            'email' => strtolower($prefix) . '@example.test',
            'no_wa' => '0812345678' . array_search($prefix, array_values($layananDanPrefix), true),
            'alamat' => 'Alamat Test',
            'jenis_layanan' => $jenisLayanan,
            'tanggal_kunjungan' => $tanggalKunjungan,
        ];

        if ($jenisLayanan === 'Lainnya') {
            $payload['keterangan_lainnya'] = 'Keperluan Umum';
        }

        $response = $this->postJson(route('pendaftaran.store'), $payload);

        $response->assertSuccessful()
            ->assertJsonPath('nomor_antrian', $prefix . '-' . Carbon::parse($tanggalKunjungan)->format('dmy') . '-001');
    }

    $response = $this->postJson(route('pendaftaran.store'), [
        'nama' => 'Pemohon A Berikutnya',
        'email' => 'a-berikutnya@example.test',
        'no_wa' => '0812345680',
        'alamat' => 'Alamat Test',
        'jenis_layanan' => 'Konsultasi Statistik',
        'tanggal_kunjungan' => $tanggalKunjungan,
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('nomor_antrian', 'A-' . Carbon::parse($tanggalKunjungan)->format('dmy') . '-002');

    $response = $this->postJson(route('pendaftaran.store'), [
        'nama' => 'Pemohon E Berikutnya',
        'email' => 'e-berikutnya@example.test',
        'no_wa' => '0812345681',
        'alamat' => 'Alamat Test',
        'jenis_layanan' => 'Pengaduan',
        'tanggal_kunjungan' => $tanggalKunjungan,
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('nomor_antrian', 'E-' . Carbon::parse($tanggalKunjungan)->format('dmy') . '-002');

    Mail::assertSent(AntrianMail::class, 8);
    expect(Registration::where('tanggal_kunjungan', $tanggalKunjungan)->count())->toBe(8);
});

test('does not use the Lainnya detail as the service or ticket prefix', function () {
    Mail::fake();

    $tanggalKunjungan = Carbon::now('Asia/Jakarta')->nextWeekday()->format('Y-m-d');

    $response = $this->postJson(route('pendaftaran.store'), [
        'nama' => 'Pemohon Lainnya',
        'email' => 'lainnya@example.test',
        'no_wa' => '0812345690',
        'alamat' => 'Alamat Test',
        'jenis_layanan' => 'Lainnya',
        'keterangan_lainnya' => 'Legalisasi Dokumen',
        'tanggal_kunjungan' => $tanggalKunjungan,
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('nomor_antrian', 'F-' . Carbon::parse($tanggalKunjungan)->format('dmy') . '-001');

    expect(Registration::first()->jenis_layanan)->toBe('Lainnya: Legalisasi Dokumen');
});

test('requires detail when the Lainnya service is selected', function () {
    Mail::fake();

    $tanggalKunjungan = Carbon::now('Asia/Jakarta')->nextWeekday()->format('Y-m-d');

    $this->postJson(route('pendaftaran.store'), [
        'nama' => 'Pemohon Tanpa Detail',
        'email' => 'tanpa-detail@example.test',
        'no_wa' => '0812345691',
        'alamat' => 'Alamat Test',
        'jenis_layanan' => 'Lainnya',
        'tanggal_kunjungan' => $tanggalKunjungan,
    ])->assertStatus(422)->assertJsonValidationErrors('keterangan_lainnya');
});

test('waiting room returns the latest completed ticket for each service today', function () {
    $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');

    Registration::create([
        'nama' => 'Selesai Lama',
        'email' => 'lama@example.test',
        'no_wa' => '08123456789',
        'alamat' => 'Alamat Test',
        'tanggal' => $today,
        'tanggal_kunjungan' => $today,
        'nomor_urut' => 1,
        'nomor_antrian' => 'A-' . Carbon::parse($today)->format('dmy') . '-001',
        'jenis_layanan' => 'Konsultasi Statistik',
        'status' => 'Selesai',
    ]);

    Registration::create([
        'nama' => 'Selesai Terakhir',
        'email' => 'terakhir@example.test',
        'no_wa' => '08123456790',
        'alamat' => 'Alamat Test',
        'tanggal' => $today,
        'tanggal_kunjungan' => $today,
        'nomor_urut' => 3,
        'nomor_antrian' => 'A-' . Carbon::parse($today)->format('dmy') . '-003',
        'jenis_layanan' => 'Konsultasi Statistik',
        'status' => 'Selesai',
    ]);

    Registration::create([
        'nama' => 'Selesai Kemarin',
        'email' => 'kemarin@example.test',
        'no_wa' => '08123456791',
        'alamat' => 'Alamat Test',
        'tanggal' => $today,
        'tanggal_kunjungan' => Carbon::parse($today)->subDay()->format('Y-m-d'),
        'nomor_urut' => 99,
        'nomor_antrian' => 'A-' . Carbon::parse($today)->subDay()->format('dmy') . '-099',
        'jenis_layanan' => 'Konsultasi Statistik',
        'status' => 'Selesai',
    ]);

    $this->getJson(route('api.ruang.tunggu'))
        ->assertSuccessful()
        ->assertJsonPath('selesai.A', 'A-' . Carbon::parse($today)->format('dmy') . '-003')
        ->assertJsonPath('selesai.B', null)
        ->assertJsonPath('selesai.F', null);
});

test('completed queues cannot be changed through admin status actions', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $registration = Registration::create([
        'nama' => 'Antrian Selesai',
        'email' => 'selesai@example.test',
        'no_wa' => '08123456789',
        'alamat' => 'Alamat Test',
        'tanggal' => Carbon::now('Asia/Jakarta')->format('Y-m-d'),
        'tanggal_kunjungan' => Carbon::now('Asia/Jakarta')->format('Y-m-d'),
        'nomor_urut' => 1,
        'nomor_antrian' => 'A-170926-001',
        'jenis_layanan' => 'Konsultasi Statistik',
        'status' => 'Selesai',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.status', [$registration->id, 'Dipanggil']))
        ->assertRedirect()
        ->assertSessionHas('error', 'Antrian yang sudah selesai tidak dapat diubah statusnya.');

    $this->actingAs($admin)
        ->postJson(route('admin.panggil', $registration->id))
        ->assertStatus(422)
        ->assertJsonPath('message', 'Antrian yang sudah selesai tidak dapat diubah statusnya.');

    expect($registration->fresh()->status)->toBe('Selesai');
});

test('admin ticket updates resend the changed ticket email', function () {
    Mail::fake();

    $admin = User::factory()->create(['role' => 'admin']);
    $tanggalKunjungan = Carbon::now('Asia/Jakarta')->nextWeekday()->format('Y-m-d');
    $registration = Registration::create([
        'nama' => 'Pemohon Lama',
        'email' => 'lama@example.test',
        'no_wa' => '08123456789',
        'alamat' => 'Alamat Lama',
        'tanggal' => Carbon::now('Asia/Jakarta')->format('Y-m-d'),
        'tanggal_kunjungan' => $tanggalKunjungan,
        'nomor_urut' => 1,
        'nomor_antrian' => 'A-' . Carbon::parse($tanggalKunjungan)->format('dmy') . '-001',
        'jenis_layanan' => 'Konsultasi Statistik',
        'status' => 'Menunggu',
    ]);

    $this->actingAs($admin)
        ->postJson(route('admin.update', $registration->id), [
            '_method' => 'PUT',
            'nama' => 'Pemohon Baru',
            'email' => 'baru@example.test',
            'no_wa' => '08123456780',
            'alamat' => 'Alamat Baru',
            'jenis_layanan' => 'Permintaan Data',
            'tanggal_kunjungan' => $tanggalKunjungan,
        ])
        ->assertSuccessful()
        ->assertJsonPath('message', 'Data tiket berhasil diperbarui dan email perubahan telah dikirim ke pemohon.');

    expect($registration->fresh())
        ->email->toBe('baru@example.test')
        ->jenis_layanan->toBe('Permintaan Data')
        ->nomor_antrian->toBe('C-' . Carbon::parse($tanggalKunjungan)->format('dmy') . '-001');

    Mail::assertSent(AntrianMail::class, function (AntrianMail $mail) use ($registration): bool {
        $mail->build();

        return $mail->subject === '[Perubahan Tiket] E-Tiket SANTIKA BPS Kabupaten Magelang - ' . $registration->fresh()->nomor_antrian
            && $mail->isPerubahan
            && $mail->pendaftaran->is($registration->fresh())
            && $mail->pendaftaran->email === 'baru@example.test';
    });
});

test('cs ticket updates resend the changed ticket email', function () {
    Mail::fake();

    $cs = User::factory()->create(['role' => 'cs']);
    $tanggalKunjungan = Carbon::now('Asia/Jakarta')->nextWeekday()->format('Y-m-d');
    $registration = Registration::create([
        'nama' => 'Pemohon Lama CS',
        'email' => 'lama-cs@example.test',
        'no_wa' => '08123456781',
        'alamat' => 'Alamat Lama CS',
        'tanggal' => Carbon::now('Asia/Jakarta')->format('Y-m-d'),
        'tanggal_kunjungan' => $tanggalKunjungan,
        'nomor_urut' => 1,
        'nomor_antrian' => 'A-' . Carbon::parse($tanggalKunjungan)->format('dmy') . '-001',
        'jenis_layanan' => 'Konsultasi Statistik',
        'status' => 'Menunggu',
    ]);

    $this->actingAs($cs)
        ->postJson(route('admin.update', $registration->id), [
            '_method' => 'PUT',
            'nama' => 'Pemohon Baru CS',
            'email' => 'baru-cs@example.test',
            'no_wa' => '08123456782',
            'alamat' => 'Alamat Baru CS',
            'jenis_layanan' => 'Permintaan Data',
            'tanggal_kunjungan' => $tanggalKunjungan,
        ])
        ->assertSuccessful()
        ->assertJsonPath('message', 'Data tiket berhasil diperbarui dan email perubahan telah dikirim ke pemohon.');

    expect($registration->fresh())
        ->email->toBe('baru-cs@example.test')
        ->jenis_layanan->toBe('Permintaan Data')
        ->nomor_antrian->toBe('C-' . Carbon::parse($tanggalKunjungan)->format('dmy') . '-001');

    Mail::assertSent(AntrianMail::class, function (AntrianMail $mail) use ($registration): bool {
        $mail->build();

        return $mail->subject === '[Perubahan Tiket] E-Tiket SANTIKA BPS Kabupaten Magelang - ' . $registration->fresh()->nomor_antrian
            && $mail->isPerubahan
            && $mail->pendaftaran->is($registration->fresh())
            && $mail->pendaftaran->email === 'baru-cs@example.test';
    });
});