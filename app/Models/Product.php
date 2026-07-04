<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    protected $fillable = [
        'nom', 'description', 'prix', 'prix_original',
        'image_url', 'image_cache_path', 'category_id',
        'categorie', 'stock', 'actif', 'source_row',
    ];

    protected $casts = [
        'prix'          => 'float',
        'prix_original' => 'float',
        'stock'         => 'integer',
        'actif'         => 'boolean',
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

    public function getDiscountPercentAttribute(): ?int
    {
        if ($this->prix_original && $this->prix_original > $this->prix) {
            return (int) round((($this->prix_original - $this->prix) / $this->prix_original) * 100);
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
