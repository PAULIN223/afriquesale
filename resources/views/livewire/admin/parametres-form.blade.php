<div class="max-w-lg">
    <h1 class="font-semibold text-gray-800 text-lg mb-1">Paramètres</h1>
    <p class="text-xs text-gray-400 mb-4">Configuration de la boutique</p>

    <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <form wire:submit="save" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nom de la boutique</label>
                <input type="text" wire:model="nomBoutique" required
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
                @error('nomBoutique')<p class="text-xs text-[#E63B2E] mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Email de contact</label>
                <input type="email" wire:model="email" required
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
                @error('email')<p class="text-xs text-[#E63B2E] mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Devise</label>
                <select wire:model="devise" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
                    @foreach(['XAF' => 'XAF — Franc CFA', 'EUR' => 'EUR — Euro', 'USD' => 'USD — Dollar', 'MAD' => 'MAD — Dirham', 'GHS' => 'GHS — Cedi', 'NGN' => 'NGN — Naira'] as $code => $label)
                    <option value="{{ $code }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Frais de livraison (FCFA)</label>
                <input type="number" wire:model="fraisLivraison" min="0" required
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
                @error('fraisLivraison')<p class="text-xs text-[#E63B2E] mt-1">{{ $message }}</p>@enderror
            </div>
            <button class="w-full py-3 bg-[#E63B2E] hover:bg-[#CC2F23] text-white rounded-xl font-semibold text-sm">
                <span wire:loading.remove wire:target="save">Enregistrer les paramètres</span>
                <span wire:loading wire:target="save">Enregistrement...</span>
            </button>
        </form>
    </div>

    {{-- Info Firebase --}}
    <div class="mt-4 bg-white rounded-2xl border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-800 mb-3 text-sm flex items-center gap-2">
            <i data-lucide="flame" class="w-4 h-4 text-orange-500"></i> Firebase Project
        </h3>
        <dl class="space-y-1.5 text-sm">
            <div class="flex justify-between"><dt class="text-gray-400">Project ID</dt><dd class="font-mono text-xs text-gray-700">{{ env('FIREBASE_PROJECT_ID') }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-400">Database URL</dt><dd class="font-mono text-xs text-gray-700 truncate max-w-[200px]">{{ env('FIREBASE_DATABASE_URL') }}</dd></div>
        </dl>
    </div>
</div>
