<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('legitimacy_requests', function (Blueprint $table) {
            // Rename logo_url → logo_url1
            $table->renameColumn('logo_url', 'logo_url1');

            // Add new logo_url2
            $table->string('logo_url2')->nullable()->after('logo_url1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legitimacy_requests', function (Blueprint $table) {
            // Drop logo_url2
            $table->dropColumn('logo_url2');

            // Rename logo_url1 back to logo_url
            $table->renameColumn('logo_url1', 'logo_url');
        });
    }
};
