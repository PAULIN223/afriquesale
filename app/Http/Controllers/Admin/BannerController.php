<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('ordre')->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image_url' => 'required|url',
            'titre'     => 'nullable|string|max:200',
            'sous_titre'=> 'nullable|string|max:300',
            'lien'      => 'nullable|string|max:500',
            'ordre'     => 'nullable|integer',
        ]);
        Banner::create($request->only('image_url', 'titre', 'sous_titre', 'lien', 'ordre') + ['actif' => true]);
        return back()->with('success', 'Bannière ajoutée.');
    }

    public function update(Request $request, int $id)
    {
        $banner = Banner::findOrFail($id);
        $banner->update($request->only('image_url', 'titre', 'sous_titre', 'lien', 'ordre') + ['actif' => $request->boolean('actif')]);
        return back()->with('success', 'Bannière mise à jour.');
    }

    public function destroy(int $id)
    {
        Banner::findOrFail($id)->delete();
        return back()->with('success', 'Bannière supprimée.');
    }
}
