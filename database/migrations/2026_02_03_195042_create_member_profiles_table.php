<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('alias')->nullable();
            $table->string('tenure')->nullable();
            $table->date('member_since')->nullable();
            $table->text('projects')->nullable();
            $table->string('status')->nullable();
            $table->string('positions')->nullable();
            $table->text('achievements')->nullable();
            $table->boolean('juantap_nfc')->default(0)
                ->comment('1 = JuanTap profile added, 0 = not added');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_profiles');
    }
};