<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['nom' => 'Électronique',        'icone' => 'smartphone'],
            ['nom' => 'Mode & Vêtements',     'icone' => 'shirt'],
            ['nom' => 'Maison & Décoration',  'icone' => 'home'],
            ['nom' => 'Beauté & Santé',       'icone' => 'heart'],
            ['nom' => 'Alimentation',         'icone' => 'shopping-basket'],
            ['nom' => 'Sport & Loisirs',      'icone' => 'activity'],
            ['nom' => 'Livres & Culture',     'icone' => 'book-open'],
            ['nom' => 'Auto & Moto',          'icone' => 'car'],
            ['nom' => 'Informatique',         'icone' => 'laptop'],
            ['nom' => 'Jouets & Enfants',     'icone' => 'gift'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['slug' => Str::slug($cat['nom'])],
                ['nom' => $cat['nom'], 'icone' => $cat['icone'], 'actif' => true]
            );
        }

        $this->command->info(count($categories) . ' catégories créées.');
    }
}
