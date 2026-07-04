<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        $cartItems = auth()->user()->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return redirect('/cart')->with('error', 'Votre panier est vide.');
        }

        $subtotal       = $cartItems->sum('subtotal');
        $fraisLivraison = (float) Setting::get('frais_livraison', 2000);
        $total          = $subtotal + $fraisLivraison;

        return view('checkout.index', compact('cartItems', 'subtotal', 'fraisLivraison', 'total'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'          => 'required|string|max:100',
            'prenom'       => 'required|string|max:100',
            'email'        => 'required|email',
            'phone'        => 'required|string|max:20',
            'adresse'      => 'required|string|max:255',
            'ville'        => 'required|string|max:100',
            'pays'         => 'required|string|max:100',
        ]);

        $cartItems = auth()->user()->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return redirect('/cart')->with('error', 'Votre panier est vide.');
        }

        $subtotal       = $cartItems->sum('subtotal');
        $fraisLivraison = (float) Setting::get('frais_livraison', 2000);
        $total          = $subtotal + $fraisLivraison;

        try {
            DB::beginTransaction();

            $order = Order::create([
                'user_id'          => auth()->id(),
                'statut'           => 'en_attente',
                'total'            => $total,
                'frais_livraison'  => $fraisLivraison,
                'numero_commande'  => Order::generateNumero(),
                'notes'            => $request->notes,
                'adresse_livraison' => [
                    'nom'     => $request->nom . ' ' . $request->prenom,
                    'email'   => $request->email,
                    'phone'   => $request->phone,
                    'adresse' => $request->adresse,
                    'ville'   => $request->ville,
                    'pays'    => $request->pays,
                ],
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id'      => $order->id,
                    'product_id'    => $item->product_id,
                    'quantite'      => $item->quantite,
                    'prix_unitaire' => $item->product->prix,
                ]);

                $item->product->decrement('stock', $item->quantite);
            }

            auth()->user()->cartItems()->delete();

            DB::commit();

            return redirect('/checkout/success/' . $order->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Une erreur est survenue. Veuillez réessayer.');
        }
    }

    public function success(int $orderId)
    {
        $order = Order::with('items.product')
            ->where('user_id', auth()->id())
            ->findOrFail($orderId);

        return view('checkout.success', compact('order'));
    }
}
