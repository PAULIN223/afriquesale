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
        'categorie', 'stock', 'availability', 'actif', 'source_row',
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
}
