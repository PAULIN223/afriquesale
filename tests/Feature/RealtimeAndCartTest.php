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

test('selected color variant keeps the chosen color and uses the variant price in cart and order', function () {
    Storage::fake('public');

    $client = User::factory()->create(['role' => 'client', 'email_verified_at' => now()]);
    $category = Category::create(['nom' => 'Textile', 'slug' => 'textile', 'actif' => true]);

    $selectedVariant = Product::create([
        'nom' => 'Robe en coton',
        'description' => 'Robe en coton',
        'prix' => 10000,
        'discounted_price' => 8000,
        'category_id' => $category->id,
        'categorie' => 'Textile',
        'product_code' => 'SKU-ROBE',
        'color' => 'Noir',
        'stock' => 5,
        'actif' => true,
        'source_row' => 'row_variant_1',
    ]);

    Livewire::actingAs($client)
        ->test(AddToCartButton::class, ['product' => $selectedVariant])
        ->call('add', 2, $selectedVariant->id, 'Noir');

    $item = CartItem::where('user_id', $client->id)->where('product_id', $selectedVariant->id)->first();
    expect($item)->not->toBeNull();
    expect($item->color)->toBe('Noir');
    expect($item->subtotal)->toBe(16000.0);

    $this->actingAs($client)->post('/checkout', [
        'nom' => 'Test Client', 'email' => 'test@test.com',
        'phone' => '123456789', 'adresse' => 'Rue 1', 'ville' => 'Yaoundé', 'pays' => 'Cameroun',
    ]);

    $this->actingAs($client)->post('/checkout/confirmer', [
        'preuve' => UploadedFile::fake()->create('preuve.jpg', 100, 'image/jpeg'),
    ]);

    $order = Order::where('user_id', $client->id)->first();
    expect($order)->not->toBeNull();

    $orderItem = $order->items()->first();
    expect($orderItem->product_color)->toBe('Noir');
    expect($orderItem->prix_unitaire)->toBe(8000.0);
    expect((float) $order->total)->toBe(2000.0 + 16000.0);
});

test('selected color variant works when legacy products do not have a product code', function () {
    $client = User::factory()->create(['role' => 'client', 'email_verified_at' => now()]);
    $category = Category::create(['nom' => 'Sacs', 'slug' => 'sacs', 'actif' => true]);

    $defaultProduct = Product::create([
        'nom' => 'Sac Dame',
        'prix' => 12000,
        'category_id' => $category->id,
        'categorie' => 'Sacs',
        'stock' => 10,
        'actif' => true,
        'source_row' => 'row_legacy_parent',
    ]);

    $selectedVariant = Product::create([
        'nom' => 'Sac Dame',
        'prix' => 12000,
        'category_id' => $category->id,
        'categorie' => 'Sacs',
        'color' => 'Rouge',
        'image_url' => 'https://example.com/sac-rouge.jpg',
        'stock' => 4,
        'actif' => true,
        'source_row' => 'row_legacy_red',
    ]);

    Livewire::actingAs($client)
        ->test(AddToCartButton::class, ['product' => $defaultProduct])
        ->call('add', 1, $selectedVariant->id, 'Rouge')
        ->assertDispatched('cart-updated');

    $item = CartItem::where('user_id', $client->id)->first();
    expect($item)->not->toBeNull();
    expect($item->product_id)->toBe($selectedVariant->id);
    expect($item->color)->toBe('Rouge');
    expect($item->color_image_url)->toBe('https://example.com/sac-rouge.jpg');
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
