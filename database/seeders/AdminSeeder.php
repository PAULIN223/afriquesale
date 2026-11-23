<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL');
        $adminPassword = env('ADMIN_PASSWORD');

        if (!filled($adminEmail) || !filled($adminPassword)) {
            $this->command->warn('AdminSeeder ignoré : définissez ADMIN_EMAIL et ADMIN_PASSWORD dans l’environnement.');

            return;
        }

        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name'              => 'AfricSales Admin',
                'password'          => Hash::make($adminPassword),
                'role'              => 'admin',
                'statut'            => 'active',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Compte administrateur initialisé.');
    }
}
