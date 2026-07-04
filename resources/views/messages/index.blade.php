@extends('layouts.app')
@section('title', 'Messages — AfricSales')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 py-6">

    <div class="flex items-center gap-3 mb-6">
        <button onclick="history.back()" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 text-sm text-gray-500 hover:text-[#E63B2E] hover:border-[#E63B2E] transition-all">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Retour
        </button>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i data-lucide="message-circle" class="w-6 h-6 text-[#E63B2E]"></i>
                Messages
            </h1>
            <p class="text-sm text-gray-400 mt-0.5">Contactez notre équipe</p>
        </div>
    </div>

    {{-- Contact info --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
        <div class="bg-white rounded-2xl border border-gray-100 p-4 text-center">
            <div class="w-10 h-10 bg-[#E63B2E]/10 rounded-xl flex items-center justify-center mx-auto mb-2">
                <i data-lucide="clock" class="w-5 h-5 text-[#E63B2E]"></i>
            </div>
            <p class="text-xs text-gray-500">Réponse en</p>
            <p class="font-bold text-gray-800 text-sm">24h max</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-4 text-center">
            <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                <i data-lucide="headphones" class="w-5 h-5 text-green-600"></i>
            </div>
            <p class="text-xs text-gray-500">Support</p>
            <p class="font-bold text-gray-800 text-sm">7j/7</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-4 text-center">
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                <i data-lucide="shield-check" class="w-5 h-5 text-blue-600"></i>
            </div>
            <p class="text-xs text-gray-500">Service</p>
            <p class="font-bold text-gray-800 text-sm">Gratuit</p>
        </div>
    </div>

    {{-- Message form --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <h2 class="font-bold text-gray-800 mb-5 flex items-center gap-2">
            <i data-lucide="send" class="w-4 h-4 text-[#E63B2E]"></i>
            Envoyer un message
        </h2>

        @if(session('success'))
        <div class="mb-5 bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 flex-shrink-0"></i>
            {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="/messages/send" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Votre nom</label>
                <input type="text" value="{{ auth()->user()->name }}" readonly
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-gray-50 text-gray-600 cursor-not-allowed">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                <input type="email" value="{{ auth()->user()->email }}" readonly
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-gray-50 text-gray-600 cursor-not-allowed">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Sujet *</label>
                <select name="sujet" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E] bg-white">
                    <option value="">Choisir un sujet...</option>
                    <option value="commande" {{ old('sujet') === 'commande' ? 'selected' : '' }}>Suivi de commande</option>
                    <option value="retour"   {{ old('sujet') === 'retour'   ? 'selected' : '' }}>Retour / Remboursement</option>
                    <option value="produit"  {{ old('sujet') === 'produit'  ? 'selected' : '' }}>Question sur un produit</option>
                    <option value="livraison"{{ old('sujet') === 'livraison'? 'selected' : '' }}>Problème de livraison</option>
                    <option value="autre"    {{ old('sujet') === 'autre'    ? 'selected' : '' }}>Autre</option>
                </select>
                @error('sujet')<p class="text-xs text-[#E63B2E] mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Message *</label>
                <textarea name="message" rows="5" required placeholder="Décrivez votre demande..."
                          class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E] resize-none">{{ old('message') }}</textarea>
                @error('message')<p class="text-xs text-[#E63B2E] mt-1">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                    class="w-full bg-[#E63B2E] hover:bg-[#CC2F23] text-white py-3 rounded-xl font-semibold text-sm transition-colors flex items-center justify-center gap-2">
                <i data-lucide="send" class="w-4 h-4"></i>
                Envoyer le message
            </button>
        </form>
    </div>

</div>
@endsection
