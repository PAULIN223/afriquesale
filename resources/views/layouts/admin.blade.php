<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
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
            <livewire:admin.web-nav-items />

            {{-- Section Application Android (Firebase) --}}
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest px-3 pt-4 pb-1">App Android 🔥</p>
            <livewire:admin.firebase-nav-items />

        </nav>

        {{-- Footer --}}
        <div class="border-t border-gray-100 p-4">
            <a href="/" target="_blank" class="flex items-center gap-2 text-xs text-gray-500 hover:text-[#E63B2E] transition-colors mb-2">
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Voir la boutique
            </a>
            <livewire:admin.logout-button />
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
    <script>document.addEventListener('livewire:init', function () { Livewire.hook('morph.updated', function () { lucide.createIcons(); }); });</script>
    @stack('scripts')
    @livewireScripts
</body>
</html>
