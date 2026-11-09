<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests can access the login page', function () {
    $this->get('http://localhost/login')->assertOk();
});

test('authenticated users are redirected from the login page to the dashboard', function () {
    $this->actingAs(User::factory()->create())
        ->get('http://localhost/login')
        ->assertRedirect(route('dashboard'));
});

test('authenticated users cannot submit the login form', function () {
    $this->actingAs(User::factory()->create())
        ->post('http://localhost/login')
        ->assertRedirect(route('dashboard'));
});
