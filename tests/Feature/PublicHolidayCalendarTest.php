<?php

use App\Http\Controllers\RegistrationController;
use App\Models\Registration;
use App\Models\User;
use App\Services\IndonesianPublicHolidayCalendar;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function validRegistrationPayload(string $visitDate): array
{
    return [
        'nama' => 'Pemohon Contoh',
        'email' => 'pemohon@example.com',
        'no_wa' => '081234567890',
        'alamat' => 'Jl. Contoh No. 1',
        'jenis_layanan' => 'Konsultasi Statistik',
        'tanggal_kunjungan' => $visitDate,
    ];
}

test('rejects a weekend visit date', function () {
    Http::preventStrayRequests();

    $saturday = Carbon::today('Asia/Jakarta')->next(Carbon::SATURDAY)->toDateString();

    $this->from('/')
        ->post('/pendaftaran', validRegistrationPayload($saturday))
        ->assertRedirect('/')
        ->assertSessionHasErrors([
            'tanggal_kunjungan' => 'Tanggal kunjungan hanya tersedia pada hari Senin sampai Jumat.',
        ]);
});

test('checks whether an email is already registered for the selected visit date', function () {
    Registration::create([
        'nama' => 'Pemohon Contoh',
        'email' => 'pemohon@example.com',
        'no_wa' => '081234567890',
        'tanggal' => '2026-10-27',
        'tanggal_kunjungan' => '2026-10-29',
        'nomor_urut' => 1,
        'nomor_antrian' => 'A-291026-001',
        'status' => 'Menunggu',
    ]);

    $this->postJson(route('pendaftaran.check-email'), [
        'email' => 'pemohon@example.com',
        'tanggal_kunjungan' => '2026-10-29',
    ])->assertSuccessful()->assertExactJson(['already_registered' => true]);

    $this->postJson(route('pendaftaran.check-email'), [
        'email' => 'pemohon@example.com',
        'tanggal_kunjungan' => '2026-10-30',
    ])->assertSuccessful()->assertExactJson(['already_registered' => false]);
});

test('rejects a duplicate registration during final submission', function () {
    Cache::flush();
    Http::fake([
        'calendar.google.com/*' => Http::response("BEGIN:VCALENDAR\r\nEND:VCALENDAR"),
    ]);

    Registration::create([
        'nama' => 'Pemohon Contoh',
        'email' => 'pemohon@example.com',
        'no_wa' => '081234567890',
        'tanggal' => '2026-10-27',
        'tanggal_kunjungan' => '2026-10-29',
        'nomor_urut' => 1,
        'nomor_antrian' => 'A-291026-001',
        'status' => 'Menunggu',
    ]);

    $this->postJson('/pendaftaran', validRegistrationPayload('2026-10-29'))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);

    $this->assertDatabaseCount('registrations', 1);
});

test('validates email and visit date on the duplicate-check endpoint', function () {
    $this->postJson(route('pendaftaran.check-email'), [
        'email' => 'not-an-email',
        'tanggal_kunjungan' => '29-10-2026',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'tanggal_kunjungan']);
});

test('rejects HTML tags and does not store the registration', function () {
    Cache::flush();
    Http::fake([
        'calendar.google.com/*' => Http::response("BEGIN:VCALENDAR\r\nEND:VCALENDAR"),
    ]);

    $monday = Carbon::today('Asia/Jakarta')->next(Carbon::MONDAY)->toDateString();
    $payload = validRegistrationPayload($monday);
    $payload['nama'] = '<b>Siti Aminah</b>';
    $payload['alamat'] = '<script>alert(1)</script>Jl. Merdeka No. 1';

    $this->from('/')
        ->post('/pendaftaran', $payload)
        ->assertRedirect('/')
        ->assertSessionHasErrors(['nama', 'alamat']);

    $this->assertDatabaseCount('registrations', 0);
});

test('rejects names containing characters outside the allowed set', function () {
    Http::preventStrayRequests();

    $saturday = Carbon::today('Asia/Jakarta')->next(Carbon::SATURDAY)->toDateString();
    $payload = validRegistrationPayload($saturday);
    $payload['nama'] = 'Siti123';

    $this->from('/')
        ->post('/pendaftaran', $payload)
        ->assertRedirect('/')
        ->assertSessionHasErrors(['nama', 'tanggal_kunjungan']);
});

