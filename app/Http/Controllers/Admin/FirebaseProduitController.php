<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class FirebaseProduitController extends Controller
{
    public function __construct(protected FirebaseService $firebase) {}

    public function index(Request $request)
    {
        $products = $this->firebase->getAllProducts();

        if ($request->filled('search')) {
            $s        = mb_strtolower($request->search);
            $products = array_filter($products, fn($p) =>
                str_contains(mb_strtolower($p['nom'] ?? $p['name'] ?? ''), $s)
            );
        }

        return view('admin.firebase.produits.index', [
            'products' => array_values($products),
        ]);
    }

    public function toggle(string $id)
    {
        $this->firebase->toggleProductStatus($id);
        return back()->with('success', 'Statut du produit mis à jour dans Firestore.');
    }

    /** Sync Google Sheet → MySQL + Firestore */
    public function sync()
    {
        Artisan::call('products:sync');
        $output = Artisan::output();
        return redirect()->route('admin.produits.index')
                         ->with('success', 'Synchronisation effectuée : ' . trim($output));
    }
}
