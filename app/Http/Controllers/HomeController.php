<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $banners  = Banner::where('actif', true)->inRandomOrder()->get();
        // Priorité aux produits avec image, puis sans image — ordre aléatoire à chaque visite
        $featured    = Product::actif()->whereNotNull('image_url')->inRandomOrder()->take(8)->get();
        if ($featured->count() < 8) {
            $featured = Product::actif()->inRandomOrder()->take(8)->get();
        }
        $newArrivals = Product::actif()->whereNotNull('image_url')
            ->whereNotIn('id', $featured->pluck('id'))
            ->inRandomOrder()->take(8)->get();
        $categories  = Category::where('actif', true)->withCount('products')->get();

        // If no banners, use featured product images
        if ($banners->isEmpty()) {
            $banners = $featured->take(5)->map(function ($p) {
                return (object)[
                    'image_url' => $p->image_url,
                    'titre'     => $p->nom,
                    'sous_titre' => number_format($p->prix, 0, ',', ' ') . ' FCFA',
                    'lien'      => '/products/' . $p->id,
                ];
            });
        }

        return view('home.index', compact('banners', 'featured', 'newArrivals', 'categories'));
    }
}
