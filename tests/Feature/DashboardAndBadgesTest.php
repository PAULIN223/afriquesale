<?php

use App\Livewire\Admin\FirebaseNavItems;
use App\Livewire\Admin\LogoutButton;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makeDashAdmin(): User
{
    return User::factory()->create(['role' => 'admin', 'statut' => 'active']);
}

test('dashboard renders with both web and android chart containers', function () {
    $admin = makeDashAdmin();

    $response = $this->actingAs($admin)->get('/admin');
    $response->assertStatus(200);
    $response->assertSee('Commandes Web');
    $response->assertSee('Nouveaux clients Web');
    $response->assertSee('Commandes App Android');
    $response->assertSee('Nouveaux clients App Android');
    // Chaque graphique doit utiliser x-init (survit à wire:navigate), pas un <script> isolé
    $response->assertSee('x-init', false);
});

test('firebase nav items component loads badges asynchronously without error', function () {
    $admin = makeDashAdmin();

    Livewire::actingAs($admin)
        ->test(FirebaseNavItems::class)
        ->assertStatus(200)
        ->call('loadBadges')
        ->assertOk();
});

test('logout button component logs the user out', function () {
    $admin = makeDashAdmin();

    Livewire::actingAs($admin)
        ->test(LogoutButton::class)
        ->call('logout')
        ->assertRedirect('/');

    $this->assertGuest();
});

test('admin pages render without a stale csrf 419 after using the app', function () {
    $admin = makeDashAdmin();

    $this->actingAs($admin)->get('/admin')->assertStatus(200);
    $this->actingAs($admin)->get('/admin/products')->assertStatus(200);
    $this->actingAs($admin)->get('/admin/customers')->assertStatus(200);
});
