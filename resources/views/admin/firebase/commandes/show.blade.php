@extends('layouts.admin')
@section('title', 'Détail Commande')
@section('page_title', 'Commande Android')
@section('page_subtitle', substr($order['id'], 0, 20) . '…')

@section('content')
@php
$statuts    = ['en_attente','confirmée','expédiée','livrée','annulée'];
$colors     = ['en_attente'=>'yellow','confirmée'=>'blue','expédiée'=>'orange','livrée'=>'green','annulée'=>'red'];
$currentSt  = $order['statut'] ?? $order['status'] ?? 'en_attente';
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Infos commande --}}
    <div class="lg:col-span-2 space-y-5">
        {{-- Produits commandés --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Produits commandés</h3>
            @forelse($order['items'] ?? [] as $item)
            <div class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0">
                @if(!empty($item['image']))
                <img src="/image-proxy?url={{ urlencode($item['image']) }}"
                     class="w-12 h-12 rounded-xl object-cover flex-shrink-0 bg-gray-50" loading="lazy">
                @else
                <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="package" class="w-5 h-5 text-gray-300"></i>
                </div>
                @endif
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-800">{{ $item['title'] ?? $item['name'] ?? '—' }}</p>
                    <p class="text-xs text-gray-400">
                        Qté : {{ $item['quantity'] ?? 1 }}
                        @if(!empty($item['size'])) · Taille : {{ $item['size'] }} @endif
                        @if(!empty($item['color'])) · Couleur : {{ $item['color'] }} @endif
                    </p>
                </div>
                <span class="text-sm font-bold text-gray-800">{{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 0, ',', ' ') }} FCFA</span>
            </div>
            @empty
            <p class="text-sm text-gray-400">Aucun produit</p>
            @endforelse

            <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between">
                <span class="font-semibold text-gray-700">Total</span>
                <span class="text-xl font-bold text-gray-900">{{ number_format($order['finalTotal'] ?? $order['total'] ?? $order['baseTotal'] ?? 0, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>

        {{-- Adresse livraison --}}
        @if(!empty($order['adresse']) || !empty($order['address']))
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-3">Adresse de livraison</h3>
            <p class="text-sm text-gray-600">{{ $order['adresse'] ?? $order['address'] ?? '—' }}</p>
        </div>
        @endif
    </div>

    {{-- Statut + actions --}}
    <div class="space-y-4">
        {{-- Statut actuel --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm">Statut actuel</h3>
            @php $c = $colors[$currentSt] ?? 'gray'; @endphp
            <span class="inline-flex px-3 py-1.5 rounded-xl text-sm font-semibold
                {{ $c==='yellow'?'bg-yellow-100 text-yellow-700':'' }}
                {{ $c==='blue'  ?'bg-blue-100 text-blue-700'    :'' }}
                {{ $c==='orange'?'bg-orange-100 text-orange-700':'' }}
                {{ $c==='green' ?'bg-green-100 text-green-700'  :'' }}
                {{ $c==='red'   ?'bg-red-100 text-red-600'      :'' }}">
                {{ $currentSt }}
            </span>
        </div>

        {{-- Changer statut --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm">Changer le statut</h3>
            <form method="POST" action="{{ route('admin.commandes.statut', $order['id']) }}" class="space-y-3">
                @csrf
                <select name="statut" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
                    @foreach($statuts as $s)
                    <option value="{{ $s }}" {{ $currentSt===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <button class="w-full py-2.5 bg-[#E63B2E] hover:bg-[#CC2F23] text-white rounded-xl text-sm font-semibold">
                    Mettre à jour + Notifier client
                </button>
            </form>
            <p class="text-xs text-gray-400 mt-2 text-center">Une notification Push sera envoyée automatiquement</p>
        </div>

        {{-- Client --}}
        @if($client)
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm">Client</h3>
            <p class="font-medium text-gray-800">{{ $client['name'] ?? '—' }}</p>
            <p class="text-xs text-gray-400">{{ $client['email'] ?? '—' }}</p>
            <a href="{{ route('admin.clients.show', $client['id']) }}" class="mt-2 inline-block text-xs text-[#E63B2E] hover:underline">Voir le profil →</a>
        </div>
        @endif
    </div>
</div>
@endsection
