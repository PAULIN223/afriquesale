<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class FirebaseOrderController extends Controller
{
    public function __construct(
        protected FirebaseService     $firebase,
        protected NotificationService $notification
    ) {}

    public function index(Request $request)
    {
        $filters = array_filter($request->only('statut', 'dateDebut', 'dateFin', 'userId'));
        $orders  = $this->firebase->getAllOrders($filters);

        $statutOf = fn($o) => $o['statut'] ?? $o['status'] ?? 'en_attente';

        $stats = [
            'total'      => count($orders),
            'en_attente' => count(array_filter($orders, fn($o) => $statutOf($o) === 'en_attente')),
            'confirmee'  => count(array_filter($orders, fn($o) => $statutOf($o) === 'confirmée')),
            'expediee'   => count(array_filter($orders, fn($o) => $statutOf($o) === 'expédiée')),
            'livree'     => count(array_filter($orders, fn($o) => $statutOf($o) === 'livrée')),
            'annulee'    => count(array_filter($orders, fn($o) => $statutOf($o) === 'annulée')),
            'montant'    => array_sum(array_map(fn($o) => $o['finalTotal'] ?? $o['total'] ?? $o['baseTotal'] ?? 0, $orders)),
        ];

        return view('admin.firebase.commandes.index', compact('orders', 'stats'));
    }

    public function show(string $id)
    {
        $order = $this->firebase->getOrderById($id);
        if (!$order) abort(404);

        $client = null;
        if (!empty($order['userId'])) {
            $client = $this->firebase->getUserById($order['userId']);
        }

        return view('admin.firebase.commandes.show', compact('order', 'client'));
    }

    public function updateStatut(Request $request, string $id)
    {
        $request->validate([
            'statut' => 'required|in:en_attente,confirmée,expédiée,livrée,annulée',
        ]);

        $order = $this->firebase->getOrderById($id);
        if (!$order) abort(404);

        $this->firebase->updateOrderStatus($id, $request->statut);

        // Notification automatique Push au client Android
        if (!empty($order['userId'])) {
            $this->notification->sendOrderStatusNotification(
                $order['userId'],
                $id,
                $request->statut
            );
        }

        return back()->with('success', 'Statut mis à jour et client notifié.');
    }
}
