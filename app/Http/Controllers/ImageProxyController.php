<?php

namespace App\Http\Controllers;

use App\Services\ImageCacheService;
use Illuminate\Http\Request;

class ImageProxyController extends Controller
{
    public function __construct(protected ImageCacheService $imageCache) {}

    public function show(Request $request)
    {
        $url = $request->query('url');

        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            return $this->placeholder();
        }

        $url    = trim($url);
        $isHit  = $this->imageCache->isCached($url);
        $result = $this->imageCache->cache($url);

        if (!$result) {
            return $this->placeholder();
        }

        [$body, $mimeType] = $result;

        return response($body, 200)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=86400')
            ->header('X-Cache', $isHit ? 'HIT' : 'MISS');
    }

    private function placeholder()
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">'
            . '<rect width="400" height="400" fill="#f9fafb"/>'
            . '<rect x="150" y="140" width="100" height="80" rx="8" fill="#e5e7eb"/>'
            . '<circle cx="200" cy="125" r="20" fill="#e5e7eb"/>'
            . '<text x="200" y="260" text-anchor="middle" fill="#9ca3af" font-size="12" font-family="sans-serif">Image indisponible</text>'
            . '</svg>';

        return response($svg, 200)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
