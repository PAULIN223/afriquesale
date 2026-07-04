<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AfricSales') — Authentification</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    @livewireStyles
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen flex flex-col" style="font-family:'Inter',sans-serif;">

    <div class="flex-1 flex flex-col items-center justify-center py-12 px-4">

        {{-- Logo --}}
        <a href="/" class="mb-8 flex items-center gap-2">
            <img src="/images/logo.png" alt="AfricSales" class="h-12 w-auto"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
            <span class="hidden items-center font-bold text-2xl">
                <span style="color:#DAA520">AFRIC</span><span style="color:#1B3A6B">SALE</span>
            </span>
        </a>

        {{-- Card --}}
        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
            @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 flex-shrink-0"></i>
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-[#E63B2E] rounded-xl px-4 py-3 text-sm flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                {{ session('error') }}
            </div>
            @endif
            @if(session('info'))
            <div class="mb-4 bg-blue-50 border border-blue-200 text-blue-700 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 flex-shrink-0"></i>
                {{ session('info') }}
            </div>
            @endif
            @if(session('warning'))
            <div class="mb-4 bg-orange-50 border border-orange-200 text-orange-700 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
                <i data-lucide="alert-triangle" class="w-4 h-4 flex-shrink-0"></i>
                {{ session('warning') }}
            </div>
            @endif

            @hasSection('content')
                @yield('content')
            @else
                {{ $slot ?? '' }}
            @endif
        </div>
    </div>

    <footer class="text-center py-4 text-xs text-gray-400">
        &copy; {{ date('Y') }} AfricSales. Tous droits réservés.
    </footer>

    <script>document.addEventListener('DOMContentLoaded', function () { lucide.createIcons(); });</script>
    @livewireScripts
</body>
</html>
