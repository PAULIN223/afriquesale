<?php

namespace App\Livewire\Client;

use Livewire\Attributes\On;
use Livewire\Component;

class CartBadge extends Component
{
    #[On('cart-updated')]
    public function refresh(): void
    {
        // Le simple fait d'écouter l'événement déclenche un nouveau render().
    }

    public function render()
    {
        $count = auth()->check() ? auth()->user()->cartItems()->sum('quantite') : 0;

        return view('livewire.client.cart-badge', ['count' => $count]);
    }
}
