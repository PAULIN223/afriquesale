<div x-data="{ justAdded: false }">
    <button type="button" wire:target="add"
            x-on:click="$wire.add((typeof qty !== 'undefined' ? qty : 1), (typeof activeId !== 'undefined' ? activeId : null), (typeof activeColor !== 'undefined' ? activeColor : null)); justAdded = true; setTimeout(() => justAdded = false, 2000)"
            x-bind:disabled="typeof isPurchasable !== 'undefined' ? !isPurchasable : {{ $product->is_purchasable ? 'false' : 'true' }}"
            class="w-full py-2.5 rounded-xl text-sm font-semibold transition-all flex items-center justify-center gap-1.5"
            :class="justAdded ? 'bg-green-500 text-white' : (typeof isPurchasable !== 'undefined' ? (isPurchasable ? 'bg-[#E63B2E] hover:bg-[#CC2F23] text-white' : 'bg-gray-100 text-gray-400 cursor-not-allowed') : ({{ $product->is_purchasable ? 'true' : 'false' }} ? 'bg-[#E63B2E] hover:bg-[#CC2F23] text-white' : 'bg-gray-100 text-gray-400 cursor-not-allowed'))">

        <span x-show="justAdded" class="flex items-center gap-1.5">
            <i data-lucide="check" class="w-3.5 h-3.5"></i> Ajouté !
        </span>

        <span x-show="!justAdded" class="flex items-center gap-1.5">
            <span wire:loading.remove wire:target="add" class="flex items-center gap-1.5">
                <i data-lucide="shopping-cart" class="w-3.5 h-3.5" x-show="typeof activeId === 'undefined' || activeId !== null"></i>
                <span x-text="typeof activeId !== 'undefined' && activeId === null ? 'Veuillez choisir une couleur' : (typeof isPurchasable !== 'undefined' ? (isPurchasable ? 'Ajouter' : 'Rupture de stock') : ({{ $product->is_purchasable ? 'true' : 'false' }} ? 'Ajouter' : 'Rupture de stock'))"></span>
            </span>
            <span wire:loading wire:target="add">...</span>
        </span>
    </button>
</div>
