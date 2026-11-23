@extends('layouts.app')

@section('title', $product->nom . ' — AfricSales')
@section('description', Str::limit($product->description, 160))
@section('og_image', $product->image_url ? '/image-proxy?url=' . urlencode($product->image_url) : '')
@section('og_type', 'product')

@section('content')
@php
    $jsonLdImages = collect($product->gallery_images)
        ->map(fn($img) => url('/image-proxy?url=' . urlencode($img)))
        ->values()
        ->all();

    $productJsonLd = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Product',
        'name'        => $product->nom,
        'description' => Str::limit(strip_tags((string) $product->description), 500) ?: $product->nom,
        'image'       => $jsonLdImages ?: [url('/images/placeholder.png')],
        'sku'         => (string) $product->id,
        'brand'       => ['@type' => 'Brand', 'name' => $product->marque ?: 'AfricSales'],
        'offers'      => [
            '@type'         => 'Offer',
            'url'           => url('/products/' . $product->id),
            'priceCurrency' => 'XOF',
            'price'         => number_format($product->display_price, 2, '.', ''),
            'availability'  => $product->is_out_of_stock ? 'https://schema.org/OutOfStock' : 'https://schema.org/InStock',
        ],
    ];
    if ($product->category) {
        $productJsonLd['category'] = $product->category->nom;
    }

    $breadcrumbJsonLd = [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => array_values(array_filter([
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Catégorie', 'item' => url('/products')],
            $product->category ? [
                '@type'    => 'ListItem',
                'position' => 3,
                'name'     => $product->category->nom,
                'item'     => url('/products?category=' . $product->category->slug),
            ] : null,
            ['@type' => 'ListItem', 'position' => $product->category ? 4 : 3, 'name' => $product->nom, 'item' => url('/products/' . $product->id)],
        ])),
    ];
