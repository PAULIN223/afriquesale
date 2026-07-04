@extends('layouts.admin')
@section('title', 'Produits')
@section('page_title', 'Gestion des Produits')
@section('page_subtitle', $products->total() . ' produit(s) au total')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <form method="GET" class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..."
               class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E] w-52">
        <select name="category" class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
            <option value="">Toutes catégories</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->nom }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-gray-100 hover:bg-gray-200 px-4 py-2.5 rounded-xl text-sm font-medium transition-colors">Filtrer</button>
    </form>
    <form method="POST" action="/admin/products/sync">
        @csrf
        <button type="submit" class="bg-[#E63B2E] hover:bg-[#CC2F23] text-white px-5 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 transition-colors">
            <i data-lucide="refresh-cw" class="w-4 h-4"></i> Sync Google Sheet
        </button>
    </form>
</div>

<div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                <th class="text-left px-5 py-3 font-semibold text-gray-600 text-xs">Produit</th>
                <th class="text-left px-3 py-3 font-semibold text-gray-600 text-xs hidden md:table-cell">Catégorie</th>
                <th class="text-left px-3 py-3 font-semibold text-gray-600 text-xs">Prix</th>
                <th class="text-left px-3 py-3 font-semibold text-gray-600 text-xs hidden sm:table-cell">Stock</th>
                <th class="text-left px-3 py-3 font-semibold text-gray-600 text-xs">Statut</th>
                <th class="px-3 py-3 font-semibold text-gray-600 text-xs text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($products as $product)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <img src="{{ $product->image_url ? '/image-proxy?url=' . urlencode($product->image_url) : '/images/placeholder.png' }}"
                             alt="{{ $product->nom }}" loading="lazy"
                             class="w-10 h-10 object-cover rounded-xl flex-shrink-0">
                        <p class="font-medium text-gray-800 line-clamp-1 max-w-[200px]">{{ $product->nom }}</p>
                    </div>
                </td>
                <td class="px-3 py-3 hidden md:table-cell text-gray-500">{{ $product->category?->nom ?? $product->categorie ?? '—' }}</td>
                <td class="px-3 py-3 font-semibold text-gray-800">{{ number_format($product->prix, 0, ',', ' ') }} FCFA</td>
                <td class="px-3 py-3 hidden sm:table-cell">
                    <span class="{{ $product->stock > 5 ? 'text-green-600' : ($product->stock > 0 ? 'text-orange-500' : 'text-red-500') }} font-medium">
                        {{ $product->stock }}
                    </span>
                </td>
                <td class="px-3 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $product->actif ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $product->actif ? 'Actif' : 'Inactif' }}
                    </span>
                </td>
                <td class="px-3 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <form method="POST" action="/admin/products/{{ $product->id }}/toggle">
                            @csrf
                            <button type="submit" class="text-xs px-2.5 py-1.5 rounded-lg border transition-colors
                                {{ $product->actif ? 'border-orange-200 text-orange-600 hover:bg-orange-50' : 'border-green-200 text-green-600 hover:bg-green-50' }}">
                                {{ $product->actif ? 'Désactiver' : 'Activer' }}
                            </button>
                        </form>
                        <form method="POST" action="/admin/products/{{ $product->id }}" onsubmit="return confirm('Supprimer ce produit ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 text-gray-400 hover:text-red-500 transition-colors rounded-lg hover:bg-red-50">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">Aucun produit. Cliquez sur "Sync Google Sheet" pour importer.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="p-4 border-t border-gray-50">
        {{ $products->withQueryString()->links() }}
    </div>
</div>
@endsection
