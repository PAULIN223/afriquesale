<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function profile()
    {
        $user = auth()->user();
        $countries = config('phone_countries');
        $countryCode = collect($countries)->search(fn (array $country) => $country['name'] === $user->pays) ?: 'CI';
        if (isset($countries[old('country_code')])) {
            $countryCode = old('country_code');
        }
        $phoneLocal = preg_replace('/\D+/', '', (string) $user->phone);
        if (str_starts_with($phoneLocal, '00')) {
            $phoneLocal = substr($phoneLocal, 2);
        }
        $phoneLocal = preg_replace('/^' . preg_quote($countries[$countryCode]['dial'], '/') . '/', '', $phoneLocal);
        $phoneLocal = ltrim($phoneLocal, '0');

        return view('account.profile', compact('user', 'countries', 'countryCode', 'phoneLocal'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'     => 'required|string|max:100',
            'country_code' => 'required|in:' . implode(',', array_keys(config('phone_countries'))),
            'phone_local'  => 'nullable|string|max:20',
            'adresse'  => 'nullable|string|max:255',
            'ville'    => 'nullable|string|max:100',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $countries = config('phone_countries');
        $localPhone = ltrim(preg_replace('/\D+/', '', (string) $request->phone_local), '0');
        $user->fill([
            'name'    => $request->name,
            'phone'   => $localPhone !== '' ? '+' . $countries[$request->country_code]['dial'] . $localPhone : null,
            'adresse' => $request->adresse,
            'ville'   => $request->ville,
            'pays'    => $countries[$request->country_code]['name'],
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
