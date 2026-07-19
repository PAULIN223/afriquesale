<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Message;
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

        $user = auth()->user();
        $hasSavedAddress = filled($user->adresse) && filled($user->ville) && filled($user->pays) && filled($user->phone);

        return view('checkout.index', compact('cartItems', 'subtotal', 'fraisLivraison', 'total', 'hasSavedAddress'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'          => 'required|string|max:150',
            'email'        => 'required|email',
            'phone'        => 'required|string|max:20',
            'adresse'      => 'required|string|max:255',
            'ville'        => 'required|string|max:100',
            'pays'         => 'required|string|max:100',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $cartItems = auth()->user()->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return redirect('/cart')->with('error', 'Votre panier est vide.');
        }

        auth()->user()->update([
            'phone'   => $request->phone,
            'adresse' => $request->adresse,
            'ville'   => $request->ville,
            'pays'    => $request->pays,
        ]);

        $request->session()->put('checkout.adresse_livraison', [
            'nom'     => $request->nom,
            'email'   => $request->email,
            'phone'   => $request->phone,
            'adresse' => $request->adresse,
            'ville'   => $request->ville,
            'pays'    => $request->pays,
        ]);
        $request->session()->put('checkout.notes', $request->notes);

        return redirect()->route('checkout.preuve');
    }

    public function preuve()
    {
        $adresse = session('checkout.adresse_livraison');
        $cartItems = auth()->user()->cartItems()->with('product')->get();

        if (! $adresse || $cartItems->isEmpty()) {
            return redirect('/checkout')->with('error', 'Veuillez d\'abord renseigner votre adresse de livraison.');
        }

        $subtotal       = $cartItems->sum('subtotal');
        $fraisLivraison = (float) Setting::get('frais_livraison', 2000);
        $total          = $subtotal + $fraisLivraison;

        return view('checkout.preuve', compact('cartItems', 'subtotal', 'fraisLivraison', 'total', 'adresse'));
    }

    public function confirmer(Request $request)
    {
        $request->validate([
            'preuve' => 'required|image|max:5120',
        ], [
            'preuve.required' => 'Merci de joindre une photo du dépôt avant de continuer.',
            'preuve.image'    => 'Le fichier envoyé doit être une image.',
            'preuve.max'      => 'L\'image ne doit pas dépasser 5 Mo.',
        ]);

        $adresse = session('checkout.adresse_livraison');
        $cartItems = auth()->user()->cartItems()->with('product')->get();

        if (! $adresse || $cartItems->isEmpty()) {
            return redirect('/checkout')->with('error', 'Votre session de commande a expiré, veuillez recommencer.');
        }

        $subtotal       = $cartItems->sum('subtotal');
        $fraisLivraison = (float) Setting::get('frais_livraison', 2000);
        $total          = $subtotal + $fraisLivraison;

        try {
            DB::beginTransaction();

            $preuvePath = $request->file('preuve')->store('preuves-paiement', 'public');

            $order = Order::create([
                'user_id'           => auth()->id(),
                'statut'            => 'en_attente',
                'total'             => $total,
                'frais_livraison'   => $fraisLivraison,
                'numero_commande'   => Order::generateNumero(),
                'notes'             => session('checkout.notes'),
                'adresse_livraison' => $adresse,
                'preuve_paiement'   => $preuvePath,
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

            Message::create([
                'user_id'    => $order->user_id,
                'sender'     => 'client',
                'body'       => "📦 Nouvelle commande passée ! Commande #{$order->numero_commande} — Montant : "
                                 . number_format($total, 0, ',', ' ') . ' FCFA' . "\n🧾 Preuve de paiement jointe ci-dessous.",
                'image_path' => $preuvePath,
                'is_read'    => false,
            ]);

            DB::commit();

            $request->session()->forget(['checkout.adresse_livraison', 'checkout.notes']);

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
