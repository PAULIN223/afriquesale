<div wire:poll.10s="refreshBadges">
    @php
    $items = [
        ['href' => '/admin/products',   'icon' => 'package',      'label' => 'Produits Sheet',  'route' => 'admin.products.index'],
        ['href' => '/admin/orders',     'icon' => 'shopping-bag', 'label' => 'Commandes Web',   'route' => 'admin.orders.index',    'badge' => $pendingOrdersWeb],
        ['href' => '/admin/customers',  'icon' => 'users',        'label' => 'Clients Web',     'route' => 'admin.customers.index', 'badge' => $newClientsWeb],
        ['href' => '/admin/web-messages','icon' => 'message-circle','label' => 'Messages Web',  'route' => 'admin.webmessages.index','badge' => $unreadMessagesWeb],
        ['href' => '/admin/categories', 'icon' => 'tag',          'label' => 'Catégories',      'route' => 'admin.categories.index'],
    ];
    @endphp
    @foreach($items as $item)
    <a href="{{ $item['href'] }}" wire:navigate class="sidebar-link flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs($item['route']) ? 'active bg-[#E63B2E] text-white shadow-sm' : 'text-gray-600' }}">
        <span class="flex items-center gap-3">
            <i data-lucide="{{ $item['icon'] }}" class="w-4 h-4 flex-shrink-0"></i> {{ $item['label'] }}
        </span>
        @if(($item['badge'] ?? 0) > 0)
        <span class="text-[10px] font-bold min-w-[18px] h-[18px] px-1 flex items-center justify-center rounded-full {{ request()->routeIs($item['route']) ? 'bg-white text-[#E63B2E]' : 'bg-[#E63B2E] text-white' }}">{{ $item['badge'] }}</span>
        @endif
    </a>
    @endforeach
</div>
