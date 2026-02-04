<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First, clear any truncated data
        DB::table('about_section')
            ->whereNotNull('mission_and_vision_description')
            ->update(['mission_and_vision_description' => DB::raw("LEFT(mission_and_vision_description, 255)")]);

        // Now change only the column that exists
        Schema::table('about_section', function (Blueprint $table) {
            $table->text('mission_and_vision_description')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('about_section', function (Blueprint $table) {
            $table->string('mission_and_vision_description', 255)->nullable()->change();
        });
    }
};