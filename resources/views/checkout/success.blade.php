@extends('layouts.app')
@section('title', 'Commande envoyée — AfricSales')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 py-12">
    <div class="bg-white rounded-2xl border border-gray-100 p-8 text-center">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-5">
            <i data-lucide="check-circle-2" class="w-10 h-10 text-green-500"></i>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Commande Envoyée</h1>
        <p class="text-gray-500 mb-1">Merci pour votre achat, <strong>{{ auth()->user()->name }}</strong>.</p>
        <p class="text-gray-400 text-sm mb-6">Votre commande <span class="font-bold text-gray-800">{{ $order->numero_commande }}</span> et votre preuve de paiement ont bien été transmises au vendeur.</p>

        <div class="bg-gray-50 rounded-2xl p-5 text-left mb-6">
            <h3 class="font-semibold text-gray-800 mb-3">Récapitulatif de la commande</h3>
            <div class="divide-y divide-gray-200/50">
                @foreach($order->items as $item)
                <div class="flex items-center gap-3 py-3 first:pt-0 last:pb-0">
                    <img src="{{ $item->image_src }}" 
                         alt="{{ $item->display_name }}"
                         class="w-12 h-12 object-cover rounded-lg flex-shrink-0 border border-gray-150">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $item->display_name }}</p>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-xs text-gray-400">Qté : {{ $item->quantite }}</span>
                            @if(filled($item->product_color))
                            <span class="text-[10px] font-medium text-gray-500 bg-white border border-gray-100 px-1.5 py-0.5 rounded">
                                Couleur : {{ $item->product_color }}
                            </span>
                            @endif
                        </div>
                    </div>
                    <span class="font-semibold text-gray-900 text-sm flex-shrink-0">{{ number_format($item->subtotal, 0, ',', ' ') }} FCFA</span>
                </div>
                @endforeach
            </div>
            <div class="border-t border-gray-200 mt-3 pt-3 flex justify-between font-bold text-gray-900">
                <span>Total</span>
                <span class="text-[#E63B2E]">{{ number_format($order->total, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>

        <div class="bg-blue-50 rounded-xl p-4 text-sm text-blue-700 flex items-start gap-2 text-left mb-6">
            <i data-lucide="info" class="w-4 h-4 flex-shrink-0 mt-0.5"></i>
            <p>Notre équipe va vérifier votre paiement et confirmera votre commande sous peu.</p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="/account/orders/{{ $order->id }}"
               class="flex-1 bg-[#E63B2E] hover:bg-[#CC2F23] text-white py-3 rounded-xl font-semibold text-sm transition-colors flex items-center justify-center gap-2">
                <i data-lucide="package" class="w-4 h-4"></i> Suivre ma commande
            </a>
            <a href="/products"
               class="flex-1 border border-gray-200 text-gray-700 hover:border-[#E63B2E] hover:text-[#E63B2E] py-3 rounded-xl font-semibold text-sm transition-colors flex items-center justify-center gap-2">
                <i data-lucide="shopping-bag" class="w-4 h-4"></i> Continuer les achats
            </a>
        </div>
    </div>
</div>
@endsection
