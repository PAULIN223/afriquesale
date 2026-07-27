@extends('layouts.auth')
@section('title', 'Mot de passe oublié')

@section('content')
<div class="flex items-center justify-between mb-7">
    <a href="/login" class="flex items-center gap-1.5 text-sm text-gray-400 hover:text-[#E63B2E] transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Connexion
    </a>
</div>
<div class="text-center mb-7">
    <div class="w-16 h-16 bg-[#E63B2E]/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <i data-lucide="key-round" class="w-8 h-8 text-[#E63B2E]"></i>
    </div>
    <h2 class="text-2xl font-bold text-gray-900">Mot de passe oublié ?</h2>
    <p class="text-gray-400 text-sm mt-1">
        Entrez votre email, nous vous enverrons un code à 6 chiffres pour réinitialiser votre mot de passe.
    </p>
</div>

<form method="POST" action="/mot-de-passe-oublie" class="space-y-4">
    @csrf
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Adresse email</label>
        <div class="relative">
            <i data-lucide="mail" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   placeholder="votre@email.com"
                   class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#E63B2E] focus:ring-2 focus:ring-[#E63B2E]/10 @error('email') border-red-300 @enderror">
        </div>
        @error('email')<p class="text-xs text-[#E63B2E] mt-1">{{ $message }}</p>@enderror
    </div>

    <button type="submit"
            class="w-full bg-[#E63B2E] hover:bg-[#CC2F23] text-white py-3.5 rounded-xl font-bold text-sm transition-all hover:shadow-lg hover:shadow-[#E63B2E]/20">
        Envoyer le code
    </button>
</form>

<p class="text-center text-sm text-gray-500 mt-6">
    Vous vous souvenez de votre mot de passe ?
    <a href="/login" class="text-[#E63B2E] font-semibold hover:underline">Se connecter</a>
</p>
@endsection
