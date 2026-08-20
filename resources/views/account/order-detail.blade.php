@extends('layouts.app')
@section('title', 'Commande ' . $order->numero_commande)

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-8">
    <div class="flex items-center gap-3 mb-6">
        <a href="/account/orders" class="p-2 hover:bg-gray-100 rounded-xl transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5 text-gray-500"></i>
        </a>
        <h1 class="text-xl font-bold text-gray-900">Commande {{ $order->numero_commande }}</h1>
    </div>

    <div class="space-y-5">
        {{-- Status + info --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 grid sm:grid-cols-3 gap-4">
            <div>
                <p class="text-xs text-gray-400 mb-1">Statut</p>
                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">{{ $order->statut_label }}</span>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">Date</p>
                <p class="text-sm font-medium text-gray-700">{{ $order->created_at->format('d/m/Y à H:i') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">Total</p>
                <p class="text-sm font-bold text-gray-900">{{ number_format($order->total, 0, ',', ' ') }} FCFA</p>
            </div>
        </div>

        {{-- Items --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h3 class="font-bold text-gray-800 mb-4">Articles commandés</h3>
            <div class="divide-y divide-gray-50">
                @foreach($order->items as $item)
                <div class="flex gap-3 py-3 items-center">
                    <img src="{{ $item->image_src }}"
                         alt="{{ $item->display_name }}" loading="lazy"
                         class="w-14 h-14 object-cover rounded-xl flex-shrink-0">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-800 text-sm truncate">{{ $item->display_name }}</p>
                        <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                            <p class="text-xs text-gray-400">{{ number_format($item->prix_unitaire, 0, ',', ' ') }} FCFA × {{ $item->quantite }}</p>
                            @if(filled($item->product_color))
                            <span class="inline-flex items-center text-[10px] font-medium text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">
                                Couleur : {{ $item->product_color }}
                            </span>
                            @endif
                        </div>
                    </div>
                    <span class="font-bold text-gray-900 text-sm flex-shrink-0">{{ number_format($item->subtotal, 0, ',', ' ') }} FCFA</span>
                </div>
                @endforeach
            </div>
            <div class="border-t border-gray-100 pt-3 mt-1 flex justify-between font-bold text-gray-900">
                <span>Total</span>
                <span class="text-[#E63B2E]">{{ number_format($order->total, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>

        {{-- Delivery address --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h3 class="font-bold text-gray-800 mb-3">Adresse de livraison</h3>
            @php $addr = $order->adresse_livraison; @endphp
            <p class="text-sm text-gray-600">{{ $addr['nom'] ?? '' }}</p>
            <p class="text-sm text-gray-600">{{ $addr['phone'] ?? '' }}</p>
            <p class="text-sm text-gray-600">{{ $addr['adresse'] ?? '' }}</p>
            <p class="text-sm text-gray-600">{{ $addr['ville'] ?? '' }}, {{ $addr['pays'] ?? '' }}</p>
        </div>
    </div>
</div>
@endsection
