@extends('layouts.admin')
@section('title', 'Paramètres')
@section('page_title', 'Paramètres de la boutique')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <form method="POST" action="/admin/settings" class="space-y-5">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nom de la boutique</label>
                    <input type="text" name="shop_name" value="{{ $settings['shop_name'] }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email de contact</label>
                    <input type="email" name="shop_email" value="{{ $settings['shop_email'] }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Téléphone</label>
                    <input type="text" name="shop_phone" value="{{ $settings['shop_phone'] }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Devise</label>
                    <input type="text" name="devise" value="{{ $settings['devise'] }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Frais de livraison (FCFA)</label>
                    <input type="number" name="frais_livraison" value="{{ $settings['frais_livraison'] }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Adresse physique</label>
                <input type="text" name="shop_address" value="{{ $settings['shop_address'] }}"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E63B2E]">
            </div>

            <button type="submit"
                    class="bg-[#E63B2E] hover:bg-[#CC2F23] text-white px-8 py-3 rounded-xl font-semibold text-sm transition-colors flex items-center gap-2">
                <i data-lucide="save" class="w-4 h-4"></i> Enregistrer les paramètres
            </button>
        </form>
    </div>
</div>
@endsection
