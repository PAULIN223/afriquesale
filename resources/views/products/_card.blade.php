<div class="product-card bg-white rounded-2xl border border-gray-100 overflow-hidden group" x-data="{ added: false }">
    <div class="relative overflow-hidden aspect-square bg-gray-50">
        <a href="/products/{{ $product->id }}">
            <img src="{{ $product->image_url ? '/image-proxy?url=' . urlencode($product->image_url) : '/images/placeholder.png' }}"
                 alt="{{ $product->nom }}"
                 loading="lazy"
                 class="w-full h-full object-cover object-center transition-transform duration-500 group-hover:scale-105">
        </a>
        {{-- Discount badge --}}
        @if($product->discount_percent)
        <span class="absolute top-2 left-2 bg-[#E63B2E] text-white text-xs font-bold px-2 py-0.5 rounded-lg">-{{ $product->discount_percent }}%</span>
        @endif
        {{-- New badge --}}
        @if($product->created_at->isAfter(now()->subDays(7)))
        <span class="absolute top-2 left-2 bg-blue-500 text-white text-xs font-bold px-2 py-0.5 rounded-lg">Nouveau</span>
        @endif
        {{-- Stock low --}}
        @if($product->stock > 0 && $product->stock <= 5)
        <span class="absolute top-2 right-2 bg-orange-500 text-white text-xs font-bold px-2 py-0.5 rounded-lg">Limité</span>
        @endif
        {{-- Wishlist + Message buttons --}}
        @auth
        @php $inWishlist = in_array($product->id, $wishlistProductIds ?? []); @endphp
        <div class="absolute top-2 right-2 flex flex-col gap-1.5">
            <form method="POST" action="/wishlist/toggle/{{ $product->id }}">
                @csrf
                <button type="submit" class="w-8 h-8 rounded-xl shadow flex items-center justify-center transition-all
                    {{ $inWishlist ? 'bg-[#E63B2E] text-white' : 'bg-white/90 text-gray-500 hover:bg-[#E63B2E] hover:text-white' }}">
                    <i data-lucide="heart" class="w-4 h-4 {{ $inWishlist ? 'fill-current' : '' }}"></i>
                </button>
            </form>
            <a href="/messages?product={{ $product->id }}" wire:navigate
               class="w-8 h-8 bg-white/90 rounded-xl shadow flex items-center justify-center hover:bg-[#E63B2E] hover:text-white transition-all text-gray-500"
               title="Poser une question sur ce produit">
                <i data-lucide="message-circle" class="w-4 h-4"></i>
            </a>
        </div>
        @endauth
    </div>
    <div class="p-3 sm:p-4">
        <a href="/products/{{ $product->id }}" class="block">
            <p class="text-xs text-gray-400 mb-1">{{ $product->category?->nom ?? $product->categorie }}</p>
            <h3 class="font-semibold text-gray-800 text-sm leading-snug line-clamp-2 hover:text-[#E63B2E] transition-colors">{{ $product->nom }}</h3>
        </a>
        <div class="flex items-center justify-between mt-2.5">
            <div>
                <span class="font-bold text-gray-900 text-base">{{ number_format($product->prix, 0, ',', ' ') }} FCFA</span>
                @if($product->prix_original)
                <span class="text-xs text-gray-400 line-through ml-1">{{ number_format($product->prix_original, 0, ',', ' ') }}</span>
                @endif
            </div>
            @if($product->stock === 0)
            <span class="text-xs text-gray-400 font-medium">Rupture</span>
            @endif
        </div>
        @if($product->stock > 0)
        <form method="POST" action="/cart/add" class="mt-3">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <button type="submit" x-on:click="added = true; setTimeout(() => added = false, 2000)"
                    class="w-full py-2.5 rounded-xl text-sm font-semibold transition-all flex items-center justify-center gap-1.5"
                    :class="added ? 'bg-green-500 text-white' : 'bg-[#E63B2E] hover:bg-[#CC2F23] text-white'">
                <span x-show="!added" class="flex items-center gap-1.5">
                    <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i> Ajouter
                </span>
                <span x-show="added" class="flex items-center gap-1.5">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i> Ajouté !
                </span>
            </button>
        </form>
        @else
        <button disabled class="mt-3 w-full py-2.5 rounded-xl text-sm font-medium bg-gray-100 text-gray-400 cursor-not-allowed">
            Indisponible
        </button>
        @endif
    </div>
</div>
