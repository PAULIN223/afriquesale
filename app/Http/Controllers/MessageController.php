<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        return view('messages.index');
    }

    public function send(Request $request)
    {
        $request->validate([
            'sujet'   => 'required|string|max:100',
            'message' => 'required|string|max:2000',
        ]);

        return back()->with('success', 'Votre message a été envoyé. Nous vous répondrons dans les plus brefs délais.');
    }
}
