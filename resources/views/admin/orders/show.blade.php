@extends('layouts.admin')
@section('title', 'Commande ' . $order->numero_commande)
@section('page_title', 'Commande ' . $order->numero_commande)

@section('content')
<div class="flex items-center gap-3 mb-5">
    <a href="/admin/orders" class="p-2 bg-white border border-gray-200 rounded-xl hover:border-[#E63B2E] transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4 text-gray-500"></i>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Items & info --}}
    <div class="lg:col-span-2 space-y-5">
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h3 class="font-bold text-gray-800 mb-4">Articles commandés</h3>
            <div class="divide-y divide-gray-50">
                @foreach($order->items as $item)
                <div class="flex gap-3 py-3">
                    <img src="{{ $item->product->image_url ? '/image-proxy?url=' . urlencode($item->product->image_url) : '/images/placeholder.png' }}"
                         loading="lazy" class="w-12 h-12 object-cover rounded-xl flex-shrink-0" alt="{{ $item->product->nom }}">
                    <div class="flex-1">
                        <p class="font-medium text-gray-800 text-sm">{{ $item->product->nom }}</p>
                        <p class="text-xs text-gray-400">{{ number_format($item->prix_unitaire, 0, ',', ' ') }} FCFA × {{ $item->quantite }}</p>
                    </div>
                    <span class="font-bold text-gray-900 text-sm">{{ number_format($item->subtotal, 0, ',', ' ') }} FCFA</span>
                </div>
                @endforeach
            </div>
            <div class="border-t border-gray-100 pt-3 mt-2 space-y-1.5 text-sm">
                <div class="flex justify-between text-gray-500"><span>Sous-total</span><span>{{ number_format($order->total - $order->frais_livraison, 0, ',', ' ') }} FCFA</span></div>
                <div class="flex justify-between text-gray-500"><span>Livraison</span><span>{{ number_format($order->frais_livraison, 0, ',', ' ') }} FCFA</span></div>
                <div class="flex justify-between font-bold text-gray-900 pt-1 border-t border-gray-100"><span>Total</span><span class="text-[#E63B2E]">{{ number_format($order->total, 0, ',', ' ') }} FCFA</span></div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h3 class="font-bold text-gray-800 mb-3">Adresse de livraison</h3>
            @php $addr = $order->adresse_livraison; @endphp
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div><p class="text-xs text-gray-400">Nom</p><p class="font-medium text-gray-700">{{ $addr['nom'] ?? '—' }}</p></div>
                <div><p class="text-xs text-gray-400">Téléphone</p><p class="font-medium text-gray-700">{{ $addr['phone'] ?? '—' }}</p></div>
                <div class="col-span-2"><p class="text-xs text-gray-400">Adresse</p><p class="font-medium text-gray-700">{{ $addr['adresse'] ?? '—' }}, {{ $addr['ville'] ?? '—' }}, {{ $addr['pays'] ?? '—' }}</p></div>
                @if(!empty($addr['email']))
                <div class="col-span-2"><p class="text-xs text-gray-400">Email</p><p class="font-medium text-gray-700">{{ $addr['email'] }}</p></div>
                @endif
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h3 class="font-bold text-gray-800 mb-3">Informations</h3>
            <div class="space-y-2 text-sm">
                <div><p class="text-xs text-gray-400">Client</p><p class="font-medium text-gray-700">{{ $order->user->name }}</p><p class="text-xs text-gray-400">{{ $order->user->email }}</p></div>
                <div><p class="text-xs text-gray-400 mt-2">Date commande</p><p class="font-medium text-gray-700">{{ $order->created_at->format('d/m/Y à H:i') }}</p></div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h3 class="font-bold text-gray-800 mb-3">Mettre à jour le statut</h3>
            <form method="POST" action="/admin/orders/{{ $order->id }}/statut">
                @csrf
                <select name="statut" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E] mb-3">
                    @foreach(\App\Models\Order::STATUTS as $key => $info)
                    <option value="{{ $key }}" {{ $order->statut === $key ? 'selected' : '' }}>{{ $info['label'] }}</option>
                    @endforeach
                </select>
                <button type="submit" class="w-full bg-[#E63B2E] hover:bg-[#CC2F23] text-white py-2.5 rounded-xl text-sm font-semibold transition-colors">
                    Enregistrer
                </button>
            </form>
        </div>

        <a href="javascript:window.print()" class="flex items-center justify-center gap-2 border border-gray-200 text-gray-700 hover:border-[#E63B2E] hover:text-[#E63B2E] py-2.5 rounded-xl text-sm font-medium transition-colors">
            <i data-lucide="printer" class="w-4 h-4"></i> Imprimer
        </a>
    </div>
</div>
@endsection
