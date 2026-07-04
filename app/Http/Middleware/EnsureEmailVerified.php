<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        if (!auth()->user()->email_verified_at) {
            session(['pending_verification_email' => auth()->user()->email]);
            return redirect('/verify-email')->with('info', 'Veuillez vérifier votre email pour continuer.');
        }

        return $next($request);
    }
}
