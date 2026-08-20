<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Services\FirebaseService;
use App\Services\ImageCacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncProducts extends Command
{
    protected $signature   = 'products:sync
        {--firebase : Pousser aussi les produits vers Firestore}
        {--cache-images : Télécharger les images pendant la synchronisation}';
    protected $description = 'Synchronise les produits depuis Google Sheets';

    const CSV_URL = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vQZX3dHS4bosZCaR9inIxqlECkfYHa1NMGY7dvGCFvgMGfJiirHy44eZf_ezB1VDalFFiLlD7Zsv-nL/pub?output=csv';

    // Positions connues du CSV Google Sheets AfricSales
    const COL = [
        'code'          => 0,
        'nom'           => 1,
        'categorie'     => 2,
        'sub_categorie' => 3,
        'prix'          => 4,
        'moq'           => 5,
        'prix_original' => 6,
        'description'   => 7,
        'marque'        => 8,
        'taille'        => 9,
        'couleur'       => 10,
        'modele'        => 11,
        'stock'         => 12,
        'availability'  => 13,
        'image'         => 14,
    ];

    public function handle(FirebaseService $firebase, ImageCacheService $imageCache): int
    {
        $this->info('Téléchargement du fichier CSV...');

        try {
            $response = Http::timeout(120)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept'     => 'text/csv,text/plain,*/*',
                ])
                ->get(self::CSV_URL);

            if (!$response->successful()) {
                $this->error('Impossible de télécharger le CSV (HTTP ' . $response->status() . ')');
                return self::FAILURE;
            }

            $csvContent = $response->body();

        } catch (\Exception $e) {
            $this->error('Erreur réseau : ' . $e->getMessage());
            return self::FAILURE;
        }

        // Normaliser les fins de ligne et supprimer BOM éventuel
        $csvContent = ltrim($csvContent, "\xEF\xBB\xBF");
        $csvContent = str_replace("\r\n", "\n", $csvContent);
        $csvContent = str_replace("\r", "\n", $csvContent);
        $lines      = array_values(array_filter(explode("\n", $csvContent)));

        if (count($lines) < 2) {
            $this->error('CSV vide ou invalide.');
            return self::FAILURE;
        }

        // Lire les headers pour vérifier les positions
        $rawHeaders = str_getcsv($lines[0]);
        $headers    = array_map(fn($h) => mb_strtolower(trim($h)), $rawHeaders);
        $this->info('Colonnes détectées (' . count($headers) . ') : ' . implode(', ', array_slice($headers, 0, 16)));

        // Détecter dynamiquement les colonnes clés (priorité sur les positions fixes)
        $colMap = self::COL;
        $this->autoDetectColumns($headers, $colMap);

        $this->info('Mapping image → col ' . $colMap['image'] . ' | prix → col ' . $colMap['prix'] . ' | nom → col ' . $colMap['nom']);

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $total   = count($lines) - 1;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach (array_slice($lines, 1) as $lineIndex => $line) {
            $bar->advance();

            $line = trim($line);
            if (empty($line)) { $skipped++; continue; }

            $row = str_getcsv($line);

            // Nom
            $nom = $this->col($row, $colMap['nom']);
            $productCode = $this->normalizeProductCode($this->col($row, $colMap['code']), $nom);
            // Nettoyer le code produit en fin de nom : "SAC DAME: AS_07002" → "SAC DAME"
            $nom = trim(preg_replace('/\s*:\s*[A-Z0-9_]{4,}$/', '', $nom));
            if (empty($nom)) { $skipped++; continue; }

            // Prix
            $prixRaw = $this->col($row, $colMap['prix']);
            $prix    = $this->parsePrice($prixRaw);
            if ($prix <= 0) { $skipped++; continue; }

            // Prix remisé depuis la colonne "Discounted Price"
            $discountedRaw   = $this->col($row, $colMap['prix_original']);
            $discountedPrice = $this->parsePrice($discountedRaw);
            $hasValidDiscount = $discountedPrice > 0 && $discountedPrice < $prix;
            $prixOriginal    = $hasValidDiscount ? $prix : null;

            // Catégorie : "En promo,Mode dame" → garder la vraie catégorie
            $catRaw = $this->col($row, $colMap['categorie']);
            $categorieName = $this->extractMainCategory($catRaw);

            // Images — essayer Image Principale d'abord, puis Image 1..20 comme galerie
            $imageUrls = $this->resolveImageUrls($row, $colMap['image']);
            $imageUrl  = $imageUrls[0] ?? null;

            // Description
            $description = $this->col($row, $colMap['description']);

            // Stock
            $stockRaw = $this->col($row, $colMap['stock']);
            $stock = (int) preg_replace('/[^\d]/', '', $stockRaw);
            if ($stock === 0) $stock = 99; // stock par défaut si absent ou non numérique dans le CSV

            // Disponibilité
            $availability = $this->normalizeAvailability($this->col($row, $colMap['availability']));
            $actif        = $availability !== 'hide';

            // Catégorie en DB
            $category = Category::findOrCreateByName($categorieName);

            $sourceRow   = 'row_' . ($lineIndex + 2);
            $displayPrice = $hasValidDiscount ? $discountedPrice : $prix;
            $productData = [
                'nom'           => ucwords(strtolower($nom)),
                'prix'          => $displayPrice,
                'prix_original' => $prixOriginal,
                'discounted_price' => $hasValidDiscount ? $discountedPrice : null,
                'moq'           => $this->col($row, $colMap['moq']) ?: null,
                'description'   => $description ?: null,
                'marque'        => $this->col($row, $colMap['marque']) ?: null,
                'size'          => $this->col($row, $colMap['taille']) ?: null,
                'color'         => $this->col($row, $colMap['couleur']) ?: null,
                'model'         => $this->col($row, $colMap['modele']) ?: null,
                'image_url'     => $imageUrl ?: null,
                'image_urls'    => $imageUrls ?: null,
                'category_id'   => $category->id,
                'categorie'     => $categorieName,
                'product_code'  => $productCode,
                'stock'         => $availability === 'out of stock' ? 0 : $stock,
                'availability'  => $availability,
                'actif'         => $actif,
            ];

            /** @var \App\Models\Product|null $existing */
            $existing = Product::firstWhere('source_row', $sourceRow);

            if ($existing !== null) {
                $existing->fill($productData)->save();
                $product = $existing;
                $updated++;
            } else {
                $product = Product::create(array_merge($productData, ['source_row' => $sourceRow]));
                $created++;
            }

            // Pousse aussi le prix à jour vers Firestore (collection "products" lue par l'app Android),
            // sur l'id MySQL du produit pour que les ré-synchronisations mettent à jour le même document.
            if ($this->option('firebase')) {
                try {
                    $firebase->createOrUpdateProduct([
                        'id'            => (string) $product->id,
                        'nom'           => $productData['nom'],
                        'prix'          => $productData['prix'],
                        'prix_original' => $productData['prix_original'],
                        'discounted_price' => $productData['discounted_price'],
                        'categorie'     => $productData['categorie'],
                        'product_code'  => $productData['product_code'],
                        'description'   => $productData['description'],
                        'moq'           => $productData['moq'],
                        'marque'        => $productData['marque'],
                        'size'          => $productData['size'],
                        'color'         => $productData['color'],
                        'model'         => $productData['model'],
                        'stock'         => $productData['stock'],
                        'availability'  => $productData['availability'],
                        'actif'         => $productData['actif'],
                        'image'         => $productData['image_url'],
                        'images'        => $productData['image_urls'],
                    ]);
                } catch (\Exception $e) {
                    Log::warning('SyncProducts — échec sync Firestore pour ' . $sourceRow . ' : ' . $e->getMessage());
                }
            }

            // Pré-cache l'image produit pour un affichage instantané côté client
            // (évite que le premier visiteur paie le coût du téléchargement à froid).
            if ($this->option('cache-images') && $imageUrl && !$imageCache->isCached($imageUrl)) {
                try {
                    $imageCache->cache($imageUrl);
                } catch (\Exception $e) {
                    Log::warning('SyncProducts — échec pré-cache image pour ' . $sourceRow . ' : ' . $e->getMessage());
                }
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Synchronisation terminée : {$created} créés, {$updated} mis à jour, {$skipped} ignorés.");

        return self::SUCCESS;
    }

    private function autoDetectColumns(array $headers, array &$colMap): void
    {
        $aliases = [
            'nom'           => ['name', 'nom', 'produit', 'product', 'titre', 'title'],
            'prix'          => ['price', 'prix', 'tarif', 'cost', 'cout'],
            'prix_original' => ['discounted price', 'discounted_price', 'prix_original', 'old_price', 'prix_barre'],
            'moq'           => ['moq', 'minimum order', 'minimum order quantity', 'quantite minimum', 'quantité minimum'],
            'description'   => ['description', 'desc', 'detail', 'details'],
            'marque'        => ['marque', 'brand'],
            'taille'        => ['size', 'taille'],
            'couleur'       => ['color', 'couleur', 'colour'],
            'modele'        => ['model', 'modele', 'modèle'],
            'categorie'     => ['category', 'categorie', 'cat', 'type'],
            'stock'         => ['stock', 'quantity', 'quantite', 'qty'],
            'availability'  => ['availability', 'disponibilite', 'available', 'status'],
            'image'         => ['image principale', 'image_principale', 'main image', 'image', 'image_url', 'photo', 'img'],
        ];

        foreach ($aliases as $key => $alts) {
            foreach ($headers as $idx => $header) {
                if (in_array(trim($header), $alts, true)) {
                    $colMap[$key] = $idx;
                    break;
                }
            }
        }
    }

    private function col(array $row, int $index): string
    {
        return isset($row[$index]) ? trim($row[$index]) : '';
    }

    private function normalizeProductCode(string $rawCode, string $rawName): ?string
    {
        $code = trim($rawCode);

        if ($code === '' && preg_match('/:\s*([A-Z0-9_]{4,})\s*$/i', $rawName, $matches)) {
            $code = $matches[1];
        }

        return $code !== '' ? strtoupper($code) : null;
    }

    /**
     * Cherche la première URL d'image valide dans la ligne CSV.
     * Commence par "Image Principale" (col 14), puis essaie Image 1..20 (col 15..34).
     */
    private function resolveImageUrls(array $row, int $mainImageCol): array
    {
        // Tester la colonne principale ET les colonnes Image 1 à Image 20
        $colsToTry = array_unique(array_merge([$mainImageCol], range(15, 34)));
        $urls = [];

        foreach ($colsToTry as $col) {
            $url = $this->normalizeImageUrl($this->col($row, $col));
            if ($url !== null) {
                $urls[] = $url;
            }
        }

        return array_values(array_unique($urls));
    }

    private function normalizeImageUrl(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        if (preg_match('/IMAGE\s*\(\s*"([^"]+)"/i', $raw, $matches)) {
            $raw = $matches[1];
        } elseif (preg_match('/https?:\/\/[^\s,"\')]+/i', $raw, $matches)) {
            $raw = $matches[0];
        }

        $raw = trim($raw, " \t\n\r\0\x0B\"'");

        if (!filter_var($raw, FILTER_VALIDATE_URL)) {
            return null;
        }

        if (preg_match('~/file/d/([^/]+)~', $raw, $matches) || preg_match('~[?&]id=([^&]+)~', $raw, $matches)) {
            return 'https://drive.google.com/thumbnail?id=' . $matches[1] . '&sz=w1200';
        }

        return $raw;
    }

    private function normalizeAvailability(string $raw): string
    {
        $value = strtolower(trim($raw));

        return match ($value) {
            'hide', 'hidden', 'no', 'non', 'false', '0' => 'hide',
            'out of stock', 'out-of-stock', 'out_of_stock', 'rupture', 'rupture de stock' => 'out of stock',
            default => 'show',
        };
    }

    private function parsePrice(string $raw): float
    {
        $raw = trim($raw);
        if (empty($raw)) return 0.0;

        // Supprimer les symboles monétaires et espaces
        $cleaned = preg_replace('/[^\d.,]/', '', $raw);
        if (empty($cleaned)) return 0.0;

        // Cas : "5,300" (virgule comme séparateur de milliers → 5300)
        // Cas : "1,234.56" (virgule comme milliers, point comme décimal)
        // Cas : "5.30" (point comme décimal)
        // Cas : "5,30" (virgule comme décimal européen)

        if (strpos($cleaned, '.') !== false && strpos($cleaned, ',') !== false) {
            // Les deux présents : supposer que , = milliers, . = décimal → "1,234.56"
            $cleaned = str_replace(',', '', $cleaned);
        } elseif (strpos($cleaned, ',') !== false) {
            $parts = explode(',', $cleaned);
            $afterComma = end($parts);
            if (strlen($afterComma) === 3 && count($parts) >= 2) {
                // "5,300" ou "1,234,567" → virgule = milliers
                $cleaned = str_replace(',', '', $cleaned);
            } else {
                // "5,30" ou "5,3" → virgule = décimal
                $cleaned = str_replace(',', '.', $cleaned);
            }
        }

        return (float) $cleaned;
    }

    private function extractMainCategory(string $raw): string
    {
        if (empty($raw)) return 'Général';

        $parts = array_map('trim', explode(',', $raw));
        $parts = array_filter($parts, fn($p) => !empty($p));

        // Mots à ignorer (tags promotionnels)
        $skipWords = ['en promo', 'promo', 'promotion', 'nouveauté', 'nouveaute', 'bestseller', 'best seller', 'flash'];

        foreach ($parts as $part) {
            if (!in_array(strtolower($part), $skipWords)) {
                return $part;
            }
        }

        return $parts[0] ?? 'Général';
    }
}
