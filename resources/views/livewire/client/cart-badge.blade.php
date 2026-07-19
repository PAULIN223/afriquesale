<a href="/cart" wire:navigate class="relative flex items-center gap-1.5 px-3 py-2 rounded-xl hover:bg-gray-50 transition-colors text-gray-700">
    <i data-lucide="shopping-cart" class="w-5 h-5 text-gray-500"></i>
    <span class="hidden md:block text-sm font-medium">Panier</span>
    @if($count > 0)
    <span class="bg-[#E63B2E] text-white text-xs px-1.5 py-0.5 rounded-full font-bold min-w-[20px] text-center">{{ $count }}</span>
    @endif
</a>
