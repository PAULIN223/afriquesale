<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_color',
        'product_image_url',
        'quantite',
        'prix_unitaire',
    ];

    protected $casts = [
        'quantite'      => 'integer',
        'prix_unitaire' => 'float',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->prix_unitaire * $this->quantite;
    }

    public function getDisplayNameAttribute(): string
    {
        $name = $this->product_name ?: $this->product?->nom ?: 'Produit';
        $color = $this->product_color ?: $this->product?->color;

        return trim($name . (filled($color) ? ' - ' . $color : ''));
    }

    public function getImageSrcAttribute(): string
    {
        $imageUrl = $this->product_image_url ?: $this->product?->image_url;

        return $imageUrl
            ? '/image-proxy?url=' . urlencode($imageUrl)
            : '/images/placeholder.png';
    }
}
