<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class ParametreController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('valeur', 'cle');
        return view('admin.firebase.parametres.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'nom_boutique'   => 'required|string|max:100',
            'email'          => 'required|email',
            'devise'         => 'required|string|max:10',
            'frais_livraison'=> 'required|numeric|min:0',
        ]);

        $fields = $request->only('nom_boutique', 'email', 'devise', 'frais_livraison');
        foreach ($fields as $cle => $valeur) {
            Setting::updateOrCreate(['cle' => $cle], ['valeur' => $valeur]);
        }

        return back()->with('success', 'Paramètres mis à jour.');
    }
}
