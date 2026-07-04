<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'shop_name'       => 'AfricSales',
            'shop_email'      => 'contact@africsales.com',
            'shop_phone'      => '+225 07 00 00 00 00',
            'devise'          => 'FCFA',
            'frais_livraison' => '2000',
            'shop_address'    => 'Abidjan, Côte d\'Ivoire',
        ];

        foreach ($defaults as $key => $value) {
            Setting::updateOrCreate(['cle' => $key], ['valeur' => $value]);
        }

        $this->command->info('Paramètres par défaut configurés.');
    }
}