@endphp
<script type="application/ld+json">@json($productJsonLd, JSON_UNESCAPED_UNICODE)</script>
<script type="application/ld+json">@json($breadcrumbJsonLd, JSON_UNESCAPED_UNICODE)</script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    {{-- Back button + Breadcrumb --}}
    <div class="flex items-center gap-3 mb-6">
        <button onclick="history.back()" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 text-sm text-gray-500 hover:text-[#E63B2E] hover:border-[#E63B2E] transition-all flex-shrink-0">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Retour
        </button>
        <nav class="flex items-center gap-2 text-sm text-gray-400 overflow-hidden">
            <a href="/" class="hover:text-[#E63B2E] flex-shrink-0">Accueil</a>
            <i data-lucide="chevron-right" class="w-3 h-3 flex-shrink-0"></i>
            <a href="/products" class="hover:text-[#E63B2E] flex-shrink-0">Catégorie</a>
            @if($product->category)
            <i data-lucide="chevron-right" class="w-3 h-3 flex-shrink-0"></i>
            <a href="/products?category={{ $product->category->slug }}" class="hover:text-[#E63B2E] flex-shrink-0">{{ $product->category->nom }}</a>
            @endif
            <i data-lucide="chevron-right" class="w-3 h-3 flex-shrink-0"></i>
            <span class="text-gray-600 font-medium truncate">{{ $product->nom }}</span>
        </nav>
    </div>

    @php
        $gallery = $product->gallery_images;
        $mainImage = $gallery[0] ?? null;
        $variantGroups = $variants
            ->filter(fn ($variant) => filled($variant->color) && !preg_match('/^couleur\s*\d+$/iu', trim($variant->color)))
            ->groupBy(fn ($variant) => mb_strtolower(preg_replace('/\s+/u', ' ', trim($variant->color)), 'UTF-8'))
            ->map(fn ($group) => $group->unique(fn ($variant) => mb_strtolower(trim((string) $variant->size) . '|' . trim((string) $variant->model), 'UTF-8'))->values());
        $initialVariantGroup = $variantGroups->count() === 1 ? $variantGroups->first() : collect();
        $needsVariantChoice = $variantGroups->count() > 1 || $variantGroups->contains(fn ($group) => $group->count() > 1);
        $initialVariant = $initialVariantGroup->count() === 1 ? $initialVariantGroup->first() : null;
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10"
         x-data="{
             activeId: {{ $needsVariantChoice ? 'null' : ($initialVariant?->id ?? $product->id) }},
             activeName: '{{ addslashes($product->nom) }}',
             activePrice: {{ $initialVariant?->display_price ?? $product->display_price }},
             activeOriginalPrice: {{ (($initialVariant?->discounted_price ?? $product->discounted_price) ? ($initialVariant?->prix ?? $product->prix) : ($initialVariant?->prix_original ?? $product->prix_original)) ?: 'null' }},
             activeDiscountPercent: {{ ($initialVariant?->discount_percent ?? $product->discount_percent) ?: 'null' }},
             activeStock: {{ $needsVariantChoice ? 0 : ($initialVariant?->stock ?? $product->stock) }},
             activeImage: @js($initialVariant?->image_src ?? ($mainImage ? '/image-proxy?url=' . urlencode($mainImage) : '/images/placeholder.png')),
             activeColor: @js($initialVariant?->color ?? ($variantGroups->count() === 1 ? $variantGroups->first()->first()->color : $product->color)),
             activeColorKey: @js($variantGroups->count() === 1 ? $variantGroups->keys()->first() : ''),
             isOutOfStock: {{ $needsVariantChoice ? 'false' : (($initialVariant?->is_out_of_stock ?? $product->is_out_of_stock) ? 'true' : 'false') }},
             isPurchasable: {{ $needsVariantChoice ? 'false' : (($initialVariant?->is_purchasable ?? $product->is_purchasable) ? 'true' : 'false') }},
             qty: 1
         }">

        {{-- Images --}}
        <div>
            <div class="aspect-square bg-gray-50 rounded-2xl overflow-hidden">
                <img :src="activeImage"
                     alt="{{ $product->nom }}{{ $product->category?->nom ? ' - ' . $product->category->nom : '' }} - AfricSales"
                     loading="eager"
                     class="w-full h-full object-cover object-center">
            </div>

            @if(count($gallery) > 1)
            <div class="grid grid-cols-5 sm:grid-cols-6 gap-2 mt-3">
                @foreach($gallery as $imageUrl)
                @php $proxied = '/image-proxy?url=' . urlencode($imageUrl); @endphp
                <button type="button" @click="activeImage = @js($proxied)"
                        class="aspect-square rounded-lg overflow-hidden border border-gray-200 hover:border-[#E63B2E] transition-colors">
                    <img src="{{ $proxied }}" alt="{{ $product->nom }}" loading="lazy" class="w-full h-full object-cover">
                </button>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Info --}}
        <div>
            @if($product->category)
            <span class="inline-block bg-[#E63B2E]/10 text-[#E63B2E] text-xs font-semibold px-3 py-1 rounded-full mb-3">
                {{ $product->category->nom }}
            </span>
            @endif

            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 leading-tight mb-4">
                <span x-text="activeName"></span>
                <span class="text-gray-500 font-normal" x-show="activeColor" x-text="' - ' + activeColor"></span>
            </h1>

            {{-- Price --}}
            <div class="flex items-center gap-3 mb-5">
                <span class="text-3xl font-bold text-gray-900" x-text="new Intl.NumberFormat('fr-FR').format(activePrice) + ' FCFA'"></span>
                <span class="text-lg text-gray-400 line-through" x-show="activeOriginalPrice && activeOriginalPrice > activePrice" x-text="new Intl.NumberFormat('fr-FR').format(activeOriginalPrice) + ' FCFA'"></span>
                <span class="bg-[#E63B2E] text-white text-sm font-bold px-2.5 py-0.5 rounded-lg" x-show="activeOriginalPrice && activeOriginalPrice > activePrice && activeDiscountPercent" x-text="'-' + activeDiscountPercent + '%'"></span>
            </div>

            {{-- Stock --}}
            <div class="flex items-center gap-2 mb-6" x-show="activeId !== null">
                <span x-show="isOutOfStock" class="flex items-center gap-1.5 text-[#E63B2E] text-sm font-medium">
                    <i data-lucide="x-circle" class="w-4 h-4"></i> Rupture de stock
                </span>
                <span x-show="!isOutOfStock && activeStock > 10" class="flex items-center gap-1.5 text-green-600 text-sm font-medium">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i> En stock (<span x-text="activeStock"></span> disponibles)
                </span>
                <span x-show="!isOutOfStock && activeStock > 0 && activeStock <= 10" class="flex items-center gap-1.5 text-orange-500 text-sm font-medium">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i> Plus que <span x-text="activeStock"></span> en stock !
                </span>
            </div>

            {{-- Couleurs et tailles disponibles --}}
            @if($variantGroups->isNotEmpty())
            <div class="border-t border-gray-100 pt-5 mb-5">
                @if($variantGroups->count() > 1)
                <h3 class="text-xs font-semibold uppercase text-gray-400 tracking-wider mb-3">Couleurs disponibles</h3>
                <div class="flex flex-wrap gap-3 mb-4">
                    @foreach($variantGroups as $colorKey => $colorVariants)
                    @php $colorVariant = $colorVariants->first(); @endphp
                    <button type="button"
                        @click="
                            activeColor = @js($colorVariant->color);
                            activeColorKey = @js($colorKey);
                            @if($colorVariants->count() === 1)
                            activeId = {{ $colorVariant->id }};
                            activePrice = {{ $colorVariant->display_price }};
                            activeOriginalPrice = {{ ($colorVariant->discounted_price ? $colorVariant->prix : $colorVariant->prix_original) ?: 'null' }};
                            activeDiscountPercent = {{ $colorVariant->discount_percent ?: 'null' }};
                            activeStock = {{ $colorVariant->stock }};
                            activeImage = @js($colorVariant->image_src);
                            isOutOfStock = {{ $colorVariant->is_out_of_stock ? 'true' : 'false' }};
                            isPurchasable = {{ $colorVariant->is_purchasable ? 'true' : 'false' }};
                            @else
                            activeId = null;
                            isPurchasable = false;
                            @endif
                            qty = 1;
                        "
                        class="group relative block w-16 h-16 rounded-xl border overflow-hidden transition-all"
                        :class="activeColorKey === @js($colorKey) ? 'border-[#E63B2E] ring-2 ring-[#E63B2E]/20' : 'border-gray-200 hover:border-[#E63B2E] bg-white'">
                        <img src="{{ $colorVariant->image_src }}" alt="{{ $colorVariant->color }}" class="w-full h-full object-cover">
                        <span class="absolute bottom-0 left-0 right-0 bg-black/60 text-white text-[9px] py-0.5 text-center truncate px-1 font-semibold">{{ $colorVariant->color }}</span>
                    </button>
                    @endforeach
                </div>
                @endif

                @foreach($variantGroups as $colorKey => $colorVariants)
                @if($colorVariants->count() > 1)
                <div class="mb-3" @if($variantGroups->count() > 1) x-show="activeColorKey === @js($colorKey)" @endif>
                    <h3 class="text-xs font-semibold uppercase text-gray-400 tracking-wider mb-2">Tailles / modèles</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($colorVariants as $v)
                        <button type="button"
                            @click="
                                activeId = {{ $v->id }};
                                activePrice = {{ $v->display_price }};
                                activeOriginalPrice = {{ ($v->discounted_price ? $v->prix : $v->prix_original) ?: 'null' }};
                                activeDiscountPercent = {{ $v->discount_percent ?: 'null' }};
                                activeStock = {{ $v->stock }};
                                activeImage = @js($v->image_src);
                                activeColor = @js($v->color);
                                activeColorKey = @js($colorKey);
                                isOutOfStock = {{ $v->is_out_of_stock ? 'true' : 'false' }};
                                isPurchasable = {{ $v->is_purchasable ? 'true' : 'false' }};
                                qty = 1;
                            "
                            :class="activeId === {{ $v->id }} ? 'border-[#E63B2E] bg-[#E63B2E]/10 text-[#E63B2E]' : 'border-gray-200 text-gray-700 hover:border-[#E63B2E]'"
                            class="px-3 py-2 rounded-lg border text-sm transition-colors">
                            {{ collect([$v->size, $v->model])->filter()->implode(' · ') ?: 'Option' }}
                        </button>
                        @endforeach
                    </div>
                </div>
                @endif
                @endforeach
            </div>
            @endif

            {{-- Sheet details --}}
            <div class="border-t border-gray-100 pt-5 mb-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-3 text-sm">
                    @foreach([
                        'Price' => ($product->prix_original ?: $product->prix) ? number_format($product->prix_original ?: $product->prix, 0, ',', ' ') . ' FCFA' : null,
                        'MOQ' => $product->moq,
                        'Discounted Price' => $product->discounted_price ? number_format($product->discounted_price, 0, ',', ' ') . ' FCFA' : null,
                        'Description' => $product->description,
                        'Marque' => $product->marque,
                        'Size' => $product->size,
                        'Color' => $product->color,
                        'Model' => $product->model,
                        'Stock' => (string) $product->stock,
                        'Availability' => ucfirst($product->availability ?? 'show'),
                    ] as $label => $value)
                    @if(filled($value))
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-400">{{ $label }}</dt>
                        <dd class="mt-0.5 text-gray-800">{{ $value }}</dd>
                    </div>
                    @endif
                    @endforeach
                </dl>
            </div>

            {{-- Description --}}
            @if($product->description)
            <div class="prose prose-sm max-w-none text-gray-600 mb-6 leading-relaxed border-t border-gray-100 pt-5">
                {{ $product->description }}
            </div>
            @endif

            <div class="space-y-3" x-show="activeId !== null && isPurchasable">
                {{-- Qty selector --}}
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-gray-700">Quantité :</span>
                    <div class="flex items-center border border-gray-200 rounded-xl overflow-hidden">
                        <button type="button" @click="qty = Math.max(1, qty - 1)"
                                class="w-10 h-10 flex items-center justify-center hover:bg-gray-50 transition-colors text-gray-600">
                            <i data-lucide="minus" class="w-4 h-4"></i>
                        </button>
                        <input type="number" x-model.number="qty" min="1" :max="activeStock"
                               class="w-14 text-center text-sm font-semibold border-x border-gray-200 h-10 focus:outline-none">
                        <button type="button" @click="qty = Math.min(activeStock, qty + 1)"
                                class="w-10 h-10 flex items-center justify-center hover:bg-gray-50 transition-colors text-gray-600">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <div class="flex gap-3">
                    <div class="flex-1">
                        <livewire:client.add-to-cart-button :product="$product" :key="'cart-btn-'.$product->id" />
                    </div>
                    @auth
                    <form method="POST" :action="'/wishlist/toggle/' + activeId">
                        @csrf
                        <button type="submit"
                                class="h-full px-4 rounded-xl border {{ $inWishlist ? 'border-[#E63B2E] bg-[#E63B2E]/10 text-[#E63B2E]' : 'border-gray-200 text-gray-500 hover:border-[#E63B2E] hover:text-[#E63B2E]' }} transition-all">
                            <i data-lucide="heart" class="w-5 h-5 {{ $inWishlist ? 'fill-current' : '' }}"></i>
                        </button>
                    </form>
                    <a :href="'/messages?product=' + activeId" wire:navigate
                       class="h-full px-4 rounded-xl border border-gray-200 text-gray-500 hover:border-[#E63B2E] hover:text-[#E63B2E] transition-all flex items-center"
                       title="Poser une question sur ce produit">
                        <i data-lucide="message-circle" class="w-5 h-5"></i>
                    </a>
                    @endauth
                </div>
            </div>

            <div x-show="activeId === null || !isPurchasable">
                <button disabled class="w-full bg-gray-100 text-gray-400 py-3.5 rounded-xl font-semibold text-sm cursor-not-allowed">
                    <span x-text="activeId === null ? 'Choisissez une couleur ou une taille' : 'Produit indisponible'"></span>
                </button>
            </div>

            {{-- Trust icons --}}
            <div class="mt-6 pt-5 border-t border-gray-100 grid grid-cols-3 gap-3">
                @foreach([['truck','Livraison rapide'],['shield-check','Paiement sécurisé'],['refresh-cw','Retours 30j']] as [$icon,$label])
                <div class="text-center">
                    <i data-lucide="{{ $icon }}" class="w-5 h-5 text-gray-400 mx-auto mb-1"></i>
                    <p class="text-xs text-gray-500">{{ $label }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Related products (même catégorie uniquement) --}}
    @if($related->count() > 0)
    <div class="mt-14">
        <div class="flex items-center gap-3 mb-5">
            <h2 class="text-xl font-bold text-gray-900">Autres produits</h2>
            @if($product->category)
            <span class="bg-[#E63B2E]/10 text-[#E63B2E] text-sm font-semibold px-3 py-1 rounded-full">
                {{ $product->category->nom }}
            </span>
            @endif
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($related as $rel)
            @include('products._card', ['product' => $rel])
            @endforeach
        </div>
        @if($product->category)
        <div class="text-center mt-6">
            <a href="/products?category={{ $product->category->slug }}"
               class="inline-flex items-center gap-2 border border-[#E63B2E] text-[#E63B2E] px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-[#E63B2E] hover:text-white transition-all">
                Voir tous les {{ $product->category->nom }}
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
        @endif
    </div>
    @endif

</div>
@endsection
