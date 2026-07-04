<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['nom', 'slug', 'icone', 'description', 'actif'];

    protected $casts = ['actif' => 'boolean'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public static function findOrCreateByName(string $name): self
    {
        $slug = \Str::slug($name);
        return self::firstOrCreate(
            ['slug' => $slug],
            ['nom' => $name, 'slug' => $slug, 'actif' => true]
        );
    }
}
