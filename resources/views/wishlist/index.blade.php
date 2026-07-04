@extends('layouts.app')
@section('title', 'Mes Préférences — AfricSales')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-6">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <button onclick="history.back()" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 text-sm text-gray-500 hover:text-[#E63B2E] hover:border-[#E63B2E] transition-all">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Retour
        </button>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i data-lucide="heart" class="w-6 h-6 text-[#E63B2E]"></i>
                Mes Préférences
                @if($items->count() > 0)
                <span class="text-base font-normal text-gray-400">({{ $items->count() }})</span>
                @endif
            </h1>
            <p class="text-sm text-gray-400 mt-0.5">Les articles que vous avez aimés</p>
        </div>
    </div>

    @if($items->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 p-16 text-center">
        <div class="w-20 h-20 bg-[#E63B2E]/10 rounded-2xl flex items-center justify-center mx-auto mb-5">
            <i data-lucide="heart" class="w-10 h-10 text-[#E63B2E]"></i>
        </div>
        <h3 class="font-bold text-gray-800 text-lg mb-2">Aucun article en préférence</h3>
        <p class="text-gray-400 text-sm mb-6">Cliquez sur le ❤️ d'un produit pour l'ajouter à vos préférences</p>
        <a href="/products" class="inline-flex items-center gap-2 bg-[#E63B2E] text-white px-6 py-3 rounded-xl text-sm font-semibold hover:bg-[#CC2F23] transition-colors">
            <i data-lucide="grid-2x2" class="w-4 h-4"></i>
            Parcourir les catégories
        </a>
    </div>
    @else

    {{-- Quick add all to cart --}}
    @auth
    <div class="bg-gradient-to-r from-[#E63B2E]/5 to-[#E63B2E]/10 border border-[#E63B2E]/20 rounded-2xl p-4 mb-6 flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-[#E63B2E] rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="shopping-cart" class="w-5 h-5 text-white"></i>
            </div>
            <div>
                <p class="font-semibold text-gray-800 text-sm">{{ $items->count() }} article(s) dans vos préférences</p>
                <p class="text-xs text-gray-500">Ajoutez-les au panier pour commander</p>
            </div>
        </div>
        <a href="/cart" class="flex-shrink-0 bg-[#E63B2E] text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-[#CC2F23] transition-colors flex items-center gap-2">
            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
            Voir mon panier
        </a>
    </div>
    @endauth

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4">
        @foreach($items as $item)
        @if($item->product)
        @include('products._card', ['product' => $item->product])
        @endif
        @endforeach
    </div>

    @endif
</div>
@endsection
