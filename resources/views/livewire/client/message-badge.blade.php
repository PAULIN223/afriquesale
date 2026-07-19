@php
$positionClass = $variant === 'mobile'
    ? 'top-1 right-[calc(50%-14px)]'
    : '-top-1 -right-1' . (request()->routeIs('messages.index') ? ' ring-2 ring-white' : '');
@endphp
<span wire:poll.10s class="absolute {{ $positionClass }} bg-[#E63B2E] text-white text-[9px] w-4 h-4 rounded-full flex items-center justify-center font-bold {{ $count > 0 ? '' : 'hidden' }}">{{ $count }}</span>
