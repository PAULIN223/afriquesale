@extends('layouts.app')

@section('title', 'Catégorie — AfricSales')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    {{-- Back button --}}
    <button onclick="history.back()" class="flex items-center gap-1.5 text-sm text-gray-400 hover:text-[#E63B2E] mb-4 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Retour
    </button>

    {{-- Horizontal category chips --}}
    <div class="flex items-center gap-2 overflow-x-auto scrollbar-hide pb-3 mb-5 -mx-1 px-1">
        <a href="/products"
           class="flex-shrink-0 px-4 py-1.5 rounded-full text-sm font-semibold border transition-all whitespace-nowrap
                  {{ !request('category') ? 'bg-[#E63B2E] text-white border-[#E63B2E]' : 'border-gray-200 text-gray-600 hover:border-[#E63B2E] hover:text-[#E63B2E]' }}">
            Tout voir
        </a>
        @foreach($categories as $cat)
        <a href="/products?category={{ $cat->slug }}"
           class="flex-shrink-0 px-4 py-1.5 rounded-full text-sm font-semibold border transition-all whitespace-nowrap
                  {{ request('category') === $cat->slug ? 'bg-[#E63B2E] text-white border-[#E63B2E]' : 'border-gray-200 text-gray-600 hover:border-[#E63B2E] hover:text-[#E63B2E]' }}">
            {{ $cat->nom }}
        </a>
        @endforeach
    </div>

    {{-- Search + Sort bar --}}
    <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
        <div class="flex items-center gap-3 flex-wrap">
            <p class="text-sm text-gray-600">
                <span class="font-semibold text-gray-900">{{ $products->total() }}</span> produits
                @if(request('search')) — "<span class="text-[#E63B2E] font-medium">{{ request('search') }}</span>" @endif
                @if(request('category'))
                    @php $activeCat = $categories->firstWhere('slug', request('category')); @endphp
                    @if($activeCat) — <span class="font-medium text-gray-700">{{ $activeCat->nom }}</span> @endif
                @endif
            </p>
            @if(request()->hasAny(['category', 'prix_min', 'prix_max', 'search']))
            <a href="/products" class="text-xs text-gray-400 hover:text-[#E63B2E] underline transition-colors">
                Réinitialiser
            </a>
            @endif
        </div>

        <form method="GET" action="/products" id="filterForm">
            @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
            @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
            @if(request('prix_min'))<input type="hidden" name="prix_min" value="{{ request('prix_min') }}">@endif
            @if(request('prix_max'))<input type="hidden" name="prix_max" value="{{ request('prix_max') }}">@endif
            <select name="sort" onchange="this.form.submit()"
                    class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-[#E63B2E] bg-white">
                <option value="newest"     {{ request('sort', 'newest') === 'newest'     ? 'selected' : '' }}>Plus récents</option>
                <option value="price_asc"  {{ request('sort') === 'price_asc'  ? 'selected' : '' }}>Prix croissant</option>
                <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Prix décroissant</option>
                <option value="name_asc"   {{ request('sort') === 'name_asc'   ? 'selected' : '' }}>Nom A-Z</option>
            </select>
        </form>
    </div>

    {{-- Price filter (collapsed by default) --}}
    <div x-data="{ open: false }" class="mb-5">
        <button @click="open = !open" class="flex items-center gap-2 text-sm text-gray-500 hover:text-[#E63B2E] transition-colors mb-2">
            <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
            <span x-text="open ? 'Masquer les filtres de prix' : 'Filtrer par prix'"></span>
        </button>
        <div x-show="open" x-transition class="flex items-center gap-2">
            <form method="GET" action="/products" class="flex items-center gap-2 flex-wrap">
                @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
                @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
                <input type="number" name="prix_min" placeholder="Prix min" value="{{ request('prix_min') }}"
                       class="w-32 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-[#E63B2E]">
                <span class="text-gray-400">→</span>
                <input type="number" name="prix_max" placeholder="Prix max" value="{{ request('prix_max') }}"
                       class="w-32 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-[#E63B2E]">
                <button type="submit" class="bg-[#E63B2E] text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-[#CC2F23] transition-colors">
                    Filtrer
                </button>
            </form>
        </div>
    </div>

    {{-- Products grid --}}
    @if($products->isEmpty())
    <div class="text-center py-20">
        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i data-lucide="search-x" class="w-8 h-8 text-gray-300"></i>
        </div>
        <h3 class="font-semibold text-gray-700 mb-2">Aucun produit trouvé</h3>
        <p class="text-gray-400 text-sm mb-4">Essayez une autre catégorie ou modifiez vos filtres</p>
        <a href="/products" class="inline-flex items-center gap-2 bg-[#E63B2E] text-white px-5 py-2.5 rounded-xl text-sm font-semibold">
            Voir tous les produits
        </a>
    </div>
    @else
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4">
        @foreach($products as $product)
        @include('products._card', ['product' => $product])
        @endforeach
    </div>

    <div class="mt-8">
        {{ $products->links() }}
    </div>
    @endif

</div>
@endsection
