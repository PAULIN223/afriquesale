<div>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <h1 class="font-semibold text-gray-800 text-lg">Gestion des Clients</h1>
        <div class="flex gap-2 items-center">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Nom ou email..."
                   class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E] w-64">
            <a href="/admin/customers/export/csv" class="flex items-center gap-2 border border-gray-200 text-gray-700 hover:border-[#E63B2E] hover:text-[#E63B2E] px-4 py-2.5 rounded-xl text-sm font-medium transition-colors">
                <i data-lucide="download" class="w-4 h-4"></i> Export CSV
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden" wire:loading.class="opacity-60">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-5 py-3 font-semibold text-gray-600 text-xs">Client</th>
                    <th class="text-left px-3 py-3 font-semibold text-gray-600 text-xs hidden md:table-cell">Téléphone</th>
                    <th class="text-left px-3 py-3 font-semibold text-gray-600 text-xs hidden sm:table-cell">Commandes</th>
                    <th class="text-left px-3 py-3 font-semibold text-gray-600 text-xs hidden sm:table-cell">Inscrit le</th>
                    <th class="text-left px-3 py-3 font-semibold text-gray-600 text-xs">Statut</th>
                    <th class="px-3 py-3 font-semibold text-gray-600 text-xs text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($customers as $customer)
                <tr class="hover:bg-gray-50" wire:key="customer-{{ $customer->id }}">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-[#E63B2E]/10 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-[#E63B2E] font-bold text-sm">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">{{ $customer->name }}</p>
                                <p class="text-xs text-gray-400">{{ $customer->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-3 text-gray-500 hidden md:table-cell">{{ $customer->phone ?? '—' }}</td>
                    <td class="px-3 py-3 font-semibold text-gray-800 hidden sm:table-cell">{{ $customer->orders_count }}</td>
                    <td class="px-3 py-3 text-gray-400 text-xs hidden sm:table-cell">{{ $customer->created_at->format('d/m/Y') }}</td>
                    <td class="px-3 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $customer->statut === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                            {{ $customer->statut === 'active' ? 'Actif' : 'Bloqué' }}
                        </span>
                    </td>
                    <td class="px-3 py-3 text-right">
                        <button type="button" wire:click="toggleStatut({{ $customer->id }})" class="text-xs px-2.5 py-1.5 rounded-lg border transition-colors
                            {{ $customer->statut === 'active' ? 'border-red-200 text-red-500 hover:bg-red-50' : 'border-green-200 text-green-600 hover:bg-green-50' }}">
                            {{ $customer->statut === 'active' ? 'Bloquer' : 'Débloquer' }}
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">Aucun client</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-50">
            {{ $customers->links() }}
        </div>
    </div>
</div>
