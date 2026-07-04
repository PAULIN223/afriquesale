<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Response;

class CustomerController extends Controller
{
    public function show(int $id)
    {
        $customer = User::where('role', 'client')->with('orders')->findOrFail($id);
        return view('admin.customers.show', compact('customer'));
    }

    public function exportCsv()
    {
        $customers = User::where('role', 'client')->withCount('orders')->latest()->get();
        $headers = ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="clients.csv"'];
        $callback = function () use ($customers) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Nom', 'Email', 'Téléphone', 'Statut', 'Commandes', 'Inscrit le'], ';');
            foreach ($customers as $c) {
                fputcsv($handle, [
                    $c->name, $c->email, $c->phone ?? '-',
                    $c->statut, $c->orders_count,
                    $c->created_at->format('d/m/Y'),
                ], ';');
            }
            fclose($handle);
        };
        return Response::stream($callback, 200, $headers);
    }
}
