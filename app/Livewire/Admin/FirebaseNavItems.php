<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class FirebaseNavItems extends Component
{
    public int $newClientsAndroid = 0;
    public int $pendingOrdersAndroid = 0;
    public int $unreadMessagesAndroid = 0;

    /**
     * Chargé après le rendu initial (wire:init) pour ne jamais bloquer
     * l'affichage de la page sur les appels réseau Firestore.
     */
    public function loadBadges(): void
    {
        $badges = Cache::remember('admin_android_badges', 60, function () {
            try {
                $firebase = app(FirebaseService::class);

                $orders  = $firebase->getAllOrders();
                $pending = count(array_filter($orders, fn($o) => ($o['statut'] ?? $o['status'] ?? '') === 'en_attente'));

                $users      = $firebase->getAllUsers();
                $lastSeen   = strtotime(Setting::get('clients_android_last_seen', now()->subYears(10)->toIso8601String()));
                $newClients = count(array_filter($users, fn($u) => strtotime($u['createdAt'] ?? 'now') > $lastSeen));

                $messages = $firebase->getAllMessages();
                $unread   = count(array_filter($messages, fn($m) => ($m['isFromUser'] ?? false) && !($m['read'] ?? false)));

                return ['pending' => $pending, 'newClients' => $newClients, 'unread' => $unread];
            } catch (\Exception $e) {
                return ['pending' => 0, 'newClients' => 0, 'unread' => 0];
            }
        });

        $this->pendingOrdersAndroid  = $badges['pending'];
        $this->newClientsAndroid     = $badges['newClients'];
        $this->unreadMessagesAndroid = $badges['unread'];
    }

    public function render()
    {
        return view('livewire.admin.firebase-nav-items');
    }
}
