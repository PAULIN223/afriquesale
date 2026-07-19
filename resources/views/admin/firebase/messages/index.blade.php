@extends('layouts.admin')
@section('title', 'Messages')
@section('page_title', 'Messages Android')
@section('page_subtitle', $total . ' message(s) au total')

@section('content')
<div class="grid grid-cols-1 gap-3">
    @forelse($conversations as $conv)
    <a href="{{ route('admin.messages.show', $conv['uid']) }}" wire:navigate
       class="bg-white rounded-2xl border {{ $conv['unread'] > 0 ? 'border-[#E63B2E]/30' : 'border-gray-100' }} p-4 flex items-center gap-4 hover:shadow-md transition-shadow">
        <div class="relative w-10 h-10 bg-[#E63B2E]/10 rounded-2xl flex items-center justify-center flex-shrink-0">
            <span class="text-[#E63B2E] font-bold">{{ strtoupper(substr($conv['name'], 0, 1)) }}</span>
            @if($conv['unread'] > 0)
            <span class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-[#E63B2E] rounded-full border-2 border-white"></span>
            @endif
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-medium text-gray-800 {{ $conv['unread'] > 0 ? 'font-bold' : '' }}">{{ $conv['name'] }}</p>
            <p class="text-xs text-gray-400 truncate">{{ $conv['last_msg'] }}</p>
        </div>
        <div class="text-right flex-shrink-0 flex items-center gap-2">
            @if($conv['unread'] > 0)
            <span class="text-xs bg-[#E63B2E] text-white px-2 py-0.5 rounded-full font-semibold">{{ $conv['unread'] }} non lu</span>
            @endif
            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">{{ $conv['count'] }} msg</span>
        </div>
    </a>
    @empty
    <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
        <i data-lucide="message-circle" class="w-10 h-10 mx-auto mb-3 text-gray-200"></i>
        <p class="text-gray-400">Aucun message</p>
    </div>
    @endforelse
</div>
@endsection
