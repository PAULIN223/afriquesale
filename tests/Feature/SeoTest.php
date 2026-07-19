<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('robots.txt disallows private areas and references the sitemap', function () {
    $response = $this->get('/robots.txt');

    $response->assertOk();
    $response->assertHeader('content-type', 'text/plain; charset=UTF-8');
    $response->assertSee('Disallow: /admin');
    $response->assertSee('Disallow: /checkout');
    $response->assertSee('Sitemap: ' . url('/sitemap.xml'));
});

test('sitemap.xml is valid xml and lists the homepage', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    $response->assertHeader('content-type', 'application/xml');
    $response->assertSee('<loc>' . url('/') . '</loc>', false);
});

test('public pages are not marked noindex', function () {
    $response = $this->get('/');

    $response->assertOk();
    expect($response->headers->has('X-Robots-Tag'))->toBeFalse();
});

test('private authenticated pages are marked noindex', function () {
    $client = User::factory()->create(['role' => 'client', 'email_verified_at' => now()]);

    $response = $this->actingAs($client)->get('/cart');

    $response->assertOk();
    $response->assertHeader('X-Robots-Tag', 'noindex, nofollow');
});

test('admin pages are marked noindex', function () {
    $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertOk();
    $response->assertHeader('X-Robots-Tag', 'noindex, nofollow');
});
