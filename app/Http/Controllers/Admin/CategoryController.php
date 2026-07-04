<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->latest()->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate(['nom' => 'required|string|max:100', 'icone' => 'nullable|string|max:50']);
        Category::create([
            'nom'   => $request->nom,
            'slug'  => Str::slug($request->nom),
            'icone' => $request->icone,
        ]);
        return back()->with('success', 'Catégorie créée avec succès.');
    }

    public function update(Request $request, int $id)
    {
        $request->validate(['nom' => 'required|string|max:100']);
        $category = Category::findOrFail($id);
        $category->update([
            'nom'   => $request->nom,
            'slug'  => Str::slug($request->nom),
            'icone' => $request->icone,
            'actif' => $request->boolean('actif', true),
        ]);
        return back()->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(int $id)
    {
        Category::findOrFail($id)->delete();
        return back()->with('success', 'Catégorie supprimée.');
    }
}
