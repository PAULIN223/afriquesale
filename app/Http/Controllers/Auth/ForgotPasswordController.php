<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    public function showForm()
    {
        return view('auth.forgot-password');
    }

    public function send(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Aucun compte n\'est associé à cet email.'])->withInput();
        }

        $this->sendCode($user);

        session(['pending_reset_email' => $user->email]);

        return redirect('/reinitialiser-mot-de-passe')
            ->with('success', 'Un code a été envoyé à ' . $user->email);
    }

    public function showResetForm()
    {
        if (!session('pending_reset_email')) {
            return redirect('/mot-de-passe-oublie');
        }
        return view('auth.reset-password');
    }

    public function reset(Request $request)
    {
        $request->validate([
            'code'     => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $email = session('pending_reset_email');
        if (!$email) {
            return redirect('/mot-de-passe-oublie');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect('/mot-de-passe-oublie');
        }

        if ($user->password_reset_code !== $request->code) {
            return back()->withErrors(['code' => 'Code incorrect.']);
        }

        if ($user->password_reset_code_expires_at && now()->isAfter($user->password_reset_code_expires_at)) {
            return back()->withErrors(['code' => 'Ce code a expiré. Demandez-en un nouveau.']);
        }

        $user->update([
            'password'                       => Hash::make($request->password),
            'password_reset_code'            => null,
            'password_reset_code_expires_at' => null,
        ]);

        session()->forget('pending_reset_email');

        return redirect('/login')->with('success', 'Votre mot de passe a été réinitialisé. Vous pouvez vous connecter.');
    }

    public function resend(Request $request)
    {
        $email = session('pending_reset_email');
        if (!$email) {
            return redirect('/mot-de-passe-oublie');
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return redirect('/mot-de-passe-oublie');
        }

        $this->sendCode($user);

        return back()->with('success', 'Un nouveau code a été envoyé à votre email.');
    }

    private function sendCode(User $user): bool
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->update([
            'password_reset_code'            => $code,
            'password_reset_code_expires_at' => now()->addMinutes(10),
        ]);

        try {
            Mail::to($user->email)->send(new PasswordResetCode($user, $code));
            return true;
        } catch (\Exception $e) {
            Log::error('Échec envoi email réinitialisation mot de passe', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
