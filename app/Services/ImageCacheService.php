<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ImageCacheService
{
    public function isCached(string $url): bool
    {
        return Storage::disk('local')->exists($this->cacheKey($url));
    }

    public function get(string $url): ?array
    {
        $cacheKey = $this->cacheKey($url);

        if (!Storage::disk('local')->exists($cacheKey)) {
            return null;
        }

        $metaKey = $cacheKey . '.mime';

        return [
            Storage::disk('local')->get($cacheKey),
            Storage::disk('local')->exists($metaKey) ? Storage::disk('local')->get($metaKey) : 'image/jpeg',
        ];
    }

    /**
     * Télécharge et met en cache l'image si elle n'y est pas déjà. Ne relève jamais
     * d'exception : un échec de téléchargement ne doit jamais interrompre l'appelant
     * (sync produits, requête HTTP du proxy, etc.).
     */
    public function cache(string $url): ?array
    {
        if ($cached = $this->get($url)) {
            return $cached;
        }

        $result = $this->curlFetch($url);
        if (!$result) {
            return null;
        }

        [$body, $mimeType] = $result;
        $cacheKey = $this->cacheKey($url);

        Storage::disk('local')->put($cacheKey, $body);
        Storage::disk('local')->put($cacheKey . '.mime', $mimeType);

        return $result;
    }

    protected function cacheKey(string $url): string
    {
        return 'image_cache/' . md5($url);
    }

    protected function curlFetch(string $url): ?array
    {
        if (!function_exists('curl_init')) {
            return null;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HTTPHEADER     => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept: image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                'Accept-Language: fr-FR,fr;q=0.9,en;q=0.8',
                'Referer: https://detail.1688.com/',
                'Cache-Control: no-cache',
            ],
        ]);

        $body     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $ctHeader = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if (!$body || $httpCode < 200 || $httpCode >= 400 || strlen($body) < 100) {
            return null;
        }

        $mimeType = 'image/jpeg';
        if ($ctHeader) {
            $ct = strtolower(trim(explode(';', $ctHeader)[0]));
            if (str_starts_with($ct, 'image/')) {
                $mimeType = $ct;
            }
        }

        if ($mimeType === 'image/jpeg') {
            $detected = $this->detectMimeFromBytes($body);
            if ($detected) {
                $mimeType = $detected;
            }
        }

        if (!str_starts_with($mimeType, 'image/')) {
            return null;
        }

        return [$body, $mimeType];
    }

    protected function detectMimeFromBytes(string $data): ?string
    {
        if (strlen($data) < 4) return null;

        $bytes = substr($data, 0, 4);

        if (substr($bytes, 0, 3) === "\xFF\xD8\xFF") return 'image/jpeg';
        if (substr($bytes, 0, 4) === "\x89PNG")      return 'image/png';
        if (substr($bytes, 0, 4) === 'GIF8')          return 'image/gif';
        if (substr($bytes, 0, 4) === 'RIFF')          return 'image/webp';

        return null;
    }
}
