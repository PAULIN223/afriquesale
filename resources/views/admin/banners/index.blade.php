@extends('layouts.admin')
@section('title', 'Bannières')
@section('page_title', 'Gestion des Bannières Carousel')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Add form --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-5 h-fit">
        <h3 class="font-bold text-gray-800 mb-4">Ajouter une bannière</h3>
        <form method="POST" action="/admin/banners" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">URL de l'image *</label>
                <input type="url" name="image_url" required placeholder="https://..."
                       class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Titre</label>
                <input type="text" name="titre" placeholder="Ex: Nouveautés Été 2024"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Sous-titre</label>
                <input type="text" name="sous_titre" placeholder="Description courte"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Lien (URL)</label>
                <input type="text" name="lien" placeholder="/products"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Ordre</label>
                <input type="number" name="ordre" value="0"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
            </div>
            <button type="submit" class="w-full bg-[#E63B2E] hover:bg-[#CC2F23] text-white py-2.5 rounded-xl text-sm font-semibold transition-colors">
                Ajouter
            </button>
        </form>
    </div>

    {{-- List --}}
    <div class="lg:col-span-2 space-y-3">
        @forelse($banners as $banner)
        <div class="bg-white rounded-2xl border border-gray-100 p-4 flex gap-4 items-center">
            <img src="{{ '/image-proxy?url=' . urlencode($banner->image_url) }}"
                 alt="{{ $banner->titre }}" loading="lazy"
                 class="w-20 h-14 object-cover rounded-xl flex-shrink-0">
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-800 text-sm">{{ $banner->titre ?? 'Sans titre' }}</p>
                <p class="text-xs text-gray-400 truncate">{{ $banner->sous_titre }}</p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $banner->actif ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400' }}">
                        {{ $banner->actif ? 'Active' : 'Inactive' }}
                    </span>
                    <span class="text-xs text-gray-400">Ordre : {{ $banner->ordre }}</span>
                </div>
            </div>
            <form method="POST" action="/admin/banners/{{ $banner->id }}" onsubmit="return confirm('Supprimer ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="p-2 text-gray-400 hover:text-red-500 rounded-xl hover:bg-red-50 transition-colors">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
            <i data-lucide="image" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
            <p class="text-gray-400 text-sm">Aucune bannière. Ajoutez-en une ci-contre.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
