@extends('layouts.admin')
@section('title', 'Produits Firebase')
@section('page_title', 'Produits Firestore')
@section('page_subtitle', count($products) . ' produit(s) dans Firebase')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <form method="GET" class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..."
               class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E] w-52">
        <button type="submit" class="bg-gray-100 hover:bg-gray-200 px-4 py-2.5 rounded-xl text-sm font-medium">Filtrer</button>
    </form>
    <form method="POST" action="{{ route('admin.produits.sync') }}">
        @csrf
        <button class="bg-[#E63B2E] hover:bg-[#CC2F23] text-white px-5 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2">
            <i data-lucide="refresh-cw" class="w-4 h-4"></i> Sync Google Sheet
        </button>
    </form>
</div>

<div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600">Produit</th>
                <th class="text-left px-3 py-3 text-xs font-semibold text-gray-600 hidden md:table-cell">Catégorie</th>
                <th class="text-left px-3 py-3 text-xs font-semibold text-gray-600">Prix</th>
                <th class="text-left px-3 py-3 text-xs font-semibold text-gray-600">Statut</th>
                <th class="text-right px-3 py-3 text-xs font-semibold text-gray-600">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($products as $product)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        @if(!empty($product['image'] ?? $product['imageUrl'] ?? ''))
                        <img src="/image-proxy?url={{ urlencode($product['image'] ?? $product['imageUrl'] ?? '') }}"
                             class="w-10 h-10 rounded-xl object-cover flex-shrink-0" loading="lazy">
                        @else
                        <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="package" class="w-4 h-4 text-gray-300"></i>
                        </div>
                        @endif
                        <p class="font-medium text-gray-800 line-clamp-1 max-w-[200px]">{{ $product['nom'] ?? $product['name'] ?? '—' }}</p>
                    </div>
                </td>
                <td class="px-3 py-3 text-gray-500 hidden md:table-cell">{{ $product['categorie'] ?? $product['category'] ?? '—' }}</td>
                <td class="px-3 py-3 font-semibold text-gray-800">{{ number_format($product['prix'] ?? $product['price'] ?? 0, 0, ',', ' ') }} FCFA</td>
                <td class="px-3 py-3">
                    @php $actif = $product['actif'] ?? true; @endphp
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $actif ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $actif ? 'Actif' : 'Inactif' }}
                    </span>
                </td>
                <td class="px-3 py-3 text-right">
                    <form method="POST" action="{{ route('admin.produits.toggle', $product['id']) }}">
                        @csrf
                        <button class="text-xs px-2.5 py-1.5 rounded-lg border transition-colors
                            {{ $actif ? 'border-orange-200 text-orange-600 hover:bg-orange-50' : 'border-green-200 text-green-600 hover:bg-green-50' }}">
                            {{ $actif ? 'Désactiver' : 'Activer' }}
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-12 text-center text-gray-400">
                <i data-lucide="boxes" class="w-8 h-8 mx-auto mb-2 opacity-30"></i>
                Aucun produit dans Firestore. Cliquez "Sync Google Sheet" pour synchroniser.
            </td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
