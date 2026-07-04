@extends('layouts.admin')
@section('title', 'Clients Android')
@section('page_title', 'Clients Android')
@section('page_subtitle', $total . ' client(s) dans l\'app Android')

@section('content')

{{-- Filtres --}}
<form method="GET" class="flex flex-wrap gap-2 mb-5">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom ou email..."
           class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E] w-52">
    <select name="statut" class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
        <option value="">Tous les statuts</option>
        <option value="actif"   {{ request('statut') === 'actif'   ? 'selected' : '' }}>Actif</option>
        <option value="bloqué"  {{ request('statut') === 'bloqué'  ? 'selected' : '' }}>Bloqué</option>
    </select>
    <button type="submit" class="bg-gray-100 hover:bg-gray-200 px-4 py-2.5 rounded-xl text-sm font-medium">Filtrer</button>
    @if(request()->hasAny(['search','statut']))
    <a href="{{ route('admin.clients.index') }}" class="px-4 py-2.5 rounded-xl text-sm text-gray-400 hover:text-[#E63B2E] border border-gray-200">Réinitialiser</a>
    @endif
</form>

<div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600">Client</th>
                <th class="text-left px-3 py-3 text-xs font-semibold text-gray-600 hidden md:table-cell">Téléphone</th>
                <th class="text-left px-3 py-3 text-xs font-semibold text-gray-600 hidden lg:table-cell">Source</th>
                <th class="text-left px-3 py-3 text-xs font-semibold text-gray-600">Statut</th>
                <th class="text-left px-3 py-3 text-xs font-semibold text-gray-600 hidden xl:table-cell">Commandes</th>
                <th class="text-right px-3 py-3 text-xs font-semibold text-gray-600">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($users as $user)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3">
                    <div>
                        <p class="font-medium text-gray-800">{{ $user['name'] ?? $user['nom'] ?? '—' }}</p>
                        <p class="text-xs text-gray-400">{{ $user['email'] ?? '—' }}</p>
                    </div>
                </td>
                <td class="px-3 py-3 text-gray-500 hidden md:table-cell">{{ $user['phone'] ?? $user['telephone'] ?? '—' }}</td>
                <td class="px-3 py-3 hidden lg:table-cell">
                    <span class="text-xs px-2 py-0.5 bg-blue-50 text-blue-600 rounded-full">{{ $user['source'] ?? 'android' }}</span>
                </td>
                <td class="px-3 py-3">
                    @php $statut = $user['statut'] ?? 'actif'; @endphp
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                        {{ $statut === 'actif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                        {{ ucfirst($statut) }}
                    </span>
                </td>
                <td class="px-3 py-3 hidden xl:table-cell">
                    @php $oc = $user['orderCounts'] ?? array_fill_keys(array_keys($statutLabels), 0); @endphp
                    <div class="flex flex-wrap gap-1 max-w-[220px]">
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full font-medium bg-yellow-100 text-yellow-700">En attente {{ $oc['en_attente'] }}</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full font-medium bg-blue-100 text-blue-700">Confirmées {{ $oc['confirmée'] }}</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full font-medium bg-orange-100 text-orange-700">Expédiées {{ $oc['expédiée'] }}</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full font-medium bg-green-100 text-green-700">Livrées {{ $oc['livrée'] }}</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full font-medium bg-red-100 text-red-600">Annulées {{ $oc['annulée'] }}</span>
                    </div>
                </td>
                <td class="px-3 py-3 text-right">
                    <div class="flex items-center justify-end gap-1.5">
                        <a href="{{ route('admin.clients.show', $user['id']) }}"
                           class="text-xs px-2.5 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">
                            Voir
                        </a>
                        @if(($user['statut'] ?? 'actif') === 'bloqué')
                        <form method="POST" action="{{ route('admin.clients.unblock', $user['id']) }}">
                            @csrf
                            <button class="text-xs px-2.5 py-1.5 rounded-lg border border-green-200 text-green-600 hover:bg-green-50">Débloquer</button>
                        </form>
                        @else
                        <form method="POST" action="{{ route('admin.clients.block', $user['id']) }}">
                            @csrf
                            <button class="text-xs px-2.5 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50">Bloquer</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">
                <i data-lucide="users" class="w-8 h-8 mx-auto mb-2 opacity-30"></i>
                Aucun client Android trouvé
            </td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
