<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admins cannot change the role of another administrator', function () {
    $currentAdmin = User::factory()->create(['role' => 'admin']);
    $anotherAdmin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($currentAdmin)
        ->from('http://localhost/rahasia/bps-manajemen-akun')
        ->put("http://localhost/rahasia/bps-manajemen-akun/update-role/{$anotherAdmin->id}", ['role' => 'cs'])
        ->assertRedirect()
        ->assertSessionHas('error', 'Role administrator tidak dapat diubah melalui halaman ini.');

    expect($anotherAdmin->refresh()->role)->toBe('admin');
});

test('admins cannot change their own role', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->from('http://localhost/rahasia/bps-manajemen-akun')
        ->put("http://localhost/rahasia/bps-manajemen-akun/update-role/{$admin->id}", ['role' => 'cs'])
        ->assertRedirect()
        ->assertSessionHas('error', 'Role administrator tidak dapat diubah melalui halaman ini.');

    expect($admin->refresh()->role)->toBe('admin');
});

test('admin account rows do not show role change controls or a protected label', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    $this->get('http://localhost/rahasia/bps-manajemen-akun')
        ->assertOk()
        ->assertDontSee('Demote ke CS')
        ->assertDontSee('Role dilindungi')
        ->assertSee('fa-trash');
});

test('admins can still promote a customer service user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customerService = User::factory()->create(['role' => 'cs']);

    $this->actingAs($admin)
        ->from('http://localhost/rahasia/bps-manajemen-akun')
        ->put("http://localhost/rahasia/bps-manajemen-akun/update-role/{$customerService->id}", ['role' => 'admin'])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($customerService->refresh()->role)->toBe('admin');
});

test('admins cannot assign an unsupported role', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customerService = User::factory()->create(['role' => 'cs']);

    $this->actingAs($admin)
        ->from('http://localhost/rahasia/bps-manajemen-akun')
        ->put("http://localhost/rahasia/bps-manajemen-akun/update-role/{$customerService->id}", ['role' => 'superadmin'])
        ->assertRedirect()
        ->assertSessionHasErrors('role');

    expect($customerService->refresh()->role)->toBe('cs');
});
