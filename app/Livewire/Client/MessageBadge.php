<?php

namespace App\Livewire\Client;

use Livewire\Component;

class MessageBadge extends Component
{
    public string $variant = 'desktop';

    public function render()
    {
        $count = auth()->check()
            ? auth()->user()->messages()->where('sender', 'admin')->where('is_read', false)->count()
            : 0;

        return view('livewire.client.message-badge', ['count' => $count]);
    }
}
