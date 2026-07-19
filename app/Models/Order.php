<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'statut', 'total', 'frais_livraison',
        'adresse_livraison', 'notes', 'numero_commande', 'preuve_paiement',
    ];

    protected $casts = [
        'adresse_livraison' => 'array',
        'total'             => 'float',
        'frais_livraison'   => 'float',
    ];

    const STATUTS = [
        'en_attente' => ['label' => 'En attente',  'color' => 'yellow'],
        'confirmee'  => ['label' => 'Confirmée',   'color' => 'blue'],
        'expediee'   => ['label' => 'Expédiée',    'color' => 'orange'],
        'livree'     => ['label' => 'Livrée',      'color' => 'green'],
        'annulee'    => ['label' => 'Annulée',     'color' => 'red'],
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatutLabelAttribute(): string
    {
        return self::STATUTS[$this->statut]['label'] ?? $this->statut;
    }

    public function getStatutColorAttribute(): string
    {
        return self::STATUTS[$this->statut]['color'] ?? 'gray';
    }

    public static function generateNumero(): string
    {
        return 'AS-' . strtoupper(substr(md5(uniqid()), 0, 8));
    }
}
