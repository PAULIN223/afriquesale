<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerificationCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class VerificationController extends Controller
{
    public function showForm()
    {
        if (!session('pending_verification_email')) {
            return redirect('/login');
        }
        return view('auth.verify');
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);

        $email = session('pending_verification_email');
        if (!$email) {
            return redirect('/login');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->withErrors(['code' => 'Utilisateur non trouvé.']);
        }

        if ($user->verification_code !== $request->code) {
            return back()->withErrors(['code' => 'Code incorrect.']);
        }

        if ($user->code_expires_at && now()->isAfter($user->code_expires_at)) {
            return back()->withErrors(['code' => 'Ce code a expiré. Demandez-en un nouveau.']);
        }

        $user->update([
            'email_verified_at' => now(),
            'verification_code' => null,
            'code_expires_at'   => null,
        ]);

        session()->forget('pending_verification_email');
        Auth::login($user);

        return redirect('/')->with('success', 'Compte vérifié ! Bienvenue sur AfricSales 🎉');
    }

    public function resend(Request $request)
    {
        $email = session('pending_verification_email');
        if (!$email) {
            return redirect('/login');
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return redirect('/login');
        }

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->update(['verification_code' => $code, 'code_expires_at' => now()->addMinutes(10)]);

        try {
            Mail::to($user->email)->send(new VerificationCode($user, $code));
        } catch (\Exception $e) {
            return back()->withErrors(['code' => 'Impossible d\'envoyer l\'email. Réessayez plus tard.']);
        }

        return back()->with('success', 'Un nouveau code a été envoyé à votre email.');
    }
}
