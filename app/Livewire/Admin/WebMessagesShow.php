<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class WebMessagesShow extends Component
{
    public User $user;
    public $chatMessages = [];
    public string $body = '';

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->refreshData();

        $this->user->messages()
            ->where('sender', 'client')
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    public function refreshData(): void
    {
        $this->chatMessages = $this->user->messages()->with('product')->oldest()->get();
    }

    public function send(): void
    {
        $this->validate(['body' => 'required|string|max:2000']);

        $this->user->messages()->create([
            'sender'  => 'admin',
            'body'    => $this->body,
            'is_read' => false,
        ]);

        $this->body = '';
        $this->refreshData();
    }

    public function render()
    {
        return view('livewire.admin.web-messages-show');
    }
}
