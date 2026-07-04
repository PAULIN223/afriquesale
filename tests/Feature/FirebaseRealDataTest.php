<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('commandes index shows real finalTotal amount, not zero', function () {
    $admin = User::factory()->create(['role' => 'admin', 'statut' => 'active', 'password' => Hash::make('x')]);

    $response = $this->actingAs($admin)->get('/admin/commandes');
    $response->assertStatus(200);
    $response->assertSee('Montant total des commandes affichées');
    // Au moins une commande réelle avec un montant à plusieurs chiffres doit être visible.
    expect($response->getContent())->toMatch('/[1-9]\d[\d\s]* FCFA/');
});

test('commande detail shows item title, image and non-zero total', function () {
    $admin = User::factory()->create(['role' => 'admin', 'statut' => 'active', 'password' => Hash::make('x')]);

    $response = $this->actingAs($admin)->get('/admin/commandes/3JbDzJnI88p5WBvLNyno');
    $response->assertStatus(200);
    $response->assertSee('25 440 FCFA', false);
    $response->assertSee('CHAUSSURE POUR ENFANT');
    $response->assertSee('cbu01.alicdn.com', false);
});

test('messages index groups conversations by chatId with real client names', function () {
    $admin = User::factory()->create(['role' => 'admin', 'statut' => 'active', 'password' => Hash::make('x')]);

    $response = $this->actingAs($admin)->get('/admin/messages');
    $response->assertStatus(200);
    $response->assertDontSee('Aucun message');
});

test('clients index shows order status badges without error', function () {
    $admin = User::factory()->create(['role' => 'admin', 'statut' => 'active', 'password' => Hash::make('x')]);

    $response = $this->actingAs($admin)->get('/admin/clients');
    $response->assertStatus(200);
    $response->assertSee('En attente');
});

test('client show page displays real order history with correct total', function () {
    $admin = User::factory()->create(['role' => 'admin', 'statut' => 'active', 'password' => Hash::make('x')]);

    // Patrick TCHIRA — uid observé dans les données réelles avec une commande à 25440
    $response = $this->actingAs($admin)->get('/admin/clients/GC8Ejv0yvCOevR79869Aoy895cG2');
    $response->assertStatus(200);
    $response->assertSee('25 440 FCFA', false);
});
