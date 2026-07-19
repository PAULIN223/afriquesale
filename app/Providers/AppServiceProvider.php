<?php

namespace App\Providers;

use App\Models\Category;
use App\Services\FirebaseService;
use App\Services\NotificationService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
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

        // Force les URLs générées (canonical, sitemap, og:url...) en HTTPS en production,
        // pour éviter tout contenu dupliqué http/https aux yeux de Google.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

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

                $wishlistProductIds = [];

                if (auth()->check()) {
                    try {
                        $wishlistProductIds = auth()->user()->wishlists()->pluck('product_id')->toArray();
                    } catch (\Exception $e) {
                        // Tables might not exist yet during migration
                    }
                }

                $wishlistCount = count($wishlistProductIds);

                $shared = compact('navCategories', 'wishlistCount', 'wishlistProductIds');
            }

            $view->with($shared);
        });
    }
}
