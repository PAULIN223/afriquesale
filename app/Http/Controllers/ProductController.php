<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::actif()->with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        if ($request->filled('prix_min')) {
            $query->where('prix', '>=', $request->prix_min);
        }
        if ($request->filled('prix_max')) {
            $query->where('prix', '<=', $request->prix_max);
        }

        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'price_asc'  => $query->orderBy('prix', 'asc'),
            'price_desc' => $query->orderBy('prix', 'desc'),
            'name_asc'   => $query->orderBy('nom', 'asc'),
            default      => $query->latest(),
        };

        $products   = $query->paginate(20)->withQueryString();
        $categories = Cache::remember('categories_with_products', 3600, fn() =>
            Category::where('actif', true)->has('products')->orderBy('nom')->get()
        );

        return view('products.index', compact('products', 'categories'));
    }

    public function show(int $id)
    {
        $product = Product::actif()->with('category')->findOrFail($id);
        $related = Product::actif()
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->whereNotNull('image_url')
            ->take(8)->get();

        $inWishlist = false;
        if (auth()->check()) {
            $inWishlist = auth()->user()->wishlists()->where('product_id', $product->id)->exists();
        }

        return view('products.show', compact('product', 'related', 'inWishlist'));
    }

    public function venteFlash()
    {
        $products = Product::actif()
            ->whereNotNull('prix_original')
            ->where('prix_original', '>', 0)
            ->whereColumn('prix_original', '>', 'prix')
            ->with('category')
            ->orderByRaw('(prix_original - prix) / prix_original DESC')
            ->paginate(24);

        return view('vente-flash.index', compact('products'));
    }
}
