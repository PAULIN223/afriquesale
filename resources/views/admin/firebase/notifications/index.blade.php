@extends('layouts.admin')
@section('title', 'Notifications')
@section('page_title', 'Notifications')
@section('page_subtitle', $unread . ' non lue(s)')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- Envoyer à tous --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <i data-lucide="send" class="w-4 h-4 text-[#E63B2E]"></i> Envoyer à tous les clients Android
        </h3>
        <form method="POST" action="{{ route('admin.notifications.sendAll') }}" class="space-y-3">
            @csrf
            <input type="text" name="title" placeholder="Titre de la notification" required
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
            <textarea name="body" rows="3" placeholder="Message..." required
                      class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E] resize-none"></textarea>
            <button class="w-full py-3 bg-[#E63B2E] hover:bg-[#CC2F23] text-white rounded-xl font-semibold text-sm flex items-center justify-center gap-2">
                <i data-lucide="bell" class="w-4 h-4"></i> Envoyer à tous
            </button>
        </form>
    </div>

    {{-- Envoyer à un client --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <i data-lucide="user" class="w-4 h-4 text-blue-500"></i> Envoyer à un client spécifique
        </h3>
        <form method="POST" action="{{ route('admin.notifications.sendUser') }}" class="space-y-3">
            @csrf
            <input type="text" name="uid" placeholder="UID du client (ex: abc123xyz)" required
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E] font-mono">
            <input type="text" name="title" placeholder="Titre" required
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
            <textarea name="body" rows="3" placeholder="Message..." required
                      class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E] resize-none"></textarea>
            <button class="w-full py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-semibold text-sm flex items-center justify-center gap-2">
                <i data-lucide="send" class="w-4 h-4"></i> Envoyer
            </button>
        </form>
    </div>
</div>

{{-- Historique notifications admin --}}
<div class="mt-5 bg-white rounded-2xl border border-gray-100 p-5">
    <h3 class="font-semibold text-gray-800 mb-4">Notifications reçues ({{ count($notifications) }})</h3>
    <div class="space-y-2">
        @forelse($notifications as $notif)
        <div class="flex items-start gap-3 p-3 rounded-xl {{ ($notif['lu'] ?? false) ? 'bg-gray-50' : 'bg-[#E63B2E]/5 border border-[#E63B2E]/10' }}">
            <div class="w-8 h-8 rounded-xl {{ ($notif['lu'] ?? false) ? 'bg-gray-100' : 'bg-[#E63B2E]/10' }} flex items-center justify-center flex-shrink-0">
                <i data-lucide="bell" class="w-4 h-4 {{ ($notif['lu'] ?? false) ? 'text-gray-400' : 'text-[#E63B2E]' }}"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800">{{ $notif['title'] ?? 'Notification' }}</p>
                <p class="text-xs text-gray-500">{{ $notif['body'] ?? '' }}</p>
                <p class="text-[10px] text-gray-400 mt-0.5">{{ isset($notif['createdAt']) ? date('d/m/Y H:i', strtotime($notif['createdAt'])) : '' }}</p>
            </div>
            @if(!($notif['lu'] ?? false))
            <form method="POST" action="{{ route('admin.notifications.read', $notif['id']) }}">
                @csrf
                <button class="text-xs text-gray-400 hover:text-[#E63B2E] whitespace-nowrap">Marquer lue</button>
            </form>
            @endif
        </div>
        @empty
        <p class="text-sm text-gray-400 text-center py-6">Aucune notification</p>
        @endforelse
    </div>
</div>
@endsection
