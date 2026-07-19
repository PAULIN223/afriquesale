@extends('layouts.app')
@section('title', 'Finaliser ma commande — AfricSales')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Finaliser ma commande</h1>

    <form method="POST" action="/checkout" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @csrf

        {{-- Delivery address --}}
        <div class="space-y-5" x-data="{ editing: {{ ($hasSavedAddress && !$errors->any()) ? 'false' : 'true' }} }">
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="font-bold text-gray-900 flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-5 h-5 text-[#E63B2E]"></i> Adresse de livraison
                    </h2>
                    @if($hasSavedAddress)
                    <button type="button" x-show="!editing" @click="editing = true"
                            class="text-xs font-semibold text-[#E63B2E] hover:underline flex items-center gap-1">
                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Modifier
                    </button>
                    @endif
                </div>

                {{-- Saved address summary --}}
                @if($hasSavedAddress)
                <div x-show="!editing" class="border border-gray-100 bg-gray-50 rounded-xl p-4 flex items-start gap-3 cursor-pointer hover:border-[#E63B2E] transition-colors" @click="editing = true">
                    <div class="w-9 h-9 rounded-full bg-[#E63B2E]/10 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="check" class="w-4 h-4 text-[#E63B2E]"></i>
                    </div>
                    <div class="text-sm">
                        <p class="font-semibold text-gray-800">{{ auth()->user()->name }} · {{ auth()->user()->phone }}</p>
                        <p class="text-gray-500">{{ auth()->user()->adresse }}, {{ auth()->user()->ville }}, {{ auth()->user()->pays }}</p>
                        <p class="text-xs text-gray-400 mt-1">Cliquez pour modifier cette adresse</p>
                    </div>
                </div>
                @endif

                <div class="grid grid-cols-2 gap-4" x-show="editing" @if($hasSavedAddress) x-cloak @endif>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nom complet *</label>
                        <input type="text" name="nom" value="{{ old('nom', auth()->user()->name) }}" required
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E] focus:ring-2 focus:ring-[#E63B2E]/10 @error('nom') border-red-300 @enderror">
                        @error('nom')<p class="text-xs text-[#E63B2E] mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Email *</label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Téléphone *</label>
                        <input type="tel" name="phone" value="{{ old('phone', auth()->user()->phone) }}" required
                               placeholder="+225 07 XX XX XX XX"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Adresse *</label>
                        <input type="text" name="adresse" value="{{ old('adresse', auth()->user()->adresse) }}" required
                               placeholder="Rue, numéro..."
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Ville *</label>
                        <input type="text" name="ville" value="{{ old('ville', auth()->user()->ville) }}" required
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Pays *</label>
                        <input type="text" name="pays" value="{{ old('pays', auth()->user()->pays ?? 'Côte d\'Ivoire') }}" required
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes (optionnel)</label>
                        <textarea name="notes" rows="2" placeholder="Instructions de livraison..."
                                  class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E] resize-none">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Payment instructions --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <h2 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                    <i data-lucide="credit-card" class="w-5 h-5 text-[#E63B2E]"></i> Mode de paiement
                </h2>
                <p class="text-sm font-bold text-[#E63B2E]">
                    Veuillez bien vouloir déposer les frais de la commande au différent numéro Orange Money
                    "698.90.94.65" et MTN Money "676.71.06.97" pour que votre commande puisse être confirmée par le vendeur.
                </p>
            </div>
        </div>

        {{-- Order summary --}}
        <div>
            <div class="bg-white rounded-2xl border border-gray-100 p-6 sticky top-24">
                <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i data-lucide="clipboard-list" class="w-5 h-5 text-[#E63B2E]"></i> Récapitulatif
                </h2>
                <div class="divide-y divide-gray-50">
                    @foreach($cartItems as $item)
                    <div class="flex gap-3 py-3">
                        <img src="{{ $item->product->image_url ? '/image-proxy?url=' . urlencode($item->product->image_url) : '/images/placeholder.png' }}"
                             alt="{{ $item->product->nom }}" loading="lazy"
                             class="w-12 h-12 object-cover rounded-lg flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 line-clamp-1">{{ $item->product->nom }}</p>
                            <p class="text-xs text-gray-400">Qté : {{ $item->quantite }}</p>
                        </div>
                        <span class="text-sm font-semibold text-gray-900">{{ number_format($item->subtotal, 0, ',', ' ') }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="border-t border-gray-100 pt-4 mt-2 space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Sous-total</span>
                        <span>{{ number_format($subtotal, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Livraison</span>
                        <span>{{ $fraisLivraison > 0 ? number_format($fraisLivraison, 0, ',', ' ') . ' FCFA' : 'Gratuit' }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-gray-900 text-base border-t border-gray-100 pt-3">
                        <span>Total à payer</span>
                        <span class="text-[#E63B2E]">{{ number_format($total, 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>
                <button type="submit"
                        class="mt-5 w-full bg-[#E63B2E] hover:bg-[#CC2F23] text-white py-3.5 rounded-xl font-bold text-sm transition-all flex items-center justify-center gap-2">
                    <i data-lucide="arrow-right-circle" class="w-4 h-4"></i> Continuer vers le paiement
                </button>
                <p class="text-center text-xs text-gray-400 mt-3 flex items-center justify-center gap-1">
                    <i data-lucide="lock" class="w-3 h-3"></i> Commande sécurisée
                </p>
            </div>
        </div>
    </form>
</div>
@endsection
