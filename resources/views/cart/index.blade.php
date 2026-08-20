@extends('layouts.app')
@section('title', 'Mon Panier — AfricSales')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
        <i data-lucide="shopping-cart" class="w-6 h-6 text-[#E63B2E]"></i>
        Mon Panier
        @if($cartItems->count() > 0)
        <span class="text-sm font-normal text-gray-400 ml-1">({{ $cartItems->sum('quantite') }} articles)</span>
        @endif
    </h1>

    @if($cartItems->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 p-16 text-center">
        <div class="w-20 h-20 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
            <i data-lucide="shopping-cart" class="w-10 h-10 text-gray-200"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-800 mb-2">Votre panier est vide</h2>
        <p class="text-gray-400 mb-6">Découvrez notre catalogue et ajoutez des produits à votre panier</p>
        <a href="/products" class="inline-flex items-center gap-2 bg-[#E63B2E] text-white px-6 py-3 rounded-xl font-semibold transition-colors hover:bg-[#CC2F23]">
            <i data-lucide="shopping-bag" class="w-4 h-4"></i> Commencer mes achats
        </a>
    </div>
    @else
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Items list --}}
        <div class="lg:col-span-2 space-y-3">
            @foreach($cartItems as $item)
            <div class="bg-white rounded-2xl border border-gray-100 p-4 flex gap-4 items-start">
                <a href="/products/{{ $item->product->id }}" class="flex-shrink-0">
                    <img src="{{ ($item->color_image_url ?: $item->product->image_url) ? '/image-proxy?url=' . urlencode($item->color_image_url ?: $item->product->image_url) : '/images/placeholder.png' }}"
                         alt="{{ $item->product->display_name }}" loading="lazy"
                         class="w-20 h-20 object-cover rounded-xl">
                </a>
                <div class="flex-1 min-w-0">
                    <a href="/products/{{ $item->product->id }}" class="font-semibold text-gray-800 text-sm hover:text-[#E63B2E] transition-colors line-clamp-2">
                        {{ $item->product->display_name }}
                    </a>
                    <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                        <span class="text-xs text-gray-400">{{ $item->product->category?->nom }}</span>
                        @if(filled($item->color))
                        <span class="inline-flex items-center text-[10px] font-medium text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">
                            Couleur : {{ $item->color }}
                        </span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between mt-3 flex-wrap gap-2">
                        {{-- Qty --}}
                        <form method="POST" action="/cart/update" class="flex items-center border border-gray-200 rounded-xl overflow-hidden">
                            @csrf
                            <input type="hidden" name="item_id" value="{{ $item->id }}">
                            <button type="submit" name="quantite" value="{{ max(1, $item->quantite - 1) }}"
                                    class="w-8 h-8 hover:bg-gray-50 text-gray-600 flex items-center justify-center">
                                <i data-lucide="minus" class="w-3 h-3"></i>
                            </button>
                            <span class="w-10 text-center text-sm font-semibold">{{ $item->quantite }}</span>
                            <button type="submit" name="quantite" value="{{ min($item->product->stock, $item->quantite + 1) }}"
                                    class="w-8 h-8 hover:bg-gray-50 text-gray-600 flex items-center justify-center">
                                <i data-lucide="plus" class="w-3 h-3"></i>
                            </button>
                        </form>
                        {{-- Price --}}
                        <span class="font-bold text-gray-900">{{ number_format($item->subtotal, 0, ',', ' ') }} FCFA</span>
                        {{-- Remove --}}
                        <form method="POST" action="/cart/remove">
                            @csrf
                            <input type="hidden" name="item_id" value="{{ $item->id }}">
                            <button type="submit" class="p-2 text-gray-400 hover:text-[#E63B2E] transition-colors rounded-lg hover:bg-red-50">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach

            <div class="flex items-center justify-between">
                <a href="/products" class="text-sm text-[#E63B2E] hover:underline flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Continuer mes achats
                </a>
                <form method="POST" action="/cart/clear">
                    @csrf
                    <button type="submit" class="text-sm text-gray-400 hover:text-[#E63B2E] transition-colors flex items-center gap-1">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Vider le panier
                    </button>
                </form>
            </div>
        </div>

        {{-- Order summary --}}
        <div>
            <div class="bg-white rounded-2xl border border-gray-100 p-5 sticky top-24">
                <h3 class="font-bold text-gray-900 mb-4">Récapitulatif</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Sous-total ({{ $cartItems->sum('quantite') }} articles)</span>
                        <span>{{ number_format($subtotal, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Frais de livraison</span>
                        @if($fraisLivraison > 0)
                        <span>{{ number_format($fraisLivraison, 0, ',', ' ') }} FCFA</span>
                        @else
                        <span class="text-green-600 font-medium">Gratuit</span>
                        @endif
                    </div>
                    <div class="border-t border-gray-100 pt-3 flex justify-between font-bold text-gray-900 text-base">
                        <span>Total</span>
                        <span>{{ number_format($total, 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>
                <a href="/checkout"
                   class="mt-5 block w-full bg-[#E63B2E] hover:bg-[#CC2F23] text-white text-center py-3.5 rounded-xl font-semibold text-sm transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="lock" class="w-4 h-4"></i> Passer commande
                </a>
                <div class="mt-4 flex items-center justify-center gap-2 text-xs text-gray-400">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                    Paiement 100% sécurisé
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
