<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'shop_name'       => Setting::get('shop_name', 'AfricSales'),
            'shop_email'      => Setting::get('shop_email', 'contact@africsales.com'),
            'shop_phone'      => Setting::get('shop_phone', ''),
            'devise'          => Setting::get('devise', 'FCFA'),
            'frais_livraison' => Setting::get('frais_livraison', '2000'),
            'shop_address'    => Setting::get('shop_address', ''),
        ];
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $keys = ['shop_name', 'shop_email', 'shop_phone', 'devise', 'frais_livraison', 'shop_address'];
        foreach ($keys as $key) {
            if ($request->has($key)) {
                Setting::set($key, $request->input($key));
            }
        }
        return back()->with('success', 'Paramètres enregistrés avec succès.');
    }
}
