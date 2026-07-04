@extends('layouts.app')
@section('title', 'Mes Commandes — AfricSales')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-6">

    <div class="flex items-center gap-3 mb-6">
        <button onclick="history.back()" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 text-sm text-gray-500 hover:text-[#E63B2E] hover:border-[#E63B2E] transition-all">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Retour
        </button>
        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
            <i data-lucide="package" class="w-6 h-6 text-[#E63B2E]"></i>
            Mes Commandes
        </h1>
    </div>

    @if($orders->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
        <div class="w-20 h-20 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
            <i data-lucide="package" class="w-10 h-10 text-gray-200"></i>
        </div>
        <h3 class="font-bold text-gray-700 text-lg mb-2">Aucune commande</h3>
        <p class="text-gray-400 text-sm mb-6">Vous n'avez pas encore passé de commande.</p>
        <a href="/products" class="inline-flex items-center gap-2 bg-[#E63B2E] text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-[#CC2F23] transition-colors">
            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
            Commencer mes achats
        </a>
    </div>
    @else
    <div class="space-y-3">
        @foreach($orders as $order)
        @php
        $colorMap = [
            'en_attente' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700'],
            'confirmee'  => ['bg' => 'bg-blue-100',   'text' => 'text-blue-700'],
            'expediee'   => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700'],
            'livree'     => ['bg' => 'bg-green-100',  'text' => 'text-green-700'],
            'annulee'    => ['bg' => 'bg-red-100',    'text' => 'text-red-600'],
        ];
        $colors = $colorMap[$order->statut] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-600'];
        @endphp
        <div class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-sm transition-shadow">
            <div class="flex items-start justify-between flex-wrap gap-3">
                <div>
                    <p class="font-bold text-gray-900">{{ $order->numero_commande }}</p>
                    <p class="text-sm text-gray-400 mt-0.5">{{ $order->created_at->format('d/m/Y à H:i') }}</p>
                </div>
                <div class="text-right flex flex-col items-end gap-1.5">
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $colors['bg'] }} {{ $colors['text'] }}">
                        @if($order->statut === 'en_attente')
                        <i data-lucide="clock" class="w-3 h-3 inline mr-1"></i>
                        @elseif($order->statut === 'livree')
                        <i data-lucide="check-circle" class="w-3 h-3 inline mr-1"></i>
                        @elseif($order->statut === 'annulee')
                        <i data-lucide="x-circle" class="w-3 h-3 inline mr-1"></i>
                        @endif
                        {{ $order->statut_label }}
                    </span>
                    <p class="font-bold text-gray-900">{{ number_format($order->total, 0, ',', ' ') }} FCFA</p>
                </div>
            </div>

            {{-- Items preview --}}
            @if($order->items->count() > 0)
            <div class="mt-3 flex items-center gap-2 overflow-x-auto scrollbar-hide">
                @foreach($order->items->take(4) as $item)
                @if($item->product?->image_url)
                <img src="/image-proxy?url={{ urlencode($item->product->image_url) }}"
                     alt="{{ $item->product->nom }}"
                     class="w-10 h-10 rounded-lg object-cover flex-shrink-0 border border-gray-100">
                @endif
                @endforeach
                @if($order->items->count() > 4)
                <span class="text-xs text-gray-400 flex-shrink-0">+{{ $order->items->count() - 4 }} autres</span>
                @endif
            </div>
            @endif

            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-50 gap-3 flex-wrap">
                <span class="text-sm text-gray-400">{{ $order->items->count() }} article(s)</span>

                <div class="flex items-center gap-2">
                    {{-- Bouton annuler — seulement si en attente --}}
                    @if($order->statut === 'en_attente')
                    <form method="POST" action="/account/orders/{{ $order->id }}/cancel"
                          onsubmit="return confirm('Confirmer l\'annulation de la commande {{ $order->numero_commande }} ?')">
                        @csrf
                        <button type="submit"
                                class="text-sm text-red-500 hover:text-red-600 border border-red-200 hover:border-red-400 px-3 py-1.5 rounded-xl transition-all flex items-center gap-1">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i> Annuler
                        </button>
                    </form>
                    @endif
                    <a href="/account/orders/{{ $order->id }}"
                       class="text-sm text-[#E63B2E] hover:underline font-medium flex items-center gap-1">
                        Détail <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-6">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
