<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class CustomersIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public function mount(): void
    {
        // Marque les clients comme "vus" : le badge de notification de la sidebar repart à 0.
        Setting::set('clients_web_last_seen', now());
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function toggleStatut(int $id): void
    {
        $user = User::findOrFail($id);
        $user->update(['statut' => $user->statut === 'active' ? 'blocked' : 'active']);

        $this->dispatch('notify', message: 'Statut du client mis à jour.');
    }

    public function render()
    {
        $query = User::where('role', 'client')->withCount('orders');

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        $customers = $query->latest()->paginate(20);

        return view('livewire.admin.customers-index', compact('customers'));
    }
}
