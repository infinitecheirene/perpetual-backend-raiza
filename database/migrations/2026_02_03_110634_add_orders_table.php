<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Buyer reference
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            // Unique order code for tracking
            $table->string('order_code')->default('');

            // Order details
            $table->decimal('total_price', 10, 2)->default(0);
            $table->string('status')->default('pending'); // pending, confirmed, processing, shipped, completed, cancelled
            $table->string('payment_method')->nullable();
            
            // Proof of payment
            $table->string('proof_of_payment')->nullable(); // path to uploaded proof of payment image
            
            // Additional notes from customer
            $table->text('notes')->nullable();
            
            $table->timestamp('ordered_at')->useCurrent();

            // Transparency & tracking
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
