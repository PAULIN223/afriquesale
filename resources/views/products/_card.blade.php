<div x-data="{
    activeId: {{ $product->variants->count() > 1 ? 'null' : $product->id }},
    activeImage: '{{ $product->image_url ? '/image-proxy?url=' . urlencode($product->image_url) : '/images/placeholder.png' }}',
    activeUrl: '/products/{{ $product->id }}',
    activePrice: {{ $product->display_price }},
    activeOriginalPrice: {{ $product->prix_original ?: 'null' }},
    activeDiscountPercent: {{ $product->discount_percent ?: 'null' }},
    isPurchasable: {{ $product->variants->count() > 1 ? 'false' : ($product->is_purchasable ? 'true' : 'false') }},
    isOutOfStock: {{ $product->is_out_of_stock ? 'true' : 'false' }}
}" class="product-card bg-white rounded-2xl border border-gray-100 overflow-hidden group">
    <div class="relative overflow-hidden aspect-square bg-gray-50">
        <a :href="activeUrl">
            <img :src="activeImage"
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
    <div class="p-3 sm:p-4">
        <a :href="activeUrl" class="block">
            <p class="text-xs text-gray-400 mb-1">{{ $product->category?->nom ?? $product->categorie }}</p>
            <h3 class="font-semibold text-gray-800 text-sm leading-snug line-clamp-2 hover:text-[#E63B2E] transition-colors">{{ $product->nom }}</h3>
        </a>

        {{-- Miniatures cliquables pour la sélection de couleur sur la carte --}}
        @if($product->variants->count() > 1)
        <div class="flex flex-wrap gap-1.5 mt-2.5">
            @foreach($product->variants as $v)
            <button type="button"
                    @click="
                        activeId = {{ $v->id }};
                        activeImage = '{{ $v->image_src }}';
                        activeUrl = '/products/{{ $v->id }}';
                        activePrice = {{ $v->display_price }};
                        activeOriginalPrice = {{ $v->prix_original ?: 'null' }};
                        activeDiscountPercent = {{ $v->discount_percent ?: 'null' }};
                        isPurchasable = {{ $v->is_purchasable ? 'true' : 'false' }};
                        isOutOfStock = {{ $v->is_out_of_stock ? 'true' : 'false' }};
                    "
                    class="group relative block w-8 h-8 rounded-lg border overflow-hidden transition-all flex-shrink-0"
                    :class="activeId === {{ $v->id }} ? 'border-[#E63B2E] ring-1 ring-[#E63B2E]/25' : 'border-gray-200 hover:border-[#E63B2E] bg-white'"
                    title="{{ $v->color ?: 'Couleur' }}">
                <img src="{{ $v->image_src }}" alt="{{ $v->color ?: $v->nom }}" class="w-full h-full object-cover">
                <div class="absolute bottom-0 left-0 right-0 bg-black/60 text-white text-[7px] py-0.5 text-center truncate px-0.5 font-semibold leading-none pointer-events-none">
                    {{ $v->color ?: 'Couleur' }}
                </div>
            </button>
            @endforeach
        </div>
        @endif

        <div class="flex items-center justify-between mt-2.5">
            <div>
                <span class="font-bold text-gray-900 text-base" x-text="new Intl.NumberFormat('fr-FR').format(activePrice) + ' FCFA'"></span>
                <span class="text-xs text-gray-400 line-through ml-1" x-show="activeOriginalPrice" x-text="new Intl.NumberFormat('fr-FR').format(activeOriginalPrice) + ' FCFA'"></span>
            </div>
            <span x-show="isOutOfStock" class="text-xs text-gray-400 font-medium">Rupture</span>
        </div>
        <div class="mt-3">
            <livewire:client.add-to-cart-button :product="$product" :key="'cart-btn-'.$product->id" />
        </div>
    </div>
</div>
