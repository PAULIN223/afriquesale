<?php

namespace App\Livewire\Client;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class MessagesThread extends Component
{
    public $chatMessages = [];
    public string $body = '';
    public ?Product $pinnedProduct = null;

    public function mount($product = null): void
    {
        $this->refreshData();

        auth()->user()->messages()
            ->where('sender', 'admin')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $productId = $product ?? request()->query('product');
        if ($productId) {
            $this->pinnedProduct = Product::find($productId);
            if ($this->pinnedProduct) {
                $this->body = "Bonjour, je suis intéressé(e) par cet article : {$this->pinnedProduct->nom}";
            }
        }
    }

    public function unpinProduct(): void
    {
        $this->pinnedProduct = null;
    }

    public function refreshData(): void
    {
        $this->chatMessages = auth()->user()->messages()->with('product')->oldest()->get();
    }

    public function send(): void
    {
        $this->validate(['body' => 'required|string|max:2000']);

        auth()->user()->messages()->create([
            'product_id' => $this->pinnedProduct?->id,
            'sender'     => 'client',
            'body'       => $this->body,
            'is_read'    => false,
        ]);

        $this->body = '';
        $this->pinnedProduct = null;
        $this->refreshData();
    }

    public function render()
    {
        return view('livewire.client.messages-thread');
    }
}
