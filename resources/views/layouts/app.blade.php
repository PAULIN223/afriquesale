<!DOCTYPE html>
<html lang="fr" x-data>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $pageTitle       = trim($__env->yieldContent('title', 'AfricSales - Marketplace Africaine'));
        $pageDescription = trim($__env->yieldContent('description', "AfricSales - La meilleure marketplace d'Afrique. Produits de qualité livrés chez vous."));
        $pageImage       = trim($__env->yieldContent('og_image', '/images/logo.png')) ?: '/images/logo.png';
        $canonicalUrl    = trim($__env->yieldContent('canonical', url()->current()));
    @endphp
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">

    <meta property="og:site_name" content="AfricSales">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:image" content="{{ url($pageImage) }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ url($pageImage) }}">

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/android-chrome-192x192.png">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    @livewireStyles
    @stack('styles')
</head>
<body class="bg-gray-50 font-sans antialiased" style="font-family: 'Inter', sans-serif;">

    {{-- HEADER --}}
    <header class="bg-white shadow-sm sticky top-0 z-50" x-data="{ mobileMenu: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-16 gap-3">

                {{-- Logo --}}
                <a href="/" class="flex-shrink-0 flex items-center gap-2">
                    <img src="/images/logo.png" alt="AfricSales" class="h-10 w-auto"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <span class="hidden items-center font-bold text-xl">
                        <span style="color:#DAA520">AFRIC</span><span style="color:#1B3A6B">SALE</span>
                    </span>
                </a>

                {{-- Search --}}
                <form action="/products" method="GET" class="flex-1 max-w-xl hidden md:block">
                    <div class="flex rounded-xl overflow-hidden border border-gray-200 focus-within:border-[#E63B2E] focus-within:ring-2 focus-within:ring-[#E63B2E]/10 transition-all">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Rechercher des produits, marques..."
                               class="flex-1 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 outline-none bg-white">
                        <button type="submit" class="px-4 py-2.5 bg-[#E63B2E] text-white hover:bg-[#CC2F23] transition-colors flex items-center">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </button>
                    </div>
                </form>

                {{-- Right icons --}}
                <div class="flex items-center gap-1">
                    @auth
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.away="open = false"
                                class="flex items-center gap-1.5 px-3 py-2 rounded-xl hover:bg-gray-50 transition-colors text-gray-700 text-sm font-medium">
                            <i data-lucide="user-circle-2" class="w-5 h-5 text-gray-500"></i>
                            <span class="hidden lg:block truncate max-w-[100px]">{{ auth()->user()->name }}</span>
                            <i data-lucide="chevron-down" class="w-3 h-3 text-gray-400"></i>
                        </button>
                        <div x-show="open" x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             class="absolute right-0 top-full mt-1 w-52 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50">
                            <div class="px-4 py-2 border-b border-gray-50 mb-1">
                                <p class="text-xs text-gray-500">Connecté en tant que</p>
                                <p class="text-sm font-semibold text-gray-800 truncate">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="/account/profile" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-[#E63B2E] transition-colors">
                                <i data-lucide="user" class="w-4 h-4"></i> Mon profil
                            </a>
                            <a href="/account/orders" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-[#E63B2E] transition-colors">
                                <i data-lucide="package" class="w-4 h-4"></i> Mes commandes
                            </a>
                            <a href="/wishlist" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-[#E63B2E] transition-colors">
                                <i data-lucide="heart" class="w-4 h-4"></i> Mes favoris
                            </a>
                            @if(auth()->user()->isAdmin())
                            <div class="border-t border-gray-50 mt-1 pt-1">
                                <a href="/admin" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-[#E63B2E] transition-colors">
                                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Administration
                                </a>
                            </div>
                            @endif
                            <div class="border-t border-gray-50 mt-1 pt-1">
                                <form method="POST" action="/logout">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-2.5 w-full px-4 py-2.5 text-sm text-[#E63B2E] hover:bg-red-50 transition-colors">
                                        <i data-lucide="log-out" class="w-4 h-4"></i> Déconnexion
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @else
                    <a href="/login" class="flex items-center gap-1.5 px-3 py-2 rounded-xl hover:bg-gray-50 transition-colors text-gray-700 text-sm">
                        <i data-lucide="user-circle-2" class="w-5 h-5 text-gray-500"></i>
                        <span class="hidden md:block">Connexion</span>
                    </a>
                    @endauth

                    <a href="/wishlist" class="relative p-2.5 rounded-xl hover:bg-gray-50 transition-colors text-gray-600">
                        <i data-lucide="heart" class="w-5 h-5"></i>
                        @if(($wishlistCount ?? 0) > 0)
                        <span class="absolute -top-0.5 -right-0.5 bg-[#E63B2E] text-white text-[10px] w-4 h-4 rounded-full flex items-center justify-center font-bold">{{ $wishlistCount }}</span>
                        @endif
                    </a>

                    <livewire:client.cart-badge />

                    <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2.5 rounded-xl hover:bg-gray-50 text-gray-600">
                        <i data-lucide="menu" class="w-5 h-5" x-show="!mobileMenu"></i>
                        <i data-lucide="x" class="w-5 h-5" x-show="mobileMenu"></i>
                    </button>
                </div>
            </div>

            {{-- Mobile search --}}
            <div class="md:hidden pb-3">
                <form action="/products" method="GET">
                    <div class="flex rounded-xl overflow-hidden border border-gray-200">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Rechercher..." class="flex-1 px-4 py-2.5 text-sm outline-none">
                        <button type="submit" class="px-4 bg-[#E63B2E] text-white">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Page navigation --}}
        <div class="border-t border-gray-100 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6">
                <nav class="flex items-center gap-1 overflow-x-auto py-2 scrollbar-hide">
                    @php
                    $navItems = [
                        ['url' => '/',               'label' => 'Accueil',     'icon' => 'home',          'route' => 'home'],
                        ['url' => '/wishlist',       'label' => 'Préférence',  'icon' => 'heart',         'route' => 'wishlist.index'],
                        ['url' => '/products',       'label' => 'Catégorie',   'icon' => 'grid-2x2',      'route' => 'products.index'],
                        ['url' => '/messages',       'label' => 'Message',     'icon' => 'message-circle','route' => 'messages.index'],
                        ['url' => '/vente-flash',    'label' => 'Vente Flash', 'icon' => 'zap',           'route' => 'vente.flash'],
                        ['url' => '/account/orders', 'label' => 'Commande',    'icon' => 'package',       'route' => 'account.orders'],
                    ];
                    @endphp
                    @foreach($navItems as $item)
                    <a href="{{ $item['url'] }}" wire:navigate
                       class="relative flex-shrink-0 flex items-center gap-1.5 px-3.5 py-1.5 text-sm font-medium rounded-lg transition-all whitespace-nowrap
                              {{ request()->routeIs($item['route']) ? 'bg-[#E63B2E] text-white' : 'text-gray-600 hover:bg-[#E63B2E]/10 hover:text-[#E63B2E]' }}">
                        <i data-lucide="{{ $item['icon'] }}" class="w-3.5 h-3.5"></i>
                        {{ $item['label'] }}
                        @if($item['route'] === 'messages.index')
                        @auth
                        <livewire:client.message-badge variant="desktop" :key="'msgbadge-desktop'" />
                        @endauth
                        @endif
                    </a>
                    @endforeach
                </nav>
            </div>
        </div>
    </header>

    {{-- Flash messages --}}
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
         class="fixed top-20 right-4 z-50 bg-green-500 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-2.5 max-w-sm">
        <i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i>
        <span class="text-sm font-medium">{{ session('success') }}</span>
        <button @click="show = false" class="ml-auto hover:opacity-75"><i data-lucide="x" class="w-4 h-4"></i></button>
    </div>
    @endif
    @if(session('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
         class="fixed top-20 right-4 z-50 bg-[#E63B2E] text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-2.5 max-w-sm">
        <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
        <span class="text-sm font-medium">{{ session('error') }}</span>
        <button @click="show = false" class="ml-auto hover:opacity-75"><i data-lucide="x" class="w-4 h-4"></i></button>
    </div>
    @endif
    @if(session('info'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
         class="fixed top-20 right-4 z-50 bg-blue-500 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-2.5 max-w-sm">
        <i data-lucide="info" class="w-5 h-5 flex-shrink-0"></i>
        <span class="text-sm font-medium">{{ session('info') }}</span>
    </div>
    @endif

    {{-- Main content --}}
    <main class="min-h-screen pb-16 md:pb-0">
        @hasSection('content')
            @yield('content')
        @else
            {{ $slot ?? '' }}
        @endif
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-900 text-white mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <img src="/images/logo.png" alt="AfricSales" class="h-10 w-auto brightness-0 invert"
                             onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
                        <span class="hidden font-bold text-xl"><span style="color:#DAA520">AFRIC</span><span class="text-white">SALE</span></span>
                    </div>
                    <p class="text-gray-400 text-sm leading-relaxed">La marketplace africaine de référence. Trouvez les meilleurs produits au meilleur prix, livrés à votre porte.</p>
                    <div class="flex items-center gap-2 mt-5">
                        <a href="#" class="w-8 h-8 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-[#E63B2E] transition-colors"><i data-lucide="facebook" class="w-3.5 h-3.5"></i></a>
                        <a href="#" class="w-8 h-8 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-[#E63B2E] transition-colors"><i data-lucide="instagram" class="w-3.5 h-3.5"></i></a>
                        <a href="#" class="w-8 h-8 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-[#E63B2E] transition-colors"><i data-lucide="twitter" class="w-3.5 h-3.5"></i></a>
                        <a href="#" class="w-8 h-8 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-[#E63B2E] transition-colors"><i data-lucide="youtube" class="w-3.5 h-3.5"></i></a>
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold mb-4 text-sm uppercase tracking-wider">Navigation</h3>
                    <ul class="space-y-2.5 text-sm text-gray-400">
                        <li><a href="/products" class="hover:text-white hover:pl-1 transition-all">Tous les produits</a></li>
                        <li><a href="/cart" class="hover:text-white hover:pl-1 transition-all">Mon panier</a></li>
                        <li><a href="/account/orders" class="hover:text-white hover:pl-1 transition-all">Mes commandes</a></li>
                        <li><a href="/wishlist" class="hover:text-white hover:pl-1 transition-all">Ma liste de souhaits</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-semibold mb-4 text-sm uppercase tracking-wider">Informations</h3>
                    <ul class="space-y-2.5 text-sm text-gray-400">
                        <li><a href="#" class="hover:text-white hover:pl-1 transition-all">À propos</a></li>
                        <li><a href="#" class="hover:text-white hover:pl-1 transition-all">Conditions d'utilisation</a></li>
                        <li><a href="#" class="hover:text-white hover:pl-1 transition-all">Politique de confidentialité</a></li>
                        <li><a href="#" class="hover:text-white hover:pl-1 transition-all">Livraison & retours</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-semibold mb-4 text-sm uppercase tracking-wider">Newsletter</h3>
                    <p class="text-gray-400 text-sm mb-3">Recevez nos offres exclusives et nouveautés.</p>
                    <div class="flex gap-2">
                        <input type="email" placeholder="votre@email.com"
                               class="flex-1 px-3 py-2.5 rounded-xl bg-gray-800 border border-gray-700 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-[#E63B2E]">
                        <button class="px-3 py-2.5 bg-[#E63B2E] rounded-xl hover:bg-[#CC2F23] transition-colors flex items-center">
                            <i data-lucide="send" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <div class="mt-5 space-y-1.5 text-sm text-gray-400">
                        <div class="flex items-center gap-2"><i data-lucide="mail" class="w-4 h-4 text-[#E63B2E]"></i> contact@africsales.com</div>
                        <div class="flex items-center gap-2"><i data-lucide="map-pin" class="w-4 h-4 text-[#E63B2E]"></i> Afrique de l'Ouest</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="border-t border-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-2">
                <p class="text-gray-500 text-sm">&copy; {{ date('Y') }} AfricSales. Tous droits réservés.</p>
                <p class="text-gray-600 text-xs">Paiement sécurisé · Livraison rapide · Service client 24/7</p>
            </div>
        </div>
    </footer>

    {{-- Bottom navigation (mobile) --}}
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 z-50 md:hidden shadow-[0_-2px_10px_rgba(0,0,0,0.06)]">
        <div class="flex">
            @php
            $bottomNav = [
                ['url' => '/',               'label' => 'Accueil',  'icon' => 'home',           'route' => 'home'],
                ['url' => '/wishlist',       'label' => 'Préférence','icon' => 'heart',          'route' => 'wishlist.index'],
                ['url' => '/products',       'label' => 'Catégorie','icon' => 'grid-2x2',        'route' => 'products.index'],
                ['url' => '/vente-flash',    'label' => 'Flash',    'icon' => 'zap',             'route' => 'vente.flash'],
                ['url' => '/messages',       'label' => 'Message',  'icon' => 'message-circle',  'route' => 'messages.index'],
                ['url' => '/account/orders', 'label' => 'Commande', 'icon' => 'package',         'route' => 'account.orders'],
            ];
            @endphp
            @foreach($bottomNav as $item)
            <a href="{{ $item['url'] }}" wire:navigate
               class="relative flex-1 flex flex-col items-center justify-center py-2 gap-0.5 transition-colors
                      {{ request()->routeIs($item['route']) ? 'text-[#E63B2E]' : 'text-gray-400 hover:text-[#E63B2E]' }}">
                @if($item['route'] === 'messages.index')
                @auth
                <livewire:client.message-badge variant="mobile" :key="'msgbadge-mobile'" />
                @endauth
                @endif
                <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5"></i>
                <span class="text-[9px] font-medium leading-none">{{ $item['label'] }}</span>
            </a>
            @endforeach
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            lucide.createIcons();
        });
        document.addEventListener('livewire:navigated', function () {
            lucide.createIcons();
        });
        document.addEventListener('livewire:init', function () {
            Livewire.hook('morph.updated', function () { lucide.createIcons(); });
        });
    </script>
    @stack('scripts')
    @livewireScripts
</body>
</html>
