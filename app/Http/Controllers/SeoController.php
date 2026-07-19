<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /cart',
            'Disallow: /checkout',
            'Disallow: /account',
            'Disallow: /wishlist',
            'Disallow: /messages',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /verify-email',
            'Disallow: /image-proxy',
            '',
            'Sitemap: ' . url('/sitemap.xml'),
        ];

        return response(implode("\n", $lines), 200)
            ->header('Content-Type', 'text/plain');
    }

    public function sitemap(): Response
    {
        $xml = Cache::remember('sitemap_xml', 3600, function () {
            $urls = [];

            $urls[] = ['loc' => url('/'), 'changefreq' => 'daily', 'priority' => '1.0'];
            $urls[] = ['loc' => url('/products'), 'changefreq' => 'daily', 'priority' => '0.9'];
            $urls[] = ['loc' => url('/vente-flash'), 'changefreq' => 'daily', 'priority' => '0.8'];

            Category::where('actif', true)->has('products')->orderBy('nom')->get()->each(function ($category) use (&$urls) {
                $urls[] = [
                    'loc'        => url('/products?category=' . $category->slug),
                    'changefreq' => 'weekly',
                    'priority'   => '0.6',
                ];
            });

            Product::actif()->orderByDesc('updated_at')->chunk(500, function ($products) use (&$urls) {
                foreach ($products as $product) {
                    $urls[] = [
                        'loc'        => url('/products/' . $product->id),
                        'lastmod'    => $product->updated_at?->toAtomString(),
                        'changefreq' => 'weekly',
                        'priority'   => '0.7',
                    ];
                }
            });

            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

            foreach ($urls as $url) {
                $xml .= '<url>' . "\n";
                $xml .= '<loc>' . htmlspecialchars($url['loc'], ENT_XML1) . '</loc>' . "\n";
                if (! empty($url['lastmod'])) {
                    $xml .= '<lastmod>' . $url['lastmod'] . '</lastmod>' . "\n";
                }
                $xml .= '<changefreq>' . $url['changefreq'] . '</changefreq>' . "\n";
                $xml .= '<priority>' . $url['priority'] . '</priority>' . "\n";
                $xml .= '</url>' . "\n";
            }

            $xml .= '</urlset>';

            return $xml;
        });

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
