<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['user_id', 'product_id', 'quantite', 'color', 'color_image_url'];

    protected $casts = ['quantite' => 'integer'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalAttribute(): float
    {
        $unitPrice = $this->product ? ($this->product->display_price ?? $this->product->prix ?? 0) : 0;

        return $unitPrice * $this->quantite;
    }
}
