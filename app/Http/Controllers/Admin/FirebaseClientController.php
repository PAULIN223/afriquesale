<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\FirebaseService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class FirebaseClientController extends Controller
{
    public function __construct(
        protected FirebaseService      $firebase,
        protected NotificationService  $notification
    ) {}

    public function index(Request $request)
    {
        $users = $this->firebase->getAllUsers();

        // Recherche par nom ou email
        if ($request->filled('search')) {
            $s     = mb_strtolower($request->search);
            $users = array_filter($users, fn($u) =>
                str_contains(mb_strtolower($u['name'] ?? ''), $s) ||
                str_contains(mb_strtolower($u['email'] ?? ''), $s)
            );
        }

        // Filtre statut
        if ($request->filled('statut')) {
            $users = array_filter($users, fn($u) =>
                ($u['statut'] ?? 'actif') === $request->statut
            );
        }

        // Filtre source
        if ($request->filled('source')) {
            $users = array_filter($users, fn($u) =>
                ($u['source'] ?? '') === $request->source
            );
        }

        // Regroupe les commandes par client pour afficher un résumé des statuts
        $ordersByUser = [];
        foreach ($this->firebase->getAllOrders() as $order) {
            $uid = $order['userId'] ?? null;
            if ($uid) $ordersByUser[$uid][] = $order;
        }

        $statutLabels = [
            'en_attente' => 'En attente',
            'confirmée'  => 'Confirmées',
            'expédiée'   => 'Expédiées',
            'livrée'     => 'Livrées',
            'annulée'    => 'Annulées',
        ];

        foreach ($users as &$u) {
            $counts = array_fill_keys(array_keys($statutLabels), 0);
            foreach ($ordersByUser[$u['id']] ?? [] as $order) {
                $s = $order['statut'] ?? $order['status'] ?? 'en_attente';
                if (isset($counts[$s])) $counts[$s]++;
            }
            $u['orderCounts'] = $counts;
        }
        unset($u);

        // Marque les clients Android comme "vus" : le badge de notification repart à 0.
        Setting::set('clients_android_last_seen', now()->toIso8601String());

        return view('admin.firebase.clients.index', [
            'users'        => array_values($users),
            'total'        => count($users),
            'statutLabels' => $statutLabels,
        ]);
    }

    public function show(string $uid)
    {
        $user   = $this->firebase->getUserById($uid);
        if (!$user) abort(404);
        $orders = $this->firebase->getOrdersByUser($uid);

        return view('admin.firebase.clients.show', compact('user', 'orders'));
    }

    public function block(string $uid)
    {
        $this->firebase->blockUser($uid);
        return back()->with('success', 'Utilisateur bloqué avec succès.');
    }

    public function unblock(string $uid)
    {
        $this->firebase->unblockUser($uid);
        return back()->with('success', 'Utilisateur débloqué avec succès.');
    }

    public function notify(Request $request, string $uid)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'body'  => 'required|string|max:300',
        ]);

        $this->notification->sendToUser($uid, $request->title, $request->body);
        return back()->with('success', 'Notification envoyée.');
    }
}
