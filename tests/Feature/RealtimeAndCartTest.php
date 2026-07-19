<?php

use App\Livewire\Client\AddToCartButton;
use App\Livewire\Client\CartBadge;
use App\Livewire\Client\MessageBadge;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Message;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makeRtProduct(int $stock = 10): Product
{
    $category = Category::create(['nom' => 'Cat', 'slug' => 'cat', 'actif' => true]);
    return Product::create([
        'nom' => 'Produit Test', 'prix' => 1000, 'category_id' => $category->id,
        'categorie' => 'Cat', 'stock' => $stock, 'actif' => true, 'source_row' => 'row_rt_1',
    ]);
}

test('add to cart button adds item without a full page reload and dispatches cart-updated', function () {
    $client  = User::factory()->create(['role' => 'client', 'email_verified_at' => now()]);
    $product = makeRtProduct();

    Livewire::actingAs($client)
        ->test(AddToCartButton::class, ['product' => $product])
        ->call('add', 2)
        ->assertDispatched('cart-updated');

    $item = CartItem::where('user_id', $client->id)->where('product_id', $product->id)->first();
    expect($item)->not->toBeNull();
    expect($item->quantite)->toBe(2);
});

test('add to cart respects stock limit', function () {
    $client  = User::factory()->create(['role' => 'client', 'email_verified_at' => now()]);
    $product = makeRtProduct(3);

    Livewire::actingAs($client)
        ->test(AddToCartButton::class, ['product' => $product])
        ->call('add', 10);

    $item = CartItem::where('user_id', $client->id)->where('product_id', $product->id)->first();
    expect($item->quantite)->toBe(3);
});

test('cart badge reflects current cart count', function () {
    $client  = User::factory()->create(['role' => 'client', 'email_verified_at' => now()]);
    $product = makeRtProduct();
    CartItem::create(['user_id' => $client->id, 'product_id' => $product->id, 'quantite' => 4]);

    Livewire::actingAs($client)
        ->test(CartBadge::class)
        ->assertSee('4');
});

test('message badge shows unread admin messages for the client', function () {
    $client = User::factory()->create(['role' => 'client', 'email_verified_at' => now()]);
    Message::create(['user_id' => $client->id, 'sender' => 'admin', 'body' => 'Bonjour', 'is_read' => false]);

    Livewire::actingAs($client)
        ->test(MessageBadge::class)
        ->assertSee('1');
});

test('placing an order requires a payment proof photo before it is sent, then notifies the admin via a message', function () {
    Storage::fake('public');

    $client = User::factory()->create(['role' => 'client', 'email_verified_at' => now()]);
    $product = makeRtProduct();
    CartItem::create(['user_id' => $client->id, 'product_id' => $product->id, 'quantite' => 1]);

    $addressResponse = $this->actingAs($client)->post('/checkout', [
        'nom' => 'Test Client', 'email' => 'test@test.com',
        'phone' => '123456789', 'adresse' => 'Rue 1', 'ville' => 'Yaoundé', 'pays' => 'Cameroun',
    ]);

    $addressResponse->assertRedirect(route('checkout.preuve'));
    expect(Order::where('user_id', $client->id)->first())->toBeNull();

    $confirmResponse = $this->actingAs($client)->post('/checkout/confirmer', [
        'preuve' => UploadedFile::fake()->create('preuve.jpg', 100, 'image/jpeg'),
    ]);

    $confirmResponse->assertRedirect();
    $order = Order::where('user_id', $client->id)->first();
    expect($order)->not->toBeNull();
    expect($order->preuve_paiement)->not->toBeNull();
    Storage::disk('public')->assertExists($order->preuve_paiement);

    $message = Message::where('user_id', $client->id)->where('sender', 'client')->first();
    expect($message)->not->toBeNull();
    expect($message->body)->toContain($order->numero_commande);
    expect($message->image_path)->toBe($order->preuve_paiement);
});
