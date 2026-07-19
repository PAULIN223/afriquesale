<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin'          => \App\Http\Middleware\AdminMiddleware::class,
            'verified.email' => \App\Http\Middleware\EnsureEmailVerified::class,
            'redirect.admin' => \App\Http\Middleware\RedirectIfAdmin::class,
            'noindex'        => \App\Http\Middleware\NoIndex::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Session/CSRF expirée (419) : redirige vers la page précédente (ou l'accueil)
        // avec un message clair plutôt que d'afficher la page d'erreur brute "419 Page Expired".
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            return redirect($request->headers->get('referer', '/'))
                ->with('error', 'Votre session a expiré. Veuillez réessayer.');
        });
    })->create();
