<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'sale@admin.com'],
            [
                'name'              => 'AfricSales Admin',
                'password'          => Hash::make('salemaket'),
                'role'              => 'admin',
                'statut'            => 'active',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin créé : sale@admin.com / salemaket');
    }
}
