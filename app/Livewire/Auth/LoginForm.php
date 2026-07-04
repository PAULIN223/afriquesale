<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class LoginForm extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function login()
    {
        $this->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->addError('email', 'Email ou mot de passe incorrect.');
            return;
        }

        $user = Auth::user();

        if ($user->isBlocked()) {
            Auth::logout();
            $this->addError('email', 'Votre compte a été suspendu. Contactez le support.');
            return;
        }

        if (!$user->email_verified_at) {
            Auth::logout();
            session(['pending_verification_email' => $user->email]);
            return redirect('/verify-email')->with('info', 'Veuillez vérifier votre email pour activer votre compte.');
        }

        session()->regenerate();

        if ($user->isAdmin()) {
            return redirect('/admin')->with('success', 'Bienvenue Administrateur ' . $user->name . ' !');
        }

        return redirect()->intended('/')->with('success', 'Bienvenue ' . $user->name . ' !');
    }

    public function render()
    {
        return view('livewire.auth.login-form');
    }
}
