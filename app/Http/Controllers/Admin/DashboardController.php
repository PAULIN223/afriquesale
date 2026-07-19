<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(protected FirebaseService $firebase) {}

    public function index()
    {
        $totalRevenue    = Order::whereIn('statut', ['confirmee', 'expediee', 'livree'])->sum('total');
        $ordersToday     = Order::whereDate('created_at', today())->count();
        $newClientsToday = User::where('role', 'client')->whereDate('created_at', today())->count();
        $totalOrders     = Order::count();
        $totalClients    = User::where('role', 'client')->count();

        // Orders chart – last 30 days
        $ordersChart = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(total) as revenue')
        )
        ->where('created_at', '>=', now()->subDays(29))
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        // Clients chart – last 30 days
        $clientsChart = User::where('role', 'client')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subDays(29))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top products
        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.id', 'products.nom', 'products.prix', 'products.image_url',
                DB::raw('SUM(order_items.quantite) as total_vendu'),
                DB::raw('SUM(order_items.quantite * order_items.prix_unitaire) as chiffre_affaires'))
            ->groupBy('products.id', 'products.nom', 'products.prix', 'products.image_url')
            ->orderByDesc('total_vendu')
            ->limit(5)
            ->get();

        // Recent orders
        $recentOrders = Order::with('user')->latest()->take(8)->get();

        // Graphiques App Android (Firestore) — mis en cache 5 min pour ne pas
        // ralentir chaque chargement du tableau de bord avec des appels réseau.
        $android = Cache::remember('dashboard_android_charts', 300, function () {
            $since = now()->subDays(29)->startOfDay();

            $ordersByDate = [];
            foreach ($this->firebase->getAllOrders() as $order) {
                $dt = $order['createdAt'] ?? null;
                if (!$dt || strtotime($dt) < $since->timestamp) continue;
                $day = date('Y-m-d', strtotime($dt));
                $ordersByDate[$day] = ($ordersByDate[$day] ?? 0) + 1;
            }
            ksort($ordersByDate);

            $clientsByDate = [];
            foreach ($this->firebase->getAllUsers() as $user) {
                $dt = $user['createdAt'] ?? null;
                if (!$dt || strtotime($dt) < $since->timestamp) continue;
                $day = date('Y-m-d', strtotime($dt));
                $clientsByDate[$day] = ($clientsByDate[$day] ?? 0) + 1;
            }
            ksort($clientsByDate);

            return [
                'ordersDates'   => array_keys($ordersByDate),
                'ordersCounts'  => array_values($ordersByDate),
                'clientsDates'  => array_keys($clientsByDate),
                'clientsCounts' => array_values($clientsByDate),
            ];
        });

        $ordersChartAndroid       = $android['ordersCounts'];
        $ordersChartAndroidDates  = $android['ordersDates'];
        $clientsChartAndroid      = $android['clientsCounts'];
        $clientsChartAndroidDates = $android['clientsDates'];

        return view('admin.dashboard', compact(
            'totalRevenue', 'ordersToday', 'newClientsToday',
            'totalOrders', 'totalClients',
            'ordersChart', 'clientsChart', 'topProducts', 'recentOrders',
            'ordersChartAndroid', 'ordersChartAndroidDates',
            'clientsChartAndroid', 'clientsChartAndroidDates'
        ));
    }
}