test('rejects a visit date marked as an Indonesian public holiday', function () {
    Cache::flush();
    Http::preventStrayRequests();

    $holiday = Carbon::today('Asia/Jakarta')->next(Carbon::MONDAY);
    $holidayStart = $holiday->format('Ymd');
    $holidayEnd = $holiday->copy()->addDay()->format('Ymd');

    Http::fake([
        'calendar.google.com/*' => Http::response(implode("\r\n", [
            'BEGIN:VCALENDAR',
            'BEGIN:VEVENT',
            "DTSTART;VALUE=DATE:{$holidayStart}",
            "DTEND;VALUE=DATE:{$holidayEnd}",
            'SUMMARY:Libur Nasional',
            'STATUS:CONFIRMED',
            'END:VEVENT',
            'END:VCALENDAR',
        ])),
    ]);

    $this->from('/')
        ->post('/pendaftaran', validRegistrationPayload($holiday->toDateString()))
        ->assertRedirect('/')
        ->assertSessionHasErrors([
            'tanggal_kunjungan' => 'Tanggal kunjungan merupakan hari libur nasional: Libur Nasional.',
        ]);
});

test('renders the custom calendar and its available holiday data', function () {
    $html = view('pages.pendaftaran', [
        'berita' => collect(),
        'publicHolidays' => ['2030-01-02' => 'Libur Nasional'],
        'holidayCalendarAvailable' => true,
    ])->render();

    expect($html)
        ->toContain('id="calendarTanggalKunjungan"')
        ->toContain('id="daysTanggalKunjungan"')
        ->toContain('Satu alamat email hanya dapat digunakan satu kali untuk tanggal kunjungan yang sama.')
        ->toContain('bootstrap@5.3.0/dist/css/bootstrap.min.css')
        ->toContain('sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM')
        ->toContain('font-awesome/6.4.0/css/all.min.css')
        ->toContain('sha384-iw3OoTErCYJJB9mCa8LNS2hbsQ7M3C0EpIsO/H5+EGAkPGc6rk+V8i04oW/K5xq0')
        ->toContain('bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js')
        ->toContain('sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz')
        ->toContain('sweetalert2@11.26.25/dist/sweetalert2.all.min.js')
        ->toContain('sha384-nLoOnA/BDh8A/jxqtckg4DumuCGOBYUnNJLZdQz/zfYNp3wcjGSoWTAzgko06G/2')
        ->toContain('2030-01-02')
        ->toContain('Libur Nasional');
});

test('renders the dashboard with public holiday calendar data', function () {
    Cache::flush();
    Http::fake([
        'calendar.google.com/*' => Http::response(implode("\r\n", [
            'BEGIN:VCALENDAR',
            'BEGIN:VEVENT',
            'DTSTART;VALUE=DATE:20300102',
            'DTEND;VALUE=DATE:20300103',
            'SUMMARY:Libur Nasional',
            'STATUS:CONFIRMED',
            'END:VEVENT',
            'END:VCALENDAR',
        ])),
    ]);

    $this->actingAs(User::factory()->create(['role' => 'admin']));

    $response = app()->call(
        [app(RegistrationController::class), 'index'],
        ['request' => Request::create('/admin')],
    );

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getContent())->toContain('data-dashboard-date-picker')
        ->and($response->getContent())->toContain('2030-01-02')
        ->and($response->getContent())->toContain('Libur Nasional')
        ->and($response->getContent())->not->toContain('Kalender hari libur nasional tidak dapat dimuat saat ini.');
});

test('opens the waiting room with today selected and holiday data available', function () {
    Cache::flush();
    Http::fake([
        'calendar.google.com/*' => Http::response("BEGIN:VCALENDAR\r\nEND:VCALENDAR"),
    ]);
    $this->actingAs(User::factory()->create(['role' => 'admin']));

    $response = app(RegistrationController::class)->ruangTunggu(
        app(IndonesianPublicHolidayCalendar::class),
    );
    $html = $response->render();

    expect($html)
        ->toContain('value="'.Carbon::now('Asia/Jakarta')->format('Y-m-d').'"')
        ->toContain('waitingRoomHolidays')
        ->not->toContain('Kalender hari libur nasional tidak dapat dimuat saat ini.');
});

test('returns waiting-room data for the date selected in the date picker', function () {
    Registration::create([
        'nama' => 'Pemohon Terpilih',
        'email' => 'terpilih@example.com',
        'no_wa' => '081234567890',
        'tanggal' => '2026-10-27',
        'tanggal_kunjungan' => '2026-10-29',
        'nomor_urut' => 1,
        'nomor_antrian' => 'A-291026-001',
        'status' => 'Menunggu',
    ]);
    Registration::create([
        'nama' => 'Pemohon Tanggal Lain',
        'email' => 'tanggal-lain@example.com',
        'no_wa' => '081234567891',
        'tanggal' => '2026-10-27',
        'tanggal_kunjungan' => '2026-10-30',
        'nomor_urut' => 1,
        'nomor_antrian' => 'A-301026-001',
        'status' => 'Menunggu',
    ]);

    $response = app(RegistrationController::class)->apiRuangTunggu(
        Request::create('/api/ruang-tunggu-data', 'GET', ['tanggal' => '2026-10-29']),
    );

    expect($response->getData(true)['menunggu'])
        ->toHaveCount(1)
        ->and($response->getData(true)['menunggu'][0]['nama'])->toBe('Pemohon Terpilih');
});
