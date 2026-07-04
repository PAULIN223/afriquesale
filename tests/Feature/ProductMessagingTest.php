<?php

use App\Livewire\Client\MessagesThread;
use App\Models\Category;
use App\Models\Message;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makeProduct(): Product
{
    $category = Category::create(['nom' => 'Test Cat', 'slug' => 'test-cat', 'actif' => true]);
    return Product::create([
        'nom' => 'Sac à Main Test', 'prix' => 5000, 'category_id' => $category->id,
        'categorie' => 'Test Cat', 'stock' => 10, 'actif' => true, 'source_row' => 'row_test_1',
    ]);
}

test('visiting messages with a product query param pins it and prefills the body', function () {
    $client  = User::factory()->create(['role' => 'client', 'email_verified_at' => now()]);
    $product = makeProduct();

    Livewire::actingAs($client)
        ->test(MessagesThread::class, ['product' => $product->id])
        ->assertSet('body', "Bonjour, je suis intéressé(e) par cet article : Sac à Main Test");
});

test('sending a message with a pinned product stores the product_id', function () {
    $client  = User::factory()->create(['role' => 'client', 'email_verified_at' => now()]);
    $product = makeProduct();

    Livewire::actingAs($client)
        ->test(MessagesThread::class, ['product' => $product->id])
        ->call('send');

    $message = Message::where('user_id', $client->id)->first();
    expect($message->product_id)->toBe($product->id);
});

test('changing order status sends an automatic message to the client', function () {
    $admin  = User::factory()->create(['role' => 'admin', 'statut' => 'active']);
    $client = User::factory()->create(['role' => 'client']);
    $order  = Order::create([
        'user_id' => $client->id, 'statut' => 'en_attente', 'total' => 10000,
        'numero_commande' => 'CMD-TEST-1', 'adresse_livraison' => ['ville' => 'Yaoundé'],
    ]);

    $this->actingAs($admin)
        ->post("/admin/orders/{$order->id}/statut", ['statut' => 'confirmee'])
        ->assertRedirect();

    $message = Message::where('user_id', $client->id)->where('sender', 'admin')->first();
    expect($message)->not->toBeNull();
    expect($message->body)->toContain('CMD-TEST-1');
    expect($message->is_read)->toBeFalse();
});
