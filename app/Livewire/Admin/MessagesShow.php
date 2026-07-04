<?php

namespace App\Livewire\Admin;

use App\Services\FirebaseService;
use App\Services\NotificationService;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class MessagesShow extends Component
{
    public string $uid;
    public array $chatMessages = [];
    public ?array $user = null;
    public string $text = '';

    public function mount(string $uid): void
    {
        $this->uid = $uid;
        $this->refreshData();
        app(FirebaseService::class)->markMessagesAsRead($uid);
    }

    public function refreshData(): void
    {
        $firebase = app(FirebaseService::class);
        $this->chatMessages = $firebase->getMessagesByUser($this->uid);
        $this->user         = $firebase->getUserById($this->uid);
    }

    public function send(FirebaseService $firebase, NotificationService $notifier): void
    {
        $this->validate(['text' => 'required|string|max:2000']);

        $firebase->sendMessage($this->uid, $this->text);
        $notifier->sendToUser($this->uid, 'Nouveau message', Str::limit($this->text, 80));

        $this->text = '';
        $this->refreshData();
    }

    public function render()
    {
        return view('livewire.admin.messages-show');
    }
}
