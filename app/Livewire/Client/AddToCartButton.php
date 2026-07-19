<?php

namespace App\Livewire\Client;

use App\Models\CartItem;
use App\Models\Product;
use Livewire\Component;

class AddToCartButton extends Component
{
    public Product $product;

    public function add($quantite = 1)
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $quantite = max(1, (int) $quantite);

        if (!$this->product->fresh()->is_purchasable) {
            return;
        }

        $cartItem = CartItem::where('user_id', auth()->id())
            ->where('product_id', $this->product->id)
            ->first();

        if ($cartItem) {
            $cartItem->update(['quantite' => min($cartItem->quantite + $quantite, $this->product->stock)]);
        } else {
            CartItem::create([
                'user_id'    => auth()->id(),
                'product_id' => $this->product->id,
                'quantite'   => min($quantite, $this->product->stock),
            ]);
        }

        $this->dispatch('cart-updated');
    }

    public function render()
    {
        return view('livewire.client.add-to-cart-button');
    }
}
