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
        Schema::create('pending_robux_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups', 'group_id')->onDelete('cascade');
            $table->integer('amount');
            $table->datetime('arrival_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_robux_tables');
    }
};
