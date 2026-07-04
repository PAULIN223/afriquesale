<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index()
    {
        $items = auth()->user()->wishlists()->with('product')->latest()->get();
        return view('wishlist.index', compact('items'));
    }

    public function toggle(int $productId)
    {
        $product = Product::findOrFail($productId);

        $existing = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            $message = "« {$product->nom} » retiré de vos favoris.";
        } else {
            Wishlist::create(['user_id' => auth()->id(), 'product_id' => $productId]);
            $message = "« {$product->nom} » ajouté à vos favoris !";
        }

        if (request()->expectsJson()) {
            return response()->json(['inWishlist' => !$existing]);
        }

        return back()->with('success', $message);
    }
}
