@extends('layouts.admin')
@section('title', 'Commandes Android')
@section('page_title', 'Commandes Android')
@section('page_subtitle', $stats['total'] . ' commande(s) au total')

@section('content')

{{-- Montant total --}}
<div class="bg-white rounded-2xl border border-gray-100 p-4 mb-4 flex items-center justify-between">
    <div>
        <p class="text-xs text-gray-400">Montant total des commandes affichées</p>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['montant'], 0, ',', ' ') }} FCFA</p>
    </div>
    <i data-lucide="wallet" class="w-8 h-8 text-[#E63B2E]/30"></i>
</div>

{{-- KPI statuts --}}
<div class="grid grid-cols-3 sm:grid-cols-5 gap-3 mb-5">
    @foreach([
        ['label'=>'En attente',  'key'=>'en_attente', 'color'=>'yellow'],
        ['label'=>'Confirmées',  'key'=>'confirmee',  'color'=>'blue'],
        ['label'=>'Expédiées',   'key'=>'expediee',   'color'=>'orange'],
        ['label'=>'Livrées',     'key'=>'livree',     'color'=>'green'],
        ['label'=>'Annulées',    'key'=>'annulee',    'color'=>'red'],
    ] as $s)
    <div class="bg-white rounded-2xl border border-gray-100 p-3 text-center">
        <p class="text-xl font-bold text-gray-800">{{ $stats[$s['key']] }}</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ $s['label'] }}</p>
    </div>
    @endforeach
</div>

{{-- Filtres --}}
<form method="GET" class="flex flex-wrap gap-2 mb-5">
    <select name="statut" class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
        <option value="">Tous les statuts</option>
        @foreach(['en_attente','confirmée','expédiée','livrée','annulée'] as $s)
        <option value="{{ $s }}" {{ request('statut')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <input type="date" name="dateDebut" value="{{ request('dateDebut') }}"
           class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
    <input type="date" name="dateFin" value="{{ request('dateFin') }}"
           class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
    <button type="submit" class="bg-gray-100 hover:bg-gray-200 px-4 py-2.5 rounded-xl text-sm font-medium">Filtrer</button>
    @if(request()->hasAny(['statut','dateDebut','dateFin']))
    <a href="{{ route('admin.commandes.index') }}" class="px-4 py-2.5 rounded-xl text-sm text-gray-400 hover:text-[#E63B2E] border border-gray-200">Réinitialiser</a>
    @endif
</form>

<div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600">Commande</th>
                <th class="text-left px-3 py-3 text-xs font-semibold text-gray-600 hidden md:table-cell">Client</th>
                <th class="text-left px-3 py-3 text-xs font-semibold text-gray-600">Statut</th>
                <th class="text-left px-3 py-3 text-xs font-semibold text-gray-600 hidden sm:table-cell">Date</th>
                <th class="text-left px-3 py-3 text-xs font-semibold text-gray-600">Total</th>
                <th class="text-right px-3 py-3 text-xs font-semibold text-gray-600">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($orders as $order)
            @php
            $orderStatut = $order['statut'] ?? $order['status'] ?? 'en_attente';
            $colors = ['en_attente'=>['bg-yellow-100','text-yellow-700'],'confirmée'=>['bg-blue-100','text-blue-700'],'expédiée'=>['bg-orange-100','text-orange-700'],'livrée'=>['bg-green-100','text-green-700'],'annulée'=>['bg-red-100','text-red-600']];
            [$bg,$tx] = $colors[$orderStatut] ?? ['bg-gray-100','text-gray-500'];
            @endphp
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 font-medium text-gray-800 font-mono text-xs">{{ substr($order['id'], 0, 12) }}…</td>
                <td class="px-3 py-3 text-gray-500 hidden md:table-cell text-xs">{{ $order['userName'] ?? ($order['userId'] ? substr($order['userId'], 0, 10) . '…' : '—') }}</td>
                <td class="px-3 py-3"><span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $bg }} {{ $tx }}">{{ $orderStatut }}</span></td>
                <td class="px-3 py-3 text-gray-400 text-xs hidden sm:table-cell">{{ isset($order['createdAt']) ? date('d/m/Y', strtotime($order['createdAt'])) : '—' }}</td>
                <td class="px-3 py-3 font-bold text-gray-800">{{ number_format($order['finalTotal'] ?? $order['total'] ?? $order['baseTotal'] ?? 0, 0, ',', ' ') }} FCFA</td>
                <td class="px-3 py-3 text-right">
                    <a href="{{ route('admin.commandes.show', $order['id']) }}"
                       class="text-xs px-2.5 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">Détail</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">
                <i data-lucide="clipboard-list" class="w-8 h-8 mx-auto mb-2 opacity-30"></i>
                Aucune commande Android trouvée
            </td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
