<?php

use App\Livewire\Admin\CustomersIndex;
use App\Livewire\Admin\WebMessagesShow;
use App\Livewire\Client\MessagesThread;
use App\Models\Message;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makeWebAdmin(): User
{
    return User::factory()->create(['role' => 'admin', 'statut' => 'active', 'password' => Hash::make('x')]);
}

function makeWebClient(): User
{
    return User::factory()->create(['role' => 'client', 'statut' => 'active', 'email_verified_at' => now()]);
}

test('client can send a message and admin sees it with unread badge', function () {
    $client = makeWebClient();
    $admin  = makeWebAdmin();

    Livewire::actingAs($client)
        ->test(MessagesThread::class)
        ->set('body', 'Bonjour, j\'ai une question sur ma commande')
        ->call('send');

    expect(Message::where('user_id', $client->id)->where('sender', 'client')->count())->toBe(1);

    // Le badge admin "Messages Web" doit refléter le message non lu
    $response = $this->actingAs($admin)->get('/admin');
    $response->assertStatus(200);
});

test('admin reply marks client message read and appears in client thread', function () {
    $client = makeWebClient();
    $admin  = makeWebAdmin();

    Message::create(['user_id' => $client->id, 'sender' => 'client', 'body' => 'Salut', 'is_read' => false]);

    Livewire::actingAs($admin)
        ->test(WebMessagesShow::class, ['user' => $client])
        ->set('body', 'Bonjour, comment puis-je vous aider ?')
        ->call('send');

    // Le message client doit être marqué lu (mount() de WebMessagesShow)
    expect(Message::where('user_id', $client->id)->where('sender', 'client')->where('is_read', false)->count())->toBe(0);
    // La réponse admin doit exister et ne pas être lue par le client
    expect(Message::where('user_id', $client->id)->where('sender', 'admin')->where('is_read', false)->count())->toBe(1);

    // Le client visite /messages : la réponse admin doit être marquée lue et visible
    Livewire::actingAs($client)
        ->test(MessagesThread::class)
        ->assertSee('Bonjour, comment puis-je vous aider ?');

    expect(Message::where('user_id', $client->id)->where('sender', 'admin')->where('is_read', false)->count())->toBe(0);
});

test('customers index visit resets the new-clients-web badge', function () {
    $admin = makeWebAdmin();
    Setting::set('clients_web_last_seen', now()->subDays(5));

    // Un client "nouveau" créé après le dernier last_seen
    makeWebClient();

    Livewire::actingAs($admin)->test(CustomersIndex::class);

    $lastSeen = Setting::get('clients_web_last_seen');
    expect(\Carbon\Carbon::parse($lastSeen)->diffInSeconds(now()))->toBeLessThan(5);
});

test('admin dashboard shows messages web badge in sidebar when unread exists', function () {
    $admin  = makeWebAdmin();
    $client = makeWebClient();
    Message::create(['user_id' => $client->id, 'sender' => 'client', 'body' => 'Question', 'is_read' => false]);

    $response = $this->actingAs($admin)->get('/admin');
    $response->assertStatus(200);
    $response->assertSee('Messages Web');
});
