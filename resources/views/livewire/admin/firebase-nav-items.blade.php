<div wire:init="loadBadges" wire:poll.20s="loadBadges">
    @php
    $items = [
        ['href' => '/admin/clients',       'icon' => 'smartphone',    'label' => 'Clients Android',  'route' => 'admin.clients.index',    'badge' => $newClientsAndroid],
        ['href' => '/admin/commandes',     'icon' => 'clipboard-list','label' => 'Commandes Android','route' => 'admin.commandes.index',  'badge' => $pendingOrdersAndroid],
        ['href' => '/admin/messages',      'icon' => 'message-circle','label' => 'Messages',         'route' => 'admin.messages.index',   'badge' => $unreadMessagesAndroid],
        ['href' => '/admin/notifications', 'icon' => 'bell',          'label' => 'Notifications',    'route' => 'admin.notifications.index'],
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
