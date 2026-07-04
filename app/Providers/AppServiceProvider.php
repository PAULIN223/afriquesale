<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Message;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\FirebaseService;
use App\Services\NotificationService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FirebaseService::class);
        $this->app->singleton(NotificationService::class);
    }

    public function boot(): void
    {
        Paginator::useTailwind();

        // Share data with all views
        View::composer('*', function ($view) {
            // Mémorisé pour la durée de la requête : le composer est appelé pour
            // chaque vue/partial rendu (ex: une carte produit par produit).
            static $shared = null;

            if ($shared === null) {
                $navCategories = Cache::remember('nav_categories', 3600, function () {
                    try {
                        return Category::where('actif', true)->has('products')->orderBy('nom')->get();
                    } catch (\Exception $e) {
                        return collect();
                    }
                });

                $cartCount           = 0;
                $wishlistProductIds  = [];
                $unreadMessagesCount = 0;

                if (auth()->check()) {
                    try {
                        $cartCount          = auth()->user()->cartItems()->sum('quantite');
                        $wishlistProductIds = auth()->user()->wishlists()->pluck('product_id')->toArray();
                        $unreadMessagesCount = auth()->user()->messages()->where('sender', 'admin')->where('is_read', false)->count();
                    } catch (\Exception $e) {
                        // Tables might not exist yet during migration
                    }
                }

                $wishlistCount = count($wishlistProductIds);

                $shared = compact('navCategories', 'cartCount', 'wishlistCount', 'wishlistProductIds', 'unreadMessagesCount');
            }

            $view->with($shared);
        });

        // Badges de notification admin (Boutique Web + App Android), calculés une seule
        // fois par requête et injectés uniquement dans le layout admin (pas les pages client).
        View::composer('layouts.admin', function ($view) {
            $pendingOrdersWeb  = Order::where('statut', 'en_attente')->count();
            $newClientsWeb     = User::where('role', 'client')
                ->where('created_at', '>', Setting::get('clients_web_last_seen', now()->subYears(10)))
                ->count();
            $unreadMessagesWeb = Message::where('sender', 'client')->where('is_read', false)->count();

            // Les compteurs Android nécessitent des appels réseau Firestore : mis en cache 30s
            // pour ne pas les répéter à chaque navigation admin (wire:navigate = requêtes fréquentes).
            $androidBadges = Cache::remember('admin_android_badges', 30, function () {
                try {
                    $firebase = app(FirebaseService::class);

                    $orders  = $firebase->getAllOrders();
                    $pending = count(array_filter($orders, fn($o) => ($o['statut'] ?? $o['status'] ?? '') === 'en_attente'));

                    $users      = $firebase->getAllUsers();
                    $lastSeen   = strtotime(Setting::get('clients_android_last_seen', now()->subYears(10)->toIso8601String()));
                    $newClients = count(array_filter($users, fn($u) => strtotime($u['createdAt'] ?? 'now') > $lastSeen));

                    $messages = $firebase->getAllMessages();
                    $unread   = count(array_filter($messages, fn($m) => ($m['isFromUser'] ?? false) && !($m['read'] ?? false)));

                    return ['pending' => $pending, 'newClients' => $newClients, 'unread' => $unread];
                } catch (\Exception $e) {
                    return ['pending' => 0, 'newClients' => 0, 'unread' => 0];
                }
            });

            $view->with([
                'pendingOrdersWeb'      => $pendingOrdersWeb,
                'newClientsWeb'         => $newClientsWeb,
                'unreadMessagesWeb'     => $unreadMessagesWeb,
                'pendingOrdersAndroid'  => $androidBadges['pending'],
                'newClientsAndroid'     => $androidBadges['newClients'],
                'unreadMessagesAndroid' => $androidBadges['unread'],
            ]);
        });
    }
}
