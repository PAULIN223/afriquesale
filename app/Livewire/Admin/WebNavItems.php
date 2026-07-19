<?php

namespace App\Livewire\Admin;

use App\Models\Message;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Livewire\Component;

class WebNavItems extends Component
{
    public int $pendingOrdersWeb = 0;
    public int $newClientsWeb = 0;
    public int $unreadMessagesWeb = 0;

    public function mount(): void
    {
        $this->refreshBadges();
    }

    public function refreshBadges(): void
    {
        $this->pendingOrdersWeb = Order::where('statut', 'en_attente')->count();
        $this->newClientsWeb    = User::where('role', 'client')
            ->where('created_at', '>', Setting::get('clients_web_last_seen', now()->subYears(10)))
            ->count();
        $this->unreadMessagesWeb = Message::where('sender', 'client')->where('is_read', false)->count();
    }

    public function render()
    {
        return view('livewire.admin.web-nav-items');
    }
}
