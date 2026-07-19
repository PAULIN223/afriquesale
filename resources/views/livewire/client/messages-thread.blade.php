<div class="max-w-2xl mx-auto px-4 sm:px-6 py-6">

    <div class="flex items-center gap-3 mb-6">
        <a href="/" wire:navigate class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 text-sm text-gray-500 hover:text-[#E63B2E] hover:border-[#E63B2E] transition-all">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Retour
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i data-lucide="message-circle" class="w-6 h-6 text-[#E63B2E]"></i>
                Messages
            </h1>
            <p class="text-sm text-gray-400 mt-0.5">Contactez notre équipe</p>
        </div>
    </div>

    {{-- Contact info --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
        <div class="bg-white rounded-2xl border border-gray-100 p-4 text-center">
            <div class="w-10 h-10 bg-[#E63B2E]/10 rounded-xl flex items-center justify-center mx-auto mb-2">
                <i data-lucide="clock" class="w-5 h-5 text-[#E63B2E]"></i>
            </div>
            <p class="text-xs text-gray-500">Réponse en</p>
            <p class="font-bold text-gray-800 text-sm">24h max</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-4 text-center">
            <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                <i data-lucide="headphones" class="w-5 h-5 text-green-600"></i>
            </div>
            <p class="text-xs text-gray-500">Support</p>
            <p class="font-bold text-gray-800 text-sm">7j/7</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-4 text-center">
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                <i data-lucide="shield-check" class="w-5 h-5 text-blue-600"></i>
            </div>
            <p class="text-xs text-gray-500">Service</p>
            <p class="font-bold text-gray-800 text-sm">Gratuit</p>
        </div>
    </div>

    {{-- Fil de discussion --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-6" wire:poll.5s.visible="refreshData">
        <h2 class="font-bold text-gray-800 mb-5 flex items-center gap-2">
            <i data-lucide="send" class="w-4 h-4 text-[#E63B2E]"></i>
            Discussion avec le support
        </h2>

        <div class="space-y-3 max-h-[50vh] overflow-y-auto pr-1 mb-4">
            @forelse($chatMessages as $msg)
            @php $isMine = $msg->sender === 'client'; @endphp
            <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[75%] px-4 py-2.5 rounded-2xl text-sm
                    {{ $isMine ? 'bg-[#E63B2E] text-white rounded-br-sm' : 'bg-gray-100 text-gray-800 rounded-bl-sm' }}">
                    @if($msg->product)
                    <a href="/products/{{ $msg->product->id }}" wire:navigate
                       class="flex items-center gap-2 mb-2 p-2 rounded-xl {{ $isMine ? 'bg-white/15' : 'bg-white' }}">
                        <img src="{{ $msg->product->image_url ? '/image-proxy?url=' . urlencode($msg->product->image_url) : '/images/placeholder.png' }}"
                             class="w-9 h-9 rounded-lg object-cover flex-shrink-0">
                        <span class="text-xs font-medium {{ $isMine ? 'text-white' : 'text-gray-700' }} line-clamp-2">{{ $msg->product->nom }}</span>
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
            <p class="text-center text-gray-400 py-8">Aucun message pour l'instant — écrivez-nous !</p>
            @endforelse
        </div>

        @if($pinnedProduct)
        <div class="flex items-center gap-2 mb-2 p-2 rounded-xl border border-[#E63B2E]/30 bg-[#E63B2E]/5">
            <img src="{{ $pinnedProduct->image_url ? '/image-proxy?url=' . urlencode($pinnedProduct->image_url) : '/images/placeholder.png' }}"
                 class="w-10 h-10 rounded-lg object-cover flex-shrink-0">
            <span class="flex-1 text-xs font-medium text-gray-700 line-clamp-2">{{ $pinnedProduct->nom }}</span>
            <button type="button" wire:click="unpinProduct" class="text-gray-400 hover:text-[#E63B2E] p-1">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        @endif

        <form wire:submit="send" class="flex gap-2 border-t border-gray-100 pt-4">
            <input type="text" wire:model="body" placeholder="Écrire un message..."
                   class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
            <button type="submit" class="bg-[#E63B2E] hover:bg-[#CC2F23] text-white px-5 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2">
                <span wire:loading.remove wire:target="send">Envoyer</span>
                <span wire:loading wire:target="send">...</span>
            </button>
        </form>
        @error('body') <p class="text-xs text-[#E63B2E] mt-1">{{ $message }}</p> @enderror
    </div>

</div>
