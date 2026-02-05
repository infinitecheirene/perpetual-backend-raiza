<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // This table already has subtotal column, so this is just a marker migration
        });
    }

    public function down(): void
    {
        // Nothing to roll back
    }
};