@extends('layouts.admin')
@section('title', 'Commandes')
@section('page_title', 'Gestion des Commandes')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <form method="GET" class="flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="N° commande ou client..."
               class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E] w-52">
        <select name="statut" class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
            <option value="">Tous les statuts</option>
            @foreach(\App\Models\Order::STATUTS as $key => $info)
            <option value="{{ $key }}" {{ request('statut') === $key ? 'selected' : '' }}>{{ $info['label'] }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-gray-100 hover:bg-gray-200 px-4 py-2.5 rounded-xl text-sm font-medium">Filtrer</button>
    </form>
    <a href="/admin/orders/export/csv" class="flex items-center gap-2 border border-gray-200 text-gray-700 hover:border-[#E63B2E] hover:text-[#E63B2E] px-4 py-2.5 rounded-xl text-sm font-medium transition-colors">
        <i data-lucide="download" class="w-4 h-4"></i> Export CSV
    </a>
</div>

<div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                <th class="text-left px-5 py-3 font-semibold text-gray-600 text-xs">N° Commande</th>
                <th class="text-left px-3 py-3 font-semibold text-gray-600 text-xs hidden md:table-cell">Client</th>
                <th class="text-left px-3 py-3 font-semibold text-gray-600 text-xs">Total</th>
                <th class="text-left px-3 py-3 font-semibold text-gray-600 text-xs">Statut</th>
                <th class="text-left px-3 py-3 font-semibold text-gray-600 text-xs hidden sm:table-cell">Date</th>
                <th class="px-3 py-3 font-semibold text-gray-600 text-xs text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($orders as $order)
            @php
            $sc = ['en_attente' => 'yellow', 'confirmee' => 'blue', 'expediee' => 'orange', 'livree' => 'green', 'annulee' => 'red'];
            $c  = $sc[$order->statut] ?? 'gray';
            @endphp
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3 font-semibold text-gray-800">{{ $order->numero_commande }}</td>
                <td class="px-3 py-3 hidden md:table-cell">
                    <p class="text-gray-700">{{ $order->user->name }}</p>
                    <p class="text-xs text-gray-400">{{ $order->user->email }}</p>
                </td>
                <td class="px-3 py-3 font-bold text-gray-800">{{ number_format($order->total, 0, ',', ' ') }} FCFA</td>
                <td class="px-3 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                        {{ $c === 'yellow' ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $c === 'blue'   ? 'bg-blue-100 text-blue-700'     : '' }}
                        {{ $c === 'orange' ? 'bg-orange-100 text-orange-700' : '' }}
                        {{ $c === 'green'  ? 'bg-green-100 text-green-700'   : '' }}
                        {{ $c === 'red'    ? 'bg-red-100 text-red-600'       : '' }}">
                        {{ $order->statut_label }}
                    </span>
                </td>
                <td class="px-3 py-3 text-gray-400 text-xs hidden sm:table-cell">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                <td class="px-3 py-3 text-right">
                    <a href="/admin/orders/{{ $order->id }}" class="inline-flex items-center gap-1 text-xs text-[#E63B2E] hover:underline font-medium">
                        Détail <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">Aucune commande</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="p-4 border-t border-gray-50">
        {{ $orders->withQueryString()->links() }}
    </div>
</div>
@endsection
