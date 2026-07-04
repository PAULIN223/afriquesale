<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class SettingsForm extends Component
{
    public string $shopName = '';
    public string $shopEmail = '';
    public string $shopPhone = '';
    public string $devise = '';
    public string $fraisLivraison = '';
    public string $shopAddress = '';

    public function mount(): void
    {
        $this->shopName       = Setting::get('shop_name', 'AfricSales');
        $this->shopEmail      = Setting::get('shop_email', 'contact@africsales.com');
        $this->shopPhone      = Setting::get('shop_phone', '');
        $this->devise         = Setting::get('devise', 'FCFA');
        $this->fraisLivraison = Setting::get('frais_livraison', '2000');
        $this->shopAddress    = Setting::get('shop_address', '');
    }

    public function save(): void
    {
        Setting::set('shop_name', $this->shopName);
        Setting::set('shop_email', $this->shopEmail);
        Setting::set('shop_phone', $this->shopPhone);
        Setting::set('devise', $this->devise);
        Setting::set('frais_livraison', $this->fraisLivraison);
        Setting::set('shop_address', $this->shopAddress);

        $this->dispatch('notify', message: 'Paramètres enregistrés avec succès.');
    }

    public function render()
    {
        return view('livewire.admin.settings-form');
    }
}
