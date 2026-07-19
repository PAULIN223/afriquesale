<div>
    <h1 class="font-semibold text-gray-800 text-lg mb-1">{{ $user->name }}</h1>
    <p class="text-xs text-gray-400 mb-4">{{ $user->email }}</p>

    <div class="bg-white rounded-2xl border border-gray-100 p-5 max-w-2xl mx-auto" wire:poll.5s.visible="refreshData">
        <div class="space-y-3 max-h-[60vh] overflow-y-auto pr-1">
            @forelse($chatMessages as $msg)
            @php $isAdmin = $msg->sender === 'admin'; @endphp
            <div class="flex {{ $isAdmin ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[75%] px-4 py-2.5 rounded-2xl text-sm
                    {{ $isAdmin ? 'bg-[#E63B2E] text-white rounded-br-sm' : 'bg-gray-100 text-gray-800 rounded-bl-sm' }}">
                    @if($msg->product)
                    <a href="/products/{{ $msg->product->id }}" target="_blank"
                       class="flex items-center gap-2 mb-2 p-2 rounded-xl {{ $isAdmin ? 'bg-white/15' : 'bg-white' }}">
                        <img src="{{ $msg->product->image_url ? '/image-proxy?url=' . urlencode($msg->product->image_url) : '/images/placeholder.png' }}"
                             class="w-9 h-9 rounded-lg object-cover flex-shrink-0">
                        <span class="text-xs font-medium {{ $isAdmin ? 'text-white' : 'text-gray-700' }} line-clamp-2">{{ $msg->product->nom }}</span>
                    </a>
                    @endif
                    @if($msg->image_path)
                    <a href="{{ $msg->image_url }}" target="_blank" class="block mb-2">
                        <img src="{{ $msg->image_url }}" class="max-h-48 rounded-lg object-cover">
                    </a>
                    @endif
                    <p class="whitespace-pre-line">{{ $msg->body }}</p>
                    <p class="text-[10px] mt-1 opacity-60">{{ $msg->created_at->format('d/m H:i') }}</p>
                </div>
            </div>
            @empty
            <p class="text-center text-gray-400 py-8">Aucun message dans cette conversation</p>
            @endforelse
        </div>

        <form wire:submit="send" class="mt-4 flex gap-2 border-t border-gray-100 pt-4">
            <input type="text" wire:model="body" placeholder="Répondre..."
                   class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
            <button type="submit" class="bg-[#E63B2E] hover:bg-[#CC2F23] text-white px-5 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2">
                <span wire:loading.remove wire:target="send">Envoyer</span>
                <span wire:loading wire:target="send">...</span>
            </button>
        </form>
        @error('body') <p class="text-xs text-[#E63B2E] mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="mt-4 text-center">
        <a href="{{ route('admin.webmessages.index') }}" wire:navigate class="text-sm text-[#E63B2E] hover:underline">← Retour aux messages</a>
    </div>
</div>
