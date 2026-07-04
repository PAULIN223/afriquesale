<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('statut')->default('en_attente'); // en_attente|confirmee|expediee|livree|annulee
            $table->decimal('total', 10, 2);
            $table->decimal('frais_livraison', 10, 2)->default(0);
            $table->json('adresse_livraison');
            $table->text('notes')->nullable();
            $table->string('numero_commande')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
