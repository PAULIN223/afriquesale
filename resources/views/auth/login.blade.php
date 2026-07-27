@extends('layouts.auth')
@section('title', 'Connexion')

@section('content')
<div class="flex items-center justify-between mb-7">
    <a href="/" class="flex items-center gap-1.5 text-sm text-gray-400 hover:text-[#E63B2E] transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Accueil
    </a>
</div>
<div class="text-center mb-7">
    <h2 class="text-2xl font-bold text-gray-900">Bienvenue !</h2>
    <p class="text-gray-400 text-sm mt-1">Connectez-vous à votre compte AfricSales</p>
</div>

<form method="POST" action="/login" class="space-y-4">
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

    <div>
        <div class="flex justify-between mb-1.5">
            <label class="text-sm font-medium text-gray-700">Mot de passe</label>
            <a href="/mot-de-passe-oublie" class="text-xs text-[#E63B2E] hover:underline">Mot de passe oublié ?</a>
        </div>
        <div class="relative" x-data="{ show: false }">
            <i data-lucide="lock" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
            <input :type="show ? 'text' : 'password'" name="password" required
                   placeholder="••••••••"
                   class="w-full pl-10 pr-10 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#E63B2E] focus:ring-2 focus:ring-[#E63B2E]/10">
            <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <i :data-lucide="show ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    <div class="flex items-center">
        <input type="checkbox" name="remember" id="remember" class="rounded border-gray-300 text-[#E63B2E] focus:ring-[#E63B2E]">
        <label for="remember" class="ml-2 text-sm text-gray-600">Se souvenir de moi</label>
    </div>

    <button type="submit"
            class="w-full bg-[#E63B2E] hover:bg-[#CC2F23] text-white py-3.5 rounded-xl font-bold text-sm transition-all hover:shadow-lg hover:shadow-[#E63B2E]/20">
        Se connecter
    </button>
</form>

<p class="text-center text-sm text-gray-500 mt-6">
    Pas encore de compte ?
    <a href="/register" class="text-[#E63B2E] font-semibold hover:underline">Créer un compte</a>
</p>
@endsection
