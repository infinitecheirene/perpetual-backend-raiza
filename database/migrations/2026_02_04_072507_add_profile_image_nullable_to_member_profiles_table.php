<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('member_profiles', function (Blueprint $table) {
            $table->string(column: 'profile_image')->nullable()->index()->after('member_since');;
        });
    }

    public function down()
    {
        Schema::table('member_profiles', function (Blueprint $table) {
            $table->dropColumn('profile_image');
        });
    }
};
