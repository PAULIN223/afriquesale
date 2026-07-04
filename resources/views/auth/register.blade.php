@extends('layouts.auth')
@section('title', 'Créer un compte')

@section('content')
<div class="flex items-center justify-between mb-7">
    <a href="/" class="flex items-center gap-1.5 text-sm text-gray-400 hover:text-[#E63B2E] transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Accueil
    </a>
</div>
<div class="text-center mb-7">
    <h2 class="text-2xl font-bold text-gray-900">Créer votre compte</h2>
    <p class="text-gray-400 text-sm mt-1">Rejoignez AfricSales et découvrez nos produits</p>
</div>

<form method="POST" action="/register" class="space-y-4">
    @csrf
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nom complet *</label>
        <div class="relative">
            <i data-lucide="user" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                   placeholder="Jean Dupont"
                   class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#E63B2E] focus:ring-2 focus:ring-[#E63B2E]/10 @error('name') border-red-300 @enderror">
        </div>
        @error('name')<p class="text-xs text-[#E63B2E] mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Adresse email *</label>
        <div class="relative">
            <i data-lucide="mail" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
            <input type="email" name="email" value="{{ old('email') }}" required
                   placeholder="votre@email.com"
                   class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#E63B2E] focus:ring-2 focus:ring-[#E63B2E]/10 @error('email') border-red-300 @enderror">
        </div>
        @error('email')<p class="text-xs text-[#E63B2E] mt-1">{{ $message }}</p>@enderror
    </div>

    <div x-data="{ show: false }">
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Mot de passe *</label>
        <div class="relative">
            <i data-lucide="lock" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
            <input :type="show ? 'text' : 'password'" name="password" required
                   placeholder="Minimum 8 caractères"
                   class="w-full pl-10 pr-10 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#E63B2E] focus:ring-2 focus:ring-[#E63B2E]/10 @error('password') border-red-300 @enderror">
            <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                <i :data-lucide="show ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
            </button>
        </div>
        @error('password')<p class="text-xs text-[#E63B2E] mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirmer le mot de passe *</label>
        <div class="relative">
            <i data-lucide="lock" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
            <input type="password" name="password_confirmation" required
                   placeholder="Répétez votre mot de passe"
                   class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#E63B2E] focus:ring-2 focus:ring-[#E63B2E]/10">
        </div>
    </div>

    <div class="bg-blue-50 rounded-xl p-3 text-xs text-blue-600 flex items-start gap-2">
        <i data-lucide="info" class="w-3.5 h-3.5 flex-shrink-0 mt-0.5"></i>
        Un code de vérification à 6 chiffres sera envoyé à votre adresse email pour activer votre compte.
    </div>

    <button type="submit"
            class="w-full bg-[#E63B2E] hover:bg-[#CC2F23] text-white py-3.5 rounded-xl font-bold text-sm transition-all hover:shadow-lg hover:shadow-[#E63B2E]/20">
        Créer mon compte
    </button>
</form>

<p class="text-center text-sm text-gray-500 mt-6">
    Déjà un compte ?
    <a href="/login" class="text-[#E63B2E] font-semibold hover:underline">Se connecter</a>
</p>
@endsection
