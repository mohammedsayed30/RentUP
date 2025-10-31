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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key to the users table
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            
            $table->string('code')->unique();
            
            
            $table->decimal('amount_decimal', 10, 2); 
            
            
            $table->enum('status', ['placed', 'processing', 'shipped', 'delivered', 'cancelled'])
                  ->default('placed');
            
            
            $table->timestamp('placed_at')->useCurrent();
            
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
