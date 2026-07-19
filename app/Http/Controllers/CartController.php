<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cartItems    = auth()->user()->cartItems()->with('product')->get();
        $subtotal     = $cartItems->sum('subtotal');
        $fraisLivraison = (float) Setting::get('frais_livraison', 2000);
        $total        = $subtotal + $fraisLivraison;

        return view('cart.index', compact('cartItems', 'subtotal', 'fraisLivraison', 'total'));
    }

    public function add(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id', 'quantite' => 'integer|min:1']);
        $product = Product::findOrFail($request->product_id);

        if (!auth()->check()) {
            return redirect('/login')->with('intended', '/cart');
        }

        if (!$product->is_purchasable) {
            return back()->with('error', 'Ce produit est en rupture de stock.');
        }

        $cartItem = CartItem::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            $newQty = $cartItem->quantite + ($request->quantite ?? 1);
            $cartItem->update(['quantite' => min($newQty, $product->stock)]);
        } else {
            CartItem::create([
                'user_id'    => auth()->id(),
                'product_id' => $product->id,
                'quantite'   => min($request->quantite ?? 1, $product->stock),
            ]);
        }

        return back()->with('success', "« {$product->nom} » ajouté au panier !");
    }

    public function update(Request $request)
    {
        $request->validate(['item_id' => 'required|exists:cart_items,id', 'quantite' => 'required|integer|min:1']);

        $item = CartItem::where('id', $request->item_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $item->update(['quantite' => min($request->quantite, $item->product->stock)]);

        return back()->with('success', 'Panier mis à jour.');
    }

    public function remove(Request $request)
    {
        CartItem::where('id', $request->item_id)
            ->where('user_id', auth()->id())
            ->delete();

        return back()->with('success', 'Produit retiré du panier.');
    }

    public function clear()
    {
        auth()->user()->cartItems()->delete();
        return back()->with('success', 'Panier vidé.');
    }
}
