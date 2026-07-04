<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Administration') — AfricSales Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    @livewireStyles
    @stack('styles')
</head>
<body class="bg-gray-50" style="font-family:'Inter',sans-serif;" x-data="{ sidebarOpen: false, toast: null }" x-on:notify.window="toast = $event.detail.message; setTimeout(() => toast = null, 4000)">

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 w-64 bg-white border-r border-gray-100 shadow-sm z-40 flex flex-col transition-transform duration-300"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

        {{-- Logo --}}
        <div class="h-16 flex items-center px-5 border-b border-gray-100">
            <a href="/admin" wire:navigate class="flex items-center gap-2">
                <img src="/images/logo.png" alt="" class="h-8 w-auto"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
                <span class="hidden font-bold text-sm"><span style="color:#DAA520">AFRIC</span><span style="color:#1B3A6B">SALE</span></span>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest ml-1">Admin</span>
            </a>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5">

            {{-- Dashboard --}}
            <a href="/admin" wire:navigate class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.dashboard') ? 'active bg-[#E63B2E] text-white shadow-sm' : 'text-gray-600' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4 flex-shrink-0"></i> Tableau de bord
            </a>

            {{-- Section Boutique Web (MySQL) --}}
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest px-3 pt-4 pb-1">Boutique Web</p>
            @php
            $shopItems = [
                ['href' => '/admin/products',   'icon' => 'package',      'label' => 'Produits Sheet',  'route' => 'admin.products.index'],
                ['href' => '/admin/orders',     'icon' => 'shopping-bag', 'label' => 'Commandes Web',   'route' => 'admin.orders.index',    'badge' => $pendingOrdersWeb ?? 0],
                ['href' => '/admin/customers',  'icon' => 'users',        'label' => 'Clients Web',     'route' => 'admin.customers.index', 'badge' => $newClientsWeb ?? 0],
                ['href' => '/admin/web-messages','icon' => 'message-circle','label' => 'Messages Web',  'route' => 'admin.webmessages.index','badge' => $unreadMessagesWeb ?? 0],
                ['href' => '/admin/categories', 'icon' => 'tag',          'label' => 'Catégories',      'route' => 'admin.categories.index'],
                ['href' => '/admin/banners',    'icon' => 'image',        'label' => 'Bannières',       'route' => 'admin.banners.index'],
                ['href' => '/admin/settings',   'icon' => 'settings-2',   'label' => 'Config boutique', 'route' => 'admin.settings.index'],
            ];
            @endphp
            @foreach($shopItems as $item)
            <a href="{{ $item['href'] }}" wire:navigate class="sidebar-link flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs($item['route']) ? 'active bg-[#E63B2E] text-white shadow-sm' : 'text-gray-600' }}">
                <span class="flex items-center gap-3">
                    <i data-lucide="{{ $item['icon'] }}" class="w-4 h-4 flex-shrink-0"></i> {{ $item['label'] }}
                </span>
                @if(($item['badge'] ?? 0) > 0)
                <span class="text-[10px] font-bold min-w-[18px] h-[18px] px-1 flex items-center justify-center rounded-full {{ request()->routeIs($item['route']) ? 'bg-white text-[#E63B2E]' : 'bg-[#E63B2E] text-white' }}">{{ $item['badge'] }}</span>
                @endif
            </a>
            @endforeach

            {{-- Section Application Android (Firebase) --}}
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest px-3 pt-4 pb-1">App Android 🔥</p>
            @php
            $firebaseItems = [
                ['href' => '/admin/clients',       'icon' => 'smartphone',    'label' => 'Clients Android',  'route' => 'admin.clients.index',    'badge' => $newClientsAndroid ?? 0],
                ['href' => '/admin/commandes',     'icon' => 'clipboard-list','label' => 'Commandes Android','route' => 'admin.commandes.index',  'badge' => $pendingOrdersAndroid ?? 0],
                ['href' => '/admin/produits',      'icon' => 'boxes',         'label' => 'Produits Firebase', 'route' => 'admin.produits.index'],
                ['href' => '/admin/messages',      'icon' => 'message-circle','label' => 'Messages',         'route' => 'admin.messages.index',   'badge' => $unreadMessagesAndroid ?? 0],
                ['href' => '/admin/notifications', 'icon' => 'bell',          'label' => 'Notifications',    'route' => 'admin.notifications.index'],
                ['href' => '/admin/parametres',    'icon' => 'sliders',       'label' => 'Paramètres App',   'route' => 'admin.parametres.index'],
            ];
            @endphp
            @foreach($firebaseItems as $item)
            <a href="{{ $item['href'] }}" wire:navigate class="sidebar-link flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs($item['route']) ? 'active bg-[#E63B2E] text-white shadow-sm' : 'text-gray-600' }}">
                <span class="flex items-center gap-3">
                    <i data-lucide="{{ $item['icon'] }}" class="w-4 h-4 flex-shrink-0"></i> {{ $item['label'] }}
                </span>
                @if(($item['badge'] ?? 0) > 0)
                <span class="text-[10px] font-bold min-w-[18px] h-[18px] px-1 flex items-center justify-center rounded-full {{ request()->routeIs($item['route']) ? 'bg-white text-[#E63B2E]' : 'bg-[#E63B2E] text-white' }}">{{ $item['badge'] }}</span>
                @endif
            </a>
            @endforeach

        </nav>

        {{-- Footer --}}
        <div class="border-t border-gray-100 p-4">
            <a href="/" target="_blank" class="flex items-center gap-2 text-xs text-gray-500 hover:text-[#E63B2E] transition-colors mb-2">
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Voir la boutique
            </a>
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="flex items-center gap-2 text-xs text-gray-500 hover:text-[#E63B2E] transition-colors">
                    <i data-lucide="log-out" class="w-3.5 h-3.5"></i> Déconnexion
                </button>
            </form>
        </div>
    </aside>

    {{-- Overlay mobile --}}
    <div x-show="sidebarOpen" @click="sidebarOpen = false"
         class="fixed inset-0 bg-black/40 z-30 lg:hidden" x-transition></div>

    {{-- Main area --}}
    <div class="lg:ml-64 flex flex-col min-h-screen">

        {{-- Topbar --}}
        <header class="bg-white border-b border-gray-100 h-16 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-20">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-xl hover:bg-gray-50 text-gray-500">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <div>
                    <h1 class="font-semibold text-gray-800 text-sm">@yield('page_title', 'Tableau de bord')</h1>
                    <p class="text-xs text-gray-400">@yield('page_subtitle', '')</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-600 hidden sm:block">{{ auth()->user()->name }}</span>
                <div class="w-8 h-8 bg-[#E63B2E]/10 rounded-xl flex items-center justify-center">
                    <span class="text-[#E63B2E] font-bold text-sm">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                </div>
            </div>
        </header>

        {{-- Flash messages --}}
        @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="mx-4 mt-4 bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 flex-shrink-0"></i>
            {{ session('success') }}
            <button @click="show = false" class="ml-auto"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        @endif
        @if(session('error'))
        <div class="mx-4 mt-4 bg-red-50 border border-red-200 text-[#E63B2E] rounded-xl px-4 py-3 text-sm flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
            {{ session('error') }}
        </div>
        @endif

        {{-- Toast Livewire (dispatch('notify', message: '...')) --}}
        <div x-show="toast" x-transition
             class="mx-4 mt-4 bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 flex-shrink-0"></i>
            <span x-text="toast"></span>
            <button @click="toast = null" class="ml-auto"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>

        {{-- Page content --}}
        <main class="flex-1 p-4 sm:p-6">
            @hasSection('content')
                @yield('content')
            @else
                {{ $slot ?? '' }}
            @endif
        </main>
    </div>

    <script>document.addEventListener('DOMContentLoaded', function () { lucide.createIcons(); });</script>
    <script>document.addEventListener('livewire:navigated', function () { lucide.createIcons(); });</script>
    @stack('scripts')
    @livewireScripts
</body>
</html>
