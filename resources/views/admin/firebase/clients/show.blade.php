@extends('layouts.admin')
@section('title', 'Profil Client')
@section('page_title', $user['name'] ?? 'Profil Client')
@section('page_subtitle', $user['email'] ?? '')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Profil --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-[#E63B2E]/10 rounded-2xl flex items-center justify-center">
                    <span class="text-[#E63B2E] font-bold text-lg">{{ strtoupper(substr($user['name'] ?? 'C', 0, 1)) }}</span>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">{{ $user['name'] ?? '—' }}</p>
                    <p class="text-xs text-gray-400">{{ $user['email'] ?? '—' }}</p>
                </div>
            </div>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-400">Téléphone</dt><dd class="font-medium">{{ $user['phone'] ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Source</dt><dd><span class="bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full text-xs">{{ $user['source'] ?? 'android' }}</span></dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Statut</dt>
                    <dd><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ ($user['statut'] ?? 'actif') === 'actif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">{{ $user['statut'] ?? 'actif' }}</span></dd>
                </div>
                <div class="flex justify-between"><dt class="text-gray-400">Inscrit le</dt><dd class="text-xs">{{ $user['createdAt'] ? date('d/m/Y', strtotime($user['createdAt'])) : '—' }}</dd></div>
            </dl>
        </div>

        {{-- Actions --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-3">
            <h3 class="font-semibold text-gray-800 text-sm mb-3">Actions</h3>
            @if(($user['statut'] ?? 'actif') === 'bloqué')
            <form method="POST" action="{{ route('admin.clients.unblock', $user['id']) }}">
                @csrf
                <button class="w-full py-2.5 rounded-xl bg-green-50 text-green-600 text-sm font-medium hover:bg-green-100">Débloquer l'utilisateur</button>
            </form>
            @else
            <form method="POST" action="{{ route('admin.clients.block', $user['id']) }}">
                @csrf
                <button class="w-full py-2.5 rounded-xl bg-red-50 text-red-600 text-sm font-medium hover:bg-red-100">Bloquer l'utilisateur</button>
            </form>
            @endif

            {{-- Notification --}}
            <form method="POST" action="{{ route('admin.clients.notify', $user['id']) }}" class="space-y-2 pt-2 border-t border-gray-100">
                @csrf
                <p class="text-xs font-semibold text-gray-600">Envoyer une notification</p>
                <input type="text" name="title" placeholder="Titre" required
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-[#E63B2E]">
                <textarea name="body" rows="2" placeholder="Message..." required
                          class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-[#E63B2E] resize-none"></textarea>
                <button class="w-full py-2.5 rounded-xl bg-[#E63B2E] text-white text-sm font-medium hover:bg-[#CC2F23]">
                    <i data-lucide="bell" class="w-3.5 h-3.5 inline mr-1"></i> Envoyer
                </button>
            </form>
        </div>
    </div>

    {{-- Commandes --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Historique des commandes ({{ count($orders) }})</h3>
            @forelse($orders as $order)
            @php
            $orderStatut = $order['statut'] ?? $order['status'] ?? 'en_attente';
            $colors = ['en_attente'=>'yellow','confirmée'=>'blue','expédiée'=>'orange','livrée'=>'green','annulée'=>'red'];
            $c = $colors[$orderStatut] ?? 'gray';
            @endphp
            <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $order['id'] }}</p>
                    <p class="text-xs text-gray-400">{{ isset($order['createdAt']) ? date('d/m/Y H:i', strtotime($order['createdAt'])) : '—' }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                        {{ $c==='yellow'?'bg-yellow-100 text-yellow-700':'' }}
                        {{ $c==='blue'  ?'bg-blue-100 text-blue-700'    :'' }}
                        {{ $c==='orange'?'bg-orange-100 text-orange-700':'' }}
                        {{ $c==='green' ?'bg-green-100 text-green-700'  :'' }}
                        {{ $c==='red'   ?'bg-red-100 text-red-600'      :'' }}
                        {{ $c==='gray'  ?'bg-gray-100 text-gray-500'    :'' }}">{{ $orderStatut }}</span>
                    <span class="text-sm font-bold text-gray-800">{{ number_format($order['finalTotal'] ?? $order['total'] ?? $order['baseTotal'] ?? 0, 0, ',', ' ') }} FCFA</span>
                    <a href="{{ route('admin.commandes.show', $order['id']) }}" class="text-xs text-[#E63B2E] hover:underline">Voir</a>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-6">Aucune commande</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
