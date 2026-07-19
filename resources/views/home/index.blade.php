@extends('layouts.app')

@section('title', 'AfricSales — Marketplace Africaine')
@section('description', 'Découvrez les meilleurs produits africains. Livraison rapide et paiement sécurisé.')

@section('content')
@php
    $orgJsonLd = [
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => 'AfricSales',
        'url'      => url('/'),
        'logo'     => url('/images/logo.png'),
    ];

    $siteJsonLd = [
        '@context'        => 'https://schema.org',
        '@type'           => 'WebSite',
        'name'            => 'AfricSales',
        'url'             => url('/'),
        'potentialAction' => [
            '@type'       => 'SearchAction',
            'target'      => [
                '@type'       => 'EntryPoint',
                'urlTemplate' => url('/products') . '?search={search_term_string}',
            ],
            'query-input' => 'required name=search_term_string',
        ],
    ];
@endphp
<script type="application/ld+json">@json($orgJsonLd, JSON_UNESCAPED_UNICODE)</script>
<script type="application/ld+json">@json($siteJsonLd, JSON_UNESCAPED_UNICODE)</script>


{{-- Hero Carousel --}}
<section class="relative bg-gray-900">
    <div class="swiper hero-swiper">
        <div class="swiper-wrapper">
            @foreach($banners as $banner)
            <div class="swiper-slide">
                <div class="relative h-[320px] sm:h-[440px] md:h-[500px] overflow-hidden">
                    <img src="{{ $banner->image_url ? '/image-proxy?url=' . urlencode($banner->image_url) : '/images/placeholder.png' }}"
                         alt="{{ $banner->titre ?? 'AfricSales' }}"
                         loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                         class="w-full h-full object-cover object-center">
                    <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/30 to-transparent"></div>
                    @if($banner->titre)
                    <div class="absolute inset-0 flex items-center">
                        <div class="max-w-7xl mx-auto px-6 sm:px-10">
                            <div class="max-w-lg">
                                <p class="text-white/70 text-sm font-medium mb-2 uppercase tracking-widest">Découvrez</p>
                                <h2 class="text-white font-bold text-2xl sm:text-4xl md:text-5xl leading-tight mb-3">{{ $banner->titre }}</h2>
                                @if($banner->sous_titre)
                                <p class="text-white/80 text-base sm:text-lg mb-6">{{ $banner->sous_titre }}</p>
                                @endif
                                <a href="{{ $banner->lien ?? '/products' }}"
                                   class="inline-flex items-center gap-2 bg-[#E63B2E] hover:bg-[#CC2F23] text-white px-6 py-3 rounded-xl font-semibold transition-all hover:gap-3">
                                    Découvrir <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-prev !text-white !w-10 !h-10 !bg-white/20 rounded-full !backdrop-blur-sm hover:!bg-white/40 transition-all after:!text-sm"></div>
        <div class="swiper-button-next !text-white !w-10 !h-10 !bg-white/20 rounded-full !backdrop-blur-sm hover:!bg-white/40 transition-all after:!text-sm"></div>
    </div>
</section>

{{-- Trust badges --}}
<section class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach([
                ['icon' => 'truck', 'title' => 'Livraison rapide', 'sub' => 'Partout en Afrique'],
                ['icon' => 'shield-check', 'title' => 'Paiement sécurisé', 'sub' => 'Transaction protégée'],
                ['icon' => 'refresh-cw', 'title' => 'Retours faciles', 'sub' => '30 jours pour retourner'],
                ['icon' => 'headphones', 'title' => 'Support 24/7', 'sub' => 'Nous sommes là pour vous'],
            ] as $badge)
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-[#E63B2E]/10 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i data-lucide="{{ $badge['icon'] }}" class="w-5 h-5 text-[#E63B2E]"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">{{ $badge['title'] }}</p>
                    <p class="text-xs text-gray-400">{{ $badge['sub'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Featured Products --}}
@if($featured->count() > 0)
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Produits en vedette</h2>
            <p class="text-sm text-gray-500 mt-0.5">Sélection des meilleurs produits du moment</p>
        </div>
        <a href="/products" class="text-sm font-medium text-[#E63B2E] hover:underline flex items-center gap-1">
            Tout voir <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($featured as $product)
        @include('products._card', ['product' => $product])
        @endforeach
    </div>
</section>
@endif

{{-- Categories --}}
@if($categories->count() > 0)
<section class="bg-gray-50 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-7">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Parcourir par catégorie</h2>
            <p class="text-sm text-gray-500 mt-1">Trouvez exactement ce que vous cherchez</p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
            @foreach($categories->take(12) as $category)
            <a href="/products?category={{ $category->slug }}"
               class="bg-white rounded-2xl p-4 text-center hover:shadow-md hover:-translate-y-0.5 transition-all group border border-gray-100">
                <div class="w-12 h-12 bg-[#E63B2E]/10 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:bg-[#E63B2E] transition-colors">
                    <i data-lucide="{{ $category->icone ?? 'grid' }}" class="w-6 h-6 text-[#E63B2E] group-hover:text-white transition-colors"></i>
                </div>
                <p class="text-sm font-semibold text-gray-700 group-hover:text-[#E63B2E] transition-colors">{{ $category->nom }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $category->products_count ?? 0 }} produits</p>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Promo Banner --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
    <div class="bg-gradient-to-r from-[#1B3A6B] to-[#2D5A9E] rounded-2xl overflow-hidden relative">
        <div class="absolute inset-0 opacity-10">
            <div class="w-80 h-80 bg-white rounded-full -top-20 -right-20 absolute"></div>
            <div class="w-40 h-40 bg-white rounded-full top-10 right-40 absolute"></div>
        </div>
        <div class="relative z-10 p-8 sm:p-12 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div>
                <p class="text-white/70 text-sm font-medium uppercase tracking-widest mb-2">Offre spéciale</p>
                <h3 class="text-white font-bold text-2xl sm:text-3xl mb-2">Livraison gratuite</h3>
                <p class="text-white/80">Sur toutes vos commandes supérieures à 25 000 FCFA</p>
            </div>
            <a href="/products"
               class="flex-shrink-0 bg-[#E63B2E] hover:bg-[#CC2F23] text-white px-7 py-3.5 rounded-xl font-bold text-sm transition-all hover:scale-105">
                En profiter maintenant
            </a>
        </div>
    </div>
</section>

{{-- New Arrivals --}}
@if($newArrivals->count() > 0)
<section class="max-w-7xl mx-auto px-4 sm:px-6 pb-12">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Nouvelles arrivées</h2>
            <p class="text-sm text-gray-500 mt-0.5">Les derniers ajouts à notre catalogue</p>
        </div>
        <a href="/products?sort=newest" class="text-sm font-medium text-[#E63B2E] hover:underline flex items-center gap-1">
            Tout voir <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($newArrivals as $product)
        @include('products._card', ['product' => $product])
        @endforeach
    </div>
</section>
@endif

@endsection

@push('scripts')
<script>
new Swiper('.hero-swiper', {
    loop: true,
    autoplay: { delay: 5000, disableOnInteraction: false },
    pagination: { el: '.swiper-pagination', clickable: true },
    navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
    effect: 'fade',
    fadeEffect: { crossFade: true },
});
</script>
@endpush
