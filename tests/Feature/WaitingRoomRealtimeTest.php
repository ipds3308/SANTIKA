<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('waiting room page loads data and polling refreshes every fifteen seconds', function () {
    Http::fake([
        'calendar.google.com/*' => Http::response("BEGIN:VCALENDAR\r\nEND:VCALENDAR"),
    ]);

    $this->get('http://localhost/ruang-tunggu')
        ->assertOk()
        ->assertSee('assets/admin-dashboard-polling-', false)
        ->assertSee('ambilDataRuangTunggu();', false)
        ->assertDontSee('setInterval(ambilDataRuangTunggu', false);

    $pollingScript = file_get_contents(resource_path('js/admin-dashboard-polling.js'));

    expect($pollingScript)
        ->toContain('const POLL_INTERVAL_MS = 15_000;')
        ->toContain("document.visibilityState !== 'visible'");
});
