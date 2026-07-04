@extends('layouts.app')

@section('title', $product->nom . ' — AfricSales')
@section('description', Str::limit($product->description, 160))
@section('og_image', $product->image_url ? '/image-proxy?url=' . urlencode($product->image_url) : '')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    {{-- Back button + Breadcrumb --}}
    <div class="flex items-center gap-3 mb-6">
        <button onclick="history.back()" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 text-sm text-gray-500 hover:text-[#E63B2E] hover:border-[#E63B2E] transition-all flex-shrink-0">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Retour
        </button>
        <nav class="flex items-center gap-2 text-sm text-gray-400 overflow-hidden">
            <a href="/" class="hover:text-[#E63B2E] flex-shrink-0">Accueil</a>
            <i data-lucide="chevron-right" class="w-3 h-3 flex-shrink-0"></i>
            <a href="/products" class="hover:text-[#E63B2E] flex-shrink-0">Catégorie</a>
            @if($product->category)
            <i data-lucide="chevron-right" class="w-3 h-3 flex-shrink-0"></i>
            <a href="/products?category={{ $product->category->slug }}" class="hover:text-[#E63B2E] flex-shrink-0">{{ $product->category->nom }}</a>
            @endif
            <i data-lucide="chevron-right" class="w-3 h-3 flex-shrink-0"></i>
            <span class="text-gray-600 font-medium truncate">{{ $product->nom }}</span>
        </nav>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">

        {{-- Image --}}
        <div class="aspect-square bg-gray-50 rounded-2xl overflow-hidden">
            <img src="{{ $product->image_url ? '/image-proxy?url=' . urlencode($product->image_url) : '/images/placeholder.png' }}"
                 alt="{{ $product->nom }}"
                 loading="eager"
                 class="w-full h-full object-cover object-center">
        </div>

        {{-- Info --}}
        <div x-data="{ qty: 1 }">
            @if($product->category)
            <span class="inline-block bg-[#E63B2E]/10 text-[#E63B2E] text-xs font-semibold px-3 py-1 rounded-full mb-3">
                {{ $product->category->nom }}
            </span>
            @endif

            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 leading-tight mb-4">{{ $product->nom }}</h1>

            {{-- Price --}}
            <div class="flex items-center gap-3 mb-5">
                <span class="text-3xl font-bold text-gray-900">{{ number_format($product->prix, 0, ',', ' ') }} FCFA</span>
                @if($product->prix_original)
                <span class="text-lg text-gray-400 line-through">{{ number_format($product->prix_original, 0, ',', ' ') }}</span>
                <span class="bg-[#E63B2E] text-white text-sm font-bold px-2.5 py-0.5 rounded-lg">-{{ $product->discount_percent }}%</span>
                @endif
            </div>

            {{-- Stock --}}
            <div class="flex items-center gap-2 mb-6">
                @if($product->stock > 10)
                <span class="flex items-center gap-1.5 text-green-600 text-sm font-medium">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i> En stock ({{ $product->stock }} disponibles)
                </span>
                @elseif($product->stock > 0)
                <span class="flex items-center gap-1.5 text-orange-500 text-sm font-medium">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i> Plus que {{ $product->stock }} en stock !
                </span>
                @else
                <span class="flex items-center gap-1.5 text-[#E63B2E] text-sm font-medium">
                    <i data-lucide="x-circle" class="w-4 h-4"></i> Rupture de stock
                </span>
                @endif
            </div>

            {{-- Description --}}
            @if($product->description)
            <div class="prose prose-sm max-w-none text-gray-600 mb-6 leading-relaxed border-t border-gray-100 pt-5">
                {{ $product->description }}
            </div>
            @endif

            @if($product->stock > 0)
            <form method="POST" action="/cart/add" class="space-y-3">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                {{-- Qty selector --}}
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-gray-700">Quantité :</span>
                    <div class="flex items-center border border-gray-200 rounded-xl overflow-hidden">
                        <button type="button" @click="qty = Math.max(1, qty - 1)"
                                class="w-10 h-10 flex items-center justify-center hover:bg-gray-50 transition-colors text-gray-600">
                            <i data-lucide="minus" class="w-4 h-4"></i>
                        </button>
                        <input type="number" name="quantite" x-model="qty" min="1" max="{{ $product->stock }}"
                               class="w-14 text-center text-sm font-semibold border-x border-gray-200 h-10 focus:outline-none">
                        <button type="button" @click="qty = Math.min({{ $product->stock }}, qty + 1)"
                                class="w-10 h-10 flex items-center justify-center hover:bg-gray-50 transition-colors text-gray-600">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                            class="flex-1 bg-[#E63B2E] hover:bg-[#CC2F23] text-white py-3.5 rounded-xl font-semibold text-sm transition-all flex items-center justify-center gap-2">
                        <i data-lucide="shopping-cart" class="w-4 h-4"></i> Ajouter au panier
                    </button>
                    @auth
                    <form method="POST" action="/wishlist/toggle/{{ $product->id }}">
                        @csrf
                        <button type="submit"
                                class="h-full px-4 rounded-xl border {{ $inWishlist ? 'border-[#E63B2E] bg-[#E63B2E]/10 text-[#E63B2E]' : 'border-gray-200 text-gray-500 hover:border-[#E63B2E] hover:text-[#E63B2E]' }} transition-all">
                            <i data-lucide="heart" class="w-5 h-5 {{ $inWishlist ? 'fill-current' : '' }}"></i>
                        </button>
                    </form>
                    <a href="/messages?product={{ $product->id }}" wire:navigate
                       class="h-full px-4 rounded-xl border border-gray-200 text-gray-500 hover:border-[#E63B2E] hover:text-[#E63B2E] transition-all flex items-center"
                       title="Poser une question sur ce produit">
                        <i data-lucide="message-circle" class="w-5 h-5"></i>
                    </a>
                    @endauth
                </div>
            </form>
            @else
            <button disabled class="w-full bg-gray-100 text-gray-400 py-3.5 rounded-xl font-semibold text-sm cursor-not-allowed">
                Produit indisponible
            </button>
            @endif

            {{-- Trust icons --}}
            <div class="mt-6 pt-5 border-t border-gray-100 grid grid-cols-3 gap-3">
                @foreach([['truck','Livraison rapide'],['shield-check','Paiement sécurisé'],['refresh-cw','Retours 30j']] as [$icon,$label])
                <div class="text-center">
                    <i data-lucide="{{ $icon }}" class="w-5 h-5 text-gray-400 mx-auto mb-1"></i>
                    <p class="text-xs text-gray-500">{{ $label }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Related products (même catégorie uniquement) --}}
    @if($related->count() > 0)
    <div class="mt-14">
        <div class="flex items-center gap-3 mb-5">
            <h2 class="text-xl font-bold text-gray-900">Autres produits</h2>
            @if($product->category)
            <span class="bg-[#E63B2E]/10 text-[#E63B2E] text-sm font-semibold px-3 py-1 rounded-full">
                {{ $product->category->nom }}
            </span>
            @endif
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($related as $rel)
            @include('products._card', ['product' => $rel])
            @endforeach
        </div>
        @if($product->category)
        <div class="text-center mt-6">
            <a href="/products?category={{ $product->category->slug }}"
               class="inline-flex items-center gap-2 border border-[#E63B2E] text-[#E63B2E] px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-[#E63B2E] hover:text-white transition-all">
                Voir tous les {{ $product->category->nom }}
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
        @endif
    </div>
    @endif

</div>
@endsection
