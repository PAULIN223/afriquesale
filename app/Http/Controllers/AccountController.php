<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function profile()
    {
        return view('account.profile', ['user' => auth()->user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'     => 'required|string|max:100',
            'phone'    => 'nullable|string|max:20',
            'adresse'  => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->fill([
            'name'    => $request->name,
            'phone'   => $request->phone,
            'adresse' => $request->adresse,
        ]);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'Profil mis à jour avec succès.');
    }

    public function orders()
    {
        $orders = Order::with('items')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('account.orders', compact('orders'));
    }

    public function showOrder(int $id)
    {
        $order = Order::with('items.product')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return view('account.order-detail', compact('order'));
    }

    public function cancelOrder(int $id)
    {
        $order = Order::where('user_id', auth()->id())
            ->where('statut', 'en_attente')
            ->findOrFail($id);

        $order->update(['statut' => 'annulee']);

        return redirect('/account/orders')->with('success', 'Commande #' . $order->numero_commande . ' annulée avec succès.');
    }
}
