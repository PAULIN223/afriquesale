<?php

use App\Livewire\Admin\CustomersIndex;
use App\Livewire\Admin\ParametresForm;
use App\Livewire\Admin\SettingsForm;
use App\Livewire\Auth\LoginForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makeAdmin(): User
{
    return User::factory()->create([
        'role'     => 'admin',
        'statut'   => 'active',
        'password' => Hash::make('salemaket'),
    ]);
}

test('login form authenticates admin and redirects to /admin', function () {
    $admin = makeAdmin();

    Livewire::test(LoginForm::class)
        ->set('email', $admin->email)
        ->set('password', 'salemaket')
        ->call('login')
        ->assertRedirect('/admin');

    $this->assertAuthenticatedAs($admin);
});

test('login form rejects bad credentials without redirect', function () {
    $admin = makeAdmin();

    Livewire::test(LoginForm::class)
        ->set('email', $admin->email)
        ->set('password', 'wrong-password')
        ->call('login')
        ->assertHasErrors('email')
        ->assertNoRedirect();

    $this->assertGuest();
});

test('settings form saves shop settings', function () {
    Livewire::actingAs(makeAdmin())
        ->test(SettingsForm::class)
        ->set('shopName', 'Test Shop Name')
        ->call('save')
        ->assertDispatched('notify');

    expect(\App\Models\Setting::get('shop_name'))->toBe('Test Shop Name');
});

test('parametres form validates required fields', function () {
    Livewire::actingAs(makeAdmin())
        ->test(ParametresForm::class)
        ->set('email', 'not-an-email')
        ->call('save')
        ->assertHasErrors(['email']);
});

test('customers index renders and search filters results', function () {
    Livewire::actingAs(makeAdmin())
        ->test(CustomersIndex::class)
        ->assertStatus(200)
        ->set('search', 'zzz_no_such_customer_zzz')
        ->assertSee('Aucun client');
});

test('customers index toggle statut updates the user', function () {
    $customer = User::factory()->create(['role' => 'client', 'statut' => 'active']);

    Livewire::actingAs(makeAdmin())
        ->test(CustomersIndex::class)
        ->call('toggleStatut', $customer->id)
        ->assertDispatched('notify');

    expect($customer->fresh()->statut)->toBe('blocked');
});

test('classic blade admin pages still render inside the hybrid layout', function () {
    $admin = makeAdmin();

    $this->actingAs($admin)->get('/admin')->assertStatus(200);
    $this->actingAs($admin)->get('/admin/products')->assertStatus(200);
});
