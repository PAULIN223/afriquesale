<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerificationCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    public function showForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'role'              => 'client',
            'verification_code' => $code,
            'code_expires_at'   => now()->addMinutes(10),
            'statut'            => 'active',
        ]);

        $mailSent = true;
        try {
            Mail::to($user->email)->send(new VerificationCode($user, $code));
        } catch (\Exception $e) {
            $mailSent = false;
            Log::error('Échec envoi email vérification', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }

        session(['pending_verification_email' => $user->email]);

        if (!$mailSent) {
            return redirect('/verify-email')->with('warning',
                'Compte créé, mais l\'email n\'a pas pu être envoyé. Cliquez sur "Renvoyer le code" sur cette page.'
            );
        }

        return redirect('/verify-email')->with('success', 'Un code de vérification a été envoyé à ' . $user->email);
    }
}
