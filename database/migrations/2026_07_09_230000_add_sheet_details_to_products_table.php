<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('moq')->nullable()->after('prix_original');
            $table->decimal('discounted_price', 10, 2)->nullable()->after('moq');
            $table->string('marque')->nullable()->after('description');
            $table->string('size')->nullable()->after('marque');
            $table->string('color')->nullable()->after('size');
            $table->string('model')->nullable()->after('color');
            $table->string('availability')->default('show')->after('stock');
            $table->json('image_urls')->nullable()->after('image_url');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'moq',
                'discounted_price',
                'marque',
                'size',
                'color',
                'model',
                'availability',
                'image_urls',
            ]);
        });
    }
};
