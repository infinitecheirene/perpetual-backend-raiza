<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('objectives', function (Blueprint $table) {
            $table->id();
            $table->string('objectives_header')->nullable();
            $table->string('objectives_title')->nullable();
            $table->text('objectives_description')->nullable();
            $table->json('objectives_card_title')->nullable();
            $table->json('objectives_card_content')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('objectives');
    }
};