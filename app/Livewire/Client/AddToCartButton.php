<?php

namespace App\Livewire\Client;

use App\Models\CartItem;
use App\Models\Product;
use Livewire\Component;

class AddToCartButton extends Component
{
    public Product $product;

    public function add($quantite = 1, $productId = null, $selectedColor = null)
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $hasVariantChoices = Product::actif()
            ->inVariantGroup($this->product)
            ->where(function ($q) {
                $q->whereNotNull('image_url')->orWhereNotNull('image_urls');
            })
            ->when(!filled($this->product->product_code), fn ($q) => $q->whereNotNull('color'))
            ->count() > 1;

        if (!$productId && $hasVariantChoices) {
            return;
        }

        $productId = $productId ?: $this->product->id;
        $product = Product::findOrFail($productId);

        if (
            $product->id !== $this->product->id
            && !Product::actif()->inVariantGroup($this->product)->whereKey($product->id)->exists()
        ) {
            return;
        }

        $quantite = max(1, (int) $quantite);

        if (!$product->fresh()->is_purchasable) {
            return;
        }

        $color = trim((string) ($selectedColor ?? $product->color ?? $this->product->color ?? ''));
        $image = $product->image_url ?: $this->product->image_url;

        $cartItem = CartItem::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->where('color', $color !== '' ? $color : $product->color)
            ->first();

        if ($cartItem) {
            $cartItem->update([
                'quantite'        => min($cartItem->quantite + $quantite, $product->stock),
                'color'           => $color !== '' ? $color : $product->color,
                'color_image_url' => $image,
            ]);
        } else {
            CartItem::create([
                'user_id'         => auth()->id(),
                'product_id'      => $product->id,
                'quantite'        => min($quantite, $product->stock),
                'color'           => $color !== '' ? $color : $product->color,
                'color_image_url' => $image,
            ]);
        }

        $this->dispatch('cart-updated');
    }

    public function render()
    {
        return view('livewire.client.add-to-cart-button');
    }
}
