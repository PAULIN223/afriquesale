<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    protected $fillable = [
        'nom', 'description', 'prix', 'prix_original', 'discounted_price',
        'moq', 'marque', 'size', 'color', 'model',
        'image_url', 'image_urls', 'image_cache_path', 'category_id',
        'categorie', 'product_code', 'stock', 'availability', 'actif', 'source_row',
    ];

    protected $casts = [
        'prix'             => 'float',
        'prix_original'    => 'float',
        'discounted_price' => 'float',
        'image_urls'       => 'array',
        'stock'            => 'integer',
        'actif'            => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(Product::class, 'product_code', 'product_code')
            ->where('actif', true)
            ->whereNotNull('product_code')
            ->where('product_code', '!=', '');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function getImageSrcAttribute(): string
    {
        if ($this->image_url) {
            return '/image-proxy?url=' . urlencode($this->image_url);
        }
        return '/images/placeholder.png';
    }

    public function getGalleryImagesAttribute(): array
    {
        $images = $this->image_urls ?: [];

        if ($this->image_url) {
            array_unshift($images, $this->image_url);
        }

        return array_values(array_unique(array_filter($images)));
    }

    public function getDisplayPriceAttribute(): float
    {
        return $this->discounted_price ?: $this->prix;
    }

    public function getDisplayNameAttribute(): string
    {
        return trim($this->nom . (filled($this->color) ? ' - ' . $this->color : ''));
    }

    public function getIsOutOfStockAttribute(): bool
    {
        return strtolower((string) $this->availability) === 'out of stock' || $this->stock <= 0;
    }

    public function getIsPurchasableAttribute(): bool
    {
        return $this->actif && !$this->is_out_of_stock;
    }

    public function getDiscountPercentAttribute(): ?int
    {
        if ($this->prix > 0 && $this->discounted_price && $this->discounted_price < $this->prix) {
            return (int) round((($this->prix - $this->discounted_price) / $this->prix) * 100);
        }

        if ($this->prix_original && $this->prix_original > $this->display_price) {
            return (int) round((($this->prix_original - $this->display_price) / $this->prix_original) * 100);
        }
        return null;
    }

    public function scopeActif(Builder $query): Builder
    {
        return $query->where('actif', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeInVariantGroup(Builder $query, Product $product): Builder
    {
        if (filled($product->product_code)) {
            return $query->where('product_code', $product->product_code);
        }

        $sourceRowNumber = $product->source_row_number;

        if ($sourceRowNumber !== null) {
            $bounds = $product->legacyVariantRowBounds();
            $rows = array_map(
                fn (int $row) => 'row_' . $row,
                range($bounds['start'], $bounds['end'])
            );

            return $query
                ->where('nom', $product->nom)
                ->where('category_id', $product->category_id)
                ->whereIn('source_row', $rows);
        }

        return $query
            ->where('nom', $product->nom)
            ->where('category_id', $product->category_id);
    }

    public function getSourceRowNumberAttribute(): ?int
    {
        return preg_match('/^row_(\d+)$/', (string) $this->source_row, $matches)
            ? (int) $matches[1]
            : null;
    }

    public function legacyVariantRowBounds(): array
    {
        $sourceRowNumber = $this->source_row_number;

        if ($sourceRowNumber === null) {
            return ['start' => 0, 'end' => 0];
        }

        $siblings = self::query()
            ->where('nom', $this->nom)
            ->where('category_id', $this->category_id)
            ->get(['source_row', 'color'])
            ->map(function (Product $product) {
                return [
                    'row' => $product->source_row_number,
                    'color' => $product->color,
                ];
            })
            ->filter(fn (array $product) => $product['row'] !== null)
            ->sortBy('row')
            ->values();

        $start = $sourceRowNumber;

        if (filled($this->color)) {
            $previousBase = $siblings
                ->filter(fn (array $product) => $product['row'] < $sourceRowNumber && !filled($product['color']))
                ->sortByDesc('row')
                ->first();

            if ($previousBase) {
                $start = $previousBase['row'];
            }
        }

        $nextBase = $siblings
            ->filter(fn (array $product) => $product['row'] > $start && !filled($product['color']))
            ->first();

        $end = $nextBase ? $nextBase['row'] - 1 : $start + 50;

        return ['start' => $start, 'end' => max($start, min($end, $start + 50))];
    }
}
