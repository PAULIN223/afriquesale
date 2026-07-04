<div>
    <h1 class="font-semibold text-gray-800 text-lg mb-1">{{ $user['name'] ?? 'Client' }}</h1>
    <p class="text-xs text-gray-400 mb-4">{{ $user['email'] ?? $uid }}</p>

    <div class="bg-white rounded-2xl border border-gray-100 p-5 max-w-2xl mx-auto" wire:poll.5s.visible="refreshData">
        <div class="space-y-3 max-h-[60vh] overflow-y-auto pr-1">
            @forelse($chatMessages as $msg)
            @php $isClient = $msg['isFromUser'] ?? true; @endphp
            <div class="flex {{ $isClient ? 'justify-start' : 'justify-end' }}">
                <div class="max-w-[75%] px-4 py-2.5 rounded-2xl text-sm
                    {{ $isClient ? 'bg-gray-100 text-gray-800 rounded-bl-sm' : 'bg-[#E63B2E] text-white rounded-br-sm' }}">
                    <p>{{ $msg['text'] ?? $msg['body'] ?? '' }}</p>
                    @if(!empty($msg['product']['title']))
                    <p class="text-[11px] mt-1 {{ $isClient ? 'text-gray-500' : 'opacity-80' }}">
                        <i data-lucide="package" class="w-3 h-3 inline"></i> {{ $msg['product']['title'] }}
                    </p>
                    @endif
                    <p class="text-[10px] mt-1 opacity-60">{{ isset($msg['createdAt']) ? date('d/m H:i', strtotime($msg['createdAt'])) : '' }}</p>
                </div>
            </div>
            @empty
            <p class="text-center text-gray-400 py-8">Aucun message dans cette conversation</p>
            @endforelse
        </div>

        <form wire:submit="send" class="mt-4 flex gap-2 border-t border-gray-100 pt-4">
            <input type="text" wire:model="text" placeholder="Répondre..."
                   class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
            <button type="submit" class="bg-[#E63B2E] hover:bg-[#CC2F23] text-white px-5 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2">
                <span wire:loading.remove wire:target="send">Envoyer</span>
                <span wire:loading wire:target="send">...</span>
            </button>
        </form>
        @error('text') <p class="text-xs text-[#E63B2E] mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="mt-4 text-center">
        <a href="{{ route('admin.messages.index') }}" wire:navigate class="text-sm text-[#E63B2E] hover:underline">← Retour aux messages</a>
    </div>
</div>
