<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $query->where('nom', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $products   = $query->latest()->paginate(20)->withQueryString();
        $categories = Category::all();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function toggle(int $id)
    {
        $product = Product::findOrFail($id);
        $product->update(['actif' => !$product->actif]);

        return back()->with('success', 'Statut du produit mis à jour.');
    }

    public function sync()
    {
        Artisan::call('products:sync');
        $output = Artisan::output();
        return redirect('/admin/products')->with('success', 'Synchronisation effectuée : ' . trim($output));
    }

    public function destroy(int $id)
    {
        Product::findOrFail($id)->delete();
        return back()->with('success', 'Produit supprimé.');
    }
}
