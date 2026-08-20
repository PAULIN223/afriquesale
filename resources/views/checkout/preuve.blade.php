@extends('layouts.app')
@section('title', 'Preuve de paiement — AfricSales')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Preuve de paiement</h1>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl p-4 mb-6">
        @foreach($errors->all() as $error)
        <p>{{ $error }}</p>
        @endforeach
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="space-y-5">
            {{-- Payment instructions --}}
            <div class="bg-white rounded-2xl border border-red-100 p-6">
                <h2 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-[#E63B2E]"></i> Paiement requis
                </h2>
                <p class="text-sm font-bold text-[#E63B2E] mb-4">
                    Veuillez bien vouloir déposer les frais de la commande au différent numéro Orange Money
                    "698.90.94.65" et MTN Money "676.71.06.97" pour que votre commande puisse être confirmée par le vendeur.
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="bg-gray-50 rounded-xl p-3 flex items-center gap-3 border border-gray-100">
                        <div class="w-9 h-9 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="smartphone" class="w-4 h-4 text-orange-500"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Orange Money</p>
                            <p class="font-bold text-gray-800 text-sm">698.90.94.65</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 flex items-center gap-3 border border-gray-100">
                        <div class="w-9 h-9 rounded-full bg-yellow-100 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="smartphone" class="w-4 h-4 text-yellow-600"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">MTN Money</p>
                            <p class="font-bold text-gray-800 text-sm">676.71.06.97</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Upload proof --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-6" x-data="{ preview: null, fileName: '' }">
                <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i data-lucide="camera" class="w-5 h-5 text-[#E63B2E]"></i> Photo du dépôt
                </h2>
                <p class="text-sm text-gray-500 mb-4">
                    Une fois le dépôt effectué, joignez une photo (capture d'écran ou reçu) pour que votre commande soit envoyée au vendeur.
                </p>

                <form method="POST" action="{{ route('checkout.confirmer') }}" enctype="multipart/form-data">
                    @csrf

                    <label class="flex flex-col items-center justify-center gap-2 border-2 border-dashed border-gray-200 rounded-xl px-4 py-8 cursor-pointer hover:border-[#E63B2E] transition-colors"
                           x-bind:class="preview ? 'border-[#E63B2E]' : ''">
                        <template x-if="preview">
                            <img :src="preview" class="max-h-48 rounded-lg object-contain mb-2">
                        </template>
                        <template x-if="!preview">
                            <i data-lucide="upload" class="w-8 h-8 text-gray-300"></i>
                        </template>
                        <span class="text-sm text-gray-500" x-text="fileName || 'Cliquez pour choisir une image'"></span>
                        <input type="file" name="preuve" accept="image/*" required class="hidden"
                               @change="fileName = $event.target.files[0]?.name || ''; preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                    </label>
                    @error('preuve')<p class="text-xs text-[#E63B2E] mt-2">{{ $message }}</p>@enderror

                    <button type="submit"
                            class="mt-5 w-full bg-[#E63B2E] hover:bg-[#CC2F23] text-white py-3.5 rounded-xl font-bold text-sm transition-all flex items-center justify-center gap-2">
                        <i data-lucide="send" class="w-4 h-4"></i> Envoyer la preuve et valider ma commande
                    </button>
                </form>
            </div>
        </div>

        {{-- Order summary --}}
        <div>
            <div class="bg-white rounded-2xl border border-gray-100 p-6 sticky top-24">
                <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i data-lucide="clipboard-list" class="w-5 h-5 text-[#E63B2E]"></i> Récapitulatif
                </h2>
                <div class="divide-y divide-gray-50">
                    @foreach($cartItems as $item)
                    <div class="flex gap-3 py-3">
                        <img src="{{ ($item->color_image_url ?: $item->product->image_url) ? '/image-proxy?url=' . urlencode($item->color_image_url ?: $item->product->image_url) : '/images/placeholder.png' }}"
                             alt="{{ $item->product->display_name }}" loading="lazy"
                             class="w-12 h-12 object-cover rounded-lg flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 line-clamp-1">{{ $item->product->display_name }}</p>
                            <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                <span class="text-xs text-gray-400">Qté : {{ $item->quantite }}</span>
                                @if(filled($item->color ?: $item->product->color))
                                <span class="inline-flex items-center text-[10px] font-medium text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">
                                    Couleur : {{ $item->color ?: $item->product->color }}
                                </span>
                                @endif
                            </div>
                        </div>
                        <span class="text-sm font-semibold text-gray-900">{{ number_format($item->subtotal, 0, ',', ' ') }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="border-t border-gray-100 pt-4 mt-2 space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Sous-total</span>
                        <span>{{ number_format($subtotal, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Livraison</span>
                        <span>{{ $fraisLivraison > 0 ? number_format($fraisLivraison, 0, ',', ' ') . ' FCFA' : 'Gratuit' }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-gray-900 text-base border-t border-gray-100 pt-3">
                        <span>Total à payer</span>
                        <span class="text-[#E63B2E]">{{ number_format($total, 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>
                <div class="border-t border-gray-100 pt-4 mt-4 text-sm text-gray-500">
                    <p class="font-semibold text-gray-700 mb-1">Livraison à :</p>
                    <p>{{ $adresse['nom'] }} · {{ $adresse['phone'] }}</p>
                    <p>{{ $adresse['adresse'] }}, {{ $adresse['ville'] }}, {{ $adresse['pays'] }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
