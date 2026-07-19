@extends('layouts.admin')
@section('title', 'Tableau de bord')
@section('page_title', 'Tableau de bord')
@section('page_subtitle', 'Vue d\'ensemble de votre boutique')

@section('content')
{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['label' => 'Chiffre d\'affaires', 'value' => number_format($totalRevenue, 0, ',', ' ') . ' FCFA', 'icon' => 'trending-up', 'color' => 'green', 'sub' => 'Commandes confirmées'],
        ['label' => 'Commandes du jour',   'value' => $ordersToday,        'icon' => 'shopping-bag',     'color' => 'blue',   'sub' => 'Nouvelles aujourd\'hui'],
        ['label' => 'Nouveaux clients',    'value' => $newClientsToday,     'icon' => 'user-plus',        'color' => 'purple', 'sub' => 'Inscrits aujourd\'hui'],
        ['label' => 'Total clients',       'value' => $totalClients,        'icon' => 'users',            'color' => 'orange', 'sub' => 'Clients enregistrés'],
    ] as $kpi)
    <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs text-gray-400 mb-1">{{ $kpi['label'] }}</p>
                <p class="text-xl font-bold text-gray-900">{{ $kpi['value'] }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $kpi['sub'] }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0
                        {{ $kpi['color'] === 'green'  ? 'bg-green-100' : '' }}
                        {{ $kpi['color'] === 'blue'   ? 'bg-blue-100'  : '' }}
                        {{ $kpi['color'] === 'purple' ? 'bg-purple-100': '' }}
                        {{ $kpi['color'] === 'orange' ? 'bg-orange-100': '' }}">
                <i data-lucide="{{ $kpi['icon'] }}" class="w-5 h-5
                    {{ $kpi['color'] === 'green'  ? 'text-green-600'  : '' }}
                    {{ $kpi['color'] === 'blue'   ? 'text-blue-600'   : '' }}
                    {{ $kpi['color'] === 'purple' ? 'text-purple-600' : '' }}
                    {{ $kpi['color'] === 'orange' ? 'text-orange-600' : '' }}"></i>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
    {{-- Orders chart --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Commandes Web — 30 derniers jours</h3>
        </div>
        <div wire:ignore
             x-data
             x-init='new ApexCharts($el, {
                chart: { type: "area", height: 200, toolbar: { show: false } },
                series: [{ name: "Commandes", data: @json($ordersChart->pluck("count")) }],
                xaxis: { categories: @json($ordersChart->pluck("date")), labels: { rotate: -45, style: { fontSize: "10px" } } },
                colors: ["#E63B2E"],
                fill: { type: "gradient", gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } },
                stroke: { curve: "smooth", width: 2 },
                dataLabels: { enabled: false },
                grid: { borderColor: "#f3f4f6" },
                tooltip: { y: { formatter: v => v + " commandes" } },
             }).render()'></div>
    </div>

    {{-- Clients chart --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Nouveaux clients Web — 30 derniers jours</h3>
        </div>
        <div wire:ignore
             x-data
             x-init='new ApexCharts($el, {
                chart: { type: "bar", height: 200, toolbar: { show: false } },
                series: [{ name: "Clients", data: @json($clientsChart->pluck("count")) }],
                xaxis: { categories: @json($clientsChart->pluck("date")), labels: { rotate: -45, style: { fontSize: "10px" } } },
                colors: ["#1B3A6B"],
                dataLabels: { enabled: false },
                grid: { borderColor: "#f3f4f6" },
                plotOptions: { bar: { borderRadius: 4 } },
                tooltip: { y: { formatter: v => v + " clients" } },
             }).render()'></div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
    {{-- Android orders chart --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800 flex items-center gap-1.5">Commandes App Android <span>🔥</span> — 30 derniers jours</h3>
        </div>
        <div wire:ignore
             x-data
             x-init='new ApexCharts($el, {
                chart: { type: "area", height: 200, toolbar: { show: false } },
                series: [{ name: "Commandes", data: @json($ordersChartAndroid ?? []) }],
                xaxis: { categories: @json($ordersChartAndroidDates ?? []), labels: { rotate: -45, style: { fontSize: "10px" } } },
                colors: ["#f97316"],
                fill: { type: "gradient", gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } },
                stroke: { curve: "smooth", width: 2 },
                dataLabels: { enabled: false },
                grid: { borderColor: "#f3f4f6" },
                tooltip: { y: { formatter: v => v + " commandes" } },
             }).render()'></div>
    </div>

    {{-- Android clients chart --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800 flex items-center gap-1.5">Nouveaux clients App Android <span>🔥</span> — 30 derniers jours</h3>
        </div>
        <div wire:ignore
             x-data
             x-init='new ApexCharts($el, {
                chart: { type: "bar", height: 200, toolbar: { show: false } },
                series: [{ name: "Clients", data: @json($clientsChartAndroid ?? []) }],
                xaxis: { categories: @json($clientsChartAndroidDates ?? []), labels: { rotate: -45, style: { fontSize: "10px" } } },
                colors: ["#a855f7"],
                dataLabels: { enabled: false },
                grid: { borderColor: "#f3f4f6" },
                plotOptions: { bar: { borderRadius: 4 } },
                tooltip: { y: { formatter: v => v + " clients" } },
             }).render()'></div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    {{-- Top products --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Top 5 produits vendus</h3>
            <a href="/admin/products" class="text-xs text-[#E63B2E] hover:underline">Voir tout</a>
        </div>
        <div class="space-y-3">
            @forelse($topProducts as $i => $p)
            <div class="flex items-center gap-3">
                <span class="w-5 h-5 bg-gray-100 rounded text-xs font-bold text-gray-500 flex items-center justify-center flex-shrink-0">{{ $i + 1 }}</span>
                <img src="{{ $p->image_url ? '/image-proxy?url=' . urlencode($p->image_url) : '/images/placeholder.png' }}"
                     alt="{{ $p->nom }}" loading="lazy" class="w-10 h-10 object-cover rounded-xl flex-shrink-0">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-700 truncate">{{ $p->nom }}</p>
                    <p class="text-xs text-gray-400">{{ $p->total_vendu }} vendus</p>
                </div>
                <span class="text-sm font-bold text-gray-900">{{ number_format($p->chiffre_affaires, 0, ',', ' ') }}</span>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-4">Aucune vente pour le moment</p>
            @endforelse
        </div>
    </div>

    {{-- Recent orders --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Commandes récentes</h3>
            <a href="/admin/orders" class="text-xs text-[#E63B2E] hover:underline">Voir tout</a>
        </div>
        <div class="space-y-2.5">
            @forelse($recentOrders as $order)
            @php
            $statColors = ['en_attente' => 'yellow', 'confirmee' => 'blue', 'expediee' => 'orange', 'livree' => 'green', 'annulee' => 'red'];
            $c = $statColors[$order->statut] ?? 'gray';
            @endphp
            <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $order->numero_commande }}</p>
                    <p class="text-xs text-gray-400">{{ $order->user->name }}</p>
                </div>
                <div class="text-right">
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                        {{ $c === 'yellow' ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $c === 'blue'   ? 'bg-blue-100 text-blue-700'     : '' }}
                        {{ $c === 'orange' ? 'bg-orange-100 text-orange-700' : '' }}
                        {{ $c === 'green'  ? 'bg-green-100 text-green-700'   : '' }}
                        {{ $c === 'red'    ? 'bg-red-100 text-red-600'       : '' }}">{{ $order->statut_label }}</span>
                    <p class="text-xs font-bold text-gray-700 mt-0.5">{{ number_format($order->total, 0, ',', ' ') }} FCFA</p>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-4">Aucune commande</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
