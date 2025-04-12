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
        Schema::create('group_roblox_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups', 'group_id')->onDelete('cascade');
            $table->foreignId('roblox_user_id')->constrained('roblox_users', 'roblox_user_id')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_has_users');
    }
};
