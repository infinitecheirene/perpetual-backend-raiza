<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Only create if it doesn't exist
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();

                // Core product info
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('category')->nullable();

                // Merchandising details
                $table->decimal('price', 10, 2);
                $table->unsignedInteger('stock')->default(0);

                // Media
                $table->string('image_url')->nullable();

                // Fraternity association - NULLABLE and no cascade
                $table->unsignedBigInteger('fraternity_id')->nullable();
                // Remove the foreign key constraint or make it nullable without cascade
                // This allows products to exist independently

                // Transparency & tracking
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};