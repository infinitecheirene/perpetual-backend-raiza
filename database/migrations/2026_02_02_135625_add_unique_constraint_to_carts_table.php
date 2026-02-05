<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            // Add unique constraint if it doesn't exist
            $table->unique(['user_id', 'product_id'], 'carts_user_product_unique');
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropUnique('carts_user_product_unique');
        });
    }
};