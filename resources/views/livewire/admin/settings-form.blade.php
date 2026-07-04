<div class="max-w-2xl">
    <h1 class="font-semibold text-gray-800 text-lg mb-4">Paramètres de la boutique</h1>
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <form wire:submit="save" class="space-y-5">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nom de la boutique</label>
                    <input type="text" wire:model="shopName"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email de contact</label>
                    <input type="email" wire:model="shopEmail"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Téléphone</label>
                    <input type="text" wire:model="shopPhone"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Devise</label>
                    <input type="text" wire:model="devise"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Frais de livraison (FCFA)</label>
                    <input type="number" wire:model="fraisLivraison"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Adresse physique</label>
                <input type="text" wire:model="shopAddress"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
            </div>

            <button type="submit"
                    class="bg-[#E63B2E] hover:bg-[#CC2F23] text-white px-8 py-3 rounded-xl font-semibold text-sm transition-colors flex items-center gap-2">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span wire:loading.remove wire:target="save">Enregistrer les paramètres</span>
                <span wire:loading wire:target="save">Enregistrement...</span>
            </button>
        </form>
    </div>
</div>
