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
            $table->text('certification_details')->nullable()->after('certificate_date');
            $table->string('school_name')->nullable()->after('certification_details');
            $table->string('address', 500)->nullable()->after('school_name');
            $table->string('logo_url')->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legitimacy_requests', function (Blueprint $table) {
            $table->dropColumn([
                'certification_details',
                'school_name',
                'address',
                'logo_url'
            ]);
        });
    }
};