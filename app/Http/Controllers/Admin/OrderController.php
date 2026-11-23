<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user');

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_commande', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")
                                                    ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        $orders = $query->latest()->paginate(20)->withQueryString();
        return view('admin.orders.index', compact('orders'));
    }

    public function show(int $id)
    {
        $order = Order::with(['user', 'items.product'])->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    public function preuve(int $id)
    {
        $order = Order::findOrFail($id);
        abort_unless(filled($order->preuve_paiement), 404);

        $path = ltrim(str_replace('\\', '/', $order->preuve_paiement), '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }
        abort_if(collect(explode('/', $path))->contains('..'), 404);

        $disk = Storage::disk('public');
        if ($disk->exists($path)) {
            return response()->file($disk->path($path));
        }

        // Older deployments may have written files directly under public/storage.
        $legacyPath = public_path('storage/' . $path);
        abort_unless(is_file($legacyPath), 404);

        return response()->file($legacyPath);
    }

    public function updateStatut(Request $request, int $id)
    {
        $request->validate(['statut' => 'required|in:en_attente,confirmee,expediee,livree,annulee']);

        $order = Order::findOrFail($id);
        $order->update(['statut' => $request->statut]);

        $label = Order::STATUTS[$request->statut]['label'] ?? $request->statut;
        Message::create([
            'user_id' => $order->user_id,
            'sender'  => 'admin',
            'body'    => "Votre commande #{$order->numero_commande} est maintenant : {$label}.",
            'is_read' => false,
        ]);

        return back()->with('success', 'Statut de la commande mis à jour et client notifié.');
    }

    public function exportCsv()
    {
        $orders = Order::with('user')->latest()->get();
        $headers = ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="commandes.csv"'];
        $callback = function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($handle, ['N° Commande', 'Client', 'Email', 'Total (FCFA)', 'Statut', 'Date'], ';');
            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->numero_commande,
                    $order->user->name,
                    $order->user->email,
                    $order->total,
                    $order->statut_label,
                    $order->created_at->format('d/m/Y H:i'),
                ], ';');
            }
            fclose($handle);
        };
        return Response::stream($callback, 200, $headers);
    }
}
