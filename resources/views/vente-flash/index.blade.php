@extends('layouts.app')
@section('title', 'Vente Flash — AfricSales')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    {{-- Back + Header --}}
    <div class="flex items-center gap-3 mb-6">
        <button onclick="history.back()" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 text-sm text-gray-500 hover:text-[#E63B2E] hover:border-[#E63B2E] transition-all">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Retour
        </button>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i data-lucide="zap" class="w-6 h-6 text-[#E63B2E]"></i>
                Vente Flash
            </h1>
            <p class="text-sm text-gray-400 mt-0.5">Produits à prix réduit — offres limitées</p>
        </div>
    </div>

    {{-- Free delivery banner --}}
    <div class="bg-gradient-to-r from-[#E63B2E] to-[#CC2F23] rounded-2xl p-5 mb-6 text-white flex items-center gap-4 flex-wrap">
        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
            <i data-lucide="truck" class="w-6 h-6"></i>
        </div>
        <div class="flex-1">
            <p class="font-bold text-lg">🚚 Livraison GRATUITE</p>
            <p class="text-white/80 text-sm">Pour toute commande de <strong>30 articles ou plus</strong> sur les produits Vente Flash</p>
        </div>
        @auth
        @php $cartQty = auth()->user()->cartItems()->sum('quantite'); @endphp
        @if($cartQty > 0)
        <div class="bg-white/20 rounded-xl px-4 py-2 text-center flex-shrink-0">
            <p class="text-xs text-white/70">Panier actuel</p>
            <p class="font-bold text-lg">{{ $cartQty }}/30</p>
            @if($cartQty >= 30)
            <p class="text-xs font-semibold">✅ Livraison gratuite !</p>
            @else
            <p class="text-xs text-white/80">encore {{ 30 - $cartQty }} articles</p>
            @endif
        </div>
        @endif
        @endauth
    </div>

    @if($products->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 p-16 text-center">
        <div class="w-20 h-20 bg-[#E63B2E]/10 rounded-2xl flex items-center justify-center mx-auto mb-5">
            <i data-lucide="zap" class="w-10 h-10 text-[#E63B2E]"></i>
        </div>
        <h3 class="font-bold text-gray-800 text-lg mb-2">Aucune vente flash en ce moment</h3>
        <p class="text-gray-400 text-sm mb-6">Revenez bientôt pour découvrir nos offres exceptionnelles</p>
        <a href="/products" class="inline-flex items-center gap-2 bg-[#E63B2E] text-white px-6 py-3 rounded-xl text-sm font-semibold hover:bg-[#CC2F23] transition-colors">
            <i data-lucide="grid-2x2" class="w-4 h-4"></i>
            Voir tous les produits
        </a>
    </div>
    @else
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-600">
            <span class="font-semibold text-gray-900">{{ $products->total() }}</span> offres flash disponibles
        </p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4">
        @foreach($products as $product)
        <div class="product-card bg-white rounded-2xl border border-[#E63B2E]/20 overflow-hidden group" x-data="{ added: false }">
            <div class="relative overflow-hidden aspect-square bg-gray-50">
                <a href="/products/{{ $product->id }}">
                    <img src="{{ $product->image_url ? '/image-proxy?url=' . urlencode($product->image_url) : '/images/placeholder.png' }}"
                         alt="{{ $product->nom }}"
                         loading="lazy"
                         class="w-full h-full object-cover object-center transition-transform duration-500 group-hover:scale-105">
                </a>
                {{-- Discount badge --}}
                <span class="absolute top-2 left-2 bg-[#E63B2E] text-white text-xs font-bold px-2 py-1 rounded-lg flex items-center gap-1">
                    <i data-lucide="zap" class="w-3 h-3"></i> -{{ $product->discount_percent }}%
                </span>
                {{-- Wishlist + Message --}}
                @auth
                @php $inWishlist = in_array($product->id, $wishlistProductIds ?? []); @endphp
                <div class="absolute top-2 right-2 flex flex-col gap-1.5">
                    <form method="POST" action="/wishlist/toggle/{{ $product->id }}">
                        @csrf
                        <button type="submit" class="w-8 h-8 rounded-xl shadow flex items-center justify-center transition-all
                            {{ $inWishlist ? 'bg-[#E63B2E] text-white' : 'bg-white/90 text-gray-500 hover:bg-[#E63B2E] hover:text-white' }}">
                            <i data-lucide="heart" class="w-4 h-4 {{ $inWishlist ? 'fill-current' : '' }}"></i>
                        </button>
                    </form>
                    <a href="/messages?product={{ $product->id }}" wire:navigate
                       class="w-8 h-8 bg-white/90 rounded-xl shadow flex items-center justify-center hover:bg-[#E63B2E] hover:text-white transition-all text-gray-500"
                       title="Poser une question sur ce produit">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                    </a>
                </div>
                @endauth
            </div>
            <div class="p-3">
                <a href="/products/{{ $product->id }}" class="block">
                    <p class="text-xs text-gray-400 mb-1">{{ $product->category?->nom }}</p>
                    <h3 class="font-semibold text-gray-800 text-sm leading-snug line-clamp-2 hover:text-[#E63B2E] transition-colors">{{ $product->nom }}</h3>
                </a>
                <div class="mt-2">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-gray-900 text-base">{{ number_format($product->prix, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400 line-through">{{ number_format($product->prix_original, 0, ',', ' ') }} FCFA</span>
                        <span class="text-xs text-green-600 font-semibold">
                            Économie {{ number_format($product->prix_original - $product->prix, 0, ',', ' ') }} FCFA
                        </span>
                    </div>
                </div>
                @if($product->stock > 0)
                <form method="POST" action="/cart/add" class="mt-3">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <button type="submit" x-on:click="added = true; setTimeout(() => added = false, 2000)"
                            class="w-full py-2.5 rounded-xl text-sm font-semibold transition-all flex items-center justify-center gap-1.5"
                            :class="added ? 'bg-green-500 text-white' : 'bg-[#E63B2E] hover:bg-[#CC2F23] text-white'">
                        <span x-show="!added" class="flex items-center gap-1.5">
                            <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i> Ajouter
                        </span>
                        <span x-show="added" class="flex items-center gap-1.5">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i> Ajouté !
                        </span>
                    </button>
                </form>
                @else
                <button disabled class="mt-3 w-full py-2.5 rounded-xl text-sm font-medium bg-gray-100 text-gray-400 cursor-not-allowed">
                    Indisponible
                </button>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-8">{{ $products->links() }}</div>
    @endif

</div>
@endsection
