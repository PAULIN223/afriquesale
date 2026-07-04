@extends('layouts.admin')
@section('title', 'Messages')
@section('page_title', 'Messages Android')
@section('page_subtitle', $total . ' message(s) au total')

@section('content')
<div class="grid grid-cols-1 gap-3">
    @forelse($conversations as $conv)
    <a href="{{ route('admin.messages.show', $conv['uid']) }}" wire:navigate
       class="bg-white rounded-2xl border border-gray-100 p-4 flex items-center gap-4 hover:shadow-md transition-shadow">
        <div class="w-10 h-10 bg-[#E63B2E]/10 rounded-2xl flex items-center justify-center flex-shrink-0">
            <span class="text-[#E63B2E] font-bold">{{ strtoupper(substr($conv['name'], 0, 1)) }}</span>
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-medium text-gray-800">{{ $conv['name'] }}</p>
            <p class="text-xs text-gray-400 truncate">{{ $conv['last_msg'] }}</p>
        </div>
        <div class="text-right flex-shrink-0">
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
