@php
    $cardVariants = $product->distinct_variants;
    $requiresColorChoice = $cardVariants->count() > 1;
    $cardIsPurchasable = $requiresColorChoice
        ? $cardVariants->contains(fn ($variant) => $variant->is_purchasable)
        : $product->is_purchasable;
@endphp
<div x-data="{
    activeId: {{ $product->id }},
    activeUrl: '/products/{{ $product->id }}',
    requiresColorChoice: {{ $requiresColorChoice ? 'true' : 'false' }},
    isPurchasable: {{ $cardIsPurchasable ? 'true' : 'false' }},
    isOutOfStock: {{ $cardIsPurchasable ? 'false' : 'true' }}
}" class="product-card h-full flex flex-col bg-white rounded-2xl border border-gray-100 overflow-hidden group">
    <div class="relative overflow-hidden aspect-square bg-gray-50">
        <a href="/products/{{ $product->id }}">
            <img src="{{ $product->image_src }}"
                 alt="{{ $product->nom }}{{ ($product->category?->nom ?? $product->categorie) ? ' - ' . ($product->category?->nom ?? $product->categorie) : '' }} - AfricSales"
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
        @if(!$product->is_out_of_stock && $product->stock > 0 && $product->stock <= 5)
        <span class="absolute top-2 right-2 bg-orange-500 text-white text-xs font-bold px-2 py-0.5 rounded-lg">Limité</span>
        @endif
        {{-- Wishlist + Message buttons --}}
        @auth
        @php $inWishlist = in_array($product->id, $wishlistProductIds ?? []); @endphp
        <div class="absolute top-2 right-2 flex flex-col gap-1.5">
            <form method="POST" :action="'/wishlist/toggle/' + activeId">
                @csrf
                <button type="submit" class="w-8 h-8 rounded-xl shadow flex items-center justify-center transition-all
                    {{ $inWishlist ? 'bg-[#E63B2E] text-white' : 'bg-white/90 text-gray-500 hover:bg-[#E63B2E] hover:text-white' }}">
                    <i data-lucide="heart" class="w-4 h-4 {{ $inWishlist ? 'fill-current' : '' }}"></i>
                </button>
            </form>
            <a :href="'/messages?product=' + activeId" wire:navigate
               class="w-8 h-8 bg-white/90 rounded-xl shadow flex items-center justify-center hover:bg-[#E63B2E] hover:text-white transition-all text-gray-500"
               title="Poser une question sur ce produit">
                <i data-lucide="message-circle" class="w-4 h-4"></i>
            </a>
        </div>
        @endauth
    </div>
    <div class="p-3 sm:p-4 flex flex-col flex-1">
        <a href="/products/{{ $product->id }}" class="block">
            <p class="text-xs text-gray-400 mb-1">{{ $product->category?->nom ?? $product->categorie }}</p>
            <h3 class="font-semibold text-gray-800 text-sm leading-snug line-clamp-2 hover:text-[#E63B2E] transition-colors">{{ $product->nom }}</h3>
        </a>

        <div class="flex items-center justify-between mt-auto pt-2.5">
            <div>
                <span class="font-bold text-gray-900 text-base">{{ number_format($product->display_price, 0, ',', ' ') }} FCFA</span>
                @if($product->discount_percent && (($product->discounted_price && $product->discounted_price < $product->prix) || ($product->prix_original && $product->prix_original > $product->display_price)))
                <span class="text-xs text-gray-400 line-through ml-1">{{ number_format($product->discounted_price ? $product->prix : $product->prix_original, 0, ',', ' ') }} FCFA</span>
                @endif
            </div>
            @if($product->discount_percent)
            <span class="bg-[#E63B2E] text-white text-xs font-bold px-2 py-0.5 rounded-lg">-{{ $product->discount_percent }}%</span>
            @endif
            <span x-show="isOutOfStock" class="text-xs text-gray-400 font-medium">Rupture</span>
        </div>
        <div class="mt-3">
            <livewire:client.add-to-cart-button :product="$product" :key="'cart-btn-'.$product->id" />
        </div>
    </div>
</div>
