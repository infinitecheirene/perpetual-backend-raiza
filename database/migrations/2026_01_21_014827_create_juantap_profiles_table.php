<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('juantap_profiles', function (Blueprint $table) {
            $table->id(); // ✅ REQUIRED PRIMARY KEY

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('profile_url')->nullable();
            $table->text('qr_code')->nullable(); // base64 or URL
            $table->enum('status', ['active', 'inactive'])->default('inactive');
            $table->enum('subscription', ['silver', 'gold', 'black'])->default('silver');
            $table->timestamps();

            $table->unique('user_id'); // one profile per user
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('juantap_profiles');
    }
};
