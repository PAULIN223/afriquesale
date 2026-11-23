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
        $query = Product::actif()->with(['category', 'variants']);

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

        // Un même produit est parfois importé sur plusieurs lignes du CSV (une par couleur/modèle).
        // On ne garde qu'un seul représentant par code article (celui avec une image, sinon le plus ancien).
        $representativeIds = (clone $query)
            ->selectRaw("SUBSTRING_INDEX(GROUP_CONCAT(id ORDER BY (image_url IS NULL) ASC, id ASC), ',', 1) as id")
            ->groupByRaw("COALESCE(NULLIF(product_code, ''), CONCAT('id#', id))")
            ->pluck('id');
        $query->whereIn('id', $representativeIds);

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

        $selectedCategory = $request->filled('category')
            ? $categories->firstWhere('slug', $request->category)
            : null;

        if ($request->filled('search')) {
            $metaTitle       = 'Résultats pour "' . $request->search . '" — AfricSales';
            $metaDescription = 'Découvrez les produits correspondant à "' . $request->search . '" sur AfricSales.';
        } elseif ($selectedCategory) {
            $metaTitle       = $selectedCategory->nom . ' — AfricSales';
            $metaDescription = 'Découvrez notre sélection ' . $selectedCategory->nom . ' au meilleur prix, avec livraison rapide partout en Afrique.';
        } else {
            $metaTitle       = 'Tous nos produits — AfricSales';
            $metaDescription = 'Parcourez tous les produits disponibles sur AfricSales : mode, électronique, maison et bien plus, au meilleur prix.';
        }

        $canonicalUrl = url('/products') . ($selectedCategory ? '?category=' . $selectedCategory->slug : '');

        return view('products.index', compact('products', 'categories', 'metaTitle', 'metaDescription', 'canonicalUrl'));
    }

    public function show(int $id)
    {
        $product = Product::actif()->with('category')->findOrFail($id);

        $variants = filled($product->product_code)
            ? Product::actif()
                ->inVariantGroup($product)
                ->orderByRaw('color IS NULL')
                ->orderBy('color')
                ->get()
            : collect();

        $related = Product::actif()
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->whereNotNull('image_url')
            ->with(['category', 'variants'])
            ->take(8)->get();

        $inWishlist = false;
        if (auth()->check()) {
            $inWishlist = auth()->user()->wishlists()->where('product_id', $product->id)->exists();
        }

        return view('products.show', compact('product', 'variants', 'related', 'inWishlist'));
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
