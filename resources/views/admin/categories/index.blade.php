@extends('layouts.admin')
@section('title', 'Catégories')
@section('page_title', 'Gestion des Catégories')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Add form --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-5 h-fit">
        <h3 class="font-bold text-gray-800 mb-4">Ajouter une catégorie</h3>
        <form method="POST" action="/admin/categories" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Nom *</label>
                <input type="text" name="nom" required placeholder="Ex: Électronique"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Icône Lucide</label>
                <input type="text" name="icone" placeholder="Ex: smartphone, shirt, home..."
                       class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
                <p class="text-xs text-gray-400 mt-1">Noms d'icônes Lucide (kebab-case)</p>
            </div>
            <button type="submit" class="w-full bg-[#E63B2E] hover:bg-[#CC2F23] text-white py-2.5 rounded-xl text-sm font-semibold transition-colors">
                Ajouter
            </button>
        </form>
    </div>

    {{-- List --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 text-xs">Catégorie</th>
                        <th class="text-left px-3 py-3 font-semibold text-gray-600 text-xs">Slug</th>
                        <th class="text-left px-3 py-3 font-semibold text-gray-600 text-xs">Produits</th>
                        <th class="text-left px-3 py-3 font-semibold text-gray-600 text-xs">Statut</th>
                        <th class="px-3 py-3 text-right font-semibold text-gray-600 text-xs">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($categories as $cat)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 bg-[#E63B2E]/10 rounded-lg flex items-center justify-center">
                                    <i data-lucide="{{ $cat->icone ?? 'grid' }}" class="w-3.5 h-3.5 text-[#E63B2E]"></i>
                                </div>
                                <span class="font-medium text-gray-800">{{ $cat->nom }}</span>
                            </div>
                        </td>
                        <td class="px-3 py-3 text-gray-400 text-xs font-mono">{{ $cat->slug }}</td>
                        <td class="px-3 py-3 font-semibold text-gray-700">{{ $cat->products_count }}</td>
                        <td class="px-3 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $cat->actif ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $cat->actif ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-right">
                            <form method="POST" action="/admin/categories/{{ $cat->id }}" class="inline" onsubmit="return confirm('Supprimer ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition-colors">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-gray-400">Aucune catégorie</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
