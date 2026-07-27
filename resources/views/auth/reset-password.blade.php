@extends('layouts.auth')
@section('title', 'Réinitialiser le mot de passe')

@section('content')
<div class="text-center mb-7">
    <div class="w-16 h-16 bg-[#E63B2E]/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <i data-lucide="lock-keyhole" class="w-8 h-8 text-[#E63B2E]"></i>
    </div>
    <h2 class="text-2xl font-bold text-gray-900">Réinitialiser le mot de passe</h2>
    <p class="text-gray-400 text-sm mt-1">
        Entrez le code à 6 chiffres envoyé à
        <span class="font-semibold text-gray-700">{{ session('pending_reset_email') }}</span>
        et choisissez un nouveau mot de passe.
    </p>
</div>

<form method="POST" action="/reinitialiser-mot-de-passe" class="space-y-5">
    @csrf
    <div x-data="{ code: '' }">
        <label class="block text-sm font-medium text-gray-700 mb-2 text-center">Code reçu par email</label>
        <input type="text" name="code" x-model="code"
               maxlength="6" pattern="[0-9]{6}" required autofocus
               placeholder="_ _ _ _ _ _"
               class="w-full text-center text-3xl font-bold tracking-[0.5em] border-2 border-gray-200 rounded-xl px-4 py-4 focus:outline-none focus:border-[#E63B2E] focus:ring-2 focus:ring-[#E63B2E]/10
                      @error('code') border-red-300 @enderror">
        @error('code')<p class="text-xs text-[#E63B2E] mt-1 text-center">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nouveau mot de passe</label>
        <div class="relative" x-data="{ show: false }">
            <i data-lucide="lock" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
            <input :type="show ? 'text' : 'password'" name="password" required minlength="8"
                   placeholder="••••••••"
                   class="w-full pl-10 pr-10 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#E63B2E] focus:ring-2 focus:ring-[#E63B2E]/10 @error('password') border-red-300 @enderror">
            <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <i :data-lucide="show ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
            </button>
        </div>
        @error('password')<p class="text-xs text-[#E63B2E] mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirmer le mot de passe</label>
        <div class="relative">
            <i data-lucide="lock" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
            <input type="password" name="password_confirmation" required minlength="8"
                   placeholder="••••••••"
                   class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#E63B2E] focus:ring-2 focus:ring-[#E63B2E]/10">
        </div>
    </div>

    <div class="bg-yellow-50 rounded-xl p-3 text-xs text-yellow-700 flex items-center gap-2">
        <i data-lucide="clock" class="w-3.5 h-3.5 flex-shrink-0"></i>
        Ce code expire dans <strong>10 minutes</strong>.
    </div>

    <button type="submit"
            class="w-full bg-[#E63B2E] hover:bg-[#CC2F23] text-white py-3.5 rounded-xl font-bold text-sm transition-all">
        Réinitialiser mon mot de passe
    </button>
</form>

<div class="text-center mt-5">
    <p class="text-sm text-gray-500 mb-2">Vous n'avez pas reçu le code ?</p>
    <form method="POST" action="/mot-de-passe-oublie/renvoyer">
        @csrf
        <button type="submit" class="text-sm text-[#E63B2E] font-semibold hover:underline flex items-center gap-1 mx-auto">
            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Renvoyer un code
        </button>
    </form>
</div>
@endsection
