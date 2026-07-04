<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class ParametresForm extends Component
{
    public string $nomBoutique = 'AfricSales';
    public string $email = 'contact@africsales.com';
    public string $devise = 'XAF';
    public string $fraisLivraison = '0';

    public function mount(): void
    {
        $settings = Setting::pluck('valeur', 'cle');

        $this->nomBoutique    = $settings['nom_boutique'] ?? 'AfricSales';
        $this->email          = $settings['email'] ?? $settings['email_contact'] ?? 'contact@africsales.com';
        $this->devise         = $settings['devise'] ?? 'XAF';
        $this->fraisLivraison = $settings['frais_livraison'] ?? '0';
    }

    public function save(): void
    {
        $this->validate([
            'nomBoutique'    => 'required|string|max:100',
            'email'          => 'required|email',
            'devise'         => 'required|string|max:10',
            'fraisLivraison' => 'required|numeric|min:0',
        ]);

        $fields = [
            'nom_boutique'    => $this->nomBoutique,
            'email'           => $this->email,
            'devise'          => $this->devise,
            'frais_livraison' => $this->fraisLivraison,
        ];

        foreach ($fields as $cle => $valeur) {
            Setting::updateOrCreate(['cle' => $cle], ['valeur' => $valeur]);
        }

        $this->dispatch('notify', message: 'Paramètres mis à jour.');
    }

    public function render()
    {
        return view('livewire.admin.parametres-form');
    }
}
