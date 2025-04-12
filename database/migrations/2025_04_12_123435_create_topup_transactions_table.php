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
        Schema::create('topup_transactions', function (Blueprint $table) {
            $table->id();
            $table->text('ref')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 8, 2);
            $table->enum('method', ['tw','pp','cc']);
            $table->enum('status', ['pending','success','failed']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topup_transactions');
    }
};
