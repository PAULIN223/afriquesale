@extends('layouts.admin')
@section('title', 'Conversation')
@section('page_title', $user ? ($user['name'] ?? 'Client') : 'Conversation')
@section('page_subtitle', $user['email'] ?? $uid)

@section('content')
<div class="bg-white rounded-2xl border border-gray-100 p-5 max-w-2xl mx-auto">
    <div class="space-y-3 max-h-[60vh] overflow-y-auto pr-1">
        @forelse($messages as $msg)
        @php $isAdmin = ($msg['sender'] ?? $msg['role'] ?? '') === 'admin'; @endphp
        <div class="flex {{ $isAdmin ? 'justify-end' : 'justify-start' }}">
            <div class="max-w-[75%] px-4 py-2.5 rounded-2xl text-sm
                {{ $isAdmin ? 'bg-[#E63B2E] text-white rounded-br-sm' : 'bg-gray-100 text-gray-800 rounded-bl-sm' }}">
                <p>{{ $msg['body'] ?? $msg['message'] ?? $msg['text'] ?? '' }}</p>
                <p class="text-[10px] mt-1 opacity-60">{{ isset($msg['createdAt']) ? date('d/m H:i', strtotime($msg['createdAt'])) : '' }}</p>
            </div>
        </div>
        @empty
        <p class="text-center text-gray-400 py-8">Aucun message dans cette conversation</p>
        @endforelse
    </div>
</div>
<div class="mt-4 text-center">
    <a href="{{ route('admin.messages.index') }}" class="text-sm text-[#E63B2E] hover:underline">← Retour aux messages</a>
</div>
@endsection
