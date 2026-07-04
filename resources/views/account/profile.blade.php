@extends('layouts.app')
@section('title', 'Mon Profil — AfricSales')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Mon Profil</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Sidebar --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 h-fit">
            <div class="text-center mb-4">
                <div class="w-16 h-16 bg-[#E63B2E]/10 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="text-[#E63B2E] font-bold text-2xl">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                </div>
                <p class="font-semibold text-gray-800">{{ $user->name }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $user->email }}</p>
            </div>
            <nav class="space-y-1">
                <a href="/account/profile" class="flex items-center gap-2 px-3 py-2 rounded-xl bg-[#E63B2E]/10 text-[#E63B2E] font-medium text-sm">
                    <i data-lucide="user" class="w-4 h-4"></i> Mon profil
                </a>
                <a href="/account/orders" class="flex items-center gap-2 px-3 py-2 rounded-xl text-gray-600 hover:bg-gray-50 text-sm transition-colors">
                    <i data-lucide="package" class="w-4 h-4"></i> Mes commandes
                </a>
                <a href="/wishlist" class="flex items-center gap-2 px-3 py-2 rounded-xl text-gray-600 hover:bg-gray-50 text-sm transition-colors">
                    <i data-lucide="heart" class="w-4 h-4"></i> Mes favoris
                </a>
            </nav>
        </div>

        {{-- Form --}}
        <div class="md:col-span-2 bg-white rounded-2xl border border-gray-100 p-6">
            @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}
            </div>
            @endif
            <form method="POST" action="/account/profile" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nom complet *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
                    @error('name')<p class="text-xs text-[#E63B2E] mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <input type="email" value="{{ $user->email }}" disabled
                           class="w-full border border-gray-100 rounded-xl px-4 py-2.5 text-sm bg-gray-50 text-gray-400 cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Téléphone</label>
                    <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Adresse</label>
                    <input type="text" name="adresse" value="{{ old('adresse', $user->adresse) }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
                </div>
                <div class="border-t border-gray-100 pt-4">
                    <p class="text-sm font-semibold text-gray-700 mb-3">Changer le mot de passe</p>
                    <div class="space-y-3">
                        <input type="password" name="password" placeholder="Nouveau mot de passe (min. 8 caractères)"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
                        <input type="password" name="password_confirmation" placeholder="Confirmer le mot de passe"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
                    </div>
                </div>
                <button type="submit"
                        class="bg-[#E63B2E] hover:bg-[#CC2F23] text-white px-6 py-2.5 rounded-xl font-semibold text-sm transition-colors">
                    Enregistrer les modifications
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
