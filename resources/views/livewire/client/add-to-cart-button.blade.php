<div x-data="{ justAdded: false }">
    @if($product->is_purchasable)
    <button type="button" wire:target="add"
            x-on:click="$wire.add(typeof qty !== 'undefined' ? qty : 1); justAdded = true; setTimeout(() => justAdded = false, 2000)"
            class="w-full py-2.5 rounded-xl text-sm font-semibold transition-all flex items-center justify-center gap-1.5"
            :class="justAdded ? 'bg-green-500 text-white' : 'bg-[#E63B2E] hover:bg-[#CC2F23] text-white'">
        <span wire:loading.remove wire:target="add" x-show="!justAdded" class="flex items-center gap-1.5">
            <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i> Ajouter
        </span>
        <span x-show="justAdded" class="flex items-center gap-1.5">
            <i data-lucide="check" class="w-3.5 h-3.5"></i> Ajouté !
        </span>
        <span wire:loading wire:target="add" x-show="!justAdded">...</span>
    </button>
    @else
    <button disabled class="w-full py-2.5 rounded-xl text-sm font-medium bg-gray-100 text-gray-400 cursor-not-allowed">
        Rupture de stock
    </button>
    @endif
</div>
